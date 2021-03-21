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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function grocy_install() {
    
    $scanModeType = array( 
        'JGROCY-A',
        'JGROCY-C',
        'JGROCY-O'
    );

    $msgScanModeType = array(
        'JGROCY-A' => __('Grocy: Passage en mode achat', __FILE__),
        'JGROCY-C' => __('Grocy: Passage en mode consommation', __FILE__),
        'JGROCY-O' => __('Grocy: Passage en mode ouverture', __FILE__),
    );

    config::save('scan_mode_type'    , $scanModeType   , 'grocy');
    config::save('msg_scan_mode_type', $msgScanModeType, 'grocy');
    config::save('time_mode', 120    , 'grocy');
    config::save('type_mode_sync'    , array( 'real' => 1, 'diff' => 0 ), 'grocy');
    config::save('tmp_queue'         , '' , 'grocy');

	$sql = file_get_contents(dirname(__FILE__) . '/grocy.sql');
	DB::Prepare($sql, array(), DB::FETCH_TYPE_ROW);
}

function grocy_update() {
    config::save('time_mode'      , 120, 'grocy');
    config::save('type_mode_sync' , array( 'real' => 1, 'diff' => 0 ), 'grocy');
    config::save('tmp_queue'      , '' , 'grocy');
}


function grocy_remove() {

    config::remove('scanModeType'      , 'grocy');
    config::remove('msg_scan_mode_type', 'grocy');
    config::remove('time_mode'         , 'grocy');
    config::remove('type_mode_sync'    , 'grocy');
    config::remove('tmp_queue'         , 'grocy');

    $grocy = plugin::byId('grocy');
    $eqLogics = eqLogic::byType($plugin->grocy());

    foreach ($eqLogics as $eqLogic) {
        $eqLogic->remove();
    }

    DB::Prepare('DROP TABLE IF EXISTS `grocy_extend`', array(), DB::FETCH_TYPE_ROW);
}


