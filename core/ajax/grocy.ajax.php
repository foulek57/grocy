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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');
    
    $scanModeType    = config::byKey( 'scan_mode_type'   , 'grocy' );
    $msgScanModeType = config::byKey( 'msg_scan_mode_type', 'grocy' );

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }
    
    ajax::init();

	if (init('action') == 'checkGrocyInstance') {
        $result = grocy::checkGrocyInstance();
        if( is_json( $result ) ) {
            ajax::success();
        } else {
            if( is_object() ) {
                ajax::error(displayException($result), $result->getCode());
            } else {
                ajax::error('Erreur de connexion');
            }
            
        }
        log::add('grocy','debug','check : ' . $result );
    }
   
    if( init('action') == 'startScanMode') {
        $type = init('type');
        if( grocy::startScanMode( 'scan', $type ) ) {
            ajax::success();
        } else {
            ajax::error('Erreur de connexion');
        }       
    }    

    if( init('action') == 'stopScanMode') {
        if( grocy::stopScanMode() ) { 
            ajax::success();
        } else {
            ajax::error('Erreur de connexion');
        }       
    }

    if( init('action') == 'syncGrocy') {
        if( grocy::syncGrocy() ) {
            ajax::success( __('Tâche effectuée.', __FILE__) );
        } else {
            ajax::error( __('Erreur lors de la synchronisation, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'supAllProducts') {
        if( grocy::supAllProducts() ) {
            ajax::success();
        } else {
            ajax::error( __('Erreur lors de la suppression des produits, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'syncAllProductsStock') {
        if( grocy::syncAllProductsStock() ) {
            ajax::success();
        } else {
            ajax::error( __('Erreur lors de la synchronisation du stock des produits, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'supAllInQueue') {
        if( grocy::supAllInQueue() ) {
            ajax::success();
        } else {
            ajax::error( __('Erreur lors de la suppression de la file d\'attente, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'supProductInQueue') {
        $eqLogicId = init( 'eqlogicid' );
        if( grocy::supProductInQueue( $eqLogicId ) ) {
            ajax::success();
        } else {
            ajax::error( __('Erreur lors de la suppression du produit dans la file d\'attente, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'getGrocyLocations') {
        if( $locations = grocy::getGrocyLocations() ) {
            ajax::success( array( 'locations' => $locations ) );
        } else {
            ajax::error( __('Erreur lors de la récupération des emplacements, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'getGrocyQuantityUnits') {
        if( $quantityUnits = grocy::getGrocyQuantityUnits() ) {
            ajax::success( array( 'quantityUnits' => $quantityUnits ) );
        } else {
            ajax::error( __('Erreur lors de la récupération des unités de quantité, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'getGrocyProductGroups') {
        if( $productGroups = grocy::getGrocyProductGroups() ) {
            ajax::success( array( 'productGroups' => $productGroups ) );
        } else {
            ajax::error( __('Erreur lors de la récupération des unités de quantité, voir les logs.', __FILE__) );
        }
    }

    if( init('action') == 'createProductInGrocy') {

        if( $product = grocy::createProductInGrocy( init( 'formdata' ) ) ) {
            ajax::success( $product );
        } else {
            ajax::error( __('Erreur lors de la création du produit dans Grocy, voir les logs.', __FILE__) );
        }
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}

