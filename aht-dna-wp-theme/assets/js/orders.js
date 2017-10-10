jQuery(document).ready(function($) {
	var details = $('#order_details');
	var table = $('#orders').DataTable({
		select : true,
		pageLength : 20,
		order : [ [ 1, 'desc' ] ],
		columnDefs : [ {
			targets : [ 5, 6 ],
			orderable : false
		}, {
			targets : [ 0, 2 ],
			visible : false
		}, {
			type : 'date-uk',
			targets : 3
		} ]
	});

	table.on('select', function(e, dt, type, indexes) {
		if (table.rows('.selected').data().length === 1) {
			var rowData = table.rows(indexes).data().toArray();
			getOrders(rowData[0][0], details);
		} else {
			details.empty();
		}
	}).on('deselect', function(e, dt, type, indexes) {
		details.empty();
	});

});