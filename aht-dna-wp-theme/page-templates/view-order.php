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
		else{ $wpdb->insert('client', $data); }
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
			foreach ( $test_details as $test){
				
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
	<section class="row well" style="margin-top:15px;">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<!-- <button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="order" disabled="disabled" data-toggle="modal" data-target="#orderModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button> -->
					<h3 class="panel-title"><i class="fa fa-flask"></i>&nbsp;Test Details</h3>
				</div>
				<div class="panel-body" id="details_order">
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="client" disabled="disabled" data-toggle="modal" data-target="#clientModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
					<h3 class="panel-title"><i class="fa fa-user"></i>&nbsp;Client Details</h3>
				</div>
				<div class="panel-body" id="details_client">
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="animal" disabled="disabled" data-toggle="modal" data-target="#animalModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
					<h3 class="panel-title"><i class="fa fa-paw"></i>&nbsp;Animal Details</h3>
				</div>
				<div class="panel-body" id="details_animal">
				</div>
			</div>
		</div>
	</section>


	<!-- <div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="orderModalLabel"><i class="fa fa-flask"></i>&nbsp;Test Details</h2>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label  class="control-label col-sm-2">Order#</label>
							<div class="col-sm-4"><p class="form-control-static"><?php echo $order_id; ?></p></div>
							<label  class="control-label col-sm-2">Test#</label>
							<div class="col-sm-4"><p class="form-control-static" id="test_id"></p></div>
						</div>
						<div class="form-group">
							<label for="test_test_name" class="control-label col-sm-2">Test</label>
							<div class="col-sm-10"><p class="form-control-static" id="test_test_name"></p></div>
						</div>
						<div class="form-group">
							<label for="test_breed" class="control-label col-sm-2">Breed</label>
							<div class="col-sm-10"><p class="form-control-static" id="test_breed"></p></div>
						</div>
						<div>
						Progress...
						</div>			
					</div>
					<div class="modal-footer">
						<input type="hidden" id="test_id" name="test_id" value="">
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>-->
	
	<div class="modal fade" id="clientModal" tabindex="-1" role="dialog" aria-labelledby="clientModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="clientModalLabel"><i class="fa fa-user"></i>&nbsp;Client Details</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label for="client_name" class="control-label col-sm-2">Name</label>
									<div class="col-sm-10"><input type="text" class="form-control" id="client_FullName" name="client_FullName" value=""></div>
								</div>
								<div class="form-group">
									<label for="client_email" class="control-label col-sm-2">Email</label>
									<div class="col-sm-4"><input type="email" class="form-control" id="client_Email" name="client_Email" value=""></div>
									<label for="client_phone" class="control-label col-sm-2">Phone</label>
									<div class="col-sm-4"><input type="tel" class="form-control" id="client_Tel" name="client_Tel" value=""></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="well">
									<h3 style="margin-top: 0px;margin-bottom: 20px;">Home Address</h3>
									<div class="form-group">
										<label for="client_address" class="control-label col-sm-2">Address</label>
										<div class="col-sm-10"><input type="text" class="form-control" id="client_Address" name="client_Address" value=""></div>
									</div>
									<div class="form-group">
										<label for="client_town" class="control-label col-sm-2">Town</label>
										<div class="col-sm-5 "><input type="text" class="form-control" id="client_Town" name="client_Town" value=""></div>
										<label for="client_postcode" class="control-label col-sm-2">Postcode</label>
										<div class="col-sm-3"><input type="text" class="form-control" id="client_Postcode" name="client_Postcode" value=""></div>
									</div>
									<div class="form-group">
										<label for="client_county" class="control-label col-sm-2">County</label>
										<div class="col-sm-5 "><input type="text" class="form-control" id="client_County" name="client_County" value=""></div>
										<label for="client_country" class="control-label col-sm-2">Country</label>
										<div class="col-sm-3"><input type="text" class="form-control" id="client_Country" name="client_Country" value=""></div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="well">
									<h3 style="margin-top: 0px;margin-bottom: 20px;">Delivery Address</h3>
									<div class="form-group">
										<label for="client_address" class="control-label col-sm-2">Address</label>
										<div class="col-sm-10"><input type="text" class="form-control" id="client_ShippingAddress" name="client_ShippingAddress" value=""></div>
									</div>
									<div class="form-group">
										<label for="client_town" class="control-label col-sm-2">Town</label>
										<div class="col-sm-5 "><input type="text" class="form-control" id="client_ShippingTown" name="client_ShippingTown" value=""></div>
										<label for="client_postcode" class="control-label col-sm-2">Postcode</label>
										<div class="col-sm-3"><input type="text" class="form-control" id="client_ShippingPostcode" name="client_ShippingPostcode" value=""></div>
									</div>
									<div class="form-group">
										<label for="client_county" class="control-label col-sm-2">County</label>
										<div class="col-sm-5 "><input type="text" class="form-control" id="client_ShippingCounty" name="client_ShippingCounty" value=""></div>
										<label for="client_country" class="control-label col-sm-2">Country</label>
										<div class="col-sm-3"><input type="text" class="form-control" id="client_ShippingCountry" name="client_ShippingCountry" value=""></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="client_id" name="client_id" value="">
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" name="client-submitted">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="animalModal" tabindex="-1" role="dialog" aria-labelledby="animalModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="animalModalLabel"><i class="fa fa-paw"></i>&nbsp;Animal Details</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label for="animal_RegisteredName" class="control-label col-sm-2">Name</label>
									<div class="col-sm-10"><input type="text" class="form-control" id="animal_RegisteredName" name="animal_RegisteredName" value=""></div>
								</div>
								<div class="form-group">
									<label for="animal_RegistrationNo" class="control-label col-sm-2">Reg No.</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_RegistrationNo" name="animal_RegistrationNo" value=""></div>
									<label for="animal_PetName" class="control-label col-sm-2">Pet Name</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_PetName" name="animal_PetName" value=""></div>
								</div>
								<div class="form-group">
									<label for="animal_BirthDate" class="control-label col-sm-2">DOB</label>
									<div class="col-sm-4"><input type="text" value="" name="animal_BirthDate" id="animal_BirthDate" class="form-control datepick" autocomplete="off" placeholder="dd/mm/yyyy"></div>
									<label class="control-label col-sm-2">Sex</label>
									<div class="col-sm-4">
										<div class="radioerror"></div>
										<label class="radio-inline"><input type="radio" id="animal_Sex-Male" class="radiorequired" value="Male" name="animal_Sex" checked="checked"> Male</label>
										<label class="radio-inline"><input type="radio" id="animal_Sex-Female" class="radiorequired" value="Female" name="animal_Sex"> Female</label>
									</div>
								</div>
								<div class="form-group">
									<label for="animal_TattooOrChip" class="control-label col-sm-2">Microchip</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_TattooOrChip" name="animal_TattooOrChip" value=""></div>
									<label for="animal_Colour" class="control-label col-sm-2">Colour</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_Colour" name="animal_Colour" value=""></div>
								</div>
							</div>
						</div>					
					</div>
					<div class="modal-footer">
						<input type="hidden" id="animal_id" name="animal_id" value="">
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" name="animal-submitted">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>

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