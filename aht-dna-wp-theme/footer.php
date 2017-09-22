					
					<div class="clearfix"></div>
					<footer>
						<p><span style="text-align:left;float:left">&copy; <?php echo date('Y'); ?> DENNIS</span></p>
					</footer>
				</main><!--/#content.col-sm-9.col-md-10-->
				<!-- end: Content -->

			</div><!--/row-->
		</div><!--/.container-fluid-->
		
		
		<!-- start: JavaScript-->
		<script>
		var DennisAjax = {"ajax_url":"<?php echo admin_url( 'admin-ajax.php' ); ?>"};
		</script>
		<?php wp_footer(); ?>
		
		<script>
		$(document).ready(function() {
			$('.datepick').datepicker({
				format: 'dd/mm/yyyy'
			});
		});
		<!-- <script src="<?php echo get_template_directory_uri() . '/assets/js/custom.js';?>"></script> -->
		<!-- end: JavaScript-->

	</body>
</html>