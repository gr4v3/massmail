<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script type="text/javascript">
var onHostSelect = function(evt)
{
	/*
	var request = new Request.JSON({
			url:"../grid/view/logins/" + evt.target.getDataByRow(evt.row).id
			});
		request.addEvent("complete", function(){});
		request.send();
	*/
}
var AddService = function(id,type)
{
	
}
var DeleteService = function(){
	
}
var EditService = function(host_id,type)
{

	var request = new Request.JSON({
		url:"../grid/get/settings/",
		data:{
			'host_id':host_id,
			'type':type
		}
	});
		request.addEvent("complete", function(data){

			if(!data) return false;
			var obj = new Win({
					action:"../grid/set/settings/",
					'onExecute':function(){
						omniGrid.cache.hosts.refresh();
					},
					unique:true
				});
				
				new Hash(data).each(function(value,index){

						var myTable = new HtmlTable({properties:{
								border:0,
								cellspacing:2,
								'class':'host_settings'
						}});
							new Input({name:'single',value:'true',type:'hidden'}).injectInside(myTable);
							new Input({name:'host_id',value:host_id,type:'hidden'}).injectInside(myTable);


						new Hash(value).each(function(subvalue,subindex){
							
							myTable.push([subindex,new Input({name:subindex + '[' + subvalue.id + ']',value:subvalue.value})]);
							
						});

						myTable.inject(obj.content);
						obj.show(true);
				})

		}).send();

}
var accordionHostSelect = function(obj)
{
	var id = omniGrid.cache['hosts'].getDataByRow(obj.row).id;
	
	createSelectBox({
		properties:{'name':'settings_id'},
		data:'host_id='+id,
		table:'settings',
		onSuccess:function(response){

			var data = new Hash(JSON.decode(response));
			if(!data) return false;
			
			
				data.each(function(value,index){

					var myTable = new HtmlTable({properties:{
							border:0,
							cellspacing:2,
							'class':'host_settings'
					}});
					myTable.push([
						{ 
							content:index,
							properties: {
								colspan: 2,
								'class': 'title'
							}
						}
					]);
					
					
					var edit = EditService.pass([id, index]);
					var add = AddService.pass([id, index]);
					var del = DeleteService.pass([id, index]);

					
					var div = new Element('div');
					
					new Input({'type':'button','value':'modify'}).addEvent('click',edit).injectInside(div);
					new Input({'type':'button','value':'delete'}).addEvent('click',del).injectInside(div);
					
					
					new Hash(value).each(function(v,i){
						myTable.push([i,v.value],{'class': 'content'});
					});
					
					myTable.push([
						{
							content:div,
							properties: {
								colspan: 2,
								'class': 'control'
							}
						}
					]);
					
					myTable.inject(obj.parent);
				})

				

		}
	})
}



window.addEvent("domready", function(){

var settings = $merge(common, {
	columnModel: tables.host,
	url:"../grid/view/hosts",
	buttons:[
	  {name: 'Add', bclass: 'add', onclick : gridButtonClick},
	  {name: 'Delete', bclass: 'delete', onclick : gridButtonClick},
	  {name: 'Edit', bclass: 'edit', onclick : gridButtonClick},
	  {separator: true},
	  {name: 'Duplicate', bclass: 'duplicate', onclick : gridButtonClick}
	],
	sortOn:'hostname',
	accordion:true,
	accordionRenderer:accordionHostSelect,
	resizeColumns:true
});


datagrid = new omniGrid('hosts',settings);
omniGrid.cache.combine({'hosts':datagrid});

});

</script>
<div class="module">
	<div class="title"><?php echo $title; ?></div>
	<div class="content">
		<div id="hosts"></div>
	</div>
</div>
