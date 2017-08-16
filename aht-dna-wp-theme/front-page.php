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
									<form id="search-form" role="form" method="post" action="/search/">
										<div class="col-md-6 col-md-offset-3">
											<div class="input-group input-group-lg">
												<input type="text" class="form-control search-form" placeholder="Search">
												<span class="input-group-btn">
													<button type="submit" class="btn btn-primary search-btn" data-target="#search-form" name="q"><i class="fa fa-search"></i></button>
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
							<h4>This Month</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-purple.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-flask" aria-hidden="true"></i><?php echo countTests(date('Y'), date('m')); ?></div>
							</div>
							<div class="text-muted">Tests ordered this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-flask" aria-hidden="true"></i><?php echo countTests(date('Y')); ?></div>						
							</div>
							<div class="text-muted">Tests ordered this year.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Month</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-purple.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo countOrders(date('Y'), date('m')); ?></div>	
							</div>
							<div class="text-muted">Total orders this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo countOrders(date('Y')); ?></div>	
							</div>
							<div class="text-muted">Total orders this year.</div>
						</div>
					</section>

<?php get_footer(); ?>