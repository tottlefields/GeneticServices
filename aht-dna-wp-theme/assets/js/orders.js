
jQuery(document).ready(function($){
	var details = $('#order_details');
	var table = $('#orders').DataTable({
			select: true,
			order: [[ 1, 'asc' ]]	
	});
	
	table.on( 'select', function ( e, dt, type, indexes ) {
			if (table.rows('.selected').data().length === 1){
				var rowData = table.rows( indexes ).data().toArray();
				console.log(rowData[0][1]);
				details.prepend('<h4>Order #'+rowData[0][1]+'</h4>');
				//details.prepend( '<div><b>'+type+' selection</b> - '+JSON.stringify( rowData )+'</div>' );
			}
			else{
				details.empty();
			}
	} )
	.on( 'deselect', function ( e, dt, type, indexes ) {
			details.empty();
			//var rowData = table.rows( indexes ).data().toArray();
			//details.prepend( '<div><b>'+type+' <i>de</i>selection</b> - '+JSON.stringify( rowData )+'</div>' );
	} );

	var data = {
		'action': 'my_action',
		'whatever': 1234
	};
	
	jQuery.ajax({
			type: "post",
            dataType: "json",
            url: DennisAjax.ajax_url,
            data: data,
            success: function(msg){
                console.log(msg);
            }
        });
	
	
});