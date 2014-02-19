
Element.implement({
    center:function(){
        var that = this;
        if ( !this.retrieve('effect:center')) $(window).addEvent('resize',function(){ that.center();});
        this.store('effect:center',true);
        var window_size = $(window).getSize();
        var self_size = this.getSize();
        var offsettop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
        var left = (window_size.x - self_size.x ) / 2;
        var top = (window_size.y - self_size.y ) / 2;
        this.setStyles({
            left:left,
            top:top + offsettop,
            position:'absolute'
        })
        return this;
    }
});

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
                        if(!obj.noundo) {
                                var add_step = true;
                                this.undo.each(function(value){
                                        if(value.current === next) add_step = false;
                                });
                                if(add_step) this.undo.push({current:next,previous:this.previous});
                        }

                }

                if(next) {

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
                        next.setStyle('display','inline');
                        next.removeClass('hidedialog');
                        if(!next.hasClass('normal')) next.center();
                        next.data = obj.data;	

                        if($defined(obj.follow) && obj.follow != 'undefined') {
                                var task = $(next.form.task);
                                        task.setProperty('value',obj.follow);
                        }
                        next.center();

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
function IsJsonString(str) {
    try {
        JSON.decode(str);
    } catch (e) {
        return false;
    }
    return true;
}
var formprocess = function(dialog,steps,form){

	var name = form.getProperty('name')||form.getProperty('id');
	var send_process = form.get("send");

	

		send_process.addEvent('onSuccess',function(raw){
			if(!IsJsonString(raw)) return;
			var response = JSON.decode(raw);
				JRequest = response;
				steps.activate(response);

		});

	var execute = $(form.execute);
	var cancel = $(form.cancel);
	var back = $(form.back);

		if(execute) execute.addEvent('mouseup',function(){

			dialog.set('opacity',0.1);
			
			if(!$('loading')) {
				new Element('img',{
					src:'../css/loading.gif',
					width:50,
					id:'loading'
				}).injectInside(document.body).center();
			}


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
	dialog.set('style','position:absolute;display:none;');
	dialog.set('opacity',0);
}
	
window.addEvent('domready',function(){

	var root = $(document.body);
	var steps = new Hash({});

	
	
	var dialogs = root.getElements('.dialog');
		dialogs.each(function(dialog){

			var form = dialog.getElement('form');
			dialog.form = form;
			formprocess(dialog,steps,form);

			new Element('div',{
					'class':'logout'
				}).addEvent('click',function(){
					window.location = form.action.replace('office/input','auth/logout');
				}).inject(dialog,'top');
			dialog.center();
		});

		//window.addEvent('resize',function(){if(!dialogs.hasClass('normal')) dialogs.center();});

		var select_ip = steps.get('select_ip');
		var new_server = steps.get('new_server');
		var new_account = steps.get('new_account');
		var active_accounts = steps.get('active_accounts');
		var active_servers = steps.get('active_servers');
		var mailing_system_campaigns = steps.get('mailing_system_campaigns');
                var mailing_system_campaigns_edit = steps.get('mailing_system_campaigns_edit');
		
		if(select_ip) select_ip.addEvent('start',function(){

			var that = this;
			var form = that.getElement('form');
				form.lock();
			var select = $(form.ip);
				populateSelectBox(select,JSON.decode(this.data),{value:'ip',text:'text'});
				form.unlock();
		});

		

		if(active_servers) active_servers.addEvent('start',function(){

			var that = this;
			var form = that.getElement('form');
				form.clearInputs();
				form.lock();
			var select = $(form.server);
				populateSelectBox(select,JSON.decode(this.data),{value:'host_id',text:'hostname'});
				form.unlock();

		});
		if(active_accounts) active_accounts.addEvent('start',function(){

			var that = this;
			var form = that.getElement('form');
				form.clearInputs();
				form.lock();
			var select = $(form.account);
				populateSelectBox(select,JSON.decode(this.data),{value:'login_id',text:'name'});
				form.unlock();

		});
                
                if(mailing_system_campaigns) mailing_system_campaigns.addEvent('start',function(){

			var that = this;
			var form = that.getElement('form');
				form.clearInputs();
				form.lock();
			var select = $(form.content);
				populateSelectBox(select,JSON.decode(this.data),{value:'group_id',text:'name'});
				form.unlock();

		});
                
                if(mailing_system_campaigns_edit) mailing_system_campaigns_edit.addEvent('start',function(){

			var that = this;
			var form = that.getElement('form');
				form.clearInputs();
				form.lock();
                                //var select = $(form.content);
				//populateSelectBox(select,JSON.decode(this.data),{value:'group_id',text:'name'});
                                
                                
                                
                                console.log(this.data);
				form.unlock();

		});

		if(new_account) {
			new_account.addEvent('start',function(){

				var that = this;
				var form = that.getElement('form');
				    form.clearInputs();
				    form.lock();
				new Form.Validator(form);
				if($defined(this.data.ip)) {

					
					var ip = $(document.body).getElement('#ip');
					var server = $(document.body).getElement('#server');
					var country = $(document.body).getElement('#country');
					var ip_content = $(document.body).getElement('.ip');

					//if(ip) ip.set('text',this.data.ip);
					if(ip) ip.innerText = this.data.ip;
					if(server) server.set('text',this.data.servername);
					if(country) country.set('text',this.data.country);


					if($defined(this.data.avaialable_ips) && this.data.login_id) {

						
							ip_content.empty();
							ip_content.getParent().set('style','visibility:visible;position:static;');
						var select = new Element('select').set('name','ip').injectInside(ip_content);
						populateSelectBox(select,JSON.decode(this.data.avaialable_ips),{value:'ip',text:'ip'},this.data.ip);
						

					} else {
						//ip.set('text',this.data.ip);
						//if(ip_content) ip_content.getParent().set('style','visibility:hidden;position:absolute;');
					}

						
					var account = new Hash(this.data);
						account.each(function(value,index){
							var obj = that.getElement('input[name='+index+']');
							if(obj) obj.value = value;
						});

				}
				form.unlock();
				if($defined(Rules)) {
					var servername = that.getElement('input[name=servername]');
					var account_rules = Rules[servername.value];
					if($defined(account_rules)) {
						account_rules.refresh();
						if($defined(account_rules))	{
							new Hash(account_rules).each(function(value,index){
								var el = that.getElement('input[name='+index+']');
								if($defined(el)) el.value = value;
								else {
									el = that.getElement('a[class='+index+']');
									if($defined(el)) el.href = value;
								}
							});
						}
					}
				}

			});
			
			
			
			
		}
		if(new_server) new_server.addEvent('start',function(){

			var that = this;
			var form = that.getElement('form');
				form.clearInputs();
				form.lock();
			
			if(!$defined(this.data.host)) {form.unlock();return;}
			var host = new Hash(this.data.host);
			var rules = new Hash(this.data.rules);
			var settings = new Hash(this.data.settings);
			var accounts_rules = new Hash(this.data.accounts_rules);
			var smtp = new Hash(this.data.settings.smtp);
				settings.erase('smtp');


			var __export__ = that.getElement('.export');
			var __import__ = that.getElement('.import');
				

			var export_radios = __export__.getElements('input[type=radio]');
			var export_textareas = __export__.getElements('textarea');
			var import_radios = __import__.getElements('input[type=radio]');
			var import_textareas = __import__.getElements('textarea');
			
				host.each(function(value,index){

					var obj = that.getElement('input[name='+index+']');
					if(obj) obj.value = value;

				});
				rules.each(function(value,index){

					var obj = that.getElement('input[name='+index+']');
					if(obj) obj.value = value;

				});
				smtp.each(function(value,index){

					var obj = __export__.getElement('.'+index);
					
					if(obj) {
						obj.value = value.value;
					}
					
					export_radios.each(function(el){
						if(el.name.contains(index) && el.value.contains(value.value)) el.checked = true;
					});
					export_textareas.each(function(el){
						if(el.name.contains(index)) el.value = value.value;
					});

				});
				settings.each(function(parent,index){

					var obj = new Hash(parent);
					
					var radio = __import__.getElement('input[value='+index+']');
					 if(radio) radio.setProperty('checked','true');


						obj.each(function(value,index){

							var obj = __import__.getElement('.'+index);
							if(obj) obj.value = value.value;

						});
				});
				accounts_rules.each(function(value,index){

					var obj = that.getElement('input[name='+index+']');
					if(obj) obj.value = value;

				});
				form.unlock();


		});

		steps.activate({task:'home',data:false});
		/*
		var buttons = root.getElements('input[type="button"]');
		var value = false;
			buttons.each(function(el){
				value = el.value;
				google.language.translate(value, "en", window.SITELANG, function(result) {
				  if (!result.error) {
					el.value = result.translation;
				  }
				})
			});
		var captions = root.getElements('caption');
			captions.each(function(el){
				value = el.get('text');
				google.language.translate(value, "en", window.SITELANG, function(result) {
				  if (!result.error) {
					el.set('html',result.translation)
				  }
				})
			});
		var smalls = root.getElements('.small');
			smalls.each(function(el){
				value = el.childNodes.item(0).nodeValue;
				google.language.translate(value, "en", window.SITELANG, function(result) {
				  if (!result.error) {
						var translated = result.translation.replace(/&#39;/gi,"'");
						el.childNodes.item(0).nodeValue = translated;
				  }
				})
			});		
		var labels = root.getElements('.label');
			labels.each(function(el){
				value = el.childNodes.item(0).nodeValue;
				google.language.translate(value, "en", window.SITELANG, function(result) {
				  if (!result.error) {
						var translated = result.translation.replace(/&#39;/gi,"'");
						el.childNodes.item(0).nodeValue = translated;
					
				  }
				})
			});
		var titles = root.getElements('.title');
			titles.each(function(el){
				value = el.childNodes.item(0).nodeValue;
				google.language.translate(value, "en", window.SITELANG, function(result) {
				  if (!result.error) {
						var translated = result.translation.replace(/&#39;/gi,"'");
						el.childNodes.item(0).nodeValue = translated;

				  }
				})
			});
		*/
});