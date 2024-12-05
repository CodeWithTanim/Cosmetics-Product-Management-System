<?php 
require_once('./../../config.php');
if(isset($_GET['id']))
$client = $conn->query("SELECT * FROM clients where id ='{$_GET['id']}'");
foreach($client->fetch_array() as $k =>$v){
	if(!is_numeric($k))
	$$k = $v;
}
?>
<div class="container-fluid">
	<form action="" id="update-client">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="firstname" class="control-label">Firstname</label>
			<input type="text" name="firstname" class="form-control form" value="<?php echo isset($firstname) ? $firstname : '' ?>" required>
		</div>
		<div class="form-group">
			<label for="lastname" class="control-label">Lastname</label>
			<input type="text" name="lastname" class="form-control form" value="<?php echo isset($lastname) ? $lastname : '' ?>" required>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Contact</label>
			<input type="text" class="form-control form-control-sm form" name="contact" value="<?php echo isset($contact) ? $contact : '' ?>" required>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Gender</label>
			<select name="gender" id="" class="custom-select select" required>
				<option <?php echo isset($gender) && $gender == "Male" ? "selected" : '' ?>>Male</option>
				<option <?php echo isset($gender) && $gender == "Female" ? "selected" : '' ?>>Female</option>
			</select>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Default Delivery Address</label>
			<textarea class="form-control form" rows='3' name="default_delivery_address"><?php echo isset($default_delivery_address) ? $default_delivery_address : '' ?></textarea>
		</div>
		<div class="form-group">
			<label for="email" class="control-label">Email</label>
			<input type="text" name="email" class="form-control form" value="<?php echo isset($email) ? $email : '' ?>" required>
		</div>
		<div class="form-group">
			<label for="password" class="control-label">New Password</label>
			<input type="password" name="password" class="form-control form" value="" placeholder="(Enter value to change password)">
			<small class="text-muted"><i>Leave this blank if you don't want to update your password.</i></small>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Status</label>
			<select name="status" id="" class="custom-select select" required>
				<option value="1" <?php echo isset($status) && $status == "1" ? "selected" : '' ?>>Active</option>
				<option value="0" <?php echo isset($status) && $status == "0" ? "selected" : '' ?>>Inactive</option>
			</select>
		</div>
	</form>
</div>
<script>
	$('#update-client').submit(function(e){
		e.preventDefault();
		start_loader()
		var _this = $(this)
			 $('.err-msg').remove();
		$.ajax({
			url:_base_url_+'classes/Master.php?f=update_client',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
		    dataType: 'json',
			success:function(resp){
				if(typeof resp =='object' && resp.status == 'success'){
					location.reload()
				}else if(resp.status == 'failed' && !!resp.msg){
					var el = $('<div>')
						el.addClass("alert alert-danger err-msg").text(resp.msg)
						_this.prepend(el)
						el.show('slow')
						$("html, body,.modal").scrollTop(0);
						end_loader()
				}else{
					alert_toast("An error occured",'error');
					end_loader();
					console.log(resp)
				}
			}
		})
	})

</script>