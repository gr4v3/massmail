<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<textarea id="form"><?php echo $data; ?></textarea>




<script type="text/javascript">


window.addEvent('domready',function(){

	var form = $('form');
	var text = form.get('text');

	var begin = text.indexOf('<form id="createaccount"');
	var end = text.indexOf('form>',begin);

	var theForm = text.slice(begin,end);



	var content = new Element('div').injectInside(document.body).set('html',theForm);


	var theForm = content.getElement('form');
		theForm.removeProperty('onsubmit');

});


</script>