					
					<div class="clearfix"></div>
					<!-- <footer>
						<p><span style="text-align:left;float:left">&copy; <?php echo date('Y'); ?> DENNIS</span></p>
					</footer> -->
				</main><!--/#content.col-sm-9.col-md-10-->
				<!-- end: Content -->

			</div><!--/row-->
		</div><!--/.container-fluid-->
		
		
		<!-- start: JavaScript-->
		<!-- <script>(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(d.href.indexOf("http")||~d.href.indexOf(e.host))&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone")</script> -->
		<script>
		var DennisAjax = {"ajax_url":"<?php echo admin_url( 'admin-ajax.php' ); ?>", "site_url":"<?php echo get_site_url(); ?>"};
		</script>
		<?php wp_footer(); ?>
		
		<script>
		$(document).ready(function() {
			$('.datepick').datepicker({
				format: 'dd/mm/yyyy'
			});
			$('.summernote').summernote({
					dialogsInBody: true
			});
		});
		<!-- <script src="<?php echo get_template_directory_uri() . '/assets/js/custom.js';?>"></script> -->
		<!-- end: JavaScript-->

	</body>
</html>
