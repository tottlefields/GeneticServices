<?php /* Template Name: Plates */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><a href="<?php echo get_site_url(); ?>/plates/add-plate/" class="btn btn-default btn-sm" style="margin-left:10px;margin-bottom:3px;">Add Plate</a><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-3">
			<div class="well"  id="plate_selection">
<?php
global $wpdb;
$sql = "select distinct extraction_plate from test_swabs where extraction_plate is not null order by extraction_plate";
$results = $wpdb->get_results($sql, OBJECT );
?>
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
				echo '<td id="'.$cell.'" width="8%"><small>'.$cell.'</small></td>';
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
