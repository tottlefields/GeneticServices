<?php /* Template Name: Samples (Untested) */ ?>
<?php get_header(); ?>

<h1><?php wp_title('', true, ''); ?><ul class="breadcrumb pull-right"
		style="font-size: 50%"><?php custom_breadcrumbs(); ?>

</h1>

<?php
$sql = "select concat(test_type, type_group) as type_group, test_type, injection_time, size_standard from analysis_conditions order by 1";
$results = $wpdb->get_results ( $sql, OBJECT );
$test_groups = array ();
foreach ( $results as $test_type ) {
	$description = ($test_type->test_type == 'G') ? 'Genotyping - ' . $test_type->injection_time . 's injection time (' . $test_type->size_standard . ')' : 'Taqman';
	$test_groups [$test_type->type_group] = array (
			'test_type' => $test_type->test_type,
			'desc' => $description
	);
}

$sql = "select test_code, controls from test_codes order by 1";
$results = $wpdb->get_results ( $sql, OBJECT );
$controls = array ();
foreach ( $results as $test_code ) {
	$controls [$test_code->test_code] = $test_code->controls;
}

$sql = 'select order_id, t.id as ID, concat("AHT",order_id,"/",t.id) as barcode, due_date, datediff(due_date, date(NOW())) as days, a.breed_id, b.breed, 
t.test_code, test_type, s.id as swab_id, swab, s.extraction_plate, s.extraction_well, concat(test_type, type_group) as type_group, multiplex
from order_tests t inner join test_swabs s on t.id=s.test_id  
left outer join (
    select id, test_id, swab_failed, plate_allocated from test_swabs where plate_allocated=1
) tmp on s.test_id=tmp.test_id
left outer join test_codes on t.test_code=test_codes.test_code  
inner join animal a on a.id=animal_id  
inner join breed_list b on b.id=breed_id  
where s.extraction_plate is not null and s.plate_allocated=0 
and ((tmp.plate_allocated is NULL and tmp.swab_failed is not NULL) OR tmp.plate_allocated is NULL)
and cancelled_date is null and b.is_primary=1 
order by extraction_plate, right(extraction_well,1) asc, left(extraction_well,1) desc, swab';
$results = $wpdb->get_results ( $sql, OBJECT );

$table_G = '';
$table_T = '';
$q_plates = array (
		'G' => array (),
		'T' => array ()
);

if (count ( $results ) > 0) {
	$table_G = makeTableHeader ( 'G' );
	$table_T = makeTableHeader ( 'T' );

	$counts = array (
			'test_type' => array (
					'G',
					'T'
			),
			'test_code' => array (),
			'test_group' => array ()
	);
	$types2test = array (
			'G' => array (),
			'T' => array ()
	);
	$swabs_seen = array ();

	foreach ( $results as $swab ) {
		// $label = '&nbsp;';
		$repeat = '&nbsp;';
		$badge = 'badge-info';
		$test_code = (isset ( $swab->multiplex )) ? $swab->multiplex : $swab->test_code;
		$table = '';

		if ($swab->test_type == 'T') {
			$table = 'table_T';
			// $label = '<span class="label label-success" style="font-size:0.8em">T</span>';
		}
		if ($swab->test_type == 'G') {
			$table = 'table_G';
			// $label = '<span class="label label-warning" style="font-size:0.8em">G</span>';
		}

		if (! isset ( $q_plates [$swab->test_type] [$swab->extraction_plate] )) {
			$q_plates [$swab->test_type] [$swab->extraction_plate] = 0;
		}
		if (! isset ( $counts ['test_group'] [$swab->type_group] )) {
			$counts ['test_group'] [$swab->type_group] = array ();
		}
		/*
		 * if (!isset($counts['test_group'][$swab->type_group][$swab->test_code])) {
		 * $counts['test_group'][$swab->type_group][$swab->test_code] = 0;
		 * }
		 * if (!isset($counts['test_type'][$swab->test_code])) {
		 * $counts['test_type'][$swab->test_code] = 0;
		 * }
		 */
		if (! isset ( $counts ['test_group'] [$swab->type_group] [$test_code] )) {
			$counts ['test_group'] [$swab->type_group] [$test_code] = 0;
		}
		if (! isset ( $counts ['test_type'] [$test_code] )) {
			$counts ['test_type'] [$test_code] = 0;
		}
		if (! isset ( $types2test [$swab->test_type] [$swab->test_code] )) {
			$types2test [$swab->test_type] [$swab->test_code] = 1;
		} else {
			$types2test [$swab->test_type] [$swab->test_code] ++;
		}
		$q_plates [$swab->test_type] [$swab->extraction_plate] ++;
		$counts ['test_type'] [$swab->test_type] ++;
		// $counts['test_group'][$swab->type_group][$swab->test_code]++;
		// $counts['test_code'][$swab->test_code]++;
		$counts ['test_group'] [$swab->type_group] [$test_code] ++;
		$counts ['test_code'] [$test_code] ++;

		$due_date = new DateTime ( $swab->due_date );
		if (isset ( $swabs_seen [$swab->ID] )) {
			$repeat = '<i class="far fa-clone"></i>';
		} elseif ($swab->swab != 'A') {
			$repeat = '<i class="fas fa-redo" aria-hidden="true"></i>';
		}
		if ($swab->days <= 4) {
			$badge = 'badge-important';
		}

		$$table .= '
			<tr id="row' . $swab->swab_id . '" class="type-' . $swab->test_type . ' test-' . $test_code . ' breed-' . $swab->breed_id . ' plate-' . $swab->extraction_plate . '" data-plate="' . $swab->extraction_plate . '" data-well="' . $swab->extraction_well . '" data-test_code="' . $swab->test_code . '">
                <td>' . $swab->swab_id . '</td>
				<!--<td class="text-center"><input type="checkbox" class="checkboxRow" name="sampleList[]" value="' . $swab->swab_id . '" /></td>-->
				<td class="text-center"><a href="' . get_site_url () . '/orders/view?id=' . $swab->order_id . '">AHT' . $swab->order_id . '</a></td>
				<td class="text-center">' . $swab->barcode . '</td>
				<td class="text-center">' . $repeat . '</td>
				<td class="text-center">' . $due_date->format ( 'd/m/Y' ) . '</td>
				<td class="text-center"><span class="badge ' . $badge . '">' . $swab->days . '</span></td>
				<td>' . $swab->breed . '</td>
				<td class="text-center">' . $swab->test_code . '</td>
				<td class="text-center">' . $swab->type_group . '</td>';
		$plate_link = (isset ( $swab->extraction_well ) && $swab->extraction_well != '') ? get_site_url () . '/plate/' . $swab->extraction_plate . '/well/' . $swab->extraction_well . '/' : get_site_url () . '/plate/' . $swab->extraction_plate . '/';
		$$table .= '<td class="text-center"><a href="' . $plate_link . '">' . $swab->extraction_plate . '</td>
				<td class="text-center">' . $swab->extraction_well . '</td>
				<td class="text-center">' . $swab->extraction_plate . '</td>
			</tr>';
		$swabs_seen [$swab->ID] ++;
	}

	$table_G .= makeTableFooter ();
	$table_T .= makeTableFooter ();
	?>

<script>
		var types2test = <?php echo json_encode($types2test); ?>;
	</script>

<?php
}
?>




