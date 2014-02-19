/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
Hash.implement({
		previous:false,
		undo:[],
		back:function(){

			var obj = this.undo.pop();
			if(!obj) return;

				obj.current.set('opacity',0);
				obj.current.addClass('hidedialog');

				obj.previous.set('opacity',1);
				obj.previous.removeClass('hidedialog');

				obj.previous.fireEvent('start');

			this.previous = obj.previous;
		},
		cancel:function(){
			this.undo.each(function(obj){
				if($defined(obj.current)) {obj.current.set('opacity',0);obj.current.addClass('hidedialog');}
			});
		},
		activate:function(obj){

			var next = this.get(obj.task);




			if(this.previous){

				this.previous.set('opacity',0);
				this.previous.addClass('hidedialog');
				if(!obj.noundo) this.undo.push({current:next,previous:this.previous});

			}

			if(next) {
				next.set('opacity',1);
				var info = obj.info;
				if($defined(info) && info != '') {

					new Message({
						icon: "cautionMedium.png",
						iconPath: '../js/MessageClass/images/',
						title: "Caution",
						top : true,
						message: info
					}).say();
				}


				var parent = next.getParent('.dialog');
				if(parent) {parent.set('opacity',1);parent.removeClass('hidedialog');}

				var childs = next.getElements('.dialog');
				if(childs) {childs.set('opacity',0);childs.addClass('hidedialog');}

				next.set('opacity',1);
				next.removeClass('hidedialog');
				if(!next.hasClass('normal')) next.center();
				next.data = obj.data;

				if($defined(obj.follow) && obj.follow != 'undefined') {
					var task = $(next.form.task);
						task.setProperty('value',obj.follow);
				}

				next.fireEvent('start');
				this.previous = next;

			}
		},
		verbose:function(offset,content){
			if(!offset) offset = '&nbsp;';
			if(!content) {
				verbose = $(document.body).getElement('.verbose');
				if(verbose) content = verbose;
				else content = new Element('div').injectInside(document.body).set('class','verbose');
				content.makeDraggable();
			}

				this.each(function(value,index){

					var row = new Element('div').addClass('row').inject(content,'top');

					var title = new Element('div').addClass('row').inject(row,'top').set('html','#'+content.childNodes.length);
					var data = new Element('div').addClass('row').inject(row,'top');


					if($type(value) == 'object') new Hash(value).verbose(offset+'&nbsp;',data);
					else if($type(value) == 'element') new Hash(value.getProperties('id', 'name', 'class')).verbose(offset+'&nbsp;&nbsp;&nbsp;',data);
					else if($defined(value)) new Element('div').set('html',offset+'<b>index:</b>&nbsp;'+index+'&nbsp;&nbsp;&nbsp;&nbsp;<b>value:</b> '+value).inject(data,'top');

				});
		}
	});
	var JRequest = {};


var redirect = function(obj,task,follow,method)
{
	if(!obj) return;
	var form = obj.form;
	var execute = form.execute;

	if(task) {
		if(!form.task) new Element('input').set({'name':'task','type':'hidden','value':task}).injectInside(form);
		else form.task.value = task;
	}
	if(follow) {
		if(!form.follow) new Element('input').set({'name':'follow','type':'hidden','value':follow}).injectInside(form);
		else form.follow.value = follow;
	}
	if(method) {
		if(!form.method) new Element('input').set({'name':'method','type':'hidden','value':method}).injectInside(form);
		else form.method.value = method;
	}

	if(execute) $(execute).fireEvent('mouseup');
}
var populateSelectBox = function(obj,data,filter,post)
{
	if(!filter) filter = {'value':'id','text':'name'};
	obj.empty();

	if(!$defined(data.each))
	{
		var option = new Element('option').set('text',data[filter.text])
			.set('value',data[filter.value])
			.injectInside(obj);
			if(data['status_id']=='2') {
				option.set('disabled','true');
				option.set('class','disabled');
			}
		if(post == data[filter.value]) option.set('selected','selected');

	} else {
		data.each(function(value){
			var option = new Element('option').set('text',value[filter.text])
			.set('value',value[filter.value])
			.injectInside(obj);
			if(value['status_id']=='2') {
				option.set('disabled','true');
				option.set('class','disabled');
			}
			if(post == value[filter.value]) option.set('selected','selected');
		});
	}

}

var formprocess = function(dialog,steps,form){

	var name = form.getProperty('name')||form.getProperty('id');
	var send_process = form.get("send");
		send_process.addEvent('onSuccess',function(raw){

			var response = JSON.decode(raw);
				JRequest = response;
				steps.activate(response);

		});

	var execute = $(form.execute);
	var cancel = $(form.cancel);
	var back = $(form.back);

		if(execute) execute.addEvent('mouseup',function(){


			var send_process = form.get("send");
				send_process.send({data:form,url:send_process.options.url});

		});

		if(cancel) cancel.addEvent('mouseup',function(){steps.cancel();steps.activate({task:'home',data:false});});
		if(back) back.addEvent('mouseup',function(){steps.back();});

		form.lock = function(){

			var controls = this.getElement('.control').getElements('input');
			if(controls) controls.set('disabled','true');
		}
		form.unlock = function(){

			var controls = this.getElement('.control').getElements('input');
			if(controls) controls.removeProperty('disabled');
		}
		form.clearInputs = function(){

			var task = this.getElement('input[name="task"]').value;
			var inputs = this.getElements('input[type="text"]');
				inputs.set('value','');
				inputs = this.getElements('input[name="id"]');
				inputs.set('value','');
				inputs = this.getElements('input[type="hidden"]');
				inputs.set('value','');
				inputs = this.getElements('textarea');
				inputs.set('value','');
				this.getElement('input[name="task"]').setProperty('value',task)
		}

	steps.set(name, dialog);
	dialog.set('style','position:absolute;');
	dialog.set('opacity',0);
}


