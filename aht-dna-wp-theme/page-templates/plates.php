<?php /* Template Name: Plates */ ?>
<?php get_header(); ?>

	<h1 class="hidden-print"><?php wp_title('', true,''); ?><a href="<?php echo get_site_url(); ?>/plates/add-plate/" class="btn btn-default btn-sm" style="margin-left:10px;margin-bottom:3px;">Add Plate</a><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<h1 class="visible-print-block" id="plate_id"></h1>
	<section class="row">
		<div class="col-md-3 hidden-print">
			<div class="well" id="plate_selection">
				<ul class="nav nav-tabs nav-justified" role="tablist">
					<li role="plate_type" class="active"><a href="#extraction" aria-controls="extraction" role="tab" data-toggle="tab">Extraction</a></li>
					<li role="plate_type"><a href="#taqman" aria-controls="taqman" role="tab" data-toggle="tab">TaqMan</a></li>
					<li role="plate_type"><a href="#genotyping" aria-controls="genotyping" role="tab" data-toggle="tab">Genotyping</a></li>
				</ul>
				<div class="tab-content" style="padding-top:20px;">
					<div role="tabpanel" class="tab-pane active" id="extraction">
<?php
global $wpdb;
$sql = "select distinct extraction_plate as plate from test_swabs where extraction_plate is not null and extraction_plate<>'dennis' order by extraction_plate";
$results = $wpdb->get_results($sql, OBJECT );
if (count($results) > 0){
	echo '<select class="form-control plate_select" id="extraction_plate"><option value="0">Select Extraction Plate...</option>';
	foreach ($results as $plate){
		echo '<option value="'.$plate->plate.'">'.$plate->plate.'</option>';
	}
	echo '</select>';
}
else{
	echo '<p>No extraction plates currently recorded on the system.</p>';	
}
?>
					</div>
					<div role="tabpanel" class="tab-pane" id="taqman">
<?php
$sql = "select distinct test_plate as plate from test_swab_results where test_plate is not null and left(test_plate,6)='TaqMan' order by 1";
$results = $wpdb->get_results($sql, OBJECT );
if (count($results) > 0){
	echo '<select class="form-control plate_select" id="taqman_plate"><option value="0">Select TaqMan Plate...</option>';
	foreach ($results as $plate){
		echo '<option value="'.$plate->plate.'">'.$plate->plate.'</option>';
	}
	echo '</select>';
}
else{
	echo '<p>No TaqMan plates currently recorded on the system.</p>';	
}
?>
					</div>
					<div role="tabpanel" class="tab-pane" id="genotyping">
<?php
$sql = "select distinct test_plate as plate from test_swab_results where test_plate is not null and right(test_plate,1)='G' order by 1";
$results = $wpdb->get_results($sql, OBJECT );
if (count($results) > 0){
	echo '<select class="form-control plate_select" id="genotpying_plate"><option value="0">Select Genotyping Plate...</option>';
	foreach ($results as $plate){
		echo '<option value="'.$plate->plate.'">'.$plate->plate.'</option>';
	}
	echo '</select>';
}
else{
	echo '<p>No Genotyping plates currently recorded on the system.</p>';	
}
?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-9">
			<table width="100%" class="plate_table">
		<?php
		$letters = range('A', 'H');
		for ($r=0; $r<9; $r++){
			echo '<tr style="height:50px;">';
			for ($c=0; $c<13; $c++){
				$cell = $letters[$r-1].($c);
				if ($r == 0 & $c == 0){ echo '<td style="border:0px">&nbsp;</td>'; }
				elseif($r == 0){ echo '<th>'.$c.'</th>'; }
				elseif($c == 0){ echo '<th>'.$letters[$r-1].'</th>'; }
				else{
				echo '<td id="'.$cell.'" width="8%"><small class="cell_id">'.$cell.'</small><small class="contents"></small></td>';
				}
				//echo '<div class="cell" id="'.$cell.'">
				//<small>'.$cell.'</small>
				//</div>';
			}
			echo '</tr>';
		}		
		?>
			</table>
		</div>
	</section>

<?php get_footer(); ?>
