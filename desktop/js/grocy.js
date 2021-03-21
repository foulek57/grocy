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


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

$('.eqLogicAction[data-action=discover]').on('click', function (e) {
    bootbox.confirm('{{En acceptant, le plugin va créer les emplacements sous forme d\'objet}}',
      function (result) {
        if (result) {
            $('#div_alert').showAlert({message: '{{Début de la synchronisation ...}}', level: 'warning'});
            $.ajax({
              type: "POST",
              url: "plugins/grocy/core/ajax/grocy.ajax.php",
              data: {
                action: "syncGrocy"
              },
              dataType: 'json',
              global: false,
              error: function (request, status, error) {
                handleAjaxError(request, status, error);
              },
              success: function (data) {
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({message: data.result, level: 'danger'});
                  return;
                }
                $('#div_alert').showAlert({message: '{{Synchronisation réalisée avec succès}}', level: 'success'});
                setTimeout(function(){
                  location.reload();
                }, 1000);
              }
            });
          }
      });
});

$('.eqLogicAction[data-action=supAllProducts]').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action: "supAllProducts"
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			} else {
				$('#div_alert').showAlert({message: 'Suppression des produits ok', level: 'success'});
      }
      setTimeout(function(){
        location.reload();
      }, 3000);
		}
	});
});

$('.eqLogicAction[data-action=syncAllProductsStock]').on('click', function () {
  $('#div_alert').showAlert({message: '{{Début de la synchronisation ...}}', level: 'warning'});
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action: "syncAllProductsStock"
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			} else {
				$('#div_alert').showAlert({message: 'Synchronisation du stock des produits: OK', level: 'success'});
      }
      setTimeout(function(){
        location.reload();
      }, 3000);
		}
	});
});


