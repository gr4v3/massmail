/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var Win = new Class({
	Implements: [Events,Options],
	container:false,
	title:false,
	content:false,
	control:false,
	unique:true,
	options:{
		title:'Dialog Box',
		action:'',
		onExecute:$empty,
		onCancel:$empty,
		onClose:$empty
	},
	initialize:function(options)
	{
		var doc = $(document.body);
		var that = this;

		this.setOptions(options);

		if(this.options.unique) if(doc.getElements('.dialog')) doc.getElements('.dialog').destroy();
		this.container = new Element('div').injectOnly(doc).addClass('dialog');
		this.title = new Element('div').injectInside(this.container).addClass('title').set('html',this.options.title);
		this.content = new Element('form').injectInside(this.container).addClass('content')
		.setProperties({
			'method':'post',
			'action':this.options.action
		});
		this.control = new Element('div').injectInside(this.container).addClass('control');

		var button = new Element('div').injectInside(this.title).setStyle('float','right').addClass('close');
			button.addEvent('mouseup',function(){
				that.close();
			});
			button = new Element('div').injectInside(this.control).addClass('yes');
			button.addEvent('mouseup',function(){
				that.execute();
			});
			/*
			button = new Element('div').injectInside(this.control).addClass('no');
			button.addEvent('mouseup',function(){
				that.close();
			});
			*/
		    new Drag(this.container,{
				handle:this.title
			}).attach();
			
		return this;
	},
	show:function(bool)
	{
		this.container.display(bool);
	},
	hide:function()
	{
		this.container.setStyle('display','none');
	},
	close:function()
	{
		this.container.destroy();
		this.fireEvent('close');
	},
	execute:function()
	{
		var form = this.container.getElement('form');
		var that = this;
		if(form) {
			var b=form.get("send");
				b.addEvent('onSuccess',function(){
					that.fireEvent('execute');
					that.close();
				});
				b.send({
					data:form,
					url:b.options.url
				});
		}
	}
});

