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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Grocy URL}}</label>
            <div class="col-lg-8">
                <input class="configKey form-control" data-l1key="url" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Grocy API KEY}}</label>
            <div class="col-lg-8">
                <input class="configKey form-control" data-l1key="apikey" />
            </div>
        </div>
		<div class="form-group ashmode jeedom">
			<label class="col-lg-4 control-label">{{Tester les accès}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Vous devez avoir sauvegardé pour tester}}"></i></sup>
			</label>
			<div class="col-lg-8">
				<a class="btn btn-default" id="bt_checkGrocyInstance"><i class="fa fa-paper-plane" aria-hidden="true"></i> {{Tester}}</a>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 col-xs-12 control-label">{{Commande de notification}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Commande par défaut pour vous informer du passage (commande de type message).}}"></i></sup>
			</label>
			<div class="col-sm-3 col-xs-12">
				<div class="input-group">
					<input type="text" class="grocyNotifAttr form-control roundedLeft configKey form-control" data-l1key="notif_cmd" />
					<span class="input-group-btn">
						<a class="btn btn-default cursor bt_selectWarnMeCmd roundedRight" title="{{Rechercher une commande}}"><i class="fas fa-list-alt"></i></a>
					</span>
				</div>
			</div>
		</div>
		<div class="form-group">
            <label class="col-lg-4 control-label">{{Temps actif du mode C ou O}}</label>
            <div class="col-lg-8">
                <input class="configKey form-control" data-l1key="time_mode" />
            </div>
        </div>
		<div class="form-group">
            <label class="col-lg-4 control-label">{{Nombre de jour pour la DLC par défaut}}</label>
            <div class="col-lg-8">
                <input class="configKey form-control" data-l1key="default_best_before_days" />
            </div>
        </div>
		<div class="form-group">
            <label class="col-lg-4 control-label">{{Nombre de jour pour la DLC après ouverture par défaut}}</label>
            <div class="col-lg-8">
                <input class="configKey form-control" data-l1key="default_best_before_days_after_open" />
            </div>
        </div>
		<div class="form-group" style="display:none">
            <label class="col-lg-4 control-label">{{Mode temps réel ou différé}}</label>
            <div class="col-lg-8">
				<label class="radio-inline"><input type="radio" name="rd_type_mode_sync" class="configKey" data-l1key="type_mode_sync" data-l2key="real"> {{Temps réel}}</label>
				<label class="radio-inline"><input type="radio" name="rd_type_mode_sync" class="configKey" data-l1key="type_mode_sync" data-l2key="diff"> {{Différé}}</label>
            </div>
        </div>
  </fieldset>
</form>
<?php 
//Si possible dans l'avenir installation en local ou via docker
// if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
//     echo 'J\'ai au moins la version 7.3.0 de PHP ; ma version : ' . PHP_VERSION . "\n";
// }
?>
<script type="text/javascript">
$('.bt_selectWarnMeCmd').on('click', function () {
  jeedom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
    $('.grocyNotifAttr[data-l1key="notif_cmd"]').value(result.human);
  });
});

$('#bt_checkGrocyInstance').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action: "checkGrocyInstance",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alertPluginConfiguration').showAlert({message: data.result, level: 'danger'});
				return;
			}
			$('#div_alertPluginConfiguration').showAlert({message: '{{Connexion ok.}}', level: 'success'});
		}
	});
});
</script>