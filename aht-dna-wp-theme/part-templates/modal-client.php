
	<!-- CLIENT/DELIVERY MODAL -->
	<div class="modal fade" id="clientModal" tabindex="-1" role="dialog" aria-labelledby="clientModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="clientModalLabel"><i class="fas fa-user"></i>&nbsp;Client Details</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label for="client_name" class="control-label col-sm-2">Name</label>
									<div class="col-sm-10"><input type="text" class="form-control" id="client_FullName" name="client_FullName" value=""></div>
								</div>
								<div class="form-group">
									<label for="client_email" class="control-label col-sm-2">Email</label>
									<div class="col-sm-4"><input type="email" class="form-control" id="client_Email" name="client_Email" value=""></div>
									<label for="client_phone" class="control-label col-sm-2">Phone</label>
									<div class="col-sm-4"><input type="tel" class="form-control" id="client_Tel" name="client_Tel" value=""></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="well">
									<h3 style="margin-top: 0px;margin-bottom: 20px;">Home Address</h3>
									<div class="form-group">
										<label for="client_address" class="control-label col-sm-2">Address</label>
										<div class="col-sm-10"><input type="text" class="form-control" id="client_Address" name="client_Address" value=""></div>
									</div>
									<div class="form-group">
										<label for="client_town" class="control-label col-sm-2">Town</label>
										<div class="col-sm-5 "><input type="text" class="form-control" id="client_Town" name="client_Town" value=""></div>
										<label for="client_postcode" class="control-label col-sm-2">Postcode</label>
										<div class="col-sm-3"><input type="text" class="form-control" id="client_Postcode" name="client_Postcode" value=""></div>
									</div>
									<div class="form-group">
										<label for="client_county" class="control-label col-sm-2">County</label>
										<div class="col-sm-5 "><input type="text" class="form-control" id="client_County" name="client_County" value=""></div>
										<label for="client_country" class="control-label col-sm-2">Country</label>
										<div class="col-sm-3"><input type="text" class="form-control" id="client_Country" name="client_Country" value=""></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="client_id" name="client_id" value="">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" name="client-submitted">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>