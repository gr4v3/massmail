<?php
echo form_open('import/emails_stack_csv');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
echo 'fields found in csv<br />';
echo '<div>'.$file_name.'</div>';
if (! empty($fields)) {
	foreach($fields as $value => $label) {
		echo '<div>';
			echo '<span><input type="checkbox" value="'.$value.'" name="field[]" /></span>';
			echo '<span>'.$label.'</span>';
			echo '<span> rename field to </span>';
			echo '<span><input type="text" value="'.$label.'" name="rename[]" /></span>';
		echo '</div>';
	}
}
echo '<input type="hidden" name="file_name" value="'.$file_name.'" />';
echo '<input type="hidden" name="file_path" value="'.$file_path.'" />';
echo '<input type="hidden" name="task" value="select_fields" />';
echo '<input type="submit" value="submit" />';
echo form_close();
?>
