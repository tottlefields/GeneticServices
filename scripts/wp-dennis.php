<?php
global $wpdb;

$MAX_ID = trim ( fgets ( STDIN ) ); // reads one line from STDIN;
if (! isset ( $MAX_ID ) || $MAX_ID == 'NULL') {
	$MAX_ID = 0;
}
$post_ids = array ();

echo "MAX ID = " . $MAX_ID . "\n";

// Get all the IDs you want to choose from
$sql = $wpdb->prepare ( "SELECT ID FROM $wpdb->posts WHERE ID > %d", $MAX_ID );
$results = $wpdb->get_results ( $sql );
foreach ( $results as $row ) {
	array_push ( $post_ids, $row->ID );
}

if (count ( $post_ids ) == 0) {
	exit ();
}

$args = array (
		'post_type' => 'orders',
		'post_status' => array (
				'publish' 
		),
		// 'author' => '16213',
		'order' => 'ASC',
		'numberposts' => - 1,
		'post__in' => $post_ids 
);

$orders = array ();
$animals = array ();
$clients = array ();

// get posts
$posts = get_posts ( $args );
global $post;
foreach ( $posts as $post ) {
	$postMeta = get_post_custom ( $post->ID );
	
	if (! get_field ( 'paid-pm' )) {
		continue;
	}
	
	$orderId = str_replace ( 'Order #', '', get_the_title () );
	$a = explode ( ' &#8211;', $orderId );
	$orderId = $a [0];
	
	$clientId = $post->post_author;
	
	if (! isset ( $orders [$orderId] )) {
		
		$reportFormat = 'EMAIL';
		if (get_field ( 'report-by-post-pm' ) != 'FALSE') {
			$reportFormat = 'POST';
		} elseif (get_field ( 'report-by-fax-pm' ) != 'FALSE') {
			$reportFormat = 'FAX';
		}
		
		$vetReport = NULL;
		if (get_field ( 'vet-report-by-email-pm' ) != 'FALSE') {
			$vetReport = 'EMAIL';
		} elseif (get_field ( 'vet-report-by-post-pm' ) != 'FALSE') {
			$vetReport = 'POST';
		} elseif (get_field ( 'vet-report-by-fax-pm' ) != 'FALSE') {
			$vetReport = 'FAX';
		}
		
		$research = 0;
		if (get_field ( 'agree-to-research-pm' ) != 'FALSE') {
			$research = 1;
		}
		
		$orders [$orderId] = array (
				'ClientID' => $clientId,
				'OrderDate' => date ( 'Y-m-d', strtotime ( get_the_date () ) ),
				'ReportFormat' => $reportFormat,
				'VetReportFormat' => $vetReport,
				'Paid' => get_field ( 'paid-pm' ),
				'AgreeResearch' => $research,
				'tests' => array () 
		);
	}
	
	// Test Data
	$testDataPost = get_post ( $postMeta ['test-id-pm'] [0] );
	$testDataMeta = get_post_custom ( $postMeta ['test-id-pm'] [0] );
	
	$test = array (
			'OrderID' => $orderId,
			'AnimalID' => $postMeta ['animal-id-pm'] [0],
			'Quantity' => $postMeta ['quantity-pm'] [0],
			'TestCode' => $testDataMeta ['test-code-pm'] [0],
			'SampleType' => 'Swab',
			'VetID' => NULL 
	);
	array_push ( $orders [$orderId] ['tests'], $test );
	
	if (! isset ( $clients [$clientId] )) {
		$address = explode ( "\n", str_replace ( "\r", '', $postMeta ['address-pm'] [0] ) );
		$address = array_pad ( $address, 3, "" );
		$client = array (
				'ClientID' => $clientId,
				'Tel' => $postMeta ['tel-pm'] [0],
				'Fax' => $postMeta ['fax-pm'] [0],
				'Email' => $postMeta ['email-pm'] [0],
				'FullName' => $postMeta ['fullname-pm'] [0],
				'Address' => array_shift ( $address ),
				'Address2' => array_shift ( $address ),
				'Address3' => implode ( ", ", $address ),
				'Town' => $postMeta ['town-pm'] [0],
				'County' => $postMeta ['county-pm'] [0],
				'Postcode' => $postMeta ['postcode-pm'] [0],
				'Country' => $postMeta ['country-pm'] [0]
		);
		$clients [$clientId] = $client;
		$wpdb->replace ( 'client', $client );
		if ($wpdb->last_error) {
			echo 'ERROR detected when inserting client with ClientID=' . $clientId . "\n" . $wpdb->last_error;
		}
	}
	
	$animalId = $postMeta ['animal-id-pm'] [0];
	if (! isset ( $animals [$animalId] )) {
		$breed = get_term_by ( 'id', $postMeta ['breed-id-pm'] [0], 'test-breeds' );
		$parent = get_term_by ( 'id', $breed->parent, 'test-breeds' );
		$animalData = $wpdb->get_row ( "SELECT *, str_to_date(`birth-date`, '%d/%m/%Y') as dob FROM wp_animals WHERE id='" . $animalId . "'", 'ARRAY_A' );
		$animal = array (
				'AnimalID' => $animalId,
				'ClientID' => $clientId,
				'Species' => $parent->name,
				'Breed' => $breed->name,
				'RegisteredName' => $animalData ['registered-name'],
				'RegistrationNo' => $animalData ['registration-number'],
				'Sex' => substr ( $animalData ['sex'], 0, 1 ),
				'BirthDate' => $animalData ['dob'],
				'TattooOrChip' => $animalData ['tattoo-chip'],
				'PetName' => $animalData ['pet-name'],
				'Colour' => $animalData ['colour'] 
		);
		$animals [$animalId] = $animal;
		$wpdb->replace ( 'animal', $animal );
		if ($wpdb->last_error) {
			echo 'ERROR detected when inserting animal with AnimalID=' . $animalId . "\n" . $wpdb->last_error;
		}
	}
	$MAX_ID = $post->ID;
}

