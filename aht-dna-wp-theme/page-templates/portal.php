<?php /* Template Name: Portal */ ?>
<?php get_header(); ?>
<?php
    global $post;
    $post_slug=$post->post_name;
    
    ?>
    <h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-12">
<?php
	global $wpdb;

	$sql = "select concat('\"', breed, '\":{', group_concat(concat('\"',test_code, '\":\"', test_name,'\"')), '}') as row 
    		from breed_test_lookup inner join test_codes using (test_code) 
    		inner join breed_list on ID=breed_id group by breed_id order by breed";
	$results = $wpdb->get_results($sql, OBJECT );
	
	if ( $results ){
		echo '<pre>
var breedTests = {
	"all": {"CP":"Canine DNA profiles (ISAG 2006)"},';
		foreach ( $results as $row ){
			echo "\t".$row->row.",\n";
		}
		echo '};</pre>';
	}
?>
		</div>
	</section>

<?php get_footer(); ?>