<?php /* Template Name: View Animal */ ?>
<?php
if(empty($_REQUEST) || !isset($_REQUEST['id'])){
	//wp_redirect(get_site_url().'/orders/');
	exit;	
}
$animal_id = $_REQUEST['id'];

$animals = animalSearch(array('id' => $animal_id));
$animal_details = $animals[0];
$name = (isset($animal_details->RegisteredName)) ? $animal_details->RegisteredName.' <em>('.$animal_details->PetName.')</em>' : $animal_details->PetName;

$clients = clientSearch(array('id' => $animal_details->client_id));
$client_details = $clients[0];

$test_details = getTestsByAnimal($animal_id);

?>
<?php get_header(); ?>

	<h1><i class="fa fa-paw"></i>&nbsp;<?php echo $name; ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
			
	<section class="row">
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
				<?php if (current_user_can('editor') || current_user_can('administrator')) { ?>
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="animal" data-toggle="modal" data-target="#animalModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
				<?php } ?>
					<h3 class="panel-title"><i class="fa fa-paw"></i>&nbsp;Animal Details</h3>
				</div>
				<div class="panel-body" id="details_animal">
					<?php debug_array($animal_details); ?>
				</div>
			</div>
		</div>
	
		<div class="col-md-9">
		<?php if ( $test_details ){ ?>
			<table id="orderDetails" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
				<thead>
					<th></th>
					<th class="text-center">OrderID</th>
					<th class="text-center">Order Date</th>
					<th class="text-center">TestID</th>
					<th>Test</th>
					<th>Client</th>
					<th class="text-center">Status</th>
					<th class="text-center">Result</th>
					<th class="text-center">Actions</th>
				</thead>
				<tbody>
		
			<?php
			$test_count = 0;
			foreach ( $test_details as $test){
				$test->order_status = $order_steps[0];
				if($test->kit_sent != ''){$test->order_status = $order_steps[1];}
				if($test->returned_date != ''){$test->order_status = $order_steps[2];}				
				if($test->cancelled_date != ''){ $test->order_status = 'Cancelled'; }
				
				$status_label = '<span class="label label-info">'.str_replace('(s)', '', $test->order_status).'</span>';
				if ($test->order_status == 'Cancelled'){ $status_label = '<span class="label label-danger">'.str_replace('(s)', '', $test->order_status).'</span>'; $class_disabled = ' disabled'; }
								
				$client = '<a href="'.get_site_url().'/clients/view?id='.$client_details->id.'"><i class="fa fa-user" aria-hidden="true"></i>'.$client_details->FullName.'</a>';
				if ($client_details->Email != ''){
					$client .= '&nbsp;<a href="mailto:'.$client_details->Email.'"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
				}
				
				$portalID = ($test->PortalID == '') ? '<span style="color:#BBBBBB">N/A</span>' : $test->PortalID;
				$other_id = '';
				if (isset($test->PortalID)){ $other_id = $test->PortalID; }
				elseif (isset($test->OrderID)) { $other_id = $test->OrderID; }
				
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
					<td>'.$test->id.'</td>
					<td class="text-center"><a href="'.get_site_url().'/orders/view?id='.$test->order_id.'">AHT'.$test->order_id.'</a></td>
					<td class="text-center">'.$test->order_date.'</td>
					<td class="text-center">'.$test->id.'<span class="hidden-sm hidden-xs">&nbsp;<em><small>'.$other_id.'</small></em></td>
					<td>'.$test->test_name.'</td>
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
	
<?php get_template_part('part-templates/modal', 'animal'); ?>

<?php
function footer_js(){
	global $animal_details;
?>
<script>
jQuery(document).ready(function($) {
		
	populateAnimalModal(<?php echo json_encode($animal_details); ?>);
	   
	var table = $('#orderDetails').DataTable({
		order : [ [ 0, 'desc' ] ],
		"ordering": false,
		"paging": false,
		columnDefs : [ {
			targets : [ 0 ],
			visible : false
		} ]
	});
})
</script>
<?php } 
add_action('wp_footer', 'footer_js', 100); ?>

<?php get_footer(); ?>