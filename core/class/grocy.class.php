<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
include_file('core', 'grocy.api', 'class', 'grocy');
include_file('core', 'grocy.jeedom', 'class', 'grocy');

class grocy extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */
    
	public static function cron() {
		if ( config::byKey( 'scan_mode', 'grocy' ) == 1 ) {
            if( config::byKey( 'scan_type', 'grocy' ) != 'JGROCY-A' ) {
                $diff = time() - config::byKey( 'scan_latest_timestamp', 'grocy' );
                log::add('grocy','debug', 'cron > diff: ' . $diff );
                $myStopTime = config::byKey( 'time_mode', 'grocy' );
                log::add('grocy','debug', 'cron > diff: ' . $diff . ' myStopTime ' . $myStopTime );
                if( $diff >= $myStopTime ) {
                    log::add('grocy','debug', 'Je stop le mode en cours.' );
                    self::stopScanMode();
                }
            }
        }
    }
    
	public static function cron15() {
        log::add('grocy','debug','Mise à jour des infos');
        
        self::syncAllProductsStock();

    }

    public static function cronDaily() {
        grocy::buildGrocyCache();
    }
    
    public static function grocyExtend( $_eqLogic, $do = null, $_data = null ) {

        if( is_null( $do ) ) {
            log::add('grocy','debug','grocyExtend > do: '. $do );
            return false;
        }

        log::add('grocy','debug','grocyExtend > grocy: '. print_r( $_eqLogic, true ) );

        $product_id = is_null( $_data ) ? (int)$_eqLogic->getConfiguration('product_id') : $_data['product_id'];
        $barcode    = is_null( $_data ) ? $_eqLogic->getConfiguration('barcode') : $_data['barcode'];
        $eqlogic_id = is_null( $_data ) ? $_eqLogic->getId() : $_data['eqlogic_id'];

        log::add('grocy','debug','grocyExtend > product_id: '. print_r( $product_id, true ) );

        try {

            $grocy = new grocy_extend();
            utils::a2o($grocy, array(
                'product_id' => $product_id,
                'barcode'    => $barcode,
                'eqlogic_id' => $eqlogic_id
                )
            );
    
            log::add('grocy','debug','grocyExtend > grocy: '. print_r( $grocy, true ) );
            log::add('grocy','debug','grocyExtend > do: '. $do );
    
            if( $do == 'save' ) {
                return $grocy->save();
            } else {
                return $grocy->remove();
            }

        } catch (\Throwable $th) {
            log::add('grocy','error','grocyExtend > Throwable: '. print_r( $th, true ) );
        }
    }

	public static function checkGrocyInstance() {

        $url    = config::byKey('url','grocy');
        $apikey = config::byKey('apikey','grocy');

        $http   = new grocyAPI($url, $apikey);
        $result = $http->checkInstance();
        log::add('grocy','debug','checkGrocyInstance: ' . print_r( $result, true ) );
        return $result;
    }
    
    public static function startScanMode( $_stateMode, $_stateType ) {

        log::add('grocy','debug','Demande de passage en mode: ' . $_stateType );
        if ( config::byKey('scan_mode', 'grocy') == 0 ) {

            return self::doToStartScanMode( $_stateMode, $_stateType );

        } else {

            if( self::checkModeScan( $_stateMode, $_stateType ) ) {

                return self::doToStartScanMode( $_stateMode, $_stateType );
            }
 
            return false;
        }
    }

    public static function checkModeScan( $_stateMode, $_stateType ) {

        $currentScanType = config::byKey( 'scan_type', 'grocy' );

        if( $_stateType == 'JGROCY-A' || $currentScanType == 'JGROCY-A' ) {

            log::add('grocy','info', 'Vous ne pouvez pas basculer du mode ' . $currentScanType . ' vers le mode ' . $_stateType );
            return false;
        }

        if( $scan_type == $_stateType ) {

            log::add('grocy','info', 'Vous êtes déjà dans le mode de scan ' . $_stateType );
            return false;                
        }

        $scanModeType = config::byKey( 'scan_mode_type', 'grocy' );
        unset( $scanModeType[0] );

        if( in_array( $_stateType, $scanModeType ) ) { 

            log::add('grocy','debug', 'Validation de switcher du mode ' . $currentScanType . ' au mode ' . $_stateType );
            return true;
        }

        log::add('grocy','info', 'Vous ne pouvez pas basculer du mode ' . $currentScanType . ' vers le mode ' . $_stateType );
        return false;
    }

    public static function stopScanMode() {

        config::save('scan_mode'            , 0, 'grocy'); 
        config::save('scan_type'            , '', 'grocy');
        config::save('scan_products'        , '', 'grocy');
        config::save('scan_latest_timestamp', '', 'grocy');  
        log::add('grocy','debug','Désactivation du mode scan' );
        return true;
    }

    public static function scanProduct( $_barcode ) {

        $today = date("Y-m-d");

        $grocy = utils::o2a( grocy_extend::byBarcode( $_barcode ) );
        log::add('grocy','debug','scanProduct > grocy: ' . print_r( $grocy, true )  );

        if( isset( $grocy['eqlogic_id'] ) ) {

            log::add('grocy','debug','scanProduct > eqlogic_id: '. $grocy['eqlogic_id']);

            $eqLogic = eqLogic::byId( $grocy['eqlogic_id'] );

            if( $eqLogic->getConfiguration('tmp') ) {

                $op = 1;
                log::add('grocy','debug','Il s\'agit d\'un produit temporaire' );

            } else {

                if ( config::byKey( 'scan_mode', 'grocy' ) == 0 ) {

                    self::startScanMode( 'scan', 'JGROCY-C');
                }
    
                self::setTimestamp(); 
                $op = config::byKey('scan_type', 'grocy') == 'JGROCY-A' ? 1 : 0;
            }

            return self::newStock( $eqLogic, $op, 1 );

        } else {

            $result = json_decode( self::searchBarcodeInOpenFoodFactsDB( $_barcode ), true );

            log::add('grocy','debug','scanProduct > searchBarcodeInOpenFoodFactsDB: ' . print_r( $result, true )  );

            if( $result['status'] == 1 ) {

                if( isset( $result['product']['product_name'] ) ) {

                    $product       = $result['product'];

                    $openfoodfacts = array(
                        'code'             => $_barcode,
                        'product_name'     => $product['product_name'],
                        'generic_name_fr'  => $product['generic_name_fr'],
                        'ingredients_text' => $product['ingredients_text'],
                        'nutriments'       => $product['nutriments'],
                    );

                    $logicalId = 'grocy-'.$_barcode;
                    $eqLogic = new grocy();
                    $eqLogic->setName( $product['product_name'] );
                    $eqLogic->setEqType_name( 'grocy' );
                    $eqLogic->setLogicalId( $logicalId );
                    $eqLogic->setConfiguration('id_product', '0' );
                    $eqLogic->setConfiguration('barcode', $_barcode );
                    $eqLogic->setConfiguration('id_stock', '0' );
                    $eqLogic->setConfiguration('tmp', '1' );
                    $eqLogic->setConfiguration('openfoodfacts', $openfoodfacts );
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setIsVisible(0);
                    $eqLogic->save();
    
                    self::createCmd( $eqLogic->getId(), 'add1', 'action', 'Ajouter', 'other', '1', '<i class="fas fa-plus"></i>' );
                    self::createCmd( $eqLogic->getId(), 'minus1', 'action', 'Enlever', 'other', '1', '<i class="fas fa-minus"></i>');                           
                    self::createCmd( $eqLogic->getId(), 'stock', 'stock', 'Stock actuel', 'numeric', '1', 'line' );
    
                    self::grocyExtend( $eqLogic, 'save' );

                    event::add('grocy::unknowbarcodequeue', array(
                        'action' => 'add',
                        'data'   => array( 
                            'eqlogicid'     => $eqLogic->getId(),
                            'openfoodfacts' => $openfoodfacts,
                            'quantity'      => 1
                        ),
                        'message' => __('Produit crèe avec succès : ', __FILE__) . $product['product_name'],
                    ));                       
    
                    log::add('grocy_'.$today,'info','Création du produit ' . $product['product_name'] . ' dans Jeedom' );
    
                    return self::newStock( $eqLogic, 1, 1 );
                } else {
                    log::add('grocy_'.$today,'info','Le produit '.$_barcode.' est imcomplet dans OpenFoodFacts! Merci de le créer à la main dans Grocy et synchroniser Jeedom' );
                    return false;
                }

            } else {
                log::add('grocy_'.$today,'info','Le produit '.$_barcode.' est inconnu dans OpenFoodFacts! Merci de le créer à la main dans Grocy et synchroniser Jeedom' );
                return false;
            }  
        }

        log::add( 'grocy_'.$today,'info','Methode scanProduct: Erreur inconnu scan > barcode: ' . $_barcode );
        return false;
    }

    public static function syncGrocy() {
        if( self::createLocationsInJeedom() ) {
            if( self::createProductsInJeedom() ) {
                return self::syncAllProductsStock();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function syncAllProductsStock() {
        
        $url             = config::byKey('url','grocy');
        $apikey          = config::byKey('apikey','grocy');

        $http            = new grocyAPI($url, $apikey);
        $resultProductsStock = $http->getAllProductsStock();

        $setError = false;
        if( is_json( $resultProductsStock ) ) {

            $productsStock = json_decode( $resultProductsStock, true );

            foreach ( $productsStock as $productStock ) {
                
                $searchEqLogic = eqLogic::byTypeAndSearhConfiguration('grocy','"product_id":"'.$productStock['product_id'].'"');
                
                if( count( $searchEqLogic ) > 0 ) {

                    $eqLogic = $searchEqLogic[0];      
    
                    if ( is_object( $eqLogic ) ) {
    
                        $currentStockCmd = grocyCmd::byEqLogicIdAndLogicalId( $eqLogic->getId(), 'stock' );
    
                        if( is_object($currentStockCmd) ) {
    
                            $currentStockCmd->event( $productStock['amount'] );
    
                        } else {
                            $setError = true;
                            log::add('grocy','error','Commande stock introuvable pour le produit: ' . $eqLogic->getName()  );
                        }
    
                    } else {
                        $setError = true;
                        log::add('grocy','debug','syncAllProductsStock: ' . print_r( $searchEqLogic, true ) ); 
                        log::add('grocy','warning','impossible de trouver le produit ayant pour identifiant Grocy: ' . $productStock['product_id'] );
                    }  
                }
            }

            if( $setError == true ) 
                return false;
            else
                return true;

        } else {

            log::add('grocy','error','syncAllProductsStock: ' . print_r( $resultProductsStock, true ) );
            return false;
        }
    }

    public static function supAllProducts() {
        $grocy = plugin::byId('grocy');
        $eqLogics = eqLogic::byType( $grocy->getId() );

        foreach ( $eqLogics as $eqLogic ) {

            self::grocyExtend( $eqLogic, 'remove' );
            $eqLogic->remove();
        }
        config::save('tmp_queue', '' , 'grocy');
        return true;
    }

    public static function supAllInQueue() {

        $tmpQueue = config::byKey( 'tmp_queue', 'grocy');
        if( is_array( $tmpQueue ) ) {

            foreach( $tmpQueue as $eqLogicId ) {

                $eqLogicQueue = eqLogic::byId( $eqLogicId );

                if( is_object( $eqLogicQueue ) ) {

                    self::grocyExtend( $eqLogicQueue, 'remove' );
                    $eqLogicQueue->remove();

                } else {

                    log::add('grocy','debug','Le produit ayant l\'id ' . $_eqLogicId . ' n\'a pas été trouvé.');
                }
            }

            config::save('tmp_queue', '' , 'grocy');
            return true;
        }

        log::add('grocy','debug','Erreur inconnu dans supAllInQueue, voir les logs.');
        return false;
    }

    public static function supProductInQueue( $_eqLogicId ) {
        $tmpQueue = config::byKey( 'tmp_queue', 'grocy');
        if( is_array( $tmpQueue ) ) {

            if (($key = array_search( $_eqLogicId , $tmpQueue )) !== false) {

                $eqLogic = eqLogic::byId( $_eqLogicId );
                if( is_object( $eqLogic ) ) {

                    self::grocyExtend( $eqLogic, 'remove' );
                    $eqLogic->remove();

                    unset($tmpQueue[$key]);
                    config::save('tmp_queue', $tmpQueue , 'grocy');

                    $msg = __('Suppression du produit: ', __FILE__) . $_eqLogicId;

                    event::add('grocy::unknowbarcodequeue', array(
                        'action' => 'rm',
                        'data' => array( 
                            'eqlogicid' => $_eqLogicId 
                        ),
                        'message' => $msg,
                    ));  
        
                    log::add('grocy','debug', $msg );
                    return true;
                }

                log::add('grocy','debug','Le produit ayant l\'id ' . $_eqLogicId . ' n\'a pas été trouvé.');
                return false;
            }
            
            log::add('grocy','debug','Aucun produit dans la file d\'attente.');
            return false;
        }

        log::add('grocy','debug','Aucun produit dans la file d\'attente.');
        return false;
    }

    public static function getGrocyLocations() {

        $url             = config::byKey('url','grocy');
        $apikey          = config::byKey('apikey','grocy');

        $http            = new grocyAPI($url, $apikey);
        $resultLocations = $http->getLocations();

        if( is_json( $resultLocations ) ) {

            $locations = json_decode( $resultLocations, true );  

            if( isset( $locations['error_message'] ) ) {

                log::add('grocy','error','getGrocyLocations: ' . print_r( $resultLocations, true ) );
                return false;

            } else {

                return $locations;
            }
            
        }
    }

    public static function getGrocyQuantityUnits() {

        $url                 = config::byKey('url','grocy');
        $apikey              = config::byKey('apikey','grocy');

        $http                = new grocyAPI($url, $apikey);
        $resultQuantityUnits = $http->getQuantityUnits();

        if( is_json( $resultQuantityUnits ) ) {

            $quantityUnits = json_decode( $resultQuantityUnits, true );  

            if( isset( $quantityUnits['error_message'] ) ) {

                log::add('grocy','error','getGrocyQuantityUnits: ' . print_r( $resultQuantityUnits, true ) );
                return false;

            } else {

                return $quantityUnits;
            }
            
        }
    }

    public function getGrocyProductGroups() {
        $url                 = config::byKey('url','grocy');
        $apikey              = config::byKey('apikey','grocy');

        $http                = new grocyAPI($url, $apikey);
        $resultProductGroups = $http->getProductGroups();

        if( is_json( $resultProductGroups ) ) {

            $productGroups = json_decode( $resultProductGroups, true );  

            if( isset( $productGroups['error_message'] ) ) {

                log::add('grocy','error','getGrocyProductGroups: ' . print_r( $resultProductGroups, true ) );
                return false;

            } else {

                return $productGroups;
            }
        }
    }

    public static function createProductInGrocy( $_data ) {

        $data = array();
        parse_str( $_data, $data );

        $quantity  = $data['quantity'];
        $eqlogicid = $data['eqlogicid'];

        unset( $data['quantity'] );
        unset( $data['eqlogicid'] );

        if( $data['product_group_id'] == 0 ) {
            unset( $data['product_group_id'] );
        }

        $url                 = config::byKey('url','grocy');
        $apikey              = config::byKey('apikey','grocy');

        $http                = new grocyAPI($url, $apikey);
        $resultCreateProduct = $http->createProduct( $data );

        if( is_json( $resultCreateProduct ) ) {

            $product = json_decode( $resultCreateProduct, true );

            if( isset( $product['error_message'] ) ) {

                log::add('grocy','error', 'createProductInGrocy: ' . $product['error_message'] );
                return false;
            }

            $resultPurchaseProduct = $http->purchaseProduct( array( 'product_id' => $product['created_object_id'], 'amount' => $quantity ) );

            if( is_json( $resultPurchaseProduct ) ) {

                $purchaseProduct = json_decode( $resultPurchaseProduct, true );

                if( isset( $purchaseProduct['error_message'] ) ) {

                    log::add('grocy','error', 'createProductInGrocy: ' . $purchaseProduct['error_message'] );
                    return false;
                }
    
                if( self::supProductInQueue( $eqlogicid ) ) {
    
                    $url = $http->checkUrl( config::byKey('url', 'grocy') )  . 'product/' . $product['created_object_id'];
                    return array( 'url' => $url );
                }
            }
        }
    }

    public static function buildGrocyCache() {

        $cache = array();

        $cache['locations']     = grocy::getGrocyLocations();
        $cache['units']     = grocy::getGrocyQuantityUnits();
        $cache['productGroups'] = grocy::getGrocyProductGroups();

        cache::set('grocy::cache', $cache);
    }


    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        self::grocyExtend( $this, 'remove');        
    }

    public function postRemove() {
    }

    private function createLocationsInJeedom(){

        $url             = config::byKey('url','grocy');
        $apikey          = config::byKey('apikey','grocy');

        $http            = new grocyAPI($url, $apikey);
        $resultLocations = $http->getLocations();

        if( is_json( $resultLocations ) ) {

            $locations = json_decode( $resultLocations, true );

            if( isset( $locations['error_message'] ) ) {

                log::add('grocy','error','createLocationsInJeedom: ' . print_r( $resultLocations, true ) );
                return false;
            } else {

                foreach ( $locations as $location ) {
    
                    $jLocation = jeeObject::byName( $location['name'] );
                
                    if ( ! is_object( $jLocation ) ) {

                        $jLocation = new jeeObject();
                        $jLocation->setName( $location['name'] );
                        $jLocation->setIsVisible( 1 );
                        $jLocation->setFather_id( 0 );
                        $jLocation->setDisplay( 'icon','<i class="icon techno-refrigerator3"></i>' );
                        $jLocation->setConfiguration( 'location_id', $location['id'] );
                        $jLocation->save();

                        event::add('jeedom::alert', array(
                            'level' => 'warning',
                            'page' => 'grocy',
                            'message' => __('Emplacement crèe avec succès : ', __FILE__) . $location['name'],
                        ));       
                        
                        //sleep(1);
                    }        
                }

                return true;
            }
        } else {

            log::add('grocy','error','createLocationsInJeedom: ' . print_r( $resultLocations, true ) );
            return false;
        }
    }

    private function mapJeeObjectByLocationId( $location_id ) {

        $jObjects = jeeObject::searchConfiguration('location_id');

        $mapObjects = array();
        foreach ($jObjects as $jObject) {
            $mapObjects[$jObject->getConfiguration('location_id')] = $jObject->getId();
        }        

        return $mapObjects[$location_id];
    }

    private function createProductsInJeedom(){

        $url                 = config::byKey('url','grocy');
        $apikey              = config::byKey('apikey','grocy');

        $http                = new grocyAPI($url, $apikey);
        $resultProductGroups = $http->getProductGroups();

        if( is_json( $resultProductGroups ) ) {

            $productGroups = json_decode( $resultProductGroups, true );

            if( isset( $productGroups['error_message'] ) ) {

                log::add('grocy','error','resultProductGroupsJson: ' . print_r( $resultProducts, true ) );
                return false;
            } else {

                $pg = array();
                foreach ( $productGroups as $productGroup ) {
                    $pg[$productGroup['id']] = $productGroup['name'];
                }

                $resultProducts = $http->getProducts();

                if( is_json( $resultProducts ) ) {

                    $products = json_decode( $resultProducts, true );

                    if( isset( $products['error_message'] ) ) {
                        log::add('grocy','error','resultProductsJson: ' . print_r( $resultProducts, true ) );
                        return false;
                    } else {
                        foreach ( $products as $product ) {

                            $logicalId = 'grocy-'.$product['id'];

                            $jProduct = grocy::byLogicalId( $logicalId, 'grocy' );
                            if (!is_object($jProduct)) {
                    
                                $productBarcode = isset( $product['barcode'] ) ? $product['barcode'] : '';

                                $eqLogic = new grocy();
                                $eqLogic->setName( $product['name'] );
                                $eqLogic->setEqType_name( 'grocy' );
                                $eqLogic->setLogicalId( $logicalId );
                                $eqLogic->setObject_id( self::mapJeeObjectByLocationId( $product['location_id'] ) );
                                $eqLogic->setConfiguration('product_id', $product['id'] );
                                $eqLogic->setConfiguration('barcode', $productBarcode );
                                $eqLogic->setConfiguration('id_stock', $product['qu_id_stock'] );
                                // TODO $eqLogic->setConfiguration('image', $this->getImage());
                                $eqLogic->setIsEnable(1);
                                $eqLogic->setIsVisible('0');
                                $eqLogic->setDisplay('icon', '<i class="fas fa-minus"></i>');
                                $eqLogic->save();

                                self::createCmd( $eqLogic->getId(), 'add1', 'action', 'Ajouter', 'other', 0, '<i class="fas fa-plus"></i>' );
                                self::createCmd( $eqLogic->getId(), 'minus1', 'action', 'Enlever', 'other', 1, '<i class="fas fa-minus"></i>');                           
                                self::createCmd( $eqLogic->getId(), 'stock', 'stock', 'Stock actuel', 'numeric', 1, 'line' );
                                self::createCmd( $eqLogic->getId(), 'stock-terme', 'stock', 'Stock a terme', 'numeric', 0, 'line' );
                                self::createCmd( $eqLogic->getId(), 'stock-scan', 'stock', 'Quantité scanné', 'numeric', 0, 'line' );

                                if( ! empty( $productBarcode ) ) {

                                    $barcodes = explode( ',', $product['barcode'] );

                                    foreach ( $barcodes as $barcode ) {
        
                                        self::grocyExtend( $eqLogic, 'save', array(
                                            'product_id' => $product['id'],
                                            'barcode'    => $barcode,
                                            'eqlogic_id' => $eqLogic->getId()
                                            ) 
                                        );
                                    }
                                }

                                event::add('jeedom::alert', array(
                                    'level' => 'warning',
                                    'page' => 'grocy',
                                    'message' => __('Produit crèe avec succès : ', __FILE__) . $product['name'],
                                ));                                    

                                log::add('grocy','debug','eqLogic: ' . print_r( $eqLogic, true ) );
                            }
                        }

                        return true;
                    }
                } else {
                    log::add('grocy','error','resultProducts: ' . print_r( $resultProducts, true ) );
                    return false;
                }
            }
        } else {
            log::add('grocy','error','resultProductGroups: ' . print_r( $resultProducts, true ) );
            return false;
        }
    }

    private function createCmd( $_eqLogic_id, $_logicalId, $_type, $_name, $_subtype, $_visible, $_template, $_quantity = null ) {
        $cmd = grocyCmd::byEqLogicIdAndLogicalId( $_eqLogic_id, $_logicalId );
        if (!is_object($cmd)) {
            log::add('grocy', 'debug', 'Création de la commande ' . $_logicalId);

            $cmd = new grocyCmd();
            $cmd->setName( __( $_name, __FILE__ ) );
            $cmd->setEqLogic_id( $_eqLogic_id );
            $cmd->setEqType( 'grocy' );
            $cmd->setLogicalId( $_logicalId );

            if ($_subtype == 'numeric') {

                $cmd->setType( 'info' );
                $cmd->setSubType( 'numeric' );
                $cmd->setIsHistorized('0');
            } else {

                $cmd->setType( 'action' );
                if ($_subtype == 'other') {

                    $cmd->setSubType( 'other' );
                } else {

                    $cmd->setSubType( 'message' );
                }
            }
            if ($_visible == '1') {

                $cmd->setIsVisible('1');
            } else {

                $cmd->setIsVisible('0');
            }
            if ($_template == 'line') {

                $cmd->setTemplate("mobile",$_template );
                $cmd->setTemplate("dashboard",$_template );
            } else {

                $cmd->setDisplay('icon', $_template);
            }

            if( $_type == 'stock' ) {
                $quantite = is_null($_quantity) ? 0 : (int)$_quantity;
                $cmd->setConfiguration( 'value', $quantite );
            }

            $cmd->save();
            if ($_subtype == 'numeric') {

                $cmd->event(0);
            }
        }
    }

    public function addStock( $_value ) {
        log::add('grocy', 'debug', 'addStock : ' . $_value);
        $this->newStock( $this, 1, $_value );
    }

    public function rmStock( $_value ) {
        log::add('grocy', 'debug', 'rmStock : ' . $_value );
        $this->newStock( $this, 0, $_value );
    }

    private function newStock( $_eqLogic, $_op, $_value, $_noevent = false ) {

        $stockValue      = 0;
        $scanStockValue  = 0;
        $termeStockValue = 0;   
        $eqLogicId       = $_eqLogic->getId();
        $today           = date("Y-m-d");

        log::add('grocy', 'debug', 'op: ' . $_op . ' value: ' . $_value );

        $ok = false;
        $jStock = grocyCmd::byEqLogicIdAndLogicalId( $eqLogicId, 'stock' );
        if( is_object($jStock) ) {

            $stockValue = $jStock->execCmd();

            log::add('grocy', 'debug', 'stockValue : ' . $stockValue );

            if( $_eqLogic->getConfiguration('tmp') == 0 ) {

                $jScanStock = grocyCmd::byEqLogicIdAndLogicalId( $eqLogicId, 'stock-scan' );
                if( is_object($jScanStock) ) {
        
                    $scanStockValue = $jScanStock->execCmd();
        
                    log::add('grocy', 'debug', 'scanStock : ' . $scanStockValue );
        
                    $jTermeStock = grocyCmd::byEqLogicIdAndLogicalId( $eqLogicId, 'stock-terme' );
                    if( is_object($jTermeStock) ) {
            
                        $termeStockValue = $jTermeStock->execCmd();
        
                        log::add('grocy', 'debug', 'termeStockValue : ' . $termeStockValue );
                        $ok = true;
                    }
                }

                $isTmpProduct = false;

            } else {
                $isTmpProduct = true;
                $ok           = true;
            }
        }  

        if( $ok ) {

            $value   = (int)$_value ;

            if  ( $_op ) {
                $stock             = $stockValue + $value;
                $scanStock         = $stockValue + $value;
                $grocyMethodAction = 'purchaseProduct';
            } else {
                $stock             = $stockValue - $value;
                $scanStock         = $stockValue - $value;
                $grocyMethodAction = 'consumeProduct';
            }

            $stock = $stock >= 0 ? $stock : 0;
            $jStock->event( $stock );

            if( $isTmpProduct ) {

                $checkTmpQueue = config::byKey('tmp_queue','grocy');
                $tmpQueue      = is_array( $checkTmpQueue ) ? $checkTmpQueue : array() ;

                if ( ! in_array( $eqLogicId, $tmpQueue ) ) {
                    $tmpQueue[] = $eqLogicId;
                    log::add('grocy_'.$today, 'info', 'Ajout du produit ' . $_eqLogic->getName() . ' en file d\'attente.' );
                }
                config::save('tmp_queue' , $tmpQueue, 'grocy');

                if( $_noevent ) {
                    event::add('grocy::unknowbarcodequeue', array(
                        'action' => 'add',
                        'data' => array( 
                            'eqlogicid'     => $eqLogicId,
                            'quantity'      => $stock
                        ),
                        'message' => __(' : ', __FILE__) . $product['product_name'],
                    ));   
                }

            } else {

                $scanStock = $scanStock >= 0 ? $scanStock : 0;
                $jScanStock->event( $scanStock );

                $termeStock = $stockValue + $scanStockValue;
                $termeStock = $termeStock >= 0 ? $termeStock : 0;
                $jTermeStock->event($termeStock);
    
                //TODO partie a revoir pour faire un truc jolie en terme de widget
                $url    = config::byKey('url','grocy');
                $apikey = config::byKey('apikey','grocy');
    
                $http   = new grocyAPI($url, $apikey);
                $result = call_user_func_array(array( $http, $grocyMethodAction ), array( array( 'product_id' => $_eqLogic->getConfiguration( 'product_id' ), 'amount' => $value ) ));
                log::add('grocy', 'debug', 'Result request: ' . print_r( $result, true ));
            }

            $op = $_op == 1 ? ' add +' : ' rm -';
            log::add('grocy_'.$today, 'info', print_r( $_eqLogic->getName(), true ) .' old stock: ' . $stockValue . ' ' . $op . ' ' . $value . ' New stock: ' . $stock  );
            return true;
        }

        return false;
    }

    private function searchBarcodeInOpenFoodFactsDB( $_barcode ) {

        if( empty( $_barcode ) ) {
            $msg = __('Erreur: Aucun code barre', __FILE__);
            log::add('grocy','debug', $msg );
            return json_encode( array( 'error' => $msg ) );
        }
            
        $url = "https://world.openfoodfacts.org/api/v0/product/" . $_barcode . ".json";
    
        log::add('grocy','debug', $url );

        try {
    
            $request_http = new com_http( $url);
            return $request_http->exec(30); 
            
        } catch (\Throwable $th) {
    
            $msg = __('Erreur: ', __FILE__);
            log::add('grocy','debug', $msg . $th );
            return json_encode( array( 'error' => $msg ) );
        }
    
        $msg = __('Erreur: Aucun produit trouvé avec ce code barre', __FILE__);
        log::add('grocy','debug', $msg . $th );
        return json_encode( array( 'error' => $msg ) );
    }

    private function setTimestamp() {
        $timestamp = time();
        config::save('scan_latest_timestamp', $timestamp, 'grocy');  
        log::add('grocy','debug','scan_latest_timestamp: ' . $timestamp );  
    }

    private function doToStartScanMode( $_stateMode, $_stateType ) {

        self::setTimestamp();

        config::save('scan_mode', 1, 'grocy');  

        config::save('scan_type', $_stateType, 'grocy');  
        log::add('grocy','debug','Passage en mode: ' . $_stateType );

        $msgScanModeType = config::byKey( 'msg_scan_mode_type', 'grocy' );

        $scanState = array(
            'mode'  => $_stateMode,
            'type'  => $_stateType,
            'state' => 1,
            'msg'   => $msgScanModeType[$_stateType] . ', <a href="index.php?v=d&m=grocy&p=panel">'  .__('Acceder à la page', __FILE__) . '</a>'
        );
        event::add('grocy::scanState', $scanState );

        return self::sendNotification( $msgScanModeType[$_stateType] );
    }

    private function sendNotification( $_msg ) {

        $cmd = cmd::byString( config::byKey('notif_cmd', 'grocy') ); 
        if ( is_object( $cmd ) ) {
            $cmd->execCmd( $options = array( 'title' => 'Jeedom' , 'message' => $_msg ), $cache = 0);
            log::add('grocy','debug','Notif message: ' . $_msg );
            return true;
        } else {
            log::add('grocy','warning', 'Aucune commande de notfication trouvée' );
            return false;
        }
    }

    // private function getImage( $name ) {
        
    //     //file_put_contents($img, file_get_contents($url));

	// 	// $base = dirname(__FILE__) . '/../../../../';
	// 	// $path = 'plugins/grocy/core/config/'.$type.'/'.$model.'/'.$which.$ver.'.png';
	// 	// $pathDefault = 'plugins/grocy/core/config/'.$type.'/default/'.$which.$ver.'.png';
	// 	// $pathMissing = 'plugins/grocy/core/config/'.$type.'/missing/'.$which.$ver.'.png';        

	// 	// if(file_exists($base.$path)) return $path;
	// 	// else if(file_exists($base.$pathDefault)) return $pathDefault;
	// 	// else if(file_exists($base.$pathMissing)) return $pathMissing;
	// 	// else return 'plugins/unifi/plugin_info/unifi_icon.png';

    //     return '';
    // }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class grocyCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        log::add('grocy', 'debug', 'execute >  getType: ' . $this->getType() .' getLogicalId: ' . $this->getLogicalId());

        if ($this->getType() == 'info') {

            return $this->getConfiguration('value');
        } else {

            $eqLogic = $this->getEqLogic();

            switch ( $this->getLogicalId() ) {
                case 'add1':
                    $eqLogic->addStock( 1 );
                    break;
                
                case 'minus1':
                    $eqLogic->rmStock( 1 );
                    break;
            }
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}