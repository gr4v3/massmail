<?php echo form_open_multipart('import/emails_stack_csv'); ?>
	<div>
		<span><?php echo form_label('csv','type'); ?></span>
		<span><?php echo form_radio('type', 'csv', TRUE); ?></span>
	</div>
	<div>
		<span><?php echo form_label('xml','type'); ?></span>
		<span><?php echo form_radio('type', 'xml', FALSE); ?></span>
	</div>
	<div>
		<span><?php echo form_label('upload file ','userfile'); ?></span>
		<span><?php echo form_upload('userfile', 'userfile', FALSE); ?></span>
	</div>
	<div>
		<span><?php echo form_hidden('task', 'upload_file'); ?><?php echo form_submit('mysubmit', 'submit'); ?></span>
	</div>
<?php echo form_close(); ?>