<?php /* Template Name: View Animal */ ?>
<?php get_template_part('part-templates/modal-updates'); ?>
<?php
if(empty($_REQUEST) || !isset($_REQUEST['id'])){
	//wp_redirect(get_site_url().'/orders/');
	exit;	
}
$animal_id = $_REQUEST['id'];

$animals = animalSearch(array('id' => $animal_id));
$animal_details = $animals[0];
$name = $animal_details->PetName;
if (isset($animal_details->RegisteredName)){
    $name = stripslashes($animal_details->RegisteredName);
    if (isset($animal_details->PetName) && $animal_details->PetName != '') { $name .= ' <em>('.$animal_details->PetName.')</em>'; }
}
$animal_details->animal_id = $animal_details->id;

$clients = clientSearch(array('id' => $animal_details->client_id));
$client_details = $clients[0];

$test_details = getTestsByAnimal($animal_id);

?>
<?php get_header(); ?>

	<h1><i class="fa fa-paw"></i>&nbsp;<?php echo $name; ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
			
	<section class="row">
		<div class="col-md-5 col-lg-3">
			<div class="panel panel-default">
				<div class="panel-heading">
				<?php if (current_user_can('editor') || current_user_can('administrator')) { ?>
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit hide-small" id="animal" data-toggle="modal" data-target="#animalModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
				<?php } ?>
					<h3 class="panel-title"><i class="fa fa-paw"></i>&nbsp;Animal Details</h3>
				</div>
				<div class="panel-body" id="details_animal">
					<?php
					echo '
					<div class="row"><div class="col-xs-4">Name</div><div class="col-xs-8"><a href="/animals/view?id='.$animal_details->id.'">
						<strong>'.stripslashes($animal_details->RegisteredName).'</strong></a>
					</div></div>
					<div class="row"><div class="col-xs-4">Pet Name</div><div class="col-xs-8">'.$animal_details->PetName.'</div></div>
					<div class="row"><div class="col-xs-4">Reg No.</div><div class="col-xs-8">'.$animal_details->RegistrationNo.'</div></div>
					<div class="row"><div class="col-xs-4">Breed</div><div class="col-xs-8">'.$animal_details->Breed.'</div></div>
					<div class="row"><div class="col-xs-4">DOB</div><div class="col-xs-8">'.$animal_details->DOB.'</div></div>
					<div class="row"><div class="col-xs-4">Sex</div><div class="col-xs-8">'.$animal_details->sex.'</div></div>
					<div class="row"><div class="col-xs-4">Microchip</div><div class="col-xs-8">'.$animal_details->TattooOrChip.'</div></div>
					<div class="row"><div class="col-xs-4">Colour</div><div class="col-xs-8">'.$animal_details->Colour.'</div></div>';
					?>
				</div>
			</div>
		</div>
	
		<div class="col-md-7 col-lg-9">
		<?php if ( $test_details ){ ?>
			<table id="orderDetails" class="table table-striped table-bordered table-responsive nowrap" cellspacing="0" width="100%">
				<thead>
					<th></th>
					<th class="text-center" data-priority="1">OrderID</th>
					<th class="text-center"data-priority="2">Order Date</th>
					<th class="text-center">TestID</th>
					<th data-priority="4">Test</th>
					<th>Client</th>
					<th class="text-center" data-priority="3">Status</th>
					<th class="text-center">Notes</th>
					<th class="text-center"data-priority="4">Result</th>
					<th class="text-center">Actions</th>
				</thead>
				<tbody>
		
			<?php
			$test_count = 0;
			foreach ( $test_details as $test){
				$class_disabled = '';
				
				$test->order_status = $order_steps[0];
				if($test->kit_sent != ''){$test->order_status = $order_steps[1];}
				if($test->returned_date != ''){$test->order_status = $order_steps[2];}				
				if($test->cancelled_date != ''){ $test->order_status = 'Cancelled'; }
				
				$extraction = -1;
				$analysis_done = -1;
				$results_sent = -1;
				
				foreach ($test->swabs as $swab){
				    if($swab->extraction_date != ''){ $extraction = 1; }
				    else{ $extraction = 0; break; }
				}
				
				foreach ($test->analysis as $analysis){
				    if($analysis->result_entered_date != ''){ $analysis_done = 1; }
				    else{ $analysis_done = 0; break; }
				}
				
				foreach ($test->results as $result){
				    if($result->result_reported_date != '' && $result->cert_code != ''){ $results_sent = 1; }
				    else{ $results_sent = 0; break; }
				}
				
				if ($extraction == 1) { $test->order_status = $order_steps[3];}
				if ($analysis_done == 1) { $test->order_status = $order_steps[4];}
				if ($results_sent == 1) { $test->order_status = $order_steps[5];}
				
				
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
				
				$status_label = '<span class="label label-info">'.str_replace('(s)', '', $test->order_status).'</span>';
				if ($test->order_status == 'Cancelled'){
					if (isset($test->repeat_swab) && $test->repeat_swab > 0){ $status_label = '<span class="label label-warning">Repeated (#'.$test->repeat_swab.')</span>'; }
					else { $status_label = '<span class="label label-danger">'.str_replace('(s)', '', $test->order_status).'</span>'; }
					$class_disabled = ' disabled'; 
				}
				
				$actions = array(
				    'print_order'   => '<li><a href="javascript:generatePDFs(\''.$test->order_id.'\',\''.$test->id.'\')"><i class="fa fa-file-pdf-o link"></i>&nbsp;Print Order</a></li>',
				    'cancel_test'   => '<li><a href="javascript:cancelTest(\''.$test->id.'\')"><i class="fa fa-ban link"></i>&nbsp;Cancel Test</a></li>',
				    'repeat'        => '<li><a href="javascript:repeatTest(\''.$test->id.'\')"><i class="fa fa-repeat link"></i>&nbsp;Request Repeat</a></li>',
				    'dispatch'      => '<li><a href="javascript:sendSample(\''.$test->id.'\')"><i class="fa fa-paper-plane-o link"></i>&nbsp;Dispatch Sample</a></li>',
				    'receive'       => '<li><a href="javascript:receiveSample(\''.$test->id.'\')"><i class="fa fa-check-square-o link"></i>&nbsp;Receive Sample</a></li>',
				    'view_certs'    => '<li><a href="javascript:viewCert(\''.$test->order_id.'\',\''.$test->id.'\')"><i class="fa fa-file-pdf-o link"></i>&nbsp;Print Certificate(s)</a></li>'
				);
				$action_menu = '';
				
				switch ($test->order_status) {
				    case 'Order Placed':
				        $action_menu = implode("\n", array($actions['print_order'], $actions['cancel_test'], $actions['dispatch']));
				        break;
				    case 'Kit(s) Dispatched':
				        //$next_action = '<li><a href="javascript:receiveSample(\''.$test->id.'\')"><i class="fa fa-check-square-o link"></i>&nbsp;Receive Sample</a></li>';
				        $action_menu = implode("\n", array($actions['cancel_test'], $actions['receive']));
				        break;
				    case 'Result(s) Sent':
				        $action_menu = implode("\n", array($actions['view_certs']));
				        break;
				}
				
				$notes = '';
				if ($test->note_count > 0){
					foreach ($test->notes as $note){
						$note_date = DateTime::createFromFormat('Y-m-d H:i:s', $note->note_date);
						$note->php_date = $note_date->format('jS M Y (H:i)');
						$note->note_text = base64_decode($note->note_text);
					}
					$note_details["notes_".$test->id] = $test->notes;
					$notes = '<span class="badge notes_badge" id="notes_'.$test->id.'" style="cursor:pointer" data-toggle="modal" data-target="#notesModal">'.$test->note_count.'</span>';
				}
				
				$resultHTML = '';
				if ($test->swab_failed >= 1){
				    $resultHTML = '<span class="label label-default">Failed</span>';
				}
				elseif (count($test->results)>0){
				    foreach ($test->results as $result){
				        if ($resultHTML != ''){ $resultHTML .= '<hr style="height: 1px; margin: 0px; width: 1px;">'; }
				        switch ($result->test_result) {
				            case 'AFFECTED':
				                $label_class = 'danger';
				                break;
				            case 'CARRIER':
				                $label_class = 'warning';
				                break;
				            case 'NORMAL':
				                $label_class = 'success';
				                break;
				            case 'PROFILE':
				                $test->test_result = 'VIEW';
				                $label_class = 'default';
				                break;
				        }
				        if (count($test->results) == 1){
				            $resultHTML = '<button class="btn btn-xs btn-'.$label_class.'" style="border-radius:0px;" type="button">'.$result->test_result.'</button>';
				        }
				        else {
				            $resultHTML .=  '<button class="btn btn-xs btn-'.$label_class.'" style="border-radius:0px;" type="button"><span class="badge">'.$result->test_code.'</span>&nbsp;'.$result->test_result.'</button>';
				        }
				    }
				}
				
				$cert = '';
				if ($test->cert_code && preg_match('/AC\d+/',$test->cert_code)){
					$cert = '<li><a href="javascript:viewCert(\''.$test->order_id.'\', \''.$test->id.'\',\''.$test->cert_code.'\')"><i class="fa fa-certificate" aria-hidden="true"></i>&nbsp;View Certificate(s)</a></li>';
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
					<td class="text-center">'.$notes.'</td>
					<td class="text-center">'.$resultHTML.'</td>
					<td class="text-center">
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-xs dropdown-toggle'.$class_disabled.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <span class="caret"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">
								'.$action_menu.'
								<li role="separator" class="divider"></li>
								<li><a href="#" class="notes" id="note'.$test->id.'" data-toggle="modal" data-target="#addNoteModal"><i class="fa fa-file-text-o link"></i>&nbsp;Add Note</a></li>
								'.$cert.'
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
<?php get_template_part('part-templates/modal', 'notes'); ?>
<?php get_template_part('part-templates/modal', 'addNote'); ?>

<?php
function footer_js(){
	global $animal_details;
	global $note_details;
?>
<script>
jQuery(document).ready(function($) {
		
	populateAnimalModal(<?php echo json_encode($animal_details); ?>);

	var noteDetails = <?php echo json_encode($note_details); ?>;
	
	$('.notes').click(function(){
			$("#note_test_id").val(($(this).attr('id')).replace('note', ''));
	});
			
	$(".notes_badge").on("click", function(e) {
			var swabId = $(this).attr("id");
			$('#all_test_notes').empty();
			if (noteDetails[swabId].length > 0){
				populateNotesModal(noteDetails[swabId]);
			}
	});
	
	$('#addNoteModal').on('show.bs.modal', function(e) {
			$('#summernote').summernote({
				dialogsInBody: true,
				height: 200,
				toolbar: [
					['style', ['bold', 'italic', 'underline', 'clear']],
					['font', ['strikethrough', 'superscript', 'subscript']],
					['color', ['color']],
					['para', ['ul', 'ol', 'paragraph']],
				]
			});
	});
	   
	var table = $('#orderDetails').DataTable({
		order : [ [ 0, 'desc' ] ],
		"ordering": false,
		"paging": false,
		responsive: {
            details: false
        },
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
