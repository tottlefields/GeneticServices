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

	<h1><i class="fa fa-user"></i>&nbsp;<?php echo $client_details->FullName; ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
			
	<section class="row">
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
				<?php if (current_user_can('editor') || current_user_can('administrator')) { ?>
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit" id="client" data-toggle="modal" data-target="#clientModal">
						<i class="fa fa-pencil" aria-hidden="true"></i>Edit
					</button>
				<?php } ?>
					<h3 class="panel-title"><i class="fa fa-user"></i>&nbsp;Client Details</h3>
				</div>
				<div class="panel-body" id="details_client">
					<?php
					echo '
					<div class="row"><div class="col-sm-4">Name</div><div class="col-sm-8">
						<a href="/clients/view?id='.$client_details->id.'"><i class="fa fa-user" aria-hidden="true"></i>'.$client_details->FullName.'</a>
					</div></div>';
					$email = '&nbsp;';
					if ($client_details->Email !== '') {
						$email = '<a href="mailto:'.$client_details->Email.'"><i class="fa fa-envelope-o" aria-hidden="true"></i>'.$client_details->Email.'</a>';
					}
					echo '
					<div class="row"><div class="col-sm-4">Email</div><div class="col-sm-8">'.$email.'</div></div>
					<div class="row"><div class="col-sm-4">Phone</div><div class="col-sm-8">'.$client_details->Tel.'</div></div>
					<div class="row"><div class="col-sm-4">Address</div><div class="col-sm-8">'.$client_details->Address.'<br />'.$client_details->Address2.'<br />'.$client_details->Address3.'</div></div>
					<div class="row"><div class="col-sm-4">Town</div><div class="col-sm-8">'.$client_details->Town.'</div></div>
					<div class="row"><div class="col-sm-4">County</div><div class="col-sm-8">'.$client_details->County.'</div></div>
					<div class="row"><div class="col-sm-4">Postcode</div><div class="col-sm-8">'.$client_details->Postcode.'</div></div>
					<div class="row"><div class="col-sm-4">Country</div><div class="col-sm-8">'.$client_details->Country.'</div></div>';
					?>
				</div>
			</div>
			<a href="/orders/add-manual-order/?client=<?php echo $client_details->id; ?>" class="btn btn-primary btn-block">Create Manual Order</a>
		</div>
	
		<div class="col-md-9">
		<?php if ( $test_details ){ ?>
			<table id="orderDetails" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
				<thead>
					<th></th>
					<th class="text-center">OrderID</th>
					<th class="text-center">Order Date</th>
					<th>Test</th>
					<th>Breed</th>
					<th>Animal</th>
					<th class="text-center">Status</th>
					<th class="text-center">Notes</th>
					<th class="text-center">Result</th>
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
				
				$animal_name = ($test->RegisteredName && $test->RegisteredName != '') ? $test->RegisteredName : '<em>'.$test->PetName.'</em>';	
				$animal = '<a href="'.get_site_url().'/animals/view?id='.$test->animal_id.'"><i class="fa fa-paw" aria-hidden="true"></i>'.$animal_name.'</a>';
								
				$status_label = '<span class="label label-info">'.str_replace('(s)', '', $test->order_status).'</span>';
				if ($test->order_status == 'Cancelled'){
					if (isset($test->repeat_swab) && $test->repeat_swab > 0){ $status_label = '<span class="label label-warning">Repeated (#'.$test->repeat_swab.')</span>'; }
					else { $status_label = '<span class="label label-danger">'.str_replace('(s)', '', $test->order_status).'</span>'; }
					$class_disabled = ' disabled'; 
				}
				
				$next_action = '<li><a href="javascript:repeatTest(\''.$test->id.'\')"><i class="fa fa-repeat link"></i>&nbsp;Request Repeat</a></li>';
				switch ($test->order_status) {
					case 'Order Placed':
						$next_action = '<li><a href="javascript:sendSample(\''.$test->id.'\')"><i class="fa fa-paper-plane-o link"></i>&nbsp;Dispatch Sample</a></li>';
						break;
					case 'Kit(s) Dispatched':
						$next_action = '<li><a href="javascript:receiveSample(\''.$test->id.'\')"><i class="fa fa-check-square-o link"></i>&nbsp;Receive Sample</a></li>';
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
				
				$result = '';
				if ($test->test_result){
					switch ($test->test_result) {
						case 'Failed':
							$label_class = 'default';
							break;
						case 'AFFECTED':
							$label_class = 'danger';
							break;
						case 'CARRIER':
							$label_class = 'warning';
							break;
						case 'CLEAR':
							$label_class = 'success';
							break;
					}
					$result = '<span class="label label-'.$label_class.'">'.$test->test_result.'</span>';
				}
				
				echo '
				<tr>
					<td>'.$test->id.'</td>
					<td class="text-center"><a href="'.get_site_url().'/orders/view?id='.$test->order_id.'">AHT'.$test->order_id.'</a></td>
					<td class="text-center">'.$test->order_date.'</td>
					<td>'.$test->test_name.'</td>
					<td>'.$test->breed.'</td>
					<td>'.$animal.'</td>
					<td class="text-center">'.$status_label.'</td>
					<td class="text-center">'.$notes.'</td>
					<td class="text-center">'.$result.'</td>
					<td class="text-center">
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-xs dropdown-toggle'.$class_disabled.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions <span class="caret"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">
								<li><a href="javascript:generatePDFs(\''.$test->order_id.'\',\''.$test->id.'\')"><i class="fa fa-file-pdf-o link"></i>&nbsp;Print Order</a></li>
								<li><a href="javascript:cancelTest(\''.$test->id.'\')"><i class="fa fa-ban link"></i>&nbsp;Cancel Test</a></li>
								'.$next_action.'
								<!--<li><a href="#">Something else here</a></li>-->
								<li role="separator" class="divider"></li>
								<li><a href="#" class="notes" id="note'.$test->id.'" data-toggle="modal" data-target="#addNoteModal"><i class="fa fa-file-text-o link"></i>&nbsp;Add Note</a></li>
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
		ordering: false,
		pageLength: 20,
		paging : true,
		lengthChange: false,
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
