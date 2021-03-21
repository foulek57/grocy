<?php
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

function returnMsg( $_type, $_msg ) {

    if( $_type == 'error' )
        log::add('grocy','debug','ip: ' . network::getClientIp() . ' msg:' . $_msg );
        
    $msg[$_type] = $_msg;
    echo json_encode($msg);
	die();
}

if (!jeedom::apiAccess(init('apikey'), 'grocy')) {
    returnMsg( 'error', __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (Grocy)', __FILE__) );
}

$barCode         = init('text');
$scanModeType    = config::byKey( 'scan_mode_type'   , 'grocy' );

if( empty( $barCode ) ) {
    returnMsg( 'error', __('Erreur lors de la transmission du code barre', __FILE__) );
}

log::add('grocy','debug','code barre scanné: ' . $barCode );

if ( in_array( $barCode, $scanModeType ) ) {

    switch ( $barCode ) {
        //Mode scan 
        case $scanModeType[0]:

            grocy::startScanMode( 'scan', 'JGROCY-A' );

            returnMsg( 'state', 'succes' );
            break;

        //mode scan consommation
        case $scanModeType[1]:
        
            grocy::startScanMode( 'scan', 'JGROCY-C' );

            returnMsg( 'state', 'succes' );
            break;
            
        //Mode ouverture
        case $scanModeType[2]:

            grocy::startScanMode( 'scan', 'JGROCY-O' );

            returnMsg( 'state', 'succes' );
            break;
    }

    returnMsg( 'error', __('Type de code barre inconnu pour le passage en mode scan', __FILE__) );
} 

if( grocy::scanProduct( $barCode ) ) {
    returnMsg( 'state', 'succes' );
} else {
    returnMsg( 'error', __('Voir les logs!', __FILE__) );
}