jQuery(document).ready(function($) {
		$(document).on({ //no need to be inside document ready handler!
			ajaxStart: function () { $('body').addClass("loading"); },
			ajaxStop: function () { $('body').removeClass("loading"); }
		});
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				clearPlate();
				// console.log(e.target);				
				// console.log ( $(e.target).attr('id') );
		})
		
		
		$('.plate_select').on('change', function() {
			
			var plate = $(this).val();
			$("#plate_id").html(plate+'<span id="plate_details" class="pull-right"></span>');
			clearPlate();
			
			var data = {
					'action' : 'plate_details',
					'ptype' : ($(this).attr('id')).replace('_plate', ''),
					'pid' : plate
				};
				
				$.ajax({
						type : "post",
						dataType : "json",
						url : DennisAjax.ajax_url,
						data : data,
						success : function(results) {
							//console.log(results);
							$("#plate_details").html('<small>'+results.created_by+" ("+results.readable_date+")</small>");
							$("#details_plate").html('<div class="row"><div class="col-xs-5">Plate</div><div class="col-xs-7"><a href="/plate/'+plate+'">'+plate+'</a></div></div><div class="row"><div class="col-xs-5">Owner</div><div class="col-xs-7"><strong>'+results.created_by+'</strong></div></div><div class="row"><div class="col-xs-5">Generated</div><div class="col-xs-7">'+results.readable_date+'</div></div>');
							$("small.contents").html("");
							$("small.cell_id").show();
							var wells = results.wells;
							for (var x=0; x<wells.length; x++){
								var well = wells[x].well;
								$("#"+well+" > small.cell_id").hide();
								$("#"+well+" > small.contents").html('<a href="'+DennisAjax.site_url+'/orders/view/?id='+wells[x].order_id+'"><span class="hidden-print">AHT</span>'+wells[x].order_id+"/<span class=\"test-id\">"+wells[x].test_id+'</span></a><br />'+wells[x].test_code);
							}
							if(results.other_wells !== undefined){
							var otherWells = results.other_wells;
								for (var x=0; x<otherWells.length; x++){
									var well = otherWells[x].well;
									$("#"+well+" > small.cell_id").hide();
									$("#"+well+" > small.contents").html('<span class="control">'+otherWells[x].well_contents+'</span>');
								}
							}
							for (var x=1; x<=12; x++){
								if (results['col'+x] !== undefined && results['col'+x] !== null){
									var a = results['col'+x].split(":");
									$("#col"+x).html('<a href="/plate/'+a[0]+'">'+a[0]+'</a>/'+a[1]);
								}
							}
						}
				});
		});
		
		
		/*$('#first_well').on('change', function() {
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
		});*/
		
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
				//console.log(plateJSON);
				var plateIDs = plateJSON[plateType];
				//console.log(plateIDs);
				if (plateIDs.length > 0){
					//var last = plateIDs.pop();
					var last = plateIDs[0];
					// console.log(last);
					var plateID = "";
					if (plateType == 'extraction'){	
						var id = parseInt(last.replace("Q", ""));
						plateID = "Q"+(id+1);
					}
					else if (plateType == 'taqman'){	
						var id = parseInt(last.replace("TM", ""));
						plateID = "TM"+(id+1);
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
	$(".col_header").html("");
	$("#details_plate").html("");
}
	

var postPlateForm = function() {
	var plateType = $('#plate_type').val();
	var plateID = $('#new_plate').val();
	// console.log(plateType+" - "+plateID);
}
