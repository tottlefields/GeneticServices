<?php /* Template Name: Samples */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-8">
	
<?php
$sql = "select concat(test_type, type_group) as type_group, test_type, injection_time, size_standard from analysis_conditions order by 1";
$results = $wpdb->get_results($sql, OBJECT );
$test_groups = array();
foreach ($results as $test_type){
    $description = ($test_type->test_type == 'G') ? 'Genotyping - '. $test_type->injection_time.'s injection time ('.$test_type->size_standard.')' : 'Taqman';
    $test_groups[$test_type->type_group] = array(
        'test_type' => $test_type->test_type,
        'desc' => $description
    );
}
    
    
$sql = 'select order_id, t.id as ID, concat("AHT",order_id,"/",t.id) as barcode, due_date, datediff(due_date, date(NOW())) as days, a.breed_id, b.breed, test_code, test_type, swab, concat(test_type, type_group) as type_group
from order_tests t inner join test_swabs s on t.id=s.test_id 
left outer join test_codes using (test_code) 
inner join animal a on a.id=animal_id 
inner join breed_list b on b.id=breed_id 
where extraction_plate is null and cancelled_date is null and b.is_primary=1
order by due_date, test_code';
$results = $wpdb->get_results($sql, OBJECT );
		
	if ( count($results) > 0 ){ ?>
			<table id="samples" class="table table-striped table-bordered table-responsive table-condensed" cellspacing="0" style="width:100%; font-size:0.9em;">
				<thead>
					<th style="padding-right:10px; padding-left:10px;"></th>
					<!--<td class="text-center"><input type="checkbox" id="checkAll" /></td>-->
					<th class="text-center">OrderID</th>
					<th class="text-center">Barcode</th>
					<th class="text-center">Repeats</th>
					<th class="text-center">Date Due</th>
					<th class="text-center">Days</th>
					<th>Breed</th>
					<th class="text-center">Test</th>
					<th class="text-center">Test Type</th>
					<th class="text-center">Swab Count?</th>
				</thead>
				<tbody>		

		<?php
		
		$counts = array('test_type' => array('G', 'T'), 'test_code' => array(), 'test_group' => array());
		$types2test = array('G' => array(), 'T' => array());
		
		foreach ( $results as $swab ){
			$label = '&nbsp;';
			$repeat = '&nbsp;';
			$badge = 'badge-info';
			
			if ($swab->test_type == 'T'){ $label = '<span class="label label-success" style="font-size:0.8em">T</span>'; }
			if ($swab->test_type == 'G'){ $label = '<span class="label label-warning" style="font-size:0.8em">G</span>'; }
			
			if (!isset($counts['test_group'][$swab->type_group])){ $counts['test_group'][$swab->type_group] = array(); }
			if (!isset($counts['test_group'][$swab->type_group][$swab->test_code])){ $counts['test_group'][$swab->type_group][$swab->test_code] = 0; }
			if (!isset($counts['test_type'][$swab->test_code])){ $counts['test_type'][$swab->test_code] = 0; }
			if (!isset($types2test[$swab->test_type][$swab->test_code])){ $types2test[$swab->test_type][$swab->test_code] = 1; } else { $types2test[$swab->test_type][$swab->test_code]++; }
			$counts['test_type'][$swab->test_type]++;
			$counts['test_group'][$swab->type_group][$swab->test_code]++;
			$counts['test_code'][$swab->test_code]++;
			
			$due_date = new DateTime($swab->due_date);
			if ($swab->swab != 'A'){ $repeat = ' <i class="fa fa-repeat" aria-hidden="true"></i>'; }
			if ($swab->days <=4){ $badge = 'badge-important'; }
			
			echo '
			<tr id="row'.$swab->ID.'" class="type-'.$swab->test_type.' test-'.$swab->test_code.' breed-'.$swab->breed_id.'">
                <td>'.$swab->ID.'</td>
				<!--<td class="text-center"><input type="checkbox" class="checkboxRow" name="sampleList[]" value="'.$swab->ID.'" /></td>-->
				<td class="text-center"><a href="'.get_site_url().'/orders/view?id='.$swab->order_id.'">AHT'.$swab->order_id.'</a></td>
				<td class="text-center">'.$swab->barcode.'</td>
				<td class="text-center">'.$repeat.'</td>
				<td class="text-center">'.$due_date->format('d/m/Y').'</td>
				<td class="text-center"><span class="badge '.$badge.'">'.$swab->days.'</span></td>
				<td>'.$swab->breed.'</td>
				<td class="text-center">'.$swab->test_code.'</td>
				<td class="text-center">'.$label.'</td>
                <td class="text-center">';
			if ($swab->swab != 'A'){ echo $repeat; }
			else{
			    echo '<div class="checkbox"><input type="checkbox" class="duplicateSwab" data-toggle="toggle" data-on="2" data-off="1" data-size="mini" data-offstyle="primary" data-onstyle="info"></div>';
			}
                    echo '
                </td>
			</tr>';
		} ?>		
				</tbody>
			</table>
			
		<script>var types2test = <?php echo json_encode($types2test); ?>;</script>
		
		<?php
		} ?>
		
		
		</div>
		<div class="col-md-4">
		<?php if (isset($counts['test_group']) && count($counts['test_group']) > 0){ ?>
			<div class="well"  id="sample_details">
				<h2>Sample Selectors <small>(<a href="javascript:resetSamples();">Reset</a>)</small></h2>
				<h4 style="font-style: italic;border-bottom: 1px solid #eee;font-weight: 600;padding-bottom:5px;">Test Types</h4>
				<div class="row">
					<div class="col-md-6"><button class="selectSample btn btn-warning btn-block" type="button" id="selectTypeG"<?php if($counts['test_type']['G'] == ''){ echo ' disabled'; }?>>Genotyping <span class="badge"><?php echo $counts['test_type']['G']; ?></span></button></div>
					<div class="col-md-6"><button class="selectSample btn btn-success btn-block" type="button" id="selectTypeT"<?php if($counts['test_type']['T'] == ''){ echo ' disabled'; }?>>TaqMan <span class="badge"><?php echo $counts['test_type']['T']; ?></span></button></div>
				</div>
				<h4 style="font-style: italic;border-bottom: 1px solid #eee;font-weight: 600;padding-bottom:5px;">Test Codes</h4>
				<div class="row">
					<?php 
					ksort($counts['test_group']);
					foreach ($counts['test_group'] as $grouping => $test_codes){
					    $type_class = ($test_groups[$grouping]['test_type'] == 'G') ? 'warning' : 'success';
					    echo '<div class="alert alert-'.$type_class.'">';
					    echo '<p class="text-center">'.$test_groups[$grouping]['desc'].'</p><div class="row">';
					    ksort($test_codes); foreach ($test_codes as $test_code => $count){
					        echo '<div class="col-md-4"><button class="selectSample btn btn-default btn-block" type="button" id="selectTest'.$test_code.'">'.$test_code.' <span class="badge">'.$count.'</span></button></div>';
					    }
					    echo '</div></div>';
					
					} ?>
				</div>
			</div>
			<?php } ?>
			<div class="well"  style="font-weight:bold;text-align:center; font-size:2em">
				TOTAL SAMPLES<br /><span id="sample_count">0</span>
			</div>
		</div>
	
	</section>
					
<?php get_footer(); ?>