$shipping_address = explode ( "\n", str_replace ( "\r", '', $postMeta ['shipping-address-pm'] [0] ) );
$shipping_address = array_pad ( $shipping_address, 3, "" );
foreach ( $orders as $orderId => $order ) {
	$wpdb->insert ( 'orders', array (
			'OrderID' => $orderId,
			'ClientID' => $order ['ClientID'],
			'OrderDate' => $order ['OrderDate'],
			'ReportFormat' => $order ['ReportFormat'],
			'VetReportFormat' => $order ['VetReportFormat'],
			'Paid' => $order ['Paid'],
			'AgreeResearch' => $order ['AgreeResearch'],
			'ShippingName' => $postMeta ['shipping-name-pm'] [0],
			'ShippingCompany' => $postMeta ['shipping-company-pm'] [0],
			'ShippingAddress' => array_shift ( $shipping_address ),
			'ShippingAddress2' => array_shift ( $shipping_address ),
			'ShippingAddress3' => implode ( ", ", $shipping_address ),
			'ShippingTown' => $postMeta ['shipping-town-pm'] [0],
			'ShippingCounty' => $postMeta ['shipping-county-pm'] [0],
			'ShippingPostcode' => $postMeta ['shipping-postcode-pm'] [0],
			'ShippingCountry' => $postMeta ['shipping-country-pm'] [0] 
	) );
	if ($wpdb->last_error) {
		echo 'ERROR detected when inserting order with OrderID=' . $orderId . "\n" . $wpdb->last_error;
	}
	
	foreach ( $order ['tests'] as $test ) {
		$wpdb->insert ( 'order_tests', $test );
		if ($wpdb->last_error) {
			echo 'ERROR detected when inserting test row with OrderID=' . $orderId . "\n" . $wpdb->last_error;
		}
	}
}

fclose ( STDOUT );
$STDOUT = fopen ( 'last_order', 'wb' );
echo $MAX_ID . "\n";

?>
