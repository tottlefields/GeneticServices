jQuery(document).ready(function($) {
	var details = $('#order_details');
	var table = $('#samples').DataTable({
		dom: '<"toolbar">frtip',
		paging: false,
//		ordering: false,
		lengthChange: false,
		order: [[ 4, "asc" ], [7, "asc"]],
		select: { 
			style: 'multi',
//			//selector: 'td:first-child'
		},
		columnDefs: [ {
            targets: 0,
			orderable: false,
            checkboxes: { selectRow: true }
         }, {
			targets: [ 0, 2, 3 ],
			orderable: false
		}, {
			type: 'date-uk',
			targets: 3
		} ]
	});
	
	table
        .on( 'select', function ( e, dt, type, indexes ) {
            //var rowData = table.rows( indexes ).data().toArray();
            countSamples();
        } )
        .on( 'deselect', function ( e, dt, type, indexes ) {
            //var rowData = table.rows( indexes ).data().toArray();
            countSamples();
        } );
	
	$(".selectSample").on("click", function() {
			
			var button = $(this).attr('id');
			var selector;
			
			if (button.match(/selectTest/)){
				test = button.replace("selectTest", "");
				selector = "test-"+test;
			}
			if (button.match(/selectType/)){
				type = button.replace("selectType", "");
				selector = "type-"+type;
				for (var test in types2test[type]) {
					if($(this).hasClass('active')){ $("#selectTest"+test).removeClass("active"); }
					else { $("#selectTest"+test).addClass("active"); }
				}
			}			
			
			if($(this).hasClass('active')){
				$(this).removeClass('active');
				table.rows('.'+selector).deselect();
			} else { 
				$(this).addClass('active'); 
				table.rows('.'+selector).select();
			}
	});
	
	$("div.toolbar").html('<button type="button" class="btn btn-default" id="exportSampleList" disabled="disabled"><i class="fa fa-flask link"></i>&nbsp;Export Sample List</button>');
	
	$("#exportSampleList").on('click', function(e){
		var orderIds = [];
		var rows_selected = table.column(0).checkboxes.selected();
		alert(rows_selected.join());
		//generatePDFs(orderIds, null, 1);
	});
	

});

function countSamples(){
	var table = $('#samples').DataTable();
	var sampleCount = table.rows('.selected').count();
	if (sampleCount == 0 ){ $("#exportSampleList").prop("disabled", true); }
	else{ $("#exportSampleList").prop("disabled", false); }
	$('#sample_count').text(sampleCount);
}

function resetSamples(){
	var table = $('#samples').DataTable();
	table.rows().deselect();
	$(".selectSample").removeClass('active');
	countSamples();
	//location.reload();
}