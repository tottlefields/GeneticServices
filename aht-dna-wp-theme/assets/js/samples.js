jQuery(document).ready(function($) {
	var details = $('#order_details');
	var table = $('#samples').DataTable({
		dom: '<"toolbar">frtip',
		paging: false,
		ordering: false,
		lengthChange: false,
		order: [[ 3, "asc" ], [6, "asc"]],
		select: { 
			style: 'multi',
			selector: 'td:first-child'
        },
		colmnDefs: [ {
			targets: [ 0, 2 ],
			orderable: false
		}, {
			type: 'date-uk',
			targets: 3
		} ]
	});
	
	$(".selectSample").on("click", function() {
			
			var button = $(this).attr('id');
			var sampleCount = parseInt($('#sample_count').text());
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
				$("#samples tr."+selector).each(function() {
						$(this).removeClass("selected");
						if($(this).find("input[type=checkbox]").prop("checked") == true){
							$(this).find("input[type=checkbox]").prop("checked", false);
							$("#checkAll").prop("checked", false);
							sampleCount--;
						}
				});
			} else { 
				$(this).addClass('active'); 		
				$("#samples tr."+selector).each(function() {
						$(this).addClass("selected");
						//console.log($(this).find("input[type=checkbox]").prop("checked"));
						if($(this).find("input[type=checkbox]").prop("checked") == false){
							$(this).find("input[type=checkbox]").prop("checked", true);
							sampleCount++;
							if(sampleCount == table.rows( '.selected' ).count()){ $("#checkAll").prop("checked", true); }
						}
				});
			}
			
			//var table = $('#samples').DataTable();
			
			$('#sample_count').text(sampleCount);
	});
	
	$("#checkAll").on("click", function() {
		if (this.checked == true){
			//var sampleCount = parseInt($('#sample_count').text());
			$("#samples").find(".checkboxRow").prop("checked", true);
			$("#exportSampleList").prop("disabled", false);
			$("#samples").find("tr").addClass("selected");
			$(".selectSample").each(function() { $(this).addClass('active'); });
			$('#sample_count').text(table.rows( '.selected' ).count());
		}
		else{
			$("#samples").find(".checkboxRow").prop("checked", false);
			$("#exportSampleList").prop("disabled", true);
			$('#sample_count').text(0);
			$("#samples").find("tr.selected").removeClass("selected");
			$(".selectSample").each(function() { $(this).removeClass('active'); });
		}
	});
	
	$(".checkboxRow").on("click", function() {
		console.log("here - "+this.checked);
		if (this.checked == true){
			$("#exportSampleList").prop("disabled", false);
		}
		else{
			var foundChecked = false;
			$(".checkboxRow").each(function (index){
				if ($(this).prop("checked")){ foundChecked = true; }
			});
			if(!foundChecked)
				$("#exportSampleList").prop("disabled", true);
		}
	});
	
	$("div.toolbar").html('<button type="button" class="btn btn-default" id="exportSampleList" disabled="disabled"><i class="fa fa-flask link"></i>&nbsp;Export Sample List</button>');
	
	$("#exportSampleList").on('click', function(e){
		var orderIds = [];
		$(".checkboxRow").each(function (index){
			if ($(this).prop("checked")){
				orderIds.push($(this).val());
			}
		});
		console.log(orderIds);
		//generatePDFs(orderIds, null, 1);
	});
	

});

function resetSamples(){
	location.reload();
	
	/*$('#sample_count').text(0);
	$('#checkAll').prop("checked", false);
	$("#samples tr").each(function() { $(this).find("input[type=checkbox]").prop("checked", false); });
	$(".selectSample").each(function() { $(this).removeClass('active'); });
	$("#exportSampleList").prop("disabled", true);
	$(".checkboxFlag").text(0);*/
}