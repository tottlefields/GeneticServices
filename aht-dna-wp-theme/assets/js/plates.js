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
									$("#"+well+" > small.contents").html('<a href="'+DennisAjax.site_url+'/orders/view/?id='+results[x].order_id+'">AHT'+results[x].order_id+"/"+results[x].test_id+'</a><br />'+results[x].test_code);
								}
						}
				});
		});
		
		
		$('#first_well').on('change', function() {
			var well = $(this).val();
			$("input[name=gridfill]").prop('disabled', false);
			if (well === "H1"){
				$("input[name=gridfill][value=up-across]").prop('checked', true);
				$("input[name=gridfill][value=down-across]").prop('disabled', true);
				$("input[name=gridfill][value=across-down]").prop('disabled', true);
			}
			if (well === "A1"){
				$("input[name=gridfill][value=down-across]").prop('checked', true);
				$("input[name=gridfill][value=up-across]").prop('disabled', true);
				$("input[name=gridfill][value=across-up]").prop('disabled', true);
			}
		});
		
		$('.well_enter').keypress(function(event){
			
			var keycode = (event.keyCode ? event.keyCode : event.which);
			if(keycode == '13'){
				var barcode = $(this).val();
				var well = $(this).closest('td').data('well');
				var plate = $(this).closest('td').data('plate');

				var data = {
					'action' : 'extract_swabs',
					'barcode' : barcode,
					'well' : well,
					'plate' : plate
				};
				$("body").css("cursor", "progress");
				
				$.ajax({
						type : "post",
						dataType : "json",
						url : DennisAjax.ajax_url,
						data : data,
						success : function(results) {
							//console.log(wellOrder);
							if (results.status == "Success"){
								$("#"+well+" > small.cell_id").hide();
								$("#"+well+" > small.contents").html('<a href="'+DennisAjax.site_url+'/orders/view/?id='+results.data.order_id+'">'+results.data.barcode+'</a><br />'+results.data.test_code);
								
								var newWell = wellOrder[well];
								$("#"+newWell+" > small.contents >input").show();
								$("#"+newWell+" > small.contents >input").focus();
							}
							if (results.status == "Error"){								
								bootbox.alert(results.msg, function(){ 
								    $("#"+well+" > small.contents > input").val("");
									$("#"+well+" > small.contents > input").focus();
								});
							
							}
							$("body").css("cursor", "default");
						}
				});
			}

		});
		
		$('#plate_type').change(function(e){
				var plateType = $(this).val();
				console.log(plateJSON);
				var plateIDs = plateJSON[plateType];
				console.log(plateIDs);
				if (plateIDs.length > 0){
					var last = plateIDs.pop();
					var plateID = "";
					if (plateType == 'extraction'){	
						var id = parseInt(last.replace("Q", ""));
						plateID = "Q"+(id+1);
					}
					else if (plateType == 'taqman'){	
						var id = parseInt(last.replace("TaqMan", ""));
						plateID = "TaqMan"+(id+1);
					}
					else if (plateType == 'genotype'){	
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
	var plateType = $('#plate_type').val();
	var plateID = $('#new_plate').val();
	console.log(plateType+" - "+plateID);
}
