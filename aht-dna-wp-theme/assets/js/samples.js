jQuery(document).ready(function($) {
	var duplicates = new Array();
	var details = $('#order_details');
	var table = $('#samples').DataTable({
		dom: '<"toolbar">frtip',
		paging: false,
//		ordering: false,
		lengthChange: false,
		order: [[ 4, "asc" ], [7, "asc"]],
		select: { 
			style: 'multi',
			selector: ':not(input.noRowSelect)'
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
			targets: [ 2, 3, 6 ],
			visible: false
		}, {
			type: 'date-uk',
			targets: 4
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
	
	$('.duplicateSwab').change(function() {
		var swabID = $(this).closest('tr').attr('id').replace("row","");
		if($(this).prop('checked')){
			duplicates.push(swabID);
		}else{
			duplicates.splice( $.inArray(swabID, duplicates), 1 );
		}
		countSamples();
	});
	
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
	
	$("div.toolbar").html('<button type="button" class="btn btn-default" id="exportSampleList" disabled="disabled"><i class="fa fa-th link"></i>&nbsp;Generate Q Plate</button>');
	
	$("#exportSampleList").on('click', function(e){
		var orderIds = [];
		var rows_selected = table.column(0).checkboxes.selected();		
		window.location.href = "http://dennis.local/plates/?add-plate&plate_type=extraction&samples="+rows_selected.join()+"&duplicates="+duplicates.join();
	});
	

});

function countSamples(){
	var table = $('#samples').DataTable();
	var sampleCount = table.rows('.selected').count();
	if (sampleCount == 0 ){ $("#exportSampleList").prop("disabled", true); }
	else{ $("#exportSampleList").prop("disabled", false); }
	if ($('.duplicateSwab:checked').length > 0){ sampleCount += ' ('+$('.duplicateSwab:checked').length+')'}
	$('#sample_count').text(sampleCount);
}

function resetSamples(){
	var table = $('#samples').DataTable();
	table.rows().deselect();
	$(".selectSample").removeClass('active');
	$('.duplicateSwab').bootstrapToggle('off');
	countSamples();
	//location.reload();
}