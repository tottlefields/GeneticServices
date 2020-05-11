<?php /* Template Name: Manual Order */ ?>
<?php 
if (isset($_POST['new-order-submitted'])) {

	//debug_array($_REQUEST);
	//exit;
	
	if (isset($_REQUEST['client'])){
		$client_id = $_REQUEST['client'];
	}
	else{
		$clients = clientSearch(array(
			'Postcode'		=> $_REQUEST['owner-postcode'],
			'ShippingPostcode'	=> $_REQUEST['owner-postcode'],
			'FullName'		=> $_REQUEST['owner-name'],
			'Email'			=> $_REQUEST['owner-email']
		));
		if (count($clients) == 0){ $client_id = addNewClient(array(
			'FullName'	=> $_REQUEST['owner-name'],
			'Email'		=> $_REQUEST['owner-email'],
			'Tel'		=> $_REQUEST['owner-phone'],
			'Address'	=> $_REQUEST['owner-address'],
			'Town'		=> $_REQUEST['owner-town'],
			'County'	=> $_REQUEST['owner-county'],
			'Country'	=> $_REQUEST['owner-country'],
			'Postcode'	=> $_REQUEST['owner-postcode'],
		) ); }
		elseif (count($clients) == 1) { $client_id = $clients[0]->id; }
		else { echo "ERROR - multiple clients match"; exit; }
	}
	
	$clients = clientSearch(array('id' => $client_id));
	$client = $clients[0];

	$order_id = addNewOrder(array(
		'client_id'		=> $client_id,
		'OrderDate'		=> date('Y-m-d'),
		'ReportFormat'		=> $_REQUEST['format'],
		'VetReportFormat'	=> ($_REQUEST['vet-select'] > 0) ? $_REQUEST['format'] : NULL,
		'AgreeResearch'		=> ($_REQUEST['research'] == 'on') ? 1 : 0,
		'ShippingName'      => $client->FullName,
	    'ShippingAddress'   => $client->Address,
	    'ShippingTown'      => $client->Town,
	    'ShippingCounty'    => $client->County,
	    'ShippingPostcode'  => $client->Postcode,
	    'ShippingCountry'   => $client->Country,
		'Paid'				=> ($_REQUEST['payment-made'] == 'on') ? 1 : 0,
	));
	
	$contentArray = array();
	
	for ($i=1; $i<=$_REQUEST['noDogs']; $i++){
		
		$animals = animalSearch(array(
				'RegisteredName'	=> $_REQUEST['registered-name_'.$i],
				'RegistrationNo'	=> $_REQUEST['registration-number_'.$i],
				'TattooOrChip'		=> $_REQUEST['microchip_'.$i]
		), $client_id);
		if (count($animals) == 0){
			
			$sql = "select breed from breed_list WHERE is_primary=1 and ID=".$_REQUEST['breed_'.$i];
			$Breed = $wpdb->get_var($sql);			
			
			$animal_id = addNewAnimal(array(
					'RegisteredName'=> $_REQUEST['registered-name_'.$i],
					'RegistrationNo'=> $_REQUEST['registration-number_'.$i],
					'Sex'		=> substr($_REQUEST['sex_'.$i],0,1),
					'BirthDate'	=> dateToSQL($_REQUEST['birth-date_'.$i]),
					'TattooOrChip'	=> $_REQUEST['microchip_'.$i],
					'PetName'	=> $_REQUEST['pet-name_'.$i],
					'Colour'	=> $_REQUEST['colour_'.$i],
					'breed_id'	=> $_REQUEST['breed_'.$i],
					'Breed'		=> $Breed,
					'client_id'	=> $client_id
			));
		}
		elseif (count($animals) == 1) { $animal_id = $animals[0]->id; }
		else { echo "ERROR - multiple animals match"; exit; }
	
		foreach ($_REQUEST['breed_tests_'.$i] as $test){
			addOrderTest(array(
					'order_id'	=> $order_id,
					'animal_id'	=> $animal_id,
					'test_code'	=> $test,
					'VetID'		=> ($_REQUEST['vet-select'] > 0) ? $_REQUEST['vet-select'] : NULL
			));
			if(!isset($contentArray[$test])){ $order_content[$test] = 0; }
			$contentArray[$test]++;
		}
	}
	$order_content = '';
	foreach ($contentArray as $test => $count){ $order_content .= $count.':'.$test; }
	$wpdb->update('orders', array('content' => $order_content), array('id' => $order_id));
	
	wp_redirect(get_site_url().'/orders/');
	exit;
}
$client_id = 0;
if(isset($_GET['client']) && $_GET['client'] > 0){
	$client_id = $_GET['client'];
}
$clients = clientSearch(array('id' => $client_id));
$client_details = $clients[0];?>
<?php get_header(); ?>
<?php
	global $post;
	
	?>
	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-12">
			<form class="form-horizontal" id="swab_details_form" method="post">					
                <input type="hidden" name="new-order-submitted" value="1" />
				<div class="row">
					<div class="col-sm-5">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title">1. Sample Details</h3>
							</div>
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-2 control-label">Report</label>
									<div class="col-sm-4">
										<label class="radio-inline"><input type="radio" class="radiorequired" value="Email" name="format" checked="checked"/> Email</label>
										<label class="radio-inline"><input type="radio" class="radiorequired" value="Post" name="format"/> Post</label>
										<!-- <label class="radio-inline"><input type="radio" class="radiorequired" value="Fax" name="format"/> Fax</label> -->
									</div>
									<label class="col-sm-2 control-label">Paid?</label>
									<div class="col-sm-2">
										<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" data-size="small" name="payment-made" id="payment-made" />
									</div>
								</div>
									
								<div class="form-group">
									<label class="col-sm-2 control-label">Verification</label>
									<div class="col-sm-2">
										<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" data-size="small" name="vet-verified" id="vet-verified" />
									</div>
								  	<div class="col-sm-8" style="display:none" id="vet-select-div">
								  		<select id="vet-select" class="form-control" name="vet-select">
								  			<option value="0">Add another Verifier...</option> 
								  		</select>												 
								  	</div>
								</div>
								<div class="form_group">
									<div class="col-sm-2 control-label">									
										<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" data-size="small" name="research" id="research" checked="checked" />
									</div>
									<div class="col-sm-10">
										<label>The owner gives consent for the use of these samples for ongoing research into animal health and diseases at the Animal Health Trust.</label>
									</div>
								</div> 
							</div>
						</div>
					</div>			
			
					<div class="col-sm-7">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title">2. Client Details</h3>
								<input type="hidden" id="client_id" name="client_id" value="<?php echo $client_id; ?>" />
								<input type="hidden" id="client_country" name="client_country" value="<?php echo $client_details->Country; ?>" />
							</div>
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-2 control-label">Name</label>
									<div class="col-sm-3">
										<input type="text" class="form-control required<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->FullName; ?>" name="owner-name" id="owner-name"/>
									</div>
									<label class="col-sm-2 control-label">Address</label>
									<div class="col-sm-5">
									 	<input type="text" class="form-control<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->Address; ?>" name="owner-address" id="owner-address"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Email</label>
									<div class="col-sm-3">
										<input type="email" class="form-control required<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->Email; ?>" name="owner-email" id="owner-email"/>
									</div>
									<label class="col-sm-2 control-label">Town</label>
									<div class="col-sm-2">
										<input type="text" class="form-control<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->Town; ?>" placeholder="Town" name="owner-town" id="owner-town" tabindex="2" />
									</div>
									<label class="col-sm-1 control-label">County</label>
									<div class="col-sm-2">
										<input type="text" class="form-control<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->County; ?>" placeholder="County" name="owner-county" id="owner-county" tabindex="2" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Phone</label>
									<div class="col-sm-3">
										<input type="tel" class="form-control<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->Tel; ?>" name="owner-phone" id="owner-phone"/>
									</div>
									<label class="col-sm-2 control-label">Country</label>
									<?php if (isset($client_details->Country) && $client_details->Country != '') {?>
									<div class="col-sm-2">
										<input type="text" class="form-control<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->Country; ?>" name="owner-country" id="owner-country"/>
									</div>
									<?php } else { ?>
									<div class="col-sm-2">
										<select id="owner-country" class="form-control required" name="owner-country" tabindex="2">
											<option value="">Select Country...</option>
											<option value="GB">United Kingdom</option>
										</select>
									</div>
									<?php } ?>
									<label class="col-sm-1 control-label">Postcode</label>
									<div class="col-sm-2">
										<input type="text" class="form-control<?php if($client_id > 0){echo ' valid" disabled="disabled';} ?>" value="<?php echo $client_details->Postcode; ?>" placeholder="Postcode" name="owner-postcode" id="owner-postcode" tabindex="2" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>				

				<div class="row">
					<div class="col-sm-12">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title">3. Dog and Test Details</h3>
							</div>
							<div class="panel-body">
								<div class="col-xs-3 col-md-2 col-lg-1"
									style="text-align: center">
									How many dogs do you wish to order tests for?<br> 
									<input class="form-control" type="text" size="2" value="0" id="noDogs" name="noDogs" style="text-align: center; font-weight: bold; font-size: 1.5em;">
								</div>
								<div class="col-xs-9 col-md-10 col-lg-11">
									<table class="table table-condensed table-responsive table-striped" id="dogsTable" style="display: none">
										<thead></thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<input type="submit" value="Record Details" id="form_submission_but" name="submit" class="btn btn-primary pull-right" disabled="disabled" />
			</form>
		</div>
	</section>
	
	<!-- Verification Details Modal -->
	<div class="modal" id="vetModal" tabindex="-1" role="dialog" aria-labelledby="vetModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="vetModalLabel">Verifier Details</h4>
				</div>
				<div class="modal-body">
					<form class="form-horizontal" id="vet_details_form">
						<div class="form-group">
							<label class="col-sm-3 control-label">Verifier Name</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" value="" name="vet-name" id="vet-name"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Address</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" value="" name="vet-address" id="vet-address"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">City</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" value="" name="vet-city" id="vet-city"/>
							</div>
							<label class="col-sm-2 control-label">Postcode</label>
							<div class="col-sm-3">
								<input type="text" class="form-control" value="" name="vet-postcode" id="vet-postcode"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Email</label>
							<div class="col-sm-9">
								<input type="email" class="form-control" value="" name="vet-email" id="vet-email"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Phone</label>
							<div class="col-sm-4">
								<input type="tel" class="form-control" value="" name="vet-phone" id="vet-phone"/>
							</div>
							<label class="col-sm-2 control-label">Fax</label>
							<div class="col-sm-3">
								<input type="tel" class="form-control" value="" name="vet-fax" id="vet-fax"/>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal" id="but-cancel-vet">Cancel</button>
					<button type="button" class="btn btn-primary" data-dismiss="modal" id="but-add-vet">Add Verifier</button>
				</div>
			</div>
		</div>
	</div>

