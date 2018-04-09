<?php /* Template Name: Statistics */ ?>
<?php get_header(); ?>
					<h1><?php wp_title('', true,''); ?>
						<span style="font-size:50%"><ul class="breadcrumb pull-right">
							<?php custom_breadcrumbs(); ?>
						</span>
					</h1>

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
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo orderStats(date('Y'), date('m')); ?></div>	
							</div>
							<div class="text-muted">Total orders this month.</div>
						</div>
						<div class="col-md-3 col-sm-6 placeholder">
							<h4>This Year</h4>
							<div class="circle" style="background-image: url(<?php echo get_template_directory_uri() . '/assets/img/circle-green.png'; ?>);">
								<div style="line-height:200px;"><i class="fa fa-shopping-basket" aria-hidden="true"></i><?php echo orderStats(date('Y')); ?></div>	
							</div>
							<div class="text-muted">Total orders this year.</div>
						</div>
					</section>
					
					<h2 style="text-align:left">Yearly test Statistics</h2>
					<section class="row">
						<div class="col-md-4">
							<canvas id="myChart" height="400"></canvas>
							<?php
							$sql = "select date_format(OrderDate, '%Y-%m') as OrderMonth, count(*) from orders inner join order_tests on orders.id=order_id where cancelled_date is null group by 1";
						/*	+------------+----------+
							| OrderMonth | count(*) |
							+------------+----------+
							| 2017-01    |      554 |
							| 2017-02    |      609 |
							| 2017-03    |      601 |
							| 2017-04    |      466 |
							| 2017-05    |      517 |
							| 2017-06    |      501 |
							| 2017-07    |      495 |
							| 2017-08    |      475 |
							| 2017-09    |      894 |
							| 2017-10    |     1513 |
							| 2017-11    |       32 |
							+------------+----------+  */
							?>

							<script>
								var ctx = document.getElementById("myChart").getContext('2d');
								var myChart = new Chart(ctx, {
									type: 'line',
									data: {
										labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
										datasets: [/*{
											label: '2016',
											data: [554,609,601,466,517,501,495,475,894,1513,32,0],
											//backgroundColor: 'rgba(255, 99, 132, 0.2)',
											borderColor: 'rgba(255,99,132,1)',
											borderWidth: 1
										},*/{
											label: '2017',
											data: [554,609,601,466,517,501,495,475,894,1513,32],
											backgroundColor: window.chartColors.purple,
											borderColor: window.chartColors.purple,
											borderWidth: 1,
											fill: false
										}]
									},
									options: {
										maintainAspectRatio: false,
										responsive: true,
										title: { display: true, text: 'Tests Sold per Month' }
										/*scales: {
											yAxes: [{
												ticks: {
													beginAtZero:true
												}
											}]
										}*/
									}
								});
								</script>
						</div>
						
						<div class="col-md-4">
							<canvas id="myChart2" height="400"></canvas>
							<?php
							$sql = " select test_code, count(*) from order_tests where cancelled_date is null group by 1 order by 2 desc limit 15";	//5210
						/*	+-----------+----------+
							| test_code | count(*) |
							+-----------+----------+
							| SPS       |      903 |
							| CKCS_ALL  |      552 |
							| CORD1     |      478 |
							| PRA4      |      390 |
							| WP        |      362 |
							| PLL       |      351 |
							| CP        |      321 |
							| TT_ALL    |      299 |
							| RET       |      282 |
							| RCD4      |      279 |
							| PRA3      |      244 |
							| POAG      |      220 |
							| POAG3     |      204 |
							| SBT_ALL   |      167 |
							| HC        |      158 |
							+-----------+----------+	*/ 
							$sql2 = "select count(*) from order_tests where cancelled_date is null";
						/*	+----------+
							| count(*) |
							+----------+
							|     6660 |
							+----------+	*/ 

							?>

							<script>
								var ctx2 = document.getElementById("myChart2").getContext('2d');
								var myChart2 = new Chart(ctx2, {
									type: 'doughnut',
									data: {
										labels: ["SPS", "CKCS_ALL", "CORD1", "PRA4", "WP", "PLL", "CP", "TT_ALL", "RET", "RCD4", "PRA3", "POAG", "POAG3", "SBT_ALL", "HC", "Others"],
										datasets: [ {
											label: '2017',
											data: [903,552,478,390,362,351,321,299,282,279,244,220,204,167,158,1450],
											backgroundColor: [
												window.chartColors.red,
												window.chartColors.orange,
												window.chartColors.yellow,
												window.chartColors.green,
												window.chartColors.blue,
												window.chartColors.purple,
												window.chartColors.red,
												window.chartColors.orange,
												window.chartColors.yellow,
												window.chartColors.green,
												window.chartColors.blue,
												window.chartColors.purple
											],
										}]
									},
									options: {
										maintainAspectRatio: false,
										responsive: true,
										title: { display: true, text: 'Top Selling Tests' },
										legend: {display: true, position: 'right'  }
										/*scales: {
											yAxes: [{
												ticks: {
													beginAtZero:true
												}
											}]
										}*/
									}
								});
								</script>
						</div>
					</section>

<?php get_footer(); ?>