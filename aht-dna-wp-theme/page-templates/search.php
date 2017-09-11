<?php /* Template Name: Search */ ?>
<?php 
//process search...
global $wpdb;

$query = $_REQUEST['q'];

if (preg_match('/^[a-zA-Z]{2,5}\d{1,3}$/', $query)){
	//Portal ID - between 2 & 5 letters followed by between 1 and 3 digits
	$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, ShippingCountry, count(*) as TestCount 
			from orders left outer join client on client.id=client_id 
			left outer join order_tests on orders.id=order_id
			WHERE PortalID='".$query."'";
	$results = $wpdb->get_results($sql);
	if (count($results) == 1){
		wp_redirect(get_site_url().'/orders/view?id='.$results[0]->ID);
		exit;	
	}
}

if (preg_match('/^\d{1,5}\/\d{1,5}$/', $query)){
	//Swab ID - between 1 & 5 digits [OrderID] followed by between 1 and 5 digits [SwabID], seperated with '/'
	$sample_details = explode('/', $query);
	$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, ShippingCountry, count(*) as TestCount 
			from orders left outer join client on client.id=client_id 
			left outer join order_tests on orders.id=order_id
			WHERE orders.id='".$sample_details[0]."'";
	$results = $wpdb->get_results($sql);
	if (count($results) == 1){
		wp_redirect(get_site_url().'/orders/view?id='.$results[0]->ID);
		exit;	
	}
}

if (preg_match('/^9\d{14}$/', $query)){
	//Microchip
	$sql = "select * from animal where TattooOrChip ='".$query."'";
	$results = $wpdb->get_results($sql);
	if (count($results) == 1){
		wp_redirect(get_site_url().'/animals/view?id='.$results[0]->id);
		exit;	
	}
}

if (preg_match('/^KC[a-zA-Z]{2}\d{7,9}$/', $query) || preg_match('/^[a-zA-Z]{2}\d{7,9}$/', $query) || preg_match('/^K\d{6}$/', $query)){
	//Registration No.
	$sql = "select * from animal where RegistrationNo ='".$query."'";
	$results = $wpdb->get_results($sql);
	if (count($results) == 1){
		wp_redirect(get_site_url().'/animals/view?id='.$results[0]->id);
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
			<h2>Results</h2>
		
		<pre><?php print_r($_REQUEST); ?></pre>
		
		
		</div>
	</section>

<?php get_footer(); ?>