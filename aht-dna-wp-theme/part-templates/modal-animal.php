	
	<!-- ANIMAL MODAL -->
	<div class="modal fade" id="animalModal" tabindex="-1" role="dialog" aria-labelledby="animalModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form class="form form-horizontal" role="form" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h2 class="modal-title" id="animalModalLabel"><i class="fas fa-dog"></i>&nbsp;Animal Details</h2>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label for="animal_RegisteredName" class="control-label col-sm-2">Name</label>
									<div class="col-sm-10"><input type="text" class="form-control" id="animal_RegisteredName" name="animal_RegisteredName" value=""></div>
								</div>
								<div class="form-group">
									<label for="animal_RegistrationNo" class="control-label col-sm-2">Reg No.</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_RegistrationNo" name="animal_RegistrationNo" value=""></div>
									<label for="animal_PetName" class="control-label col-sm-2">Pet Name</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_PetName" name="animal_PetName" value=""></div>
								</div>
								<div class="form-group">
									<label for="animal_BirthDate" class="control-label col-sm-2">DOB</label>
									<div class="col-sm-4"><input type="text" value="" name="animal_BirthDate" id="animal_BirthDate" class="form-control datepick" autocomplete="off" placeholder="dd/mm/yyyy"></div>
									<label class="control-label col-sm-2">Sex</label>
									<div class="col-sm-4">
										<div class="radioerror"></div>
										<label class="radio-inline"><input type="radio" id="animal_Sex-Male" class="radiorequired" value="Male" name="animal_Sex" checked="checked"> Male</label>
										<label class="radio-inline"><input type="radio" id="animal_Sex-Female" class="radiorequired" value="Female" name="animal_Sex"> Female</label>
									</div>
								</div>
								<div class="form-group">
									<label for="animal_TattooOrChip" class="control-label col-sm-2">Microchip</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_TattooOrChip" name="animal_TattooOrChip" value=""></div>
									<label for="animal_Colour" class="control-label col-sm-2">Colour</label>
									<div class="col-sm-4"><input type="text" class="form-control" id="animal_Colour" name="animal_Colour" value=""></div>
								</div>
							</div>
						</div>					
					</div>
					<div class="modal-footer">
						<input type="hidden" id="animal_id" name="animal_id" value="">
						<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" name="animal-submitted">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>