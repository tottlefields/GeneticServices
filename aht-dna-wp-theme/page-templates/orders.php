<?php /* Template Name: Orders */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-9">
	
	<?php
	global $wpdb;

	$sql = "select orders.id as ID, OrderDate, ReportFormat, FullName, Email, ShippingCountry, count(*) as TestCount 
	from orders left outer join client on client.id=client_id 
	left outer join order_tests on orders.id=order_id where kit_sent is null group by orders.id limit 50";
	$results = $wpdb->get_results($sql, OBJECT );
	
	if ( $results ){
	?>
		<table id="orders" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
			<thead>
				<th></th>
				<th>OrderID</th>
				<th>Date</th>
				<th>Report Format</th>
				<th>Client</th>
				<th>#Tests</th>
			</thead>
			<tbody>
		
		<?php
		foreach ( $results as $order ){
			$client = $order->FullName;
			if ($order->Email != ''){
				$client = '<a href="mailto:'.$order->Email.'">'.$order->FullName.'</a>';
			}
			echo '
			<tr>
				<td></td>
				<td>'.$order->ID.'</td>
				<td>'.$order->OrderDate.'</td>
				<td>'.$order->ReportFormat.'</td>
				<td>'.$client.'</td>
				<td>'.$order->TestCount.'</td>
			</tr>';
		}
		?>
			</tbody>
		</table>
		
	<?php
	}
	?>
		</div>
		<div class="col-md-3" id="order_details">
		</div>
	</section>

<?php get_footer(); ?>
