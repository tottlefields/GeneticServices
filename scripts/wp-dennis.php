<?php
global $wpdb;

$MAX_ID = trim(fgets(STDIN)); // reads one line from STDIN;
if(!isset($MAX_ID) || $MAX_ID == 'NULL'){ $MAX_ID = 0; }
$post_ids = [];

// Get all the IDs you want to choose from
$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID > %d", $MAX_ID );
$results = $wpdb->get_results( $sql );
foreach ( $results as $row ) { array_push( $post_ids, $row->ID ); }

$args = array(
		'post_type'		=> 'orders',
		'post_status'	=> array('publish'),
		'author'		=> '16213',
		'order'			=> 'ASC',
		'numberposts'	=> -1,
		'post__in' 		=> $post_ids
);

$orders = array();
$animals = array();
$clients = array();

// get posts
$posts = get_posts($args);
echo count($posts)."\n";
global $post;
foreach( $posts as $post ) {
	$data = get_field('order-data-pm');
#	echo(the_ID())."\n";
#	echo "\t - ".get_field('group-id-pm')."\n";
#	echo "\t - ".get_field('test-id-pm')."\n";
#	echo "\t - ".get_field('animal-id-pm')."\n";
#	print_r($data);

	$orderId = str_replace('Order #', '', get_the_title());
	$a = explode(' &#8211;', $orderId);
	$orderId = $a[0];
	if(!isset($orders[$orderId])){
		$orders[$orderId] = array();
		$orders[$orderId]['tests'] = array();
	}
	$orders[$orderId] = array(
			'ClientID' => $post->post_author,
			'OrderDate' => date('Y-m-d', strtotime(get_the_date())),
			'ReportFormat' => $data['shipping-info']['report'],
			'VetReportFormat' => $data['vet-info']['report-delivery'][0]
	);
#	foreach (){
#		$test = array(
#				'AnimalID' => 
#				'TestCode'
#				'SampleType'
#		);
#	}
}
print_r($orders);

?>