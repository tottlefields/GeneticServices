<?php /* Template Name: View Client */ ?>
<?php get_template_part('part-templates/modal-updates'); ?>
<?php
if(empty($_REQUEST) || !isset($_REQUEST['id'])){
	//wp_redirect(get_site_url().'/orders/');
	exit;	
}
$client_id = $_REQUEST['id'];

$clients = clientSearch(array('id' => $client_id));
$client_details = $clients[0];
$client_details->client_id = $client_details->id;

$test_details = getTestsByClient($client_id);
$note_details = array();
?>
<?php get_header(); ?>

	<h1><i class="fas fa-user"></i>&nbsp;<?php echo $client_details->FullName; ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
			
	<section class="row">
		<div class="col-md-5 col-lg-3">
			<div class="panel panel-default">
				<div class="panel-heading">
				<?php if (current_user_can('editor') || current_user_can('administrator')) { ?>
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit hide-small" id="client" data-toggle="modal" data-target="#clientModal">
						<i class="far fa-edit" aria-hidden="true"></i>Edit
					</button>
				<?php } ?>
					<h3 class="panel-title"><i class="fas fa-user"></i>&nbsp;Client Details</h3>
				</div>
				<div class="panel-body" id="details_client">
					<?php
					echo '
					<div class="row"><div class="col-xs-4">Name</div><div class="col-xs-8">
						<a href="/clients/view?id='.$client_details->id.'"><i class="fas fa-user" aria-hidden="true"></i>'.$client_details->FullName.'</a>
					</div></div>';
					$email = '&nbsp;';
					if ($client_details->Email !== '') {
						$email = '<a href="mailto:'.$client_details->Email.'"><i class="far fa-envelope" aria-hidden="true"></i>'.$client_details->Email.'</a>';
					}
					echo '
					<div class="row"><div class="col-xs-4">Email</div><div class="col-xs-8">'.$email.'</div></div>
					<div class="row"><div class="col-xs-4">Phone</div><div class="col-xs-8">'.$client_details->Tel.'</div></div>
					<div class="row"><div class="col-xs-4">Address</div><div class="col-xs-8">'.$client_details->Address.'<br />'.$client_details->Address2.'<br />'.$client_details->Address3.'</div></div>
					<div class="row"><div class="col-xs-4">Town</div><div class="col-xs-8">'.$client_details->Town.'</div></div>
					<div class="row"><div class="col-xs-4">County</div><div class="col-xs-8">'.$client_details->County.'</div></div>
					<div class="row"><div class="col-xs-4">Postcode</div><div class="col-xs-8">'.$client_details->Postcode.'</div></div>
					<div class="row"><div class="col-xs-4">Country</div><div class="col-xs-8">'.$client_details->Country.'</div></div>';
					?>
				</div>
			</div>
			<a href="<?php echo get_site_url(); ?>/orders/add-manual-order/?client=<?php echo $client_details->id; ?>" class="btn btn-primary btn-block hide-small">Create Manual Order</a>
		</div>
	
		<div class="col-md-7 col-lg-9">
		<?php if ( $test_details ){ ?>
			<table id="orderDetails" class="table table-bordered table-responsive nowrap" cellspacing="0" width="100%">
				<thead>
					<th></th>
					<th class="text-center" data-priority="1">OrderID</th>
					<th class="text-center" data-priority="2">Order Date</th>
					<th data-priority="4">Test</th>
					<th>Breed</th>
					<th data-priority="2">Animal</th>
					<th class="text-center" data-priority="3">Status</th>
					<th class="text-center">Notes</th>
					<th class="text-center">Result</th>
					<th class="text-center">Actions</th>
				</thead>
				<tbody>
		
			<?php
			$test_count = 0;
			$previous_order = '';
			$order_class = 'info';
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
				
				$animal_name = ($test->RegisteredName && $test->RegisteredName != '') ? stripslashes($test->RegisteredName) : '<em>'.$test->PetName.'</em>';	
				$animal = '<a href="'.get_site_url().'/animals/view?id='.$test->animal_id.'"><i class="fas fa-dog" aria-hidden="true"></i>'.$animal_name.'</a>';
								
				$status_label = '<span class="label label-info">'.str_replace('(s)', '', $test->order_status).'</span>';
				if ($test->order_status == 'Cancelled'){
					if (isset($test->repeat_swab) && $test->repeat_swab > 0){ $status_label = '<span class="label label-warning">Repeated (#'.$test->repeat_swab.')</span>'; }
					else { $status_label = '<span class="label label-danger">'.str_replace('(s)', '', $test->order_status).'</span>'; }
					$class_disabled = ' disabled'; 
				}
				
				$actions = array(				    
				    'print_order'   => '<li><a href="javascript:generatePDFs(\''.$test->order_id.'\',\''.$test->id.'\')"><i class="far fa-file-pdf link"></i>&nbsp;Print Order</a></li>',
				    'cancel_test'   => '<li><a href="javascript:cancelTest(\''.$test->id.'\')"><i class="fas fa-ban link"></i>&nbsp;Cancel Test</a></li>',
				    'repeat'        => '<li><a href="javascript:repeatTest(\''.$test->id.'\')"><i class="fas fa-redo link"></i>&nbsp;Request New Sample</a></li>',
				    'dispatch'      => '<li><a href="javascript:sendSample(\''.$test->id.'\')"><i class="far fa-paper-plane link"></i>&nbsp;Dispatch Sample</a></li>',
				    'receive'       => '<li><a href="javascript:receiveSample(\''.$test->id.'\')"><i class="far fa-check-square link"></i>&nbsp;Receive Sample</a></li>',
				    'view_certs'    => '<li><a href="javascript:viewCert(\''.$test->order_id.'\',\''.$test->id.'\')"><i class="far fa-file-pdf link"></i>&nbsp;Print Certificate(s)</a></li>'
				);
				$action_menu = '';
				
				switch ($test->order_status) {
					case 'Order Placed':
					    $action_menu = implode("\n", array($actions['print_order'], $actions['cancel_test'], $actions['dispatch']));
						break;
					case 'Dispatched':
					    //$next_action = '<li><a href="javascript:receiveSample(\''.$test->id.'\')"><i class="far fa-check-square link"></i>&nbsp;Receive Sample</a></li>';
					    $action_menu = implode("\n", array($actions['cancel_test'], $actions['receive']));
					    break;
					case 'Results':
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
				$cert = array();
				$cert_code_seen = array();
				
				if ($test->swab_failed >= 1){
				    $resultHTML = '<span class="label label-default">Failed</span>';
				}
				elseif (count($test->results)>0){
					foreach ($test->results as $result){
						if (isset($cert_code_seen[$test->cert_code])){ continue; }
						
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
				        
				        $resultHTML .=  '<button class="btn btn-xs btn-'.$label_class.' viewCert" style="border-radius:0px;" type="button" data-order="'.$test->order_id.'" data-test="'.$test->id.'">';
				        if (count($test->results) == 1 || preg_match('/CP-/i', $result->test_code) ){ $resultHTML .= $result->test_result; }
				        else { $resultHTML .= '<span class="badge">'.$result->test_code.'</span>&nbsp;'.$result->test_result; }
				        $resultHTML .=  '</button>';
				        
				        // push cert codes to array
				        if ($result->cert_code && (preg_match('/AC\d+/',$result->cert_code) || preg_match('/P\d+/',$result->cert_code))){
				        	array_push($cert, '<a href="javascript:viewCert(\''.$test->order_id.'\', \''.$test->id.'\')"><i class="fas fa-certificate" aria-hidden="true"></i>&nbsp;'.$result->cert_code.'</a>');
				        }
				        
				        $cert_code_seen[$test->cert_code]++;
				    }
				}
				
				if ($previous_order != $test->order_id){ $order_class = ($order_class == 'info') ? 'default' : 'info'; }
				
				echo '
				<tr class="'.$order_class.'">
					<td>'.$test->id.'</td>
					<td class="text-center"><a href="'.get_site_url().'/orders/view?id='.$test->order_id.'">CAGT'.$test->order_id.'</a></td>
					<td class="text-center">'.$test->order_date.'</td>
					<td>'.$test->test_name.'</td>
					<td>'.$test->breed.'</td>
					<td>'.$animal.'</td>
					<td class="text-center">'.$status_label.'</td>
					<td class="text-center">'.$notes.'</td>
					<td class="text-center">'.$resultHTML.'</td>
					<td class="text-center">';
				if ($cert != ''){
					echo implode('<br>', $cert);
				} else {
					echo '
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-xs dropdown-toggle'.$class_disabled.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <span class="caret"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">
								'.$action_menu.'
								<li role="separator" class="divider"></li>
								<li><a href="#" class="notes" id="note'.$test->id.'" data-toggle="modal" data-target="#addNoteModal"><i class="far fa-file-alt link"></i>&nbsp;Add Note</a></li>
							</ul>
						</div>';
				}
				echo '
					</td>
				</tr>';
				$test_count++;
				$previous_order = $test->order_id;
			}
			?>
				</tbody>
			</table>
			
		<?php
		}
		?>
		</div>
	</section>
	
<?php get_template_part('part-templates/modal', 'client'); ?>
<?php get_template_part('part-templates/modal', 'notes'); ?>
<?php get_template_part('part-templates/modal', 'addNote'); ?>

<?php
function footer_js(){ 
	global $client_details;
	global $note_details;
?>
<script>
jQuery(document).ready(function($) {
		
	populateClientModal(<?php echo json_encode($client_details); ?>);

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
		pageLength: 15,
		paging : true,
		lengthChange: false,
		responsive: {
            details: false
        },
		columnDefs : [ {
			targets : [ 0 ],
			visible : false,
			orderable: true
		},{
			targets : [ 1,2,3,4,5,6,7,8,9 ],
			orderable : false	
		} ]
	});

	table.on( 'click', 'button.viewCert', function (e) {
		e.stopPropagation();
		viewCert($(this).data('order'),$(this).data('test'))
	} );
})
</script>
<?php } 
add_action('wp_footer', 'footer_js', 100); ?>

<?php get_footer(); ?>
