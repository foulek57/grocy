$('body').on('grocy::change', function (_event,_options) {
    console.log(_event);
    console.log(_options);
    $('#md_modal').dialog({title: _options.title});
    $('#md_modal').attr('data-clink', 'modal');
    $('#md_modal').load('index.php?v=d&plugin=grocy&modal=modal.grocy&type=modal').dialog('open');
});

$('body').on('grocy::scanState', function (_event,_options) {

    //$('#div_alert').showAlert({message: _options.msg, level: 'warning'});

    var params = new window.URLSearchParams(window.location.search);
    if( params.get('m') == 'grocy' && ( params.get('p') == 'panel' || params.get('p') == 'grocy' ) ) {
        location.reload();
    }
    return;
});

$('body').on('grocy::unknowbarcodequeue', function (_event,_options) {

    if(_options.action == 'add') {
        var notexist = true;
        $('#queueTable').find('tbody tr').each(function () {
            var row = $(this);
            if( row.attr('id') == _options.data.eqlogicid ) {
                var col = $(this).find('td').eq(3);
                row.fadeOut('slow', function() {
                    row.fadeIn('slow', function() {
                        col.find('input').val(_options.data.quantity);
                    });
                });
                notexist = false
            } 
        });

        if(notexist) {
            $('#template').clone().attr('id',_options.data.eqlogicid).appendTo( $('#queueTable') ); 
            
            var content = $('#'+_options.data.eqlogicid).closest('tr').html();
            content = content.replace('${product_name}',_options.data.openfoodfacts.product_name);
            content = content.replace('${barcode}',_options.data.openfoodfacts.code);
            content = content.replace('${quantity}',_options.data.quantity);
            content = content.replace('${eqlogicid}',_options.data.eqlogicid);
            content = content.replace('${eqlogicid}',_options.data.eqlogicid);
            content = content.replace('${eqlogicid}',_options.data.eqlogicid);

            $('#'+_options.data.eqlogicid).html(content).fadeIn(300, function() { $(this).show(); });
        }


    } else if (_options.action == 'rm') {
        $('#'+_options.data.eqlogicid).closest('tr').fadeOut(300, function() { $(this).remove(); });
        return;
    }
});