<section class="row">
	<div class="col-md-8">
		<div class="row">
			<div class="col-md-6">
				<button id="G-button"
					class="selectProcess btn btn-warning btn-block" type="button"
					data-test_type="G"
					<?php

if ($counts ['test_type'] ['G'] == '') {
						echo ' disabled';
					}
					?>>
					Genotyping <span class="badge"><?php echo $counts['test_type']['G']; ?></span>
				</button>
			</div>
			<div class="col-md-6">
				<button id="T-button"
					class="selectProcess btn btn-default btn-block" type="button"
					data-test_type="T"
					<?php

if ($counts ['test_type'] ['T'] == '') {
						echo ' disabled';
					}
					?>>
					TaqMan <span class="badge"><?php echo $counts['test_type']['T']; ?></span>
				</button>
			</div>
		</div>
		<?php echo $table_G; ?>
		<?php echo $table_T; ?>
		<div class="row">
			<div class="col-xs-12" style="padding-top: 15px;">
				<?php echo drawPlate("buildPlate", "30px"); ?>
				<button type="button" class="btn btn-default pull-right"
					style="margin-top: 10px;" id="createPlate" disabled="disabled"
					name="add-plate">
					<i class="fas fa-th link"></i>&nbsp;Generate Plate
				</button>
				<input type="hidden" id="plate_type" name="plate_type" value="" />
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<?php if (isset($counts['test_group']) && count($counts['test_group']) > 0) { ?>
			<div class="well" id="sample_details">
			<h2>
				Sample Selectors <small>(<a
					href="javascript:resetSamples('.sampleList');">Reset</a>)
				</small>
			</h2>

			<!--<h4 style="font-style: italic;border-bottom: 1px solid #eee;font-weight: 600;padding-bottom:5px;">Extraction Plates</h4>
				<div class="row">
					<?php
			foreach ( $q_plates as $test_type => $plates ) {
				$type_class = ($test_type == 'G') ? 'warning' : 'success';
				echo '<div class="alert alert-' . $type_class . ' div-' . $test_type . ' processToggle"><div class="row">';
				ksort ( $plates );
				foreach ( $plates as $plate => $count ) {
					echo '<div class="col-md-4"><button class="selectSample btn btn-default btn-block" type="button" data-test_type="' . $test_type . '" data-plate="' . $plate . '">' . $plate . ' <span class="badge">' . $count . '</span></button></div>';
				}
				echo '</div></div>';
			}
			?>
				</div>	-->

			<!-- <h4 style="font-style: italic;border-bottom: 1px solid #eee;font-weight: 600;padding-bottom:5px;">Test Codes</h4> -->
			<div class="row">
					<?php
			ksort ( $counts ['test_group'] );
			foreach ( $counts ['test_group'] as $grouping => $test_codes ) {
				$type_class = ($test_groups [$grouping] ['test_type'] == 'G') ? 'warning' : 'success';
				echo '<div class="alert alert-' . $type_class . ' div-' . $test_groups [$grouping] ['test_type'] . ' processToggle">';
				echo '<p class="text-center">' . $test_groups [$grouping] ['desc'] . '</p><div class="row">';
				ksort ( $test_codes );
				foreach ( $test_codes as $test_code => $count ) {
					echo '<div class="col-md-4"><button class="selectSample btn btn-default btn-block" type="button" data-test_group="' . $grouping . '" data-test_code="' . $test_code . '">' . $test_code . ' <span class="badge">' . $count . '</span></button></div>';
				}
				echo '</div></div>';
			}
			?>
				</div>
		</div>
		<?php } ?>
		<div class="well"
			style="font-weight: bold; text-align: center; font-size: 2em">
			TOTAL SAMPLES<br />
			<span id="sample_count">0</span>
		</div>
	</div>
