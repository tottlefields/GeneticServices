	<?php global $current_user; ?>
	<!-- NOTES MODAL -->
	<div class="modal fade" id="addNoteModal" tabindex="-1" role="dialog" aria-labelledby="addNoteModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post" onsubmit="return postForm()">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="notesModalLabel"><i class="far fa-file-alt"></i>&nbsp;Add a Note</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<textarea class="form-control" rows="3" id="summernote" name="note_content"></textarea>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="note_test_id" name="order_test_id" value="">
						<input type="hidden" name="username" value="<?php echo $current_user->user_login; ?>">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary" name="note-submitted">Add Note</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
		var postForm = function() {
			var content = $('textarea[name="note_content"]').text($('#summernote').summernote('code'));
			//var get_code = $('#summernote').summernote('code');
			//console.log(get_code);
			//return false;
		}
	</script>