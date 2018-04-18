	
	<!-- NOTES MODAL -->
	<div class="modal fade" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="notesModalLabel"><i class="fa fa-file-text-o"></i>&nbsp;Notes</h2>
					</div>
					<div class="modal-body">		
						<p id="all_test_notes"></p>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="notes_id" name="notes_id" value="">
						<input type="hidden" name="order_test_id" value="">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	</div>