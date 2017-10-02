<?php /* Template Name: Samples */ ?>
<?php 
if (isset($_REQUEST) && count($_REQUEST)>0){

	if (isset($_REQUEST['dispatch-kits'])){
	
		$samples = explode("\n", str_replace("\r", "", $_REQUEST['samples']));
		$error = '';
		$samples_updated = 0;
	
		$update_args = array(
				'sent_by' => $current_user->user_login,
				'kit_sent' => date('Y-m-d')
		);
	
		foreach ($samples as $swabID){
			if (preg_match('/^[a-zA-Z]{2,5}\d{1,3}$/', $swabID)){
				//Portal ID
				$sql = "select * FROM order_tests WHERE PortalID='".$swabID."' AND kit_sent IS NULL";
				$results = $wpdb->get_results($sql);
				if (count($results) == 1){
					$wpdb->update('order_tests', $update_args, array('id' => $results[0]->id));
					$samples_updated++;
				}
				else{
					$error .= "ERROR! Can't find an order with this ID (<a href='/search/?q=$swabID'>$swabID</a>)<br />Has this kit already been dispatched?<br />";
				}
			}
			elseif (preg_match('/^\d{1,5}\/\d{1,5}$/', $swabID)){
				//Swab ID
				$sample_details = explode('/', $swabID);
				$sql = "select * FROM order_tests WHERE id='".$sample_details[1]."' AND kit_sent is null";
				$results = $wpdb->get_results($sql);
				if (count($results) == 1){
					$wpdb->update('order_tests', $update_args, array('id' => $results[0]->id));
					$samples_updated++;
				}
				else{
					$error .= "ERROR! Can't find an order with this ID (<a href='/search/?q=$swabID'>$swabID</a>)<br />Has this kit already been dispatched?<br />";
				}
			}
		}
	}
	
	if (isset($_REQUEST['book-in-samples'])){
	
		$samples = explode("\n", str_replace("\r", "", $_REQUEST['samples']));
		$error = '';
		$samples_updated = 0;
	
		$update_args = array(
			'received_by' => $current_user->user_login,
			'returned_date' => date('Y-m-d')
		);
		
		foreach ($samples as $swabID){
			if (preg_match('/^[a-zA-Z]{2,5}\d{1,3}$/', $swabID)){
				//Portal ID
				$sql = "select * FROM order_tests WHERE PortalID='".$swabID."' AND kit_sent is not null AND returned_date IS NULL";
				$results = $wpdb->get_results($sql);
				if (count($results) == 1){
					$wpdb->update('order_tests', $update_args, array('id' => $results[0]->id));
					$samples_updated++;
				}
				else{
					$error .= "ERROR! Can't find an kit with with this id (<a href='/search/?q=$swabID'>$swabID</a>)<br />Has this kit already been received?<br />";
				}
			}
			elseif (preg_match('/^\d{1,5}\/\d{1,5}$/', $swabID)){
				//Swab ID
				$sample_details = explode('/', $swabID);
				$sql = "select * FROM order_tests WHERE id='".$sample_details[1]."' AND kit_sent is not null AND returned_date IS NULL";
				$results = $wpdb->get_results($sql);
				if (count($results) == 1){
					$wpdb->update('order_tests', $update_args, array('id' => $results[0]->id));
					$samples_updated++;
				}
				else{
					$error .= "ERROR! Can't find an kit with with this id (<a href='/search/?q=$swabID'>$swabID</a>)<br />Has this kit already been received?<br />";
				}
			}
		}
	}
}
?>

<?php get_header(); ?>

	<h1><?php wp_title('', true,''); ?><ul class="breadcrumb pull-right" style="font-size:50%"><?php custom_breadcrumbs(); ?></h1>
						
					<section class="row">
						<div class="col-sm-12 col-md-6">
							<div class="panel panel-default">
								<div class="panel-heading"><h2 class="panel-title">Dispatch Kits</h2></div>
								<div class="panel-body">
									<form class="form-horizontal" method="post">
										<div class="col-sm-6 col-md-4">
											<p class="form-control-static input-lg lead"><strong><?php echo $current_user->user_login; ?></strong></p>
											<p class="form-control-static input-lg lead"><strong><?php echo date('d/m/Y'); ?></strong></p>
											<?php if (isset($_REQUEST['dispatch-kits'])){ ?>
											<?php if($error != ''){?>
												<div class="alert alert-danger" role="alert"><strong>Error!</strong> <?php echo $error; ?></div>			
											<?php }
											elseif ($samples_updated > 0){?>
												<div class="alert alert-success" role="alert"><strong>Sucess!</strong> <?php echo $samples_updated; ?> samples have been updated in DENNIS.</div>
											<?php } 
											} ?>
										</div>
										<div class="col-sm-6 col-md-8">
											<textarea name="samples" class="form-control" rows="10"></textarea>
											<button type="submit" name="dispatch-kits" class="btn btn-primary btn-block" style="margin-top:15px;">Send</button>
										</div>
									</form>
								</div>
							</div>
						</div>
						
					
						<div class="col-sm-12 col-md-6">
							<div class="panel panel-default">
								<div class="panel-heading"><h2 class="panel-title">Book in Samples</h2></div>
								<div class="panel-body">
									<form class="form-horizontal" method="post">
										<div class="col-sm-6 col-md-4">
											<p class="form-control-static input-lg lead"><strong><?php echo $current_user->user_login; ?></strong></p>
											<p class="form-control-static input-lg lead"><strong><?php echo date('d/m/Y'); ?></strong></p>
											<?php if (isset($_REQUEST['book-in-samples'])){ ?>
											<?php if($error != ''){?>
												<div class="alert alert-danger" role="alert"><strong>Error!</strong> <?php echo $error; ?></div>			
											<?php }
											elseif ($samples_updated > 0){?>
												<div class="alert alert-success" role="alert"><strong>Sucess!</strong> <?php echo $samples_updated; ?> samples have been updated in DENNIS.</div>
											<?php } 
											} ?>
										</div>
										<div class="col-sm-6 col-md-8">
											<textarea name="samples" class="form-control" rows="10"></textarea>
											<button type="submit" name="book-in-samples" class="btn btn-primary btn-block" style="margin-top:15px;">Book In</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</section>
					
<?php get_footer(); ?>