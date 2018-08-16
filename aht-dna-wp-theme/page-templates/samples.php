<?php /* Template Name: Samples */ ?>
<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<div class="row" style="margin-bottom:20px;">
		<div class="col-xs-12 text-center">
<?php
	global $wpdb;
	$sql = "select test_code, min(due_date), count(*) as test_count, min(due_date)- date(NOW()) as days 
	from order_tests t inner join test_swabs s on t.id=s.test_id 
	where extraction_plate is null and cancelled_date is null group by test_code order by 2,3 desc";
	$results = $wpdb->get_results($sql, OBJECT );
	$test_codes = array();
	
	if ( $results ){
		foreach ( $results as $test ){
			$class = 'success';
			if ($test->days < 10){ $class = 'warning'; }
			if ($test->days < 4){ $class = 'danger'; }
			echo '<button class="btn btn-'.$class.'" type="button" style="margin-right:10px">'.$test->test_code.' <span class="badge">'.$test->test_count.'</span></button>';
			array_push($test_codes, $test->test_code);
			//echo '<span class="label label-success">'.$test->test_code.' <span class="badge">'.$test->test_count.'</span></span>';
		}
	}
?>
		</div>
	</div>
	<section class="row">
		<div class="col-md-8">
	
<?php		
		
	
	
	if ( count($test_codes) > 0 ){ ?>
			<table id="orders" class="table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
				<thead>
					<th></th>
					<td class="text-center"><input type="checkbox" id="checkAll" /></td>
					<th class="text-center">OrderID</th>
					<th class="text-center">Webshop</th>
					<th class="text-center">Date</th>
					<th>Client</th>
					<th class="text-center">#Swabs</th>
					<th class="text-center">Tests</th>
					<th class="text-center">PDF</th>
				</thead>
				<tbody>		

		<?php
		$sql = '';
		$results = $wpdb->get_results($sql, OBJECT );
		foreach ( $results as $test_swab ){

		} ?>		
				</tbody>
			</table>
		<?php
		} ?>
		
		</div>
		<div class="col-md-4">
			<div class="well"  id="sample_details"></div>
		</div>
	
	</section>
					
<?php get_footer(); ?>