<?php /* Template Name: Plates */ 
global $wpdb;
?>
<?php if (isset($_REQUEST['add-plate'])){
//    debug_array($_REQUEST);
//    exit;
	$result = $wpdb->insert( "plates", array(
		'plate_type' => $_REQUEST['plate_type'],
		'test_plate' => $_REQUEST['new_plate'],
		'created_by' => $_REQUEST['username'],
		'created_date' => date('Y-m-d H:i:s')
		));
	if(!$result){
		if (preg_match('/Duplicate entry/', $wpdb->last_error)){
			$error = 'The requested plate ('.$_REQUEST['new_plate'].') has already been added. Please try again, using a new identifier, or select the required plate from one of the dropdown lists below.';
		}
	}
	else{
		//echo "redirect to /plates/add-plate/".$_REQUEST['plate_type']."-".$_REQUEST['new_plate'];
		wp_redirect(get_site_url().'/plate/'.$_REQUEST['new_plate'].'/?well='.$_REQUEST['first_well'].'&fill_order='.$_REQUEST['gridfill']);
		exit;
	}
}
$editing = 0;
//if(isset($wp_query->query_vars['plate_type'])) { $plate_type_q = urldecode($wp_query->query_vars['plate_type']); }
if(isset($wp_query->query_vars['plate'])) { 
	$plate_q = urldecode($wp_query->query_vars['plate']);
	$plate_details = getPlateDetails($plate_q);
	$wells = array();
	if (count($plate_details->wells) > 0){
		foreach ($plate_details->wells as $well){
			$wells[$well->well] = '<a href="'.get_site_url().'/orders/view/?id='.$well->order_id.'">AHT'.$well->order_id.'/'.$well->test_id.'</a><br />'.$well->test_code;
		}
	}
	if(isset($_REQUEST['well']) && isset($_REQUEST['fill_order'])){
	    $editing = 1;
	    $first_well = $_REQUEST['well'];
	    $fill_order = $_REQUEST['fill_order'];
	}
}

get_header(); ?>

	<h1 class="hidden-print">
		<?php if(isset($plate_q)){ echo $plate_q; }else{ wp_title('', true,''); } ?>
		<button type="button" class="btn btn-default btn-sm" style="margin-left:10px;margin-bottom:3px;" id="animal" data-toggle="modal" data-target="#addPlateModal">Add Plate</button>
		<ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs($plate_q); ?>
	</h1>
	<h1 class="visible-print-block" id="plate_id"><?php echo $plate_q; ?></h1>
	<?php if ($error){ ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php } ?>
	<section class="row">
		<div class="col-md-3 hidden-print"<?php if(isset($plate_q) && $plate_q!=null){ echo ' style="display:none;"'; } ?>>
			<div class="well" id="plate_selection">
				<ul class="nav nav-tabs nav-justified" role="tablist">
					<li role="plate_type" class="active"><a href="#extraction" aria-controls="extraction" role="tab" data-toggle="tab">Extraction</a></li>
					<li role="plate_type"><a href="#taqman" aria-controls="taqman" role="tab" data-toggle="tab">TaqMan</a></li>
					<li role="plate_type"><a href="#genotype" aria-controls="genotype" role="tab" data-toggle="tab">Genotype</a></li>
				</ul>
				<div class="tab-content" style="padding-top:20px;">
