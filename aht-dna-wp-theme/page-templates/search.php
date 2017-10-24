<?php /* Template Name: Search */ ?>
<?php 
//process search...
global $wpdb;

if (isset($_REQUEST['q']) && $_REQUEST['q'] !== ''){
	$query = $_REQUEST['q'];
	$allResults = array();
	
	if (preg_match('/^AHT\d{1,5}$/', $query)){
		//Dennis (AHT) ID - between 1 & 5 digits [OrderID] pre-pended by 'AHT'
		$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, orders.ShippingCountry, count(*) as TestCount 
				from orders left outer join client on client.id=client_id 
				left outer join order_tests on orders.id=order_id
				WHERE orders.id='".str_replace('AHT', '', $query)."'
				GROUP BY orders.id";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->ID > 0){
			wp_redirect(get_site_url().'/orders/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['orders'])) { $allResults['orders'] = array(); }
		if (count($results) >= 1){ $allResults['orders'] = array_merge($allResults['orders'], $results); }
	}
	
	if (preg_match('/^[a-zA-Z]{2,5}\d{1,3}$/', $query)){
		//Portal ID - between 2 & 5 letters followed by between 1 and 3 digits
		$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, orders.ShippingCountry, count(*) as TestCount 
				from orders left outer join client on client.id=client_id 
				left outer join order_tests on orders.id=order_id
				WHERE PortalID='".$query."'
				GROUP BY orders.id";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->ID > 0){
			wp_redirect(get_site_url().'/orders/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['orders'])) { $allResults['orders'] = array(); }
		if (count($results) >= 1){ $allResults['orders'] = array_merge($allResults['orders'], $results); }
	}
	
	if (preg_match('/^\d{1,5}\/\d{1,5}$/', $query)){
		//Swab ID - between 1 & 5 digits [OrderID] followed by between 1 and 5 digits [SwabID], seperated with '/'
		$sample_details = explode('/', $query);
		$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, orders.ShippingCountry, count(*) as TestCount 
				from orders left outer join client on client.id=client_id 
				left outer join order_tests on orders.id=order_id
				WHERE orders.id='".$sample_details[0]."'
				GROUP BY orders.id";
		$results = $wpdb->get_results($sql);	
		if (count($results) == 1 && $results[0]->ID > 0){
			wp_redirect(get_site_url().'/orders/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['orders'])) { $allResults['orders'] = array(); }
		if (count($results) >= 1){ $allResults['orders'] = array_merge($allResults['orders'], $results); }
	}
	
	if (preg_match('/^\d{12,17}$/', $query)){
		//Microchip
		$sql = "select a.id as ID, a.*, c.* from animal a inner join client c on c.id=client_id where TattooOrChip ='".$query."'";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->ID > 0){
			wp_redirect(get_site_url().'/animals/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['animals'])) { $allResults['animals'] = array(); }
		if (count($results) >= 1){ $allResults['animals'] = array_merge($allResults['animals'], $results); }
	}
	
	if (preg_match('/^KC[a-zA-Z]{2}\d{7,9}$/', $query) || preg_match('/^[a-zA-Z]{2}\d{7,9}$/', $query) || preg_match('/^K\d{6}$/', $query)){
		//Registration No.
		$sql = "select a.id as ID, a.*, c.* from animal a inner join client c on c.id=client_id where RegistrationNo ='".$query."'";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->ID > 0){
			wp_redirect(get_site_url().'/animals/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['animals'])) { $allResults['animals'] = array(); }
		if (count($results) >= 1){ $allResults['animals'] = array_merge($allResults['animals'], $results); }
	}
	
	if (preg_match('/@/', $query)){
		//Email address
		$sql = "select c.id as ID, c.* from client c where Email ='".$query."'";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->id > 0){
			wp_redirect(get_site_url().'/clients/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['clients'])) { $allResults['clients'] = array(); }
		if (count($results) >= 1){ $allResults['clients'] = array_merge($allResults['clients'], $results); }
	}
	
	if (preg_match('/^[A-Z]{1,2}[0-9][0-9A-Z]?\s?[0-9][A-Z]{2}$/i', $query)){
		//Postcode
		$sql = "select c.id as ID, c.* from client c where REPLACE(Postcode, ' ', '') = '".str_replace(' ', '', $query)."'";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->id > 0){
			wp_redirect(get_site_url().'/clients/view?id='.$results[0]->ID);
			exit;	
		}
		if(!isset($allResults['clients'])) { $allResults['clients'] = array(); }
		if (count($results) >= 1){ $allResults['clients'] = array_merge($allResults['clients'], $results); }
	}
	
	
	$sql = "select c.id as ID, c.* from client c where FullName LIKE '%".$query."%'";
	$results = $wpdb->get_results($sql);
	if(!isset($allResults['clients'])) { $allResults['clients'] = array(); }
	if (count($results) >= 1){ $allResults['clients'] = array_merge($allResults['clients'], $results); }
	
	
	$sql = "select a.id as ID, a.*, c.* from animal a inner join client c on c.id=client_id where RegisteredName LIKE '%".$query."%'";
	$results = $wpdb->get_results($sql);
	if(!isset($allResults['animals'])) { $allResults['animals'] = array(); }
	if (count($results) >= 1){ $allResults['animals'] = array_merge($allResults['animals'], $results); }
	
	$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, orders.ShippingCountry, count(*) as TestCount
				from orders left outer join client on client.id=client_id
				left outer join order_tests on orders.id=order_id
				WHERE PortalID='".$query."' ";
	if (is_numeric($query)){
		$sql .= "or orders.id='".$query."' OR orders.OrderID='".$query."'";
	}
	$sql .= "GROUP BY orders.id";
	$results = $wpdb->get_results($sql);
	if(!isset($allResults['orders'])) { $allResults['orders'] = array(); }
	if (count($results) >= 1){ $allResults['orders'] = array_merge($allResults['orders'], $results); }
	
	
	if (preg_match('/ AND /i', $query)){
		//Assume pet name AND surname
		$query_details = explode(' AND ', strtoupper($query));
		$sql = "select a.id as ID, a.*, c.* from animal a inner join client c on c.id=client_id
			where c.FullName LIKE '%".$query_details[1]."' AND a.PetName = '".$query_details[0]."'";
		$results = $wpdb->get_results($sql);
		if (count($results) == 1 && $results[0]->ID > 0){
			wp_redirect(get_site_url().'/animals/view?id='.$results[0]->ID);
			exit;
		}
		if(!isset($allResults['animals'])) { $allResults['animals'] = array(); }
		if (count($results) >= 1){ $allResults['animals'] = array_merge($allResults['animals'], $results); }
	}
	
	$total_results = count($allResults['orders']) + count($allResults['animals']) + count($allResults['clients']);
	$most_results = '';
	$max_count = 0;
	foreach ($allResults as $tab => $array){
		if(count($array) > $max_count){
			$max_count = count($array);
			$most_results = $tab;
		}
	}
	
	if ($total_results == 1){
		wp_redirect(get_site_url().'/'.$most_results.'/view?id='.$allResults[$most_results][0]->ID);
		exit;	
	}
}


?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
					
	<div class="row">
		<div class="col-sm-12">
   			<div class="jumbotron text-center">
   				<div class="container">
					<form id="search-form" role="form" method="post" action="<?php echo get_site_url(); ?>/search/">
						<div class="col-md-6 col-md-offset-3">
							<div class="input-group input-group-lg">
								<input type="text" class="form-control search-form" placeholder="Search" name="q">
								<span class="input-group-btn">
									<button type="submit" class="btn btn-primary search-btn" data-target="#search-form"><i class="fa fa-search"></i></button>
								</span>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<section class="row">
		<div class="col-md-12">
	<?php if (isset($query)) { ?>
		<?php if ($total_results == 0) { ?>
				<div class="alert alert-danger">Your search for <strong><?php echo strtoupper($query); ?></strong> found 0 results. Please try again.</div>
		<?php } else { ?>
	
				<h2>Results</h2>
				<div class="alert alert-success">Your search for <strong><?php echo strtoupper($query); ?></strong> found <strong><?php echo $total_results; ?></strong> results.</div>
				<ul class="nav nav-tabs nav-justified" id="resultsTabs" role="tablist">
					<li<?php if(count($allResults['animals']) == 0){ echo ' class="disabled"'; }?>>
						<a href="#tab-animals" role="tab" data-toggle="tab" aria-controls="tab-animals"><i class="fa fa-paw fa-lg"></i>&nbsp;Animals&nbsp;
						<span class="badge"><?php echo count($allResults['animals']); ?></span></a>
					</li>
					<li<?php if(count($allResults['clients']) == 0){ echo ' class="disabled"'; }?>>
						<a href="#tab-clients" role="tab" data-toggle="tab" aria-controls="tab-clients"><i class="fa fa-user fa-lg"></i>&nbsp;Clients&nbsp;
						<span class="badge"><?php echo count($allResults['clients']); ?></span></a>
					</li>
					<li<?php if(count($allResults['orders']) == 0){ echo ' class="disabled"'; }?>>
						<a href="#tab-orders" role="tab" data-toggle="tab" aria-controls="tab-orders"><i class="fa fa-shopping-basket fa-lg"></i>&nbsp;Orders&nbsp;
						<span class="badge"><?php echo count($allResults['orders']); ?></span></a>
					</li>
				</ul>
				
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane" id="tab-animals">
						<table id="results-animals" class="table table-striped table-bordered table-responsive searchResults">
							<thead>
								<th>ID</th>
								<th>Name</th>
								<th>Pet Name</th>
								<th>Reg No.</th>
								<th>Breed</th>
								<th>Microchip</th>
								<th>Colour</th>
								<th>Owner</th>
							</thead>
							<tbody>
							<?php 
							foreach ($allResults['animals'] as $row){
								echo '
								<tr>
									<td>'.$row->ID.'</td>
									<td>'.$row->RegisteredName.'</td>
									<td>'.$row->PetName.'</td>
									<td>'.$row->RegistrationNo.'</td>
									<td>'.$row->Breed.'</td>
									<td>'.$row->TattooOrChip.'</td>
									<td>'.$row->Colour.'</td>
									<td>'.$row->FullName.'</td>
								</tr>';
							}						
							?>
							</tbody>
						</table>
					</div>
					<div role="tabpanel" class="tab-pane" id="tab-clients">
						<table id="results-clients" class="table table-striped table-bordered table-responsive searchResults">
							<thead>
								<th>ID</th>
								<th>Name</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Town</th>
								<th>Postcode</th>
								<th>Country</th>
							</thead>
							<tbody>
							<?php 
							foreach ($allResults['clients'] as $row){
								echo '
								<tr>
									<td>'.$row->ID.'</td>
									<td>'.$row->FullName.'</td>
									<td>'.$row->Email.'</td>
									<td>'.$row->Tel.'</td>
									<td>'.$row->Town.'</td>
									<td>'.$row->Postcode.'</td>
									<td>'.$row->Country.'</td>
								</tr>';
							}						
							?>
							</tbody>
						</table>
					</div>
					<div role="tabpanel" class="tab-pane" id="tab-orders">
						<pre><?php print_r($allResults['orders']); ?></pre>
					</div>
				</div>	
		<?php } ?>	
	<?php } ?>
		
		</div>
	</section>

<?php 
function footer_js(){ 
	global $most_results;
?>
<script>
jQuery(document).ready(function($) {
	$('a[href="#tab-<?php echo $most_results; ?>"]').tab('show');
	
	$('#resultsTabs a').click(function(e){
		e.preventDefault();
		if ($(this).parent().hasClass('disabled')){
			return false;
		}
		$(this).tab('show');
	});
	
	$('#resultsTabs a').on('shown.bs.tab', function(e){
		//console.log("here");
		//console.log($.fn.dataTable.tables);
		$.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
	});

	var dtOpts = {
	        select : true,
	        order : [ [ 1, 'asc' ] ],
	        paging: false,
	        bFilter: false,
	        scrollY: 300,
	        scrollCollapse: true,
	        columnDefs : [ {
	                targets : [ 0 ],
	                visible : false
	        } ]
		};

	var tableAnimals = $('#results-animals').DataTable(dtOpts);
	tableAnimals.on('select', function(e, dt, type, indexes) {
        if (tableAnimals.rows('.selected').data().length === 1) {
            var rowData = tableAnimals.rows(indexes).data().toArray();
            var page = ($(tableAnimals.table().node()).attr('id')).replace("results-", "");
            var url = "/"+page+"/view?id="+rowData[0][0];
            window.location.href = url;
        } 
	});	
	
	var tableClients = $('#results-clients').DataTable(dtOpts);
	tableClients.on('select', function(e, dt, type, indexes) {
        if (tableClients.rows('.selected').data().length === 1) {
            var rowData = tableClients.rows(indexes).data().toArray();
            var page = ($(tableClients.table().node()).attr('id')).replace("results-", "");
            var url = "/"+page+"/view?id="+rowData[0][0];
            window.location.href = url;
        } 
	});	
	
	var tableOrders = $('#results-orders').DataTable(dtOpts);
	tableOrders.on('select', function(e, dt, type, indexes) {
        if (tableOrders.rows('.selected').data().length === 1) {
            var rowData = tableOrders.rows(indexes).data().toArray();
            var page = ($(tableOrders.table().node()).attr('id')).replace("results-", "");
            var url = "/"+page+"/view?id="+rowData[0][0];
            window.location.href = url;
        } 
	});	
})
</script>
<?php }
add_action('wp_footer', 'footer_js', 100, 1); ?>

<?php get_footer(); ?>
