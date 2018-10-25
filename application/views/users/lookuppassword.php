<?php
//------------------------------>
// Error Message if any
//------------------------------>
if (isset($msg)) {
    echo '<h2 class="warning">' . $msg . '</h2>';
}

if (validation_errors() != '') {
    echo('<div class="form_errors"><p><strong>Please fix the following input errors:</strong></p>');
    echo(validation_errors());
    echo('</div>');
}
?>
<div class="row-fluid no-chart">
	<div class="box span5">
		<div class="box-header">
			<h2><i class="icon-edit"></i>Password Lookup</h2>
			<div class="box-icon">
				<strong><a href="javascript:history.back();" id="lnkBack">Back</a></strong>
			</div>
		</div>
		<div class="box-content">
<?php
//------------------------------>
// Fill Add form
//------------------------------> 
$attributes = array('class' => 'form-horizontal', 'id' => 'form', 'name' => 'form', 'novalidate' => 'novalidate');
echo form_open('/users/lookuppassword', $attributes);
?>        
			<fieldset>
			<table id="DataTables_Table_0" class="table table-striped table-bordered bootstrap-datatable datatable dataTable" aria-describedby="DataTables_Table_0_info">
        <tr>
				<th>By Email</th>
        </tr>
			</table>
				
				<div class="control-group">
					<label class="control-label" for="email">Email:</label>
					<div class="controls">
						<input type="text" id="email" name="email" class="input-xlarge" maxlength="100" size="30" required="required" value="<?php echo set_value('email'); ?>" />
					</div>
				</div>
        <?php if(!empty($password)){ ?>
				<div class="control-group">
					<label class="control-label" for="password">Password: </label>
					<div class="controls">
						<div class="">
							<p class="help-block" style="margin-top:6px;"><span><?php echo $password; ?> &nbsp;&nbsp;&nbsp;<a href="/users/changepassword">Change Password</a></span></p>
						</div>
					</div>
				</div>
        <?php }?>
				
				
				<div class="form-actions">
					<button type="submit" class="btn btn-primary">Lookup Password</button>
				</div>
			</fieldset>	
<?php echo form_close(); ?>
		</div>
	</div>
</div>


<script>
	$(function() {
	  $('#form').validate_popover({onsubmit: false, popoverPosition: 'right'});
	
	  $(".btn-primary").click(function(ev) {
		var valid = $('#form').validate().form();
		
		if(valid == false){
			ev.preventDefault();
			return false;
		}	
	  });
	
	  $(window).resize(function() {
			$.validator.reposition();
		});
	});
</script>