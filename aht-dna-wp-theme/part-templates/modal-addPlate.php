	<?php global $current_user; ?>
	<!-- PLATE MODAL -->
	<div class="modal fade" id="addPlateModal" tabindex="-1" role="dialog" aria-labelledby="addPlateModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post" onsubmit="return postForm()">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="platesModalLabel"><i class="fa fa-file-text-o"></i>&nbsp;Add a Plate</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label for="plate_type" class="control-label col-sm-2">Plate Type</label>
									<div class="col-sm-10"><input type="text" class="form-control" id="plate_type" name="plate_type" value=""></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="note_test_id" name="order_test_id" value="">
						<input type="hidden" name="username" value="<?php echo $current_user->user_login; ?>">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary" name="note-submitted">Add Plate</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
		var postForm = function() {
			//var content = $('textarea[name="note_content"]').text($('#summernote').summernote('code'));
			//var get_code = $('#summernote').summernote('code');
			console.log(get_code);
			return false;
		}
	</script>