<?php /* Template Name: Plates */ 
global $wpdb;
?>
<?php if (isset($_REQUEST['add-plate'])){
    
    $username = (isset($_REQUEST['username'])) ? $_REQUEST['username'] : $current_user->user_login;
    $new_plate = $_REQUEST['new_plate'];    
    if(!isset($_REQUEST['new_plate'])){
    	$new_plate = getNextPlate($_REQUEST['plate_type']);    
	}
	
    //$new_plate = 'Q2014';
    //$result = 1;
    //echo $new_plate." ";
    $now_date = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d H:i:s');    
	$result = $wpdb->insert( "plates", array(
		'plate_type' => $_REQUEST['plate_type'],
	    'test_plate' => $new_plate,
		'created_by' => $username,
	    'created_date' => $now_date
		));

	
	if(!$result){
		if (preg_match('/Duplicate entry/', $wpdb->last_error)){
			$error = 'The requested plate ('.$_REQUEST['new_plate'].') has already been added. Please try again, using a new identifier, or select the required plate from one of the dropdown lists below.';
		}
	}
	else{		
	    
	    if(isset($_REQUEST['samples'])){
	        //Plate generated by passing list of samples, poss with duplicates...
	        //debug_array($_REQUEST);
	        /*
	         * Array (
                    [add-plate] => 
                    [plate_type] => extraction
                    [samples] => 25127,25119,26743,24452,25992,21157,25833,27492,27389,27342,27476,27479,27481,27551,27552,27553,27554,27555,27556,27557,27558,27562,27598,27733,27670,26925,22718,27735,4335,27658,27811,27812,27813,27814,27815
                    [duplicates] => 27735
                )
	         */
	        
	        $wpdb->query("UPDATE test_swabs SET extraction_plate='".$new_plate."', extraction_date='".$now_date."', extracted_by='".$username."' 
			WHERE extraction_plate IS NULL AND test_id IN (".$_REQUEST['samples'].")");
	        
	        
	        $samples = explode(',', $_REQUEST['samples']);
	        $sample_count = count($samples);
	        if(isset($_REQUEST['duplicates'])){
	            $duplicates = explode(',', $_REQUEST['duplicates']);
	            $duplicate_swabs = array();
	            foreach ($duplicates as $d){
	                if(in_array($d, $samples)){ $wpdb->query("INSERT INTO test_swabs (test_id, swab) VALUES (".$d.", 'B')"); array_push($duplicate_swabs, $d); }
	            }
	            if ($sample_count + count($duplicate_swabs) <= 96){
	                $wpdb->query("UPDATE test_swabs SET extraction_plate='".$new_plate."', extraction_date='".$now_date."', extracted_by='".$username."' 
                        WHERE extraction_plate IS NULL AND test_id IN (".implode(',', $duplicate_swabs).")");
	            }
	        }
	        
	        
	        //echo $new_plate;
	        $testAnalysis_details = getTestAnalysisDetails();	        
	        $plate_details = getPlateDetails($new_plate);
	        //debug_array($plate_details);
	        
	        $swab_groups = array();
	        $test_codes_seen = array();
	        $multiplex_tests = array();
	        $total_swabs = 0;
	        
	        foreach ($plate_details->wells as $swab){
	            $analysis_details = $testAnalysis_details[$swab->test_code];
	            $analysis = $analysis_details->test_type.$analysis_details->type_group;
	            if (!isset($test_codes_seen[$swab->test_code])){
	                $test_codes_seen[$swab->test_code] = 0;
	                if ($analysis_details->no_swabs ==1 && $analysis_details->no_results > 1){ 
	                    $multiplex_tests[$analysis_details->test_code] = explode(':', $analysis_details->sub_tests);
	                }
	               
	                if (!isset($swab_groups[$analysis])){ $swab_groups[$analysis] = array(); }
	                $swab_groups[$analysis][$swab->test_code] = array();
	            }
	            array_push($swab_groups[$analysis][$swab->test_code], $swab);
	            $test_codes_seen[$swab->test_code]++;
	            $total_swabs++;
	        }
	        
	        foreach ($multiplex_tests as $parent => $sub_tests){
	            foreach ($sub_tests as $sub){
	                if(in_array($sub, array_keys($test_codes_seen))){
	                    $analysis = $testAnalysis_details[$parent]->test_type.$testAnalysis_details[$parent]->type_group;
	                    
	                    $array1 = $swab_groups[$analysis][$parent];
	                    $array2 = $swab_groups[$analysis][$sub];
	                    $merged = array_merge($array1, $array2);
	                    
	                    $swab_groups[$analysis][$parent] = $merged;
	                    $test_codes_seen[$parent] = count($merged);
	                    
	                    unset($swab_groups[$analysis][$sub]);
	                    unset($test_codes_seen[$sub]);
	                }
	            }
	        }
	        
	        $col_estimate = 0;
	        foreach ($test_codes_seen as $test_code => $test_count){
	            $max_cols = ceil($test_count/8);
	            $col_count += $max_cols;
	        }
	        
	        $well_order = array_keys(getWellOrder());
	        $wells_allocated = array();
	        
	        if($total_swabs == 96 || $col_estimate > 12){    // just fill up plate in order, no padding available!
	            foreach ($swab_groups as $analysis => $group){
	                $test_order = array_intersect_key($test_codes_seen, $group);
	                arsort($test_order);
	                foreach ($test_order as $test_code => $test_count){
    	                $tmp = array_merge($wells_allocated, $group[$test_code]);
    	                $wells_allocated = $tmp;
	                }
	            }
	        }
	        else {
	           // TODO: loop to split different analyses up but not each test code
	           
	            // This loop assumes we can pad the samples quire a lot - if more than 8 sampels for a test code do them as a block; 
	            // then start new col; group tests together where under 8 but don't split across columns.
    	        foreach ($swab_groups as $analysis => $group){
    	            $test_order = array_intersect_key($test_codes_seen, $group);
    	            arsort($test_order);
    	            
    	            $wells_left = 8;
    	            foreach ($test_order as $test_code => $test_count){
    	                if($test_count >= 8){   // test code will require multiple columns...
    	                    if ($wells_left < 8){
    	                        $wells_allocated = array_pad($wells_allocated, (count($wells_allocated) + $wells_left), 'BLANK');
    	                        $wells_left = 8;
    	                    }    	                    
    	                    $well_count = 8*ceil($test_count/8);
    	                    $tmp = array_merge($wells_allocated, $group[$test_code]);
    	                    $wells_allocated = array_pad($tmp, (count($wells_allocated) + $test_count), 'BLANK');    	                    
    	                    $wells_left = $well_count - $test_count;
    	                }
    	                elseif ($wells_left < $test_count){    // test count won't fit in wells left in this column... need to 
    	                    $wells_allocated = array_pad($wells_allocated, (count($wells_allocated) + $wells_left), 'BLANK');
    	                    $tmp = array_merge($wells_allocated, $group[$test_code]);
    	                    $wells_allocated = $tmp;
    	                    $wells_left = 8 - $test_count;
    	                }
    	                else{
    	                    $tmp = array_merge($wells_allocated, $group[$test_code]);
    	                    $wells_allocated = $tmp;	                    
    	                    $wells_left -= $test_count;
    	                }
    	            }
    	            if($wells_left < 8){   // start each analysis group in a new column
    	                $wells_allocated = array_pad($wells_allocated, (count($wells_allocated) + $wells_left), 'BLANK');
    	            }
    	        }
	        }
	        
	        if (count($wells_allocated) > count($well_order)){
	            $error = "ERROR: More wells have been allocated than are available in a single plate (".$new_plate.")."; 
	        }
	        else{
    	        $index = 0;
    	        //$wells_print = array();
    	        foreach ($wells_allocated as $swab){
    	            if ($swab != 'BLANK'){
    	                $wpdb->query("UPDATE test_swabs set extraction_well='".$well_order[$index]."' WHERE id=".$swab->swab_id);
    	                //$wells_print[$well_order[$index]] = $swab->test_code;
    	            }
    	            //else{ $wells_print[$well_order[$index]] = ""; }
    	            $index++;
    	        } 
    	        wp_redirect(get_site_url().'/plate/'.$new_plate);
    	        exit;
	        }
	        
	        
	    } else {
	    	wp_redirect(get_site_url().'/plate/'.$_REQUEST['new_plate'].'/?well='.$_REQUEST['first_well'].'&fill_order='.$_REQUEST['gridfill']);
	    	exit;
	    }
	    
	}
}
$editing = 0;
//if(isset($wp_query->query_vars['plate_type'])) { $plate_type_q = urldecode($wp_query->query_vars['plate_type']); }
if(isset($wp_query->query_vars['plate'])) { 
	$plate_q = urldecode($wp_query->query_vars['plate']);
	$plate_details = getPlateDetails($plate_q);
	if ($plate_details == null){ $error .= 'Sorry, no plate with that ID ('.$plate_q.') exists on the system. Please return to the <a href="/plates/">Plates</a> page and select a different plate.'; }
	else{
		$wells = array();
		if (count($plate_details->wells) > 0){
			foreach ($plate_details->wells as $well){
				$wells[$well->well] = '<a href="'.get_site_url().'/orders/view/?id='.$well->order_id.'">
				<span class="hide-small"><span class="hidden-print">AHT</span>'.$well->order_id.'/</span><span class="test-id">'.$well->test_id.'</span></a><br />'.$well->test_code;
			}
		}
		if (isset($plate_details->other_wells) && count($plate_details->other_wells) > 0){
			foreach ($plate_details->other_wells as $well){
				//$wells[$well->well] = '<small class="text-muted">'.$well->well_contents.'</small>';
				$wells[$well->well] = '<span class="control">'.$well->well_contents."</span>";
			}
		}
		
		if(isset($_REQUEST['well']) && isset($_REQUEST['fill_order'])){
		    $editing = 1;
		    $first_well = $_REQUEST['well'];
		    $fill_order = $_REQUEST['fill_order'];
		}
	}
}

get_header(); ?>

	<h1 class="hidden-print">
		<?php if(isset($plate_q)){ echo $plate_q; }else{ wp_title('', true,''); } ?>
		<!-- <button type="button" class="btn btn-default btn-sm" style="margin-left:10px;margin-bottom:3px;" id="animal" data-toggle="modal" data-target="#addPlateModal">Add Plate</button> -->
		<ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs($plate_q); ?>
	</h1>
	<h1 class="visible-print-block" id="plate_id"><?php echo $plate_q; ?><span id="plate_details" class="pull-right"><small><?php echo $plate_details->created_by; ?> (<?php echo $plate_details->readable_date; ?>)</small></span></h1>
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
	$sql = "select distinct test_plate as plate from plates where test_plate is not null and plate_type='".$plate_type."' order by 1 desc";
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
		<div class="col-md-12">
		<!-- <div class="<?php if(isset($plate_q) && $plate_q!=null){ echo 'col-md-12'; }else{ echo 'col-md-9'; } ?>"> -->
			<table width="100%" class="plate_table">
		<?php
		$wellRequest = (isset($wp_query->query_vars['hilight'])) ? urldecode($wp_query->query_vars['hilight']) : '';
		
		$letters = range('A', 'H');
		for ($r=0; $r<9; $r++){
			echo '<tr style="height:50px;">';
			for ($c=0; $c<13; $c++){
				$cell = $letters[$r-1].($c);
				
				if ($r == 0 & $c == 0){ 
					/*if (isset($wp_query->query_vars['plate'])){ 
					    if ($editing){ echo '<td style="border:0px"><button type="button" class="btn btn-success btn-xs hidden-print hide-small"><i class="far fa-check-square"></i>Done</button></td>';  }
					    else { echo '<td style="border:0px"><button type="button" class="btn btn-default btn-xs hidden-print hide-small"><i class="far fa-edit-square-o"></i>Edit</button></td>'; }
					}
					else { echo '<td style="border:0px">&nbsp;</td>'; } */
					echo '<td style="border:0px">&nbsp;</td>';
				}
				elseif($r == 0){ 
					if (isset($plate_details->{'col'.$c})) {
						$a = explode(':', $plate_details->{'col'.$c});
						echo '<th><a href="/plate/'.$a[0].'">'.$a[0].'</a>/'.$a[1].'</th>'; 
					} 
					else { echo '<th id="col'.$c.'" class="col_header">'.$c.'</th>'; }
				}
				elseif($c == 0){ echo '<th>'.$letters[$r-1].'</th>'; }
				else{ 
				    if(isset($wells[$cell])){ 
						echo '<td id="'.$cell.'" width="8%"';
						if ($cell == $wellRequest){ echo ' class="hilight-well"'; }
						echo '><small class="cell_id" style="display:none">'.$cell.'</small><small class="contents well_contents">'.$wells[$cell].'</small></td>'; 
					}
				    elseif ($editing){
				        echo '<td id="'.$cell.'" width="8%" data-well="'.$cell.'" data-plate="'.$plate_q.'"><small class="cell_id">'.$cell.'</small>
                        <small class="contents well_contents">';
				        if ($first_well == $cell){
				            echo '<input type="text" class="well_enter form-control input-sm" autofocus>';
				        }
				        else{ echo '<input type="text" class="well_enter form-control input-sm" style="display:none">'; }
				        echo '</small></td>'; 
				    }
					else { echo '<td id="'.$cell.'" width="8%"><small class="cell_id">'.$cell.'</small><small class="contents well_contents"></small></td>'; }
				}
			}
			echo '</tr>';
		}		
		?>
			</table>
		</div>
	</section>
	
	<?php 
	$created_date = (isset($plate_details->created_date)) ? (new DateTime($plate_details->created_date))->format('jS M Y') : '&nbsp;';
	?>
	
	<section class="row pagebreak" style="margin-top:15px;">
		<div class="col-sm-4 col-md-3 hidden-print">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fas fa-th"></i>&nbsp;Plate Details</h3>
				</div>
				<div class="panel-body" id="details_plate">
				<?php if(isset($plate_q) && $plate_q!=null) {?>
					<div class="row"><div class="col-xs-5">Plate</div><div class="col-xs-7"><?php echo $plate_q; ?></div></div>
					<div class="row"><div class="col-xs-5">Owner</div><div class="col-xs-7"><strong><?php echo $plate_details->created_by; ?></strong></div></div>
					<div class="row"><div class="col-xs-5">Generated</div><div class="col-xs-7"><?php echo $created_date; ?></div></div>
					<?php if ($plate_details->plate_type != 'extraction'){?>
					<div class="row"><div class="col-xs-12"><a href="/downloads/plate/<?php echo $plate_q; ?>/" class="btn btn-block btn-primary" type="button" style="margin-top:5px;margin-bottom:0px">Plate Import Record</a></div></div>
					<?php } ?>
				<?php } ?>
				</div>
			</div>
		</div>
		<div class="col-sm-12 col-md-9">
			<div class="panel panel-default">
				<div class="panel-heading">
					<button type="button" class="btn btn-primary btn-xs pull-right details-btn btn-edit hidden-print" id="experiment" disabled="disabled" data-toggle="modal" data-target="#experimentModal">
						<i class="far fa-edit" aria-hidden="true"></i>Edit
					</button>
					<h3 class="panel-title"><i class="fas fa-flask"></i>&nbsp;Experiment Details</h3>
				</div>
				<div class="panel-body" id="details_client">
				</div>
			</div>
		</div>
	</section>

	<!-- PLATE MODAL -->
	<div class="modal fade" id="addPlateModal" tabindex="-1" role="dialog" aria-labelledby="addPlateModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="platesModalLabel"><i class="far fa-file-alt"></i>&nbsp;Add a Plate</h2>
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
											<option value="H1" selected="selected">H1</option>
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
											<input id="down-across" type="radio" name="gridfill" value="down-across" />
											<label class="drinkcard-cc down-across"for="down-across"></label>
											<input id="across-down" type="radio" name="gridfill" value="across-down" />
											<label class="drinkcard-cc across-down" for="across-down"></label>
											
											<input checked="checked" id="up-across" type="radio" name="gridfill" value="up-across" disabled />
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
