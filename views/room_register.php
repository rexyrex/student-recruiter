<!DOCTYPE html>
<html>
<?php require 'shared/head.php'; ?>
<body>
<?php require 'shared/navbar.php'; ?>
<div class="container">
	<div class="bs-docs-section">
	<div class="row">
		<div class="main col-md-12">

			<h1 class="page-header">Add a new room</h1>
			<?php require 'shared/notice.php'; ?>

			<div class="well col-md-8 col-md-offset-2">
				<form action="<?php echo $app->urlFor('room_store'); ?>" method="post" class="form-horizontal">	            

					<div class="form-group">
						<label class="col-sm-2 control-label">
							Room name:
						</label>
						<div class="col-sm-4">
							<input class="form-control" type="text" name="name" placeholder="eg. Lab 2">
						</div>
						<div class="col-sm-4">
							<input class="form-control" type="text" name="code" placeholder="room code eg. GHOLT-LAB2">
						</div>
					</div>				
					
					<div class="form-group">
						<label class="col-sm-2 control-label">
							Building:
						</label>
						<div class="col-sm-8">
							<select class="form-control" name="building_id">
								<?php foreach($buildings as $building) : ?>
								<option value="<?php echo $building->id; ?>"><?php echo $building->name?></option>
								<?php endforeach?>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-2 control-label">
							Room size:
						</label>
						<div class="col-sm-8">
							<input class="form-control" type="text" name="size" placeholder="Max capacity of the room, eg. 30">
						</div>
					</div>	

					<div class="col-sm-offset-2">
						<button type="reset" class="btn btn-default">Reset</button>
						<button type="submit" class="btn btn-primary">Submit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	</div>
</div>
<?php require 'shared/footer.php'; ?>
</body>
</html>