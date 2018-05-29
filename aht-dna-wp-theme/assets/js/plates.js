jQuery(document).ready(function($) {
		$('.plate_select').on('change', function() {
				
				$("#plate_id").html($(this).val());
				
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
});