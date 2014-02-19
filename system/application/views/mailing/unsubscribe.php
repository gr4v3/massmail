<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if($info) echo htmlentities($info);
if($form) {
	echo form_open('mailing/unsubscribe');
	echo form_label('What is your Email ', 'email');
	echo form_input('email');
	echo form_hidden('mailing', $mailing);
	echo form_hidden('emails_id', $emails_id);
	echo form_submit('execute',lang('OK'));
	echo form_close();
}