<?php
$plates = array('extraction' => array(), 'taqman' => array(), 'genotype' => array());
foreach (array_keys($plates) as $plate_type){
	
	if ($plate_type == array_keys($plates)[0]){  echo '<div role="tabpanel" class="tab-pane active" id="'.$plate_type.'">'; }
	else { echo '<div role="tabpanel" class="tab-pane" id="'.$plate_type.'">'; }
	$sql = "select distinct test_plate as plate from plates where test_plate is not null and plate_type='".$plate_type."' order by 1";
	$results = $wpdb->get_results($sql, OBJECT );
	if (count($results) > 0){
		echo '<select class="form-control plate_select" id="'.$plate_type.'_plate"><option value="0">Select '.ucfirst($plate_type).' Plate...</option>';
		foreach ($results as $plate){
			echo '<option value="'.$plate->plate.'">'.$plate->plate.'</option>';
			array_push($plates[$plate_type], $plate->plate);
		}
		echo '</select>';
	}
	else{
		echo '<p>No '.ucfirst($plate_type).' plates currently recorded on the system.</p>';	
	}
	echo '</div>';
}
?>
				</div>
			</div>
		</div>
		<div class="<?php if(isset($plate_q) && $plate_q!=null){ echo 'col-md-12'; }else{ echo 'col-md-9'; } ?>">
			<table width="100%" class="plate_table">
		<?php
		
		$letters = range('A', 'H');
		for ($r=0; $r<9; $r++){
			echo '<tr style="height:50px;">';
			for ($c=0; $c<13; $c++){
				$cell = $letters[$r-1].($c);
				
				if ($r == 0 & $c == 0){ 
					if (isset($wp_query->query_vars['plate'])){ 
					    if ($editing){ echo '<td style="border:0px"><button type="button" class="btn btn-success btn-xs hidden-print"><i class="fa fa-check-square-o"></i>Done</button></td>';  }
					    else { echo '<td style="border:0px"><button type="button" class="btn btn-default btn-xs hidden-print"><i class="fa fa-pencil-square-o"></i>Edit</button></td>'; }
					}
					else { echo '<td style="border:0px">&nbsp;</td>'; }
				}
				elseif($r == 0){ echo '<th>'.$c.'</th>'; }
				elseif($c == 0){ echo '<th>'.$letters[$r-1].'</th>'; }
				else{ 
				    if(isset($wells[$cell])){ echo '<td id="'.$cell.'" width="8%"><small class="cell_id" style="display:none">'.$cell.'</small><small class="contents well_contents">'.$wells[$cell].'</small></td>'; }
				    elseif ($editing){
				        echo '<td id="'.$cell.'" width="8%" data-well="'.$cell.'" data-plate="'.$plate_q.'"><small class="cell_id">'.$cell.'</small>
                        <small class="contents well_contents">';
				        if ($first_well == $cell){
				            echo '<input type="text" class="well_enter form-control input-sm" autofocus>';
				        }
				        else{ echo '<input type="text" class="well_enter form-control input-sm" style="display:none">'; }
				        echo '</small></td>'; 
				    }
					else { echo '<td id="'.$cell.'" width="8%"><small class="cell_id">'.$cell.'</small><br /><small class="contents well_contents"></small></td>'; }
				}
			}
			echo '</tr>';
		}		
		?>
			</table>
		</div>
	</section>

	<!-- PLATE MODAL -->
	<div class="modal fade" id="addPlateModal" tabindex="-1" role="dialog" aria-labelledby="addPlateModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="platesModalLabel"><i class="fa fa-file-text-o"></i>&nbsp;Add a Plate</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<!-- <label for="plate_type" class="control-label col-sm-3">Plate Type</label> -->
									<div class="col-sm-6">
										<select class="form-control" id="plate_type" name="plate_type">
											<option value="0">Select Plate Type...</option>
											<option value="extraction">Extraction</option>
											<option value="taqman">TaqMan</option>
											<option value="genotype">Genotype</option>
										</select>
									</div>
									<div class="col-sm-4"><input type="text" class="form-control" id="new_plate" name="new_plate" value="" placeholder="Plate ID"></div>
									<div class="col-sm-2">
										<select class="form-control" id="first_well" name="first_well">
											<option value="A1">A1</option>
											<option value="H1">H1</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label for="plate_type" class="control-label col-sm-4">Scanning Direction</label>
									<div class="col-sm-8">
										<div class="cc-selector">
											<input checked="checked" id="down-across" type="radio" name="gridfill" value="down-across" />
											<label class="drinkcard-cc down-across"for="down-across"></label>
											<input id="across-down" type="radio" name="gridfill" value="across-down" />
											<label class="drinkcard-cc across-down" for="across-down"></label>
											
											<input id="up-across" type="radio" name="gridfill" value="up-across" disabled />
											<label class="drinkcard-cc up-across"for="up-across"></label>
											<input id="across-up" type="radio" name="gridfill" value="across-up" disabled />
											<label class="drinkcard-cc across-up" for="across-up"></label>
										</div>
										
									
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="username" value="<?php echo $current_user->user_login; ?>">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary" name="add-plate">Add Plate</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
	var plateJSON = <?php echo json_encode($plates); ?>;
	<?php if ($editing){ ?>var wellOrder = <?php echo json_encode(getWellOrder($first_well, $fill_order)); ?>;<?php } ?>
	</script>

<?php 
function footer_js(){
	global $plate_type_q; ?>
<script>
	jQuery(document).ready(function($) {
	<?php if (isset($plate_q)){ ?>
				//console.log("Selecting the <?php echo $plate_type_q; ?> tab");
	<?php } ?>
});
</script>
<?php }
add_action('wp_footer', 'footer_js', 100, 1); ?>

<?php get_footer(); ?>
