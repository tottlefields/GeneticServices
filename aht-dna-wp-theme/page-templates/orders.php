<?php /* Template Name: Orders */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-8">
	
	<?php
	global $wpdb;

	$sql = "select orders.OrderID as webshop_id, orders.id as ID, OrderDate, ReportFormat, client_id, FullName, Email, ShippingCountry, count(*) as TestCount 
	from orders left outer join client on client.id=client_id 
	left outer join order_tests on orders.id=order_id where kit_sent is null group by orders.id order by OrderDate desc";
	$results = $wpdb->get_results($sql, OBJECT );
	
	if ( $results ){
	?>
		<table id="orders" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
			<thead>
				<th></th>
				<td class="text-center"><input type="checkbox" id="checkAll" /></td>
				<th class="text-center">OrderID</th>
				<th class="text-center">Webshop</th>
				<th class="text-center">Date</th>
				<!-- <th>Report Format</th> -->
				<th>Client</th>
				<th class="text-center">#Tests</th>
				<th class="text-center">PDF</th>
			</thead>
			<tbody>
		
		<?php
		foreach ( $results as $order ){
			$client = '<a href="'.get_site_url().'/clients/view?id='.$order->client_id.'"><i class="fa fa-user" aria-hidden="true"></i>'.$order->FullName.'</a>';
			if ($order->Email != ''){
				$client .= '&nbsp;<a href="mailto:'.$order->Email.'"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
			}
			$order_date = new DateTime($order->OrderDate);
			echo '
			<tr>
				<td class="text-center">'.$order->ID.'</td>
				<td class="text-center"><input type="checkbox" class="checkboxRow" name="orderList[]" value="'.$order->ID.'" /></td>
				<td class="text-center"><a href="'.get_site_url().'/orders/view?id='.$order->ID.'">AHT'.$order->ID.'</a></td>
				<td class="text-center">'.$order->webshop_id.'</td>
				<td class="text-center">'.$order_date->format('d/m/Y').'</td>
				<!-- <td>'.$order->ReportFormat.'</td>-->
				<td>'.$client.'</td>
				<td class="text-center">'.$order->TestCount.'</td>
				<td class="text-center"><a href="javascript:generatePDFs(\''.$order->ID.'\')"><i class="fa fa-file-pdf-o link" id="pdf_'.$order->ID.'"></i></a></td>
			</tr>';
		}
		?>
			</tbody>
		</table>
		
	<?php
	}
	?>
		</div>
		<div class="col-md-4">
			<div class="well"  id="order_details"></div>
		</div>
	</section>

<?php get_footer(); ?>
