/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var common = {
	columnModel: false,
	buttons : false,
	url:"grid/view/logins",
	perPageOptions: [10,20,50,100,200],
	perPage:10,
	page:1,
	pagination:true,
	serverSort:true,
	showHeader: true,
	alternaterows: true,
	sortHeader:true,
	resizeColumns:false,
	multipleSelection:true,
	sortOn: false,
	height: 400,
	onLoaddata:function()
	{
		if(this.options.resizeColumns) return;
		var container = this.container;
		var head = container.getElements('.th');
		var content = this.elements;
		var head_div = container.getElement('.hDivBox');
		var content_ul = container.getElement('.bDiv ul');
		var content_bDiv = container.getElement('.bDiv');
		var widths = [];

			head.each(function(value,index){
				widths[index] = value.getTextWidth() + 5;
			});

			content.each(function(li,i){

				var tds = li.getElements('.td');
					tds.each(function(div,index){

						var width = div.getTextWidth();
						if(width > widths[index]) {
							widths[index] = width + 5;
						}
					});
					

			});
			var total = 0;
			content.each(function(li,i){
				var tds = li.getElements('.td');
					total = 0;
					tds.each(function(div,index){

						div.setStyle('width',widths[index]);
						head[index].setStyle('width',widths[index]);
						total+= widths[index] + 7;
						
					});
					li.setStyle('width',total);

			});
			head_div.setStyle('width',total);
			content_ul.setStyle('width',total);
	
			if(content_bDiv.scrollHeight > content_bDiv.offsetHeight ) container.setStyle('width',total + 19);
			else container.setStyle('width',total + 2);
	}
}

function createSelectBox(options)
{
	var default_reply = function(response){
		var data = JSON.decode(response);
			data.each(function(value){
				var option = new Element('option');

					console.log('******** select value **********');
					console.dir(options);
					console.dir(value);
					console.log('********************************');

					if(options.value == value.value) option.setProperty('selected',true);

					option.setProperties(value);
					option.injectInside(select);
			});
	};
    if(options.onSuccess) default_reply = options.onSuccess;

	var select = new Element('select');
		select.setProperties(options.properties);
	new Request({
		method: 'post',
		url: "../grid/get/" + options.table,
		data:options.data,
		onSuccess:default_reply
	}).send();
	
	return select;
}
function createInput(conditions,value)
{
	var header = conditions.header;
	var type = conditions.dataType;
	var name = conditions.dataIndex;
	switch(type)
	{
		case 'host':

			
			return [
				header,
				createSelectBox({
					properties:{'name':'host_id'},
					table:'hosts',
					value:value
				})
			];
			

		break;
		case 'state':

			
			return [
				header,
				createSelectBox({
					properties:{'name':'status_id'},
					table:'status',
					value:value
				})
			];
			

		break;
		case 'date':


			                      
			return [
				header,
				new Element('input').setProperties({'type':'text','name':name,'value':value}).addClass('date')
			];

		break;
		default:

			

			return [
				header,
				new Element('input').setProperties({'type':'text','name':name,'value':value}).addClass('text')
			];

	}
}




function gridButtonClick(button, grid)
{
	var columnModel = grid.options.columnModel;
	var url = false;
	
	switch(button)
	{
		case 'add':

				url = grid.options.url.replace('view','set');
			var obj = new Win({
				title:'new ',
				action:url,
				'onExecute':function(){
					grid.refresh();
				}
			});
			
			var content = obj.content;
			var myTable = new HtmlTable({properties:{border:0,cellspacing:3}});
			columnModel.each(function(value){

				myTable.push(createInput(value), {'class': 'tableRowClass'});

			});
			
			myTable.inject(content);
			obj.show(true);
			
		  break;

		  
		case 'delete':

			var indices = grid.getSelectedIndices();
			if (indices.length == 0) {
				alert("No item selected.");
				return;
			}
			if (confirm("Are you sure?")) {

					url = grid.options.url.replace('view','del');
				
				var data = [];
				for (var i=0; i<indices.length; i++) {data.push(grid.getDataByRow(indices[i]).id);}
					data = {'id':data.join(',')};
				new Request({
					method: 'post',
					url: url,
					data: data,
					onSuccess:function(){

						grid.refresh();

					}
				}).send();
			 
			}
			

		  break;
		case 'duplicate':

			

		  break;
		case 'edit':

			
			var indices = grid.getSelectedIndices();
			var datacontent = [];
			var length = indices.length;
				url = grid.options.url.replace('view','set');

				
				for (var i=0; i<indices.length; i++) {datacontent.push(grid.getDataByRow(indices[i]));}

				datacontent.each(function(data){
					var obj = new Win({
						action:url,
						'onExecute':function(){
							grid.refresh();
						},
						unique:length>1?false:true
					});

					var content = obj.content;
					var myTable = new HtmlTable({properties:{border:0,cellspacing:3}});
						myTable.push(['&nbsp;',new Element('input').setProperties({'type':'hidden','name':'id','value':data.id})], {'class':'tableRowClass'});

					columnModel.each(function(value){

						myTable.push(createInput(value,data[value.dataOriginalIndex?value.dataOriginalIndex:value.dataIndex]), {'class':'tableRowClass'});

					});

					myTable.inject(content);
					obj.show(true);
				});
			
			



		  break;
		default:

	}
	
};


var Input = new Class({
	Implements: [Events,Options],
	options:{
		type:'text'
	},
	initialize:function(options)
	{
		this.setOptions(options);
		return new Element('input').set(options);
	}
});