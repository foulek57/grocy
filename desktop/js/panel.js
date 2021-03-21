$('#bt_stopScanMode').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action: "stopScanMode",
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			}
			location.reload();
		}
	});
});

$('.bt_startScanMode').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action: "startScanMode",
			type: 'JGROCY-' + $(this).data('mode'),
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			}
			location.reload();
		}
	});
});

$('#bt_inventaire').on('click', function () {
	alert('je ne faire rien pour le moment!')
});

$('#bt_supAllProducts').on('click', function () {
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
				return;
			}
		}
	});
});

$('#bt_supAllInQueue').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action: "supAllInQueue"
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
				$('#div_alert').showAlert({message: 'Réinitialisation de la file d\'attente ok', level: 'success'});
				$("#queueRow").remove();
				//$("#queueTable").find("tr").remove();
				return;
			}
		}
	});
});

$('.product[data-action=supProductInQueue]').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/grocy/core/ajax/grocy.ajax.php",
		data: {
			action   : "supProductInQueue",
			eqlogicid: $(this).data('eqlogicid')
		},
		async:false,
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			} 
			console.log(data);
		}
	});
});

$('.product[data-action=assocProductInQueue]').on('click', function () {

	$('#md_modal').dialog({title: "{{Association}}"});

	$('#md_modal').load('index.php?v=d&plugin=grocy&modal=panel/assoc2grocy&eqlogicid='+$(this).data('eqlogicid')+'&type=modal').dialog('open');

});

$('.product[data-action=addProductInQueue]').on('click', function () {

	$('#md_modal').dialog({title: "{{Ajouter un nouveau produit à Grocy}}"});

	$('#md_modal').load('index.php?v=d&plugin=grocy&modal=panel/add2grocy&eqlogicid='+$(this).data('eqlogicid')+'&qte='+$(this).data('qte')+'&type=modal').dialog('open');

});