<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script type="text/javascript">
	window.addEvent("domready", function(){

		var settings = $merge(common, {
			columnModel: tables.login,
			url:"../grid/view/logins",
			buttons:[
			  {name: 'Add', bclass: 'add', onclick : gridButtonClick},
			  {name: 'Delete', bclass: 'delete', onclick : gridButtonClick},
			  {name: 'Edit', bclass: 'edit', onclick : gridButtonClick},
			  {separator: true},
			  {name: 'Duplicate', bclass: 'duplicate', onclick : gridButtonClick}
			],
			sortOn:'name'
		});

		datagrid = new omniGrid('logins',settings);
		omniGrid.cache.combine({'logins':datagrid});
	    //datagrid.addEvent('click', onGridSelect);

     });

</script>
<div class="module">
	<div class="title"><?php echo $title; ?></div>
	<div class="content">
		<div id="logins"></div>
	</div>
</div>