jQuery(document).ready(function($) {
	var details = $('#order_details');
	var table = $('#orders').DataTable({
		select : true,
		dom : '<"toolbar">frtip',
		pageLength: 20,
		paging : true,
		lengthChange: false,
		order : [ [ 2, 'desc' ] ],
		columnDefs : [ {
			targets : [ 1,6,7 ],
			orderable : false
		}, {
			targets : [ 0,7 ],
			visible : false
		}, {
			type : 'date-uk',
			targets : 4
		} ]
	});
	
	$("#checkAll").on("click", function() {
		if (this.checked == true){
			$("#orders").find(".checkboxRow").prop("checked", true);
			$("#exportPDFs").prop("disabled", false);
		}
		else{
			$("#orders").find(".checkboxRow").prop("checked", false);
			$("#exportPDFs").prop("disabled", true);
		}
	});
	
	$(".checkboxRow").on("click", function() {
			console.log("here - "+this.checked);
		if (this.checked == true){
			$("#exportPDFs").prop("disabled", false);
		}
		else{
			var foundChecked = false;
			$(".checkboxRow").each(function (index){
				if ($(this).prop("checked")){ foundChecked = true; }
			});
			if(!foundChecked)
				$("#exportPDFs").prop("disabled", true);
		}
	});

	table.on('select', function(e, dt, type, indexes) {
		if (table.rows('.selected').data().length === 1) {
			var rowData = table.rows(indexes).data().toArray();
			if(table.rows(indexes).nodes().to$().hasClass('danger')){
				table.rows(indexes).nodes().to$().removeClass('danger');
				table.rows(indexes).nodes().to$().addClass('danger-bak');
			}
			getOrders(rowData[0][0], details);
		} else {
			details.empty();
		}
	}).on('deselect', function(e, dt, type, indexes) {
		details.empty();
		if(table.rows(indexes).nodes().to$().hasClass('danger-bak')){
			table.rows(indexes).nodes().to$().addClass('danger');
			table.rows(indexes).nodes().to$().removeClass('danger-bak');
		}
	});
	
	$("div.toolbar").html('<button type="button" class="btn btn-default" id="exportPDFs" disabled="disabled"><i class="fa fa-file-pdf-o link"></i>&nbsp;Export PDFs</button>');
	
	$("#exportPDFs").on('click', function(e){
		var orderIds = [];
		$(".checkboxRow").each(function (index){
			if ($(this).prop("checked")){
				orderIds.push($(this).val());
			}
		});
		generatePDFs(orderIds, null, 1);
	});
	

});