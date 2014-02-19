<?php echo form_open('import/emails_stack_csv'); ?>
	<div>
		<span><?php echo form_label('webmaster_id','webmaster_id'); ?></span>
		<span><?php echo form_input('webmaster_id', FALSE, TRUE); ?></span>
	</div>
	<div>
		<span><?php echo form_label('email_collected_id','email_collected_id'); ?></span>
		<span><?php echo form_input('email_collected_id', FALSE, TRUE); ?></span>
	</div>
        <div>
		<span><?php echo form_label('niche_id','niche_id'); ?></span>
		<span><?php echo form_input('niche_id', FALSE, TRUE); ?></span>
	</div>
        <div>
		<span><?php echo form_label('lang_iso','lang_iso'); ?></span>
		<span><?php echo form_input('lang_iso', FALSE, TRUE); ?></span>
	</div>
	<div>
		<span><?php echo form_submit('mysubmit', 'submit'); ?></span>
	</div>
	<?php echo form_hidden('task', 'parse_file'); ?>
	<?php echo form_hidden('data', $data); ?>
	<?php echo form_hidden('file_path', $file_path); ?>
<?php echo form_close(); ?>