</section>


<script type="text/javascript">
	var testControls = <?php echo json_encode($controls); ?>;
	var plateJson = {'cols': [], 'wells' : {}, 'groups' : []};
	//var controls = ['N','A','C','MQ'];
</script>

<?php
function footer_js() {
	?>
<script>
		jQuery(document).ready(function($) {
			var table = $('.sampleList').DataTable({
				dom: '<"toolbar">frtip',
				paging: false,
				lengthChange: false,
				"scrollY": 300,
				scrollCollapse: true,
				ordering: false,
				select: {
					style: 'multi',
					selector: ':not(input.noRowSelect)'
				},
				columnDefs: [{
					targets: 0,
					checkboxes: {
						selectRow: true
					}
				},{
					targets: [2, 8, 11],
					visible: false
				}, {
					type: 'date-uk',
					targets: 4
				}]
			});

			$('.sampleList').DataTable()
				.on('select', function(e, dt, type, indexes) {
					countSamples(table);
				})
				.on('deselect', function(e, dt, type, indexes) {
					countSamples(table);
					if (table.rows('.selected').count() == 0){ plateJson = {'cols': [], 'wells' : {}}; $("#createPlate").attr('disabled', true);}
				});

			$("#createPlate").on('click', function(e) { createPlate(); });

			$(".selectSample").on("click", function() {

				if ($(this).data('test_code') !== undefined) {
					selector = "test-" + $(this).data('test_code');
				}
				if ($(this).data('plate') !== undefined) {
					selector = "type-" + $(this).data('test_type') + ".plate-" + $(this).data('plate');
				}

				$(this).attr('disabled', true);
				$("#createPlate").attr('disabled', false);

				updatePlate($(this).data('test_code'), $(this).data('test_group'), table.rows('.' + selector).data());

				if ($(this).hasClass('active')) {
					$(this).removeClass('active');
					table.rows('.' + selector).deselect();
				} else {
					$(this).addClass('active');
					table.rows('.' + selector).select();
				}
			});

			$(".selectProcess").on('click', function(e) {
				var testType = $(this).data('test_type');
				resetSamples('.sampleList');
				$(".processToggle").hide();
				if (testType == 'G') {
					$("#G-button").removeClass("btn-default").addClass("btn-warning");
					$("#T-button").removeClass("btn-success").addClass("btn-default");
				} else if (testType == 'T') {
					$("#T-button").removeClass("btn-default").addClass("btn-success");
					$("#G-button").removeClass("btn-warning").addClass("btn-default");
				}
				//$(".table-" + testType).show();
				$(".div-" + testType).show();
				$.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
			});

			//$(".div-T").hide();
		})
	</script>
<?php

}
add_action ( 'wp_footer', 'footer_js', 100 );
?>

<?php get_footer(); ?>

<?php
function makeTableHeader($test_type) {
	return '
	<div class="processToggle" style="display:none;">
	<table id="' . $test_type . 'samples" class="sampleList table table-striped table-bordered table-responsive table-condensed" cellspacing="0" style="width:100%; font-size:0.9em;">
		<thead>
			<th style="padding-right:10px; padding-left:10px;"></th>
			<th class="text-center">OrderID</th>
			<th class="text-center">Barcode</th>
			<th class="text-center">Repeats</th>
			<th class="text-center">Date Due</th>
			<th class="text-center">Days</th>
			<th>Breed</th>
			<th class="text-center">Test</th>
			<th class="text-center">Test Group</th>
			<th class="text-center">Q Plate</th>
			<th class="text-center">Well</th>
			<th class="text-center">Plate</th>
		</thead>
		<tbody>';
}
function makeTableFooter()
{
	return '
		</tbody>
	</table>
	</div>';
}
