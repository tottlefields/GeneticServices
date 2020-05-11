<?php /* Template Name: Orders */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><a href="<?php echo get_site_url(); ?>/orders/add-manual-order/" class="btn btn-default btn-sm" style="margin-left:10px;margin-bottom:3px;">Add Order</a><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-8">
	
	<?php
	global $wpdb;

	$sql = "select o.OrderID as webshop_id, o.id as ID, o.paid, OrderDate, ReportFormat, client_id, FullName, Email, o.ShippingCountry, count(*) as TestCount, sum(no_swabs) as SwabCount, content
	from orders o left outer join client on client.id=client_id 
	left outer join order_tests on o.id=order_id 
	left outer join test_codes using (test_code)
	where cancelled_date is null and kit_sent is null 
	group by o.id order by OrderDate desc";
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
				<th>Client</th>
				<th class="text-center">#Swabs</th>
				<th class="text-center">Tests</th>
				<th class="text-center">PDF(s)</th>
			</thead>
			<tbody>
		
		<?php
		foreach ( $results as $order ){
			$row_class = ($order->paid) ? '' : 'danger';
			$client = '';
			if ($order->client_id > 0){
				$client = '<a href="'.get_site_url().'/clients/view?id='.$order->client_id.'"><i class="fas fa-user" aria-hidden="true"></i>'.$order->FullName.'</a>';
				if ($order->Email != ''){
					$client .= '&nbsp;<a href="mailto:'.$order->Email.'"><i class="far fa-envelope" aria-hidden="true"></i></a>';
				}
			}
			$order_date = new DateTime($order->OrderDate);
			echo '
			<tr class="'.$row_class.'">
				<td class="text-center">'.$order->ID.'</td>
				<td class="text-center"><input type="checkbox" class="checkboxRow" name="orderList[]" value="'.$order->ID.'" /></td>
				<td class="text-center"><a href="'.get_site_url().'/orders/view?id='.$order->ID.'">AHT'.$order->ID.'</a></td>
				<td class="text-center">'.$order->webshop_id.'</td>
				<td class="text-center">'.$order_date->format('d/m/Y').'</td>
				<td>'.$client.'</td>
				<td class="text-center">'.$order->SwabCount.'</td>
				<td class="text-center">'.$order->content.'</td>
				<td class="text-center">
                    <a href="javascript:generatePDFs(\''.$order->ID.'\', null, 1)"><i class="far fa-file-pdf link" id="pdf_'.$order->ID.'"></i></a>';
			if (strstr($order->content, ':CPA')){ 
			    echo '&nbsp;<a href="javascript:generateParentagePDF(\''.$order->ID.'\', null, 1)"><i class="fas fa-users link" id="par_'.$order->ID.'"></i></a>'; 
			}
			echo '                
			    </td>
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
