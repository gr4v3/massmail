<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Mailing unsubscribe</title>
	<style type="text/css">
	body {
		background-color: #0061CC;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: black;
	}
	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}
	h1 {
		background-color: whitesmoke;
		border-bottom: 1px solid black;
		font-size: 19px;
		font-weight: bold;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
		text-align:center;
	}
	#body{
		margin: 0 15px 0 15px;
	}
	#body div {
		text-align:center;
		margin:10px;
	}
	#container{
		background-color: #BFD7F2;
		width:800px;
		margin:0 auto;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
	.reason td {
		text-align:right;
	}
	.error {
		position:absolute;
		visibility:hidden;
		background-color:red;
	}
	.execute {
		width:200px;
	}
	.support {
		text-align:right;
	}
	.powered {
		text-align:right !important;
		font-size:12px;
		margin:0 !important;
		padding:2px;
	}
	.footer{
		text-align: center;
		font-size: 11px;
		border-top: 1px solid black;
		line-height: 20px;
		background-color: whitesmoke;
	}
	</style>
</head>
<body>

<div id="container">
	<h1><u>Access Clic France</u> mailing unsubscription form</h1>

	<div id="body">
		<?php echo form_open('mailing/unsubscribe'); ?>
		<div>
			<div>Dear customer, please tell us the reason for your unsubscription&nbsp;</div>
			<div>
				<?php
					$options = array(
									'never requested this mailing',
									'never requested this mailing',
									'i don\'t like the mail content',
									'the links contained in the mail don\'t work'
									);

					echo form_dropdown('reason', $options, 0);
				?>
			</div>
			<div>
				<?php
					echo form_label('Confirm your request for unsubscription by typing your address in the textbox below', 'address');
					$input_properties = array(
						'id'    => 'address',
						'name'  => 'address',
						'style' => 'width:200px'
					);
					if (isset($info)) echo '<div class="error" title="address">' . htmlentities($info) . '</div>';
					if (isset($mailing_group_id)) echo form_hidden('mailing_group_id', $mailing_group_id);
					if (isset($emails_data_id)) echo form_hidden('emails_data_id', $emails_data_id);
					echo '<br /><br />';
					echo form_input($input_properties);
					echo '<br />';
					echo form_submit('execute',lang('OK'),'class="execute"');
				?>
			</div>
			<div class="support">Technical support: <b>support@mailerberrueta.net</b></div>

		</div>
		<?php echo form_close(); ?>
	</div>
	<div class="powered">powered by <a href="https://sendgrid.com">sendgrid</a></div>
	<div class="footer"><a href="https://www.accesnet.info/services/get_subadmin_cgv/langcode/en" target="_blank">Terms and Conditions</a>&nbsp;&nbsp;&nbsp;Copyright <a target="_blank">Access Clic France</a> All Rights Reserved.&nbsp;&nbsp;&nbsp;<a href="https://www.accesnet.info/services/get_privacy_policy/langcode/en" target="_blank">Privacy Policy</a></div>
</div>

</body>
</html>