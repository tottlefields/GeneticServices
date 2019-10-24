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
		$date = new DateTime(new DateTimeZone("Europe/London"));
		$returned_date = $date->format('Y-m-d H:i:s');
		$date->add(new DateInterval('P14D'));
	
		$update_args = array(
			'received_by' => $current_user->user_login,
		    'returned_date' => $returned_date,
			'due_date' => $date->format('Y-m-d')
		);
		
		foreach ($samples as $swabID){
			if (preg_match('/^[a-zA-Z]{2,5}\d{1,3}$/', $swabID)){
				//Portal ID
				$sql = "select * FROM order_tests WHERE PortalID='".$swabID."' AND kit_sent is not null AND returned_date IS NULL";
				$results = $wpdb->get_results($sql);
				if (count($results) == 1){
					$wpdb->update('order_tests', $update_args, array('id' => $results[0]->id));
					createSwabs($results[0]->id);
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
					createSwabs($results[0]->id);
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
					<h1>Welcome to DENNIS
						<span style="font-size:50%"><ul class="breadcrumb pull-right">
							<?php custom_breadcrumbs(); ?>
						</span>
					</h1>
					
					<div class="row">
						<div class="col-sm-12">
    						<div class="jumbotron text-center">
    							<div class="container">
									<form id="search-form" role="form" method="post" action="<?php echo get_site_url(); ?>/search/">
										<div class="col-md-6 col-md-offset-3">
											<div class="input-group input-group-lg">
												<input type="text" class="form-control search-form" placeholder="Search" name="q">
												<span class="input-group-btn">
													<button type="submit" class="btn btn-primary search-btn" data-target="#search-form"><i class="fa fa-search"></i></button>
												</span>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>

					<section class="row text-center placeholders">
						<?php $count = countOrders($order_steps[0]); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox blue">
								<div class="number"><i class="fa fa-shopping-basket"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Pending Orders</div>
								<?php if ($count>0){ ?><div class="footer"><a href="<?php echo get_site_url(); ?>/orders/"> View list</a></div><?php } ?>
							</div>
						</div>
						<?php $count = countUnextracted(); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox yellow">
								<div class="number"><i class="fa fa-flask"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Untested Samples</div>
								<?php if ($count>0){ ?><div class="footer"><a href="<?php echo get_site_url(); ?>/samples/"> View list</a></div><?php } ?>
							</div>
						</div>
						<?php $count = countRepeats(); ?>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox red">
								<div class="number"><i class="fa fa-repeat"></i>&nbsp;<?php echo $count; ?></div>
								<div class="title">Pending Repeats</div>
								<?php if ($count>0){ ?><div class="footer"><a href="#"> View list</a></div><?php } ?>
							</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox green">
								<div class="number"><?php echo countOrders($order_steps[4]); ?></div>
								<div class="title">List 4</div>
								<div class="footer"><a href="#"> FOOTER</a></div>
							</div>
						</div>
					</section>
					
					<section class="row">
						<div class="col-sm-12 col-md-6">
							<div class="box panel-primary">
								<div class="panel-heading box-heading"><h2 class="panel-title">Dispatch Kits</h2></div>
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
							<div class="box panel-primary">
								<div class="panel-heading box-heading"><h2 class="panel-title">Book in Samples</h2></div>
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