<?php /* Template Name: Manual Order */ ?>
<?php 
if (isset($_POST['new-order-submitted'])) {

	//debug_array($_REQUEST);
	
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
//		'Town'		=> $_REQUEST['owner-town'],
//		'County'	=> $_REQUEST['owner-county'],
//		'Country'	=> $_REQUEST['owner-country'],
		'Postcode'	=> $_REQUEST['owner-postcode'],
	) ); }
	elseif (count($clients) == 1) { $client_id = $clients[0]->id; }
	else { echo "ERROR - multiple clients match"; exit; }
	
	$animals = animalSearch(array(
			'RegisteredName'	=> $_REQUEST['registered-name'],
			'RegistrationNo'	=> $_REQUEST['registration-number'],
			'TattooOrChip'		=> $_REQUEST['microchip']
	), $client_id);
	if (count($animals) == 0){
		$animal_id = addNewAnimal(array(
				'RegisteredName'=> $_REQUEST['registered-name'],
				'RegistrationNo'=> $_REQUEST['registration-number'],
				'Sex'		=> substr($_REQUEST['sex'],0,1),
				'BirthDate'	=> dateToSQL($_REQUEST['dog-birth-date']),
				'TattooOrChip'	=> $_REQUEST['microchip'],
				'PetName'	=> $_REQUEST['pet-name'],
				'Colour'	=> $_REQUEST['colour'],
				'breed_id'	=> $_REQUEST['breed'],
				'client_id'	=> $client_id
		));
	}
	elseif (count($animals) == 1) { $animal_id = $animals[0]->id; }
	else { echo "ERROR - multiple animals match"; exit; }
	
	$order_id = addNewOrder(array(
			'client_id'		=> $client_id,
			'OrderDate'		=> date('Y-m-d'),
			'ReportFormat'		=> $_REQUEST['format'],
			'VetReportFormat'	=> ($_REQUEST['vet-select'] > 0) ? $_REQUEST['format'] : NULL,
			'AgreeResearch'		=> ($_REQUEST['research'] == 'on') ? 1 : 0
	));
	
	foreach ($_REQUEST['breed_tests'] as $test){
		addOrderTest(array(
				'order_id'	=> $order_id,
				'animal_id'	=> $animal_id,
				'test_code'	=> $test,
				'VetID'		=> ($_REQUEST['vet-select'] > 0) ? $_REQUEST['vet-select'] : NULL
		));
	}
	
	wp_redirect(get_site_url().'/orders/');
	exit;
}?>
<?php get_header(); ?>
<?php
	global $post;
	$post_slug=$post->post_name;
	
	?>
	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-12">
			<form class="form-horizontal" id="swab_details_form" method="post">					
                <input type="hidden" name="new-order-submitted" value="1" />
				<div class="row">
					<div class="col-sm-6">
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
										<label class="radio-inline"><input type="radio" class="radiorequired" value="Fax" name="format"/> Fax</label>
									</div>
									
									<label class="col-sm-2 control-label">Vertification</label>
									<div class="col-sm-1">
										<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" data-size="small" name="vet-verified" id="vet-verified" />
									</div>
								  	<div class="col-sm-3" style="display:none" id="vet-select-div">
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
						
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title">2. Dog Details</h3>
							</div>
							<div class="panel-body">
								<div class="form-group">
								  	<label class="col-sm-3 control-label">Breed</label>
								  	<div class="col-sm-9">
								  		<select id="breed-select" class="form-control required" name="breed">
								  			<option value="">Select Breed of your Dog...</option>
								  			<?php 
								  			$sql = "select ID, breed from breed_list order by breed";
								  			$results = $wpdb->get_results($sql, OBJECT );
											foreach ( $results as $breedObj ){
												echo '<option value="'.$breedObj->ID.'">'.$breedObj->breed.'</option>'."\n";
											}
								  			?>
								  		</select>
								  	</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Registered Name</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" value="" name="registered-name" id="registered-name"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Registration No.</label>
									<div class="col-sm-3">
										<input type="text" class="form-control" style="text-transform:uppercase" value="" name="registration-number" id="registration-number"/>
									</div>
									<label class="col-sm-3 control-label">Pet Name</label>
									<div class="col-sm-3">
										<input type="text" class="form-control required" value="" name="pet-name" id="pet-name"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Birth Date</label>
									<div class="col-sm-3">
										<input type="text" value="" name="dog-birth-date" id="dog-birth-date" class="form-control datepick required num" autocomplete="off" placeholder="dd/mm/yyyy" />
									</div>
									<label class="col-sm-3 control-label">Sex</label>
									<div class="col-sm-3">
										<div class="radioerror"></div>
										<label class="radio-inline"><input type="radio" class="radiorequired" value="Male" name="sex" checked="checked"/> Male</label>
										<label class="radio-inline"><input type="radio" class="radiorequired" value="Female" name="sex"/> Female</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Colour</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" value="" name="colour" id="colour"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Microchip</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" value="" name="microchip" id="microchip"/>
									</div>
								</div>
							</div>
						</div>
					</div>
			
					<div class="col-sm-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title">3. Owner Details</h3>
							</div>
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-3 control-label">Owner's Name</label>
									<div class="col-sm-9">
										<input type="text" class="form-control required" value="" name="owner-name" id="owner-name"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Address</label>
									<div class="col-sm-9">
									 	<input type="text" class="form-control" value="" name="owner-address" id="owner-address"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Postcode</label>
									<div class="col-sm-3">
										<input type="text" class="form-control" value="" name="owner-postcode" id="owner-postcode"/>
									</div>
									<label class="col-sm-2 control-label">Email</label>
									<div class="col-sm-4">
										<input type="email" class="form-control required" value="" name="owner-email" id="owner-email"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Phone</label>
									<div class="col-sm-3">
										<input type="tel" class="form-control" value="" name="owner-phone" id="owner-phone"/>
									</div>
									<label class="col-sm-2 control-label">Fax</label>
									<div class="col-sm-4">
										<input type="tel" class="form-control" value="" name="owner-fax" id="owner-fax"/>
									</div>
								</div>
							</div>
						</div>					
						
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title">4. Tests Available</h3>
							</div>
							<div class="panel-body" id="available_tests">
								<p>Please first select your dog breed from the dropdown list in DOG DETAILS section to view the list of tests available.</p>
							</div>
						</div>
						
						<input type="submit" value="Record Details" id="form_submission_but" name="submit" class="btn btn-primary pull-right" disabled="disabled"/>
					</div>
				</div>
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
								<input type="email" class="form-control required" value="" name="vet-email" id="vet-email"/>
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

<?php get_footer(); ?>
