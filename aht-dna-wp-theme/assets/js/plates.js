jQuery(document).ready(function($) {
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				clearPlate();
				console.log(e.target);				
				console.log ( $(e.target).attr('id') );
		})
		
		
		$('.plate_select').on('change', function() {
				
				$("#plate_id").html($(this).val());
				clearPlate();
				
				var data = {
					'action' : 'plate_details',
					'ptype' : ($(this).attr('id')).replace('_plate', ''),
					'pid' : $(this).val()
				};
				
				$.ajax({
						type : "post",
						dataType : "json",
						url : DennisAjax.ajax_url,
						data : data,
						success : function(results) {
								$("small.contents").html("");
								$("small.cell_id").show();
								for (var x=0; x<results.length; x++){
									var well = results[x].well;
									$("#"+well+" > small.cell_id").hide();
									$("#"+well+" > small.contents").html('<a href="/orders/view/?id='+results[x].order_id+'">AHT'+results[x].order_id+"/"+results[x].test_id+'</a><br />'+results[x].test_code);
								}
						}
				});
		});
		
		$('#plate_type').change(function(e){
				var plateType = $(this).val();
				var plateIDs = plateJSON[plateType];
				if (plateIDs.length > 0){
					var last = plateIDs.pop();
					console.log(last);
					var plateID = "";
					if (plateType == 'Extraction'){	
						var id = parseInt(last.replace("Q", ""));
						plateID = "Q"+(id+1);
					}
					else if (plateType == 'TaqMan'){	
						var id = parseInt(last.replace("TaqMan", ""));
						plateID = "TaqMan"+(id+1);
					}
					else if (plateType == 'Genotype'){	
						var id = parseInt(last.replace("G", ""));
						plateID = (id+1)+"G";
					}
					$('#new_plate').val(plateID);
				}
		});
});

function clearPlate(){
	$("small.contents").html("");
	$("small.cell_id").show();
}
	

var postPlateForm = function() {
	console.log(get_code);
	return false;
}