<?php 
$sql = "select ID, breed from breed_list WHERE is_primary=1 order by breed";
$results = $wpdb->get_results($sql, OBJECT );
$allBreeds = array();
//$breedTests = array("all" => array("CP" => "Canine DNA profiles (ISAG 2006)"));
$breedTests = array("all" => array());

foreach ( $results as $breedObj ){
    $allBreeds[$breedObj->ID] = $breedObj->breed;
    $sql2 = "SELECT test_code, test_name, concat('\"',test_code, '\":\"', test_name,'\"') as test
    from breed_test_lookup inner join test_codes using (test_code) 
    WHERE breed_id = {$breedObj->ID}
    order by test_name";
    $results2 = $wpdb->get_results($sql2, OBJECT );
    if (count($results2) > 0){
    	$breedTests[$breedObj->ID] = array();
        foreach ( $results2 as $testObj ){
            $breedTests[$breedObj->ID][$testObj->test_code] = $testObj->test_name;
        }
    }
}

$sql = "SELECT test_code, test_name, concat('\"',test_code, '\":\"', test_name,'\"') as test
	from breed_test_lookup inner join test_codes using (test_code)
	WHERE breed_id = 0 and test_code<>'WP'
    order by test_name";
$results = $wpdb->get_results($sql, OBJECT );
foreach ( $results as $testObj ){
	$breedTests["all"][$testObj->test_code] = $testObj->test_name;
}

$js_for_footer = '
<script type="text/javascript">
	var allBreeds = '.json_encode($allBreeds).';
    var breedTests = '.json_encode($breedTests).';
console.log(breedTests);
</script>';
?> 
		
<?php echo $js_for_footer; ?>						  			
<?php get_footer(); ?>
