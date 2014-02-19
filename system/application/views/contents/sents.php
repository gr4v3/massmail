<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script type="text/javascript">
    window.addEvent("domready", function(){

		var settings = $merge(common, {
			columnModel: tables.sent,
			url:"../grid/view/sent",
			buttons:[],
			sortOn:'bounce'
		});

		datagrid = new omniGrid('sents',settings);
		omniGrid.cache.combine({'sents':datagrid});
	    //datagrid.addEvent('click', onGridSelect);


		var keys = omniGrid.cache.getKeys();

     });

</script>
<div class="module">
	<div class="title"><?php echo $title; ?></div>
	<div class="content">
		<div id="sents"></div>
	</div>
</div>