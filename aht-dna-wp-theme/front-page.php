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
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox purple">
								<div class="number"><?php echo countOrders($order_steps[0]); ?></div>
								<div class="title">Pending Orders</div>
								<div class="footer"><a href="/orders/"> View list</a></div>
							</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<div class="statbox yellow">
								<div class="number"><?php echo countOrders($order_steps[1]); ?></div>
								<div class="title">Untested Samples</div>
								<div class="footer"><a href="/orders/"> View list</a></div>
							</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Month</h4>
							<div class="statbox blue">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo orderStats(date('Y'), date('m')); ?></div>	
							</div>
							<div class="text-muted">Total orders this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="statbox green">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo orderStats(date('Y')); ?></div>	
							</div>
							<div class="text-muted">Total orders this year.</div>
						</div>
					</section>

<?php get_footer(); ?>