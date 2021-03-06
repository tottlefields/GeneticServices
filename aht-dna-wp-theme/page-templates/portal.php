<?php /* Template Name: Portal */ ?>
<?php 

global $current_user;
get_currentuserinfo();

ini_set("auto_detect_line_endings", true);

if (isset($_REQUEST['submit'])){
	$tests_loaded = 0;
	$error = '';
	if ($_FILES["portal_file"]["error"] == 0){ // && preg_match('/text/', $_FILES["portal_file"]["type"])){
		$fh = fopen($_FILES['portal_file']['tmp_name'], 'rb');
		$first_line = fgets($fh);
		if (count(explode(',', $first_line)) > 1){ $error .= "File is comma-separated. Please re-save as tab-delimited and try uploading again."; }
		else{
			while (($line = fgetcsv($fh, 1000, "\t")) !== false){
				# [0] => Returned? // [1] => Swab# [2] => Test // [3] => Vet Verified? // [4] => Report?
				# [5] => Registered Name // [6] => Pet Name // [7] => Reg No. // [8] => Microchip // [9] => DOB // [10] => Breed // [11] => Colour // [12] => Sex
				# [13] => Owner // [14] => Email // [15] => Phone // [16] => Address // [17] => Town // [18] => County // [19] => Country // [20] => Postcode
				# [21] => Vet // [22] => Vet Email // [23] => Vet Phone // [24] => Vet Fax // [25] => Vet Address // [26] => Vet Town // [27] => Vet Postcode
				# [28] => Research?
				
				if ($line[0] === 'Returned?'){ continue; }
				
				$client_id = 0;				
				$clients = clientSearch(array(
						'Postcode'	=> $line[20],
						'FullName'	=> $line[13],
						'Email'		=> $line[14],
				));
				if (count($clients) == 0){ $client_id = addNewClient(array(
						'FullName'	=> $line[13],
						'Email'		=> $line[14],
						'Tel'		=> $line[15],
						'Address'	=> $line[16],
						'Town'		=> $line[17],
						'County'	=> $line[18],
						'Country'	=> $line[19],
						'Postcode'	=> $line[20],
				)); }
				elseif (count($clients) == 1) { $client_id = $clients[0]->id; }
				else { $error .= "multiple clients match : ".$line[13]. '/ '.$line[14]; break; }
				
				if ($client_id == 0){ $error .= "Something has gone wrong with getting a client id : ".$line[13]. '/ '.$line[14]; break; }

				$animal_id = 0;
				$animals = animalSearch(array(
						'RegisteredName'	=> $line[5],
						'RegistrationNo'	=> $line[7],
						'TattooOrChip'		=> $line[8],
				), $client_id);
				if (count($animals) == 0){
					$animal_id = addNewAnimal(array(
							'RegisteredName'	=> $line[5],
							'PetName'			=> $line[6],
							'RegistrationNo'	=> $line[7],
							'TattooOrChip'		=> $line[8],
							'BirthDate'			=> dateToSQL($line[9]),
							'Breed'				=> $line[10],
							'Colour'			=> $line[11],
							'Sex'				=> substr($line[12],0,1),
							'client_id'			=> $client_id
					));
				}
				elseif (count($animals) == 1) { $animal_id = $animals[0]->id; }
				else {  $error = "multiple animals match : ".$line[5]." / ".$line[7]." / ".$line[8]; break; }
				
				if ($animal_id == 0){ $error .= "Something has gone wrong with getting an animal id : ".$line[5]. '/ '.$line[7]; break; }	
	
				$orders = orderSearch(array(
						'OrderDate'	=> date('Y-m-d'),
						'client_id'	=> $client_id
				));
				if (count($orders) == 0) { 
					$order_id = addNewOrder(array(
							'client_id'			=> $client_id,
							'OrderDate'			=> date('Y-m-d'),
							'ReportFormat'		=> $line[4],
							'VetReportFormat'	=> ($line[3] == 'Yes') ? $line[4] : NULL,
							'AgreeResearch'		=> ($line[28] == 'Yes') ? 1 : 0,
							'Paid'				=> 1
					));
				}
				elseif (count($orders) == 1) { $order_id = $orders[0]->ID; }
				else {  $error .= "multiple orders match : ".$line[13].' ('.$client_id.') / '.date('Y-m-d'); break; }
				
				$vet_id = NULL;
				if ($line[3] == 'Yes'){
					$vets = vetSearch(array(
							'FullName'	=> $line[21],
							'Email'		=> $line[22],
					));
					if (count($vets) == 0) { 
						$vet_id = addNewVet(array(
								'FullName'	=> $line[21],
								'Email'		=> $line[22],
								'Tel'		=> $line[23],
								'Fax'		=> $line[24],
								'Address'	=> $line[25],
								'Town'		=> $line[26],
								'Postcode'	=> $line[27],
						));
					}
					elseif (count($vets) == 1) { $vet_id = $vets[0]->id; }
					else {  $error .= "multiple vets match : ".$line[21].' / '.$line[22]; break; }
				}
	
				$results = $wpdb->get_results("SELECT * from order_tests WHERE PortalID='".$line[1]."'", OBJECT );
				if (count($results) == 0){
					$swab_id = addOrderTest(array(
							'order_id'	=> $order_id,
							'PortalID'	=> $line[1],
							'animal_id'	=> $animal_id,
							'test_code' => $line[2],
							'VetID'		=> $vet_id,
							'kit_sent'	=> date('Y-m-d'),
							'returned_date'	=> ($line[0]) ? date('Y-m-d') : NULL,
							'received_by'	=> ($line[0]) ? $current_user->user_login : NULL
					));
					if($swab_id > 0){
						$tests_loaded++;
					}
					else {  $error .= "test hasn't loaded correctly : ".$line[1]; break; }
				}
			}
		}
	}
}

