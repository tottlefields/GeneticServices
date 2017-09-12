<?php /* Template Name: View Order */ ?>
<?php 
if(empty($_REQUEST) || !isset($_REQUEST['id'])){
	wp_redirect(get_site_url().'/orders/');
	exit;	
}
$order_id = $_REQUEST['id'];

$orders = orderSearch(array('id' => $order_id));
$order_details = $orders[0];
$test_details = getTestsByOrder($order_id);

$this_order_status = array();
$this_order_status = array_pad($this_order_status, count($order_steps), "");

if (isset($order_details->OrderDate)){
	$myDate = new DateTime($order_details->OrderDate);
	$this_order_status[0] = $myDate->format('d/m/y');
}

$kit_sent = array();
$returned_date = array();
foreach ($test_details as $result){
	$test = $result[0];
	
	$result[0]->order_status = $order_steps[0];
	
	if ($test->kit_sent == ''){
		array_push($kit_sent, $test->kit_sent);
	}
	else{
		$sentDate = new DateTime($test->kit_sent);
		array_push($kit_sent, $sentDate->format('d/m/y'));
		$result[0]->order_status = $order_steps[1];
	}
	
	if ($test->returned_date == ''){
		array_push($returned_date, $test->returned_date);		
	}
	else {
		$returnedDate = new DateTime($test->returned_date);
		array_push($returned_date, $returnedDate->format('d/m/y'));
		$result[0]->order_status = $order_steps[2];
	}
}

if (in_array('', $kit_sent)){ $this_order_status[1] = ''; } else { $this_order_status[1] = max($kit_sent); }
if (in_array('', $returned_date)){ $this_order_status[2] = ''; } else { $this_order_status[2] = max($returned_date); }
	
?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?> #<?php echo $order_id; ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	
	<section class="row">
		<div class="col-sm-12">
		<div class="sw-theme-arrows">
			<ul class="nav nav-tabs nav-justified step-anchor">
			<?php for ($i=0; $i<count($order_steps); $i++){
				if($this_order_status[$i] == ''){
					echo '<li><span>'.$order_steps[$i].'<br /><small>&nbsp;</small></span></li>';
				}
				else{
					$class = 'done';
					if(isset($this_order_status[$i+1]) && $this_order_status[$i+1] == ''){
						$class = 'active';
					}
				//	elseif($i+1 == count($order_steps)){
				//		
				//	}
					echo '<li class="'.$class.'"><span>'.$order_steps[$i].'<br /><small>'.$this_order_status[$i].'</small></span></li>';
				}
			}?>
			</ul>
		</div>
		</div>
	</section>

	<section class="row">
		<div class="col-md-12" style="margin-top:15px;">
		<?php if ( $test_details ){ ?>
			<table id="orderDetails" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
				<thead>
					<th></th>
					<th></th>
					<th class="text-center">SwabID</th>
					<th>Test</th>
					<th>Breed</th>
					<!-- <th>Report Format</th> -->
					<th>Animal</th>
					<th>Client</th>
					<th class="text-center">Status</th>
					<th class="text-center">Result</th>
					<th class="text-center">Actions</th>
				</thead>
				<tbody>
		
			<?php
			$test_count = 0;
			foreach ( $test_details as $result){
				$test = $result[0];
				
				$client = '<a href="'.get_site_url().'/clients/view?id='.$order_details->client_id.'"><i class="fa fa-user" aria-hidden="true"></i>'.$order_details->FullName.'</a>';
				if ($order_details->Email != ''){
					$client .= '&nbsp;<a href="mailto:'.$order_details->Email.'"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
				}
				
				$animal = '<a href="'.get_site_url().'/animals/view?id='.$test->animal_id.'"><i class="fa fa-paw" aria-hidden="true"></i>'.$test->RegisteredName.'</a>';
				
				$portalID = ($test->PortalID == '') ? '<span style="color:#BBBBBB">N/A</span>' : $test->PortalID;
				
				echo '
				<tr>
					<td>'.$order_id.'</td>
					<td>'.$test->id.'</td>
					<td class="text-center">'.$test->id.'<span class="hidden-sm hidden-xs">&nbsp;<em><small>'.$test->PortalID.'</small></em></td>
					<td>'.$test->test_name.'</td>
					<td>'.$test->breed.'</td>
					<!-- <td>'.$order->ReportFormat.'</td>-->
					<td>'.$animal.'</td>
					<td>'.$client.'</td>
					<td class="text-center"><span class="label label-info">'.str_replace('(s)', '', $test->order_status).'</span></td>
					<td></td>
					<td></td>
				</tr>';
				$test_count++;
			}
			?>
				</tbody>
			</table>
			
		<?php
		}
		?>
		</div>
	</section>
	<section class="row">
		<div class="col-md-4"></div>
		<div class="col-md-4"></div>
		<div class="col-md-4"></div>
	</section>

<?php
function footer_js(){ ?>
<script>
jQuery(document).ready(function($) {
	console.log("ready!");
	var table = $('#orderDetails').DataTable({
		select : true,
		order : [ [ 1, 'desc' ] ],
		"ordering": false,
		"paging": false,
		columnDefs : [ {
			targets : [ 0,1 ],
			visible : false
		} ]
	});

	table.on('select', function(e, dt, type, indexes) {
		if (table.rows('.selected').data().length === 1) {
			var rowData = table.rows(indexes).data().toArray();
			console.log(rowData[0][0]);
			getOrders(rowData[0][0], details);
		} else {
			details.empty();
		}
	}).on('deselect', function(e, dt, type, indexes) {
		//details.empty();
	});
})
</script>
<?php } 
add_action('wp_footer', 'footer_js', 100); ?>

<?php get_footer(); ?>