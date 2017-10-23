<?php /* Template Name: View Order */ ?>
<?php 
if(empty($_REQUEST) || !isset($_REQUEST['id'])){
	wp_redirect(get_site_url().'/orders/');
	exit;	
}

global $wpdb;

if (isset($_REQUEST['order_id'])){
	
	if (isset($_REQUEST['client-submitted'])){
		
		$data = array();
		$where = array();
		foreach ($_REQUEST as $key => $value){
			if (preg_match('/^client_/', $key)){
				$new_key = str_replace('client_', '', $key);
				if ($new_key === 'id'){ $where['id'] = $value;}
				elseif (preg_match('/Address/', $new_key)){
					$address = array_pad(explode(', ', $value), 3, '');
					$data[$new_key."3"] = array_pop($address);
					$data[$new_key."2"] = array_pop($address);
					$data[$new_key] = implode(', ', $address);
				}
				else{
					$data[$new_key] = $value;
				}
			}
		}
		if ($_REQUEST['client_id'] > 0){ $wpdb->update('client', $data, $where); }
		else{
			$wpdb->insert('client', $data);
			$client_id = $wpdb->insert_id;
			$wpdb->update('orders', array('client_id' => $client_id), array('id' => $_REQUEST['id']));
		}
		wp_redirect(get_site_url().'/orders/view/?id='.$_REQUEST['id']);
		exit;	
	}
	
	if (isset($_REQUEST['animal-submitted'])){
		
		$data = array();
		$where = array();
		foreach ($_REQUEST as $key => $value){
			if (preg_match('/^animal_/', $key)){
				$new_key = str_replace('animal_', '', $key);
				if ($new_key === 'id'){ $where['id'] = $value;}
				elseif (preg_match('/Sex/', $new_key)){
					$value = substr($value, 0, 1);
					$data['Sex'] = $value;
				}
				elseif (preg_match('/BirthDate/', $new_key)){
					$data[$new_key] = dateToSQL($value);
				}
				else{
					$data[$new_key] = $value;
				}
			}
		}
		$wpdb->update('animal', $data, $where);
		wp_redirect(get_site_url().'/orders/view/?id='.$_REQUEST['id']);
		exit;	
		
	}
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
foreach ($test_details as $test){

	$test->order_status = $order_steps[0];
	
	if ($test->kit_sent == ''){
		array_push($kit_sent, $test->kit_sent);
	}
	else{
		$sentDate = new DateTime($test->kit_sent);
		array_push($kit_sent, $sentDate->format('d/m/y'));
		$test->order_status = $order_steps[1];
	}
	
	if ($test->returned_date == ''){
		array_push($returned_date, $test->returned_date);		
	}
	else {
		$returnedDate = new DateTime($test->returned_date);
		array_push($returned_date, $returnedDate->format('d/m/y'));
		$test->order_status = $order_steps[2];
	}
	
	if($test->cancelled_date != ''){
		$test->order_status = 'Cancelled';
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
					<th class="text-center">TestID</th>
					<th>Test</th>
					<th>Breed</th>
					<th>Animal</th>
					<th>Client</th>
					<th class="text-center">Status</th>
					<th class="text-center">Result</th>
					<th class="text-center">Actions</th>
				</thead>
				<tbody>
		
			<?php
			$test_count = 0;
			foreach ( $test_details as $test){
				
				$client = '<a href="'.get_site_url().'/clients/view?id='.$order_details->client_id.'"><i class="fa fa-user" aria-hidden="true"></i>'.$order_details->FullName.'</a>';
				if ($order_details->Email != ''){
					$client .= '&nbsp;<a href="mailto:'.$order_details->Email.'"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
				}
				
				$animal = '<a href="'.get_site_url().'/animals/view?id='.$test->animal_id.'"><i class="fa fa-paw" aria-hidden="true"></i>'.$test->RegisteredName.'</a>';
				
				$portalID = ($test->PortalID == '') ? '<span style="color:#BBBBBB">N/A</span>' : $test->PortalID;
				
				$other_id = '';
				if (isset($test->PortalID)){ $other_id = $test->PortalID; }
				elseif (isset($test->OrderID)) { $other_id = $test->OrderID; }
				
				$status_label = '<span class="label label-info">'.str_replace('(s)', '', $test->order_status).'</span>';
				if ($test->order_status == 'Cancelled'){ $status_label = '<span class="label label-danger">'.str_replace('(s)', '', $test->order_status).'</span>'; $class_disabled = ' disabled'; }
				
				$next_action = '';
				switch ($test->order_status) {
					case 'Order Placed':
						$next_action = '<li><a href="javascript:sendSample(\''.$test->id.'\')"><i class="fa fa-paper-plane-o link"></i>&nbsp;Dispatch Sample</a></li>';
						break;
					case 'Kit(s) Dispatched':
						$next_action = '<li><a href="javascript:receiveSample(\''.$test->id.'\')"><i class="fa fa-check-square-o link"></i>&nbsp;Receive Sample</a></li>';
						break;
				}
				
				echo '
				<tr>
					<td>'.$order_id.'</td>
					<td>'.$test->id.'</td>
					<td class="text-center">'.$test->id.'<span class="hidden-sm hidden-xs">&nbsp;<em><small>'.$other_id.'</small></em></td>
					<td>'.$test->test_name.'</td>
					<td>'.$test->breed.'</td>
					<td>'.$animal.'</td>
					<td>'.$client.'</td>
					<td class="text-center">'.$status_label.'</td>
					<td></td>
					<td class="text-center">
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-xs dropdown-toggle'.$class_disabled.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <span class="caret"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">
								<li><a href="javascript:generatePDFs(\''.$order_id.'\',\''.$test->id.'\')"><i class="fa fa-file-pdf-o link"></i>&nbsp;Print Order</a></li>
								<li><a href="javascript:cancelTest(\''.$test->id.'\')"><i class="fa fa-ban link"></i>&nbsp;Cancel Test</a></li>
								'.$next_action.'
								<!--<li><a href="#">Something else here</a></li>
								<li role="separator" class="divider"></li>
								<li><a href="#">Separated link</a></li>-->
							</ul>
						</div>
					</td>
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
	<section class="row well" style="margin-top:15px;">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-flask"></i>&nbsp;Test Details</h3>
				</div>
				<div class="panel-body" id="details_order">
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
				<?php if (current_user_can('editor') || current_user_can('administrator')) { ?>
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="client" disabled="disabled" data-toggle="modal" data-target="#clientModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
				<?php } ?>
					<h3 class="panel-title"><i class="fa fa-user"></i>&nbsp;Client Details</h3>
				</div>
				<div class="panel-body" id="details_client">
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
				<?php if (current_user_can('editor') || current_user_can('administrator')) { ?>
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="animal" disabled="disabled" data-toggle="modal" data-target="#animalModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
				<?php } ?>
					<h3 class="panel-title"><i class="fa fa-paw"></i>&nbsp;Animal Details</h3>
				</div>
				<div class="panel-body" id="details_animal">
				</div>
			</div>
		</div>
	</section>
	
<?php get_template_part('part-templates/modal', 'clientDelivery'); ?>
<?php get_template_part('part-templates/modal', 'animal'); ?>

<?php
function footer_js(){ ?>
<script>
jQuery(document).ready(function($) {
	   
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
			getTestDetails(rowData[0][0], rowData[0][1], $('#details_order'), $('#details_client'), $('#details_animal'));
			$('.details-btn').prop('disabled', false);
		} else {
			$('.details-btn').prop('disabled', true);
			$('#details_order').empty();
			$('#details_client').empty();
			$('#details_animal').empty();
		}
	}).on('deselect', function(e, dt, type, indexes) {
		$('.details-btn').prop('disabled', true);
		$('#details_order').empty();
		$('#details_client').empty();
		$('#details_animal').empty();
	});
})
</script>
<?php } 
add_action('wp_footer', 'footer_js', 100); ?>

<?php get_footer(); ?>