?>
<?php get_header(); ?>
<?php
    global $post;
    $post_slug=$post->post_name;
    
    ?>
    <h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
	<section class="row">
		<div class="col-md-6">
<?php
	global $wpdb;

	$sql = "select concat('\"', breed, '\":{', group_concat(concat('\"',test_code, '\":\"', test_name,'\"') order by test_name), '}') as row 
    		from breed_test_lookup inner join test_codes using (test_code) 
    		inner join breed_list on ID=breed_id where no_results=1 group by breed order by breed";
	$results = $wpdb->get_results($sql, OBJECT );
	
	if ( $results ){
		echo '<pre style="margin-bottom:50px;">
var breedTests = {
	"all": {"CP":"Canine DNA profiles (ISAG 2006)"},
';
		foreach ( $results as $row ){
			echo "\t".$row->row.",\n";
		}
		echo '};</pre>';
	}
?>
		</div>
		<div class="col-md-6">
			<div class="panel">
			<div class="panel-body">
			<h2 style="text-align: left;">Upload Portal Export</h2>
			<?php if($error != ''){?>
				<div class="alert alert-danger" role="alert"><strong>Error!</strong> <?php echo $error; ?></div>			
			<?php }
			elseif ($tests_loaded > 0){?>
				<div class="alert alert-success" role="alert"><strong>Sucess!</strong> <?php echo $tests_loaded; ?> tests have been loaded into DENNIS.</div>
			<?php }
			elseif (isset($tests_loaded) && $tests_loaded == 0){?>
				<div class="alert alert-warning" role="alert"><strong>Warning!</strong> <?php echo $tests_loaded; ?> tests were loaded - is this expected?.</div>
			<?php }?>
			<form method="post" class="form-horizontal" enctype="multipart/form-data">
				<div class="col-md-10">
					<div class="input-group">
						<label class="input-group-btn">
							<span class="btn btn-primary">Browse&hellip;<input type="file" name="portal_file" style="display:none;"></span>
						</label>
						<input type="text" class="form-control" readonly />
					</div>
				</div>
				<div class="col-md-2">
					<button type="submit" class="btn btn-default" name="submit">GO!</button>
				</div>
			</form>
			</div>
			</div>
		</div>
	</section>

<?php get_footer(); ?>
