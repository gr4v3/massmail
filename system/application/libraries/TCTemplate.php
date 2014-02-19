<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * TCTemplate Class
 *
 * Gives support to view templates
 * v1.4 2010-02-03 eurico@totalcenter
 * improved lang function
 *
 */
class TCTemplate {

	var $template_file;
	var $template_outside;
	var $view_folder;
	var $meta_info;
	var $modules;
	var $css_files;
	var $js_files;
	var $js_code;
	var $title;
	var $post_auto_fill;
	var $form_id;
	var $form_validation;
	var $glossy_button_defaults;
	var $template_vars;

	function TCTemplate($template_file = NULL, $view_folder = NULL) {
		$this->template_file = $template_file;
		$this->template_outside = FALSE;
		$this->view_folder = $view_folder;
		$this->meta_info = '';
		$this->modules = array();
		$this->css_files = array();
		$this->js_files = array();
		$this->js_code = '';
		$this->title = FALSE;
		$this->post_auto_fill = TRUE;
		$this->form_id = NULL;
		$this->form_validation = FALSE;
		$this->glossy_button_defaults = array();
		$this->template_vars = array();
		$this->CI = & get_instance();
	}

	// forces a clean-up on the object in case it is re-utilized
	function clear() {
		$this->TCTemplate();
	}

	// assigns the template file
	// second argument specifies if template is outside of view folder, in that case, the path is based on the site root
	function set_template($template, $out = FALSE) {
		$this->template_file = $template;
		$this->template_outside = $out;
	}

	function set_view_folder($folder) {
		$this->view_folder = $folder;
	}

	function set_meta_info($meta_info) {
		$this->meta_info = $meta_info;
	}

	// assigns a module to display in the template
	function add_module($view, $position, $view_vars = NULL, $id = FALSE) {
		$view_folder = $this->view_folder?$this->view_folder.'/':'';
		$this->modules[$position][] = array(
			'id' => $id ? $id : 'module_'.$view,
			'view' => $view_folder.$view,
			'vars' => $view_vars
		);
	}

	// includes a file of a certain type; checks if file was already included
	function _include_file($file, $type, $url) {
		$file_hash = md5($file);
		$file_array = & $this->$type;
		if ( ! array_key_exists($file_hash,$file_array))
			$file_array[$file_hash] = $url;
	}

	// adds a CSS file
	// outside forces to obtain file outside of default /CSS folder
	function include_css_file($css_file, $outside = FALSE) {
		if ( ! $outside) $css_file = 'css/' . $css_file;
		$url = base_url() . $css_file;
		$this->_include_file($css_file, 'css_files', $url);
	}

	// adds a JS file (defaults to JS in root folder or remote if includes ://)
	// outside forces to obtain file outside of default /JS folder
	function include_js_file($js_file, $outside = FALSE) {
		if ( ! $outside) $js_file = 'js/' . $js_file;
		$url = strpos($js_file,'://') ? $js_file : base_url().$js_file;
		$this->_include_file($js_file, 'js_files', $url);
	}

	// adds JS code to head section
	// if no event is specified, the code is executed directly
	function include_js_code($js_code, $event = FALSE) {
		if ($event) $js_code = "window.addEvent('$event', function() { $js_code });";
		$this->js_code .= $js_code;
	}

	// renders a flash object using swfobject
	function include_flash($container_id, $swf_file, $width, $height, $flashvars = '{}', $allowScriptAccess = FALSE) {
		//Flash flashvars
		if (is_array($flashvars)) {
			foreach($flashvars as $var_name => $value)
				$flashvars[$var_name] = $var_name.': \''.$value.'\'';
			$flashvars = '{ '.implode(',', $flashvars).' }';
		}
		// Flash params
		$params = array(
			"wmode: 'transparent'",
			"menu: 'false'"
		);
		if ($allowScriptAccess)
			$params[] = "allowscriptaccess: 'always'";
		$params = '{ '.implode(',', $params).' }';
		// Flash attributes
		$attribs = "{ id: 'swf_$container_id' }";
		$code = "swfobject.embedSWF('$swf_file', '$container_id', '$width', '$height', '9.0.0', '', $flashvars, $params, $attribs);";
		$this->include_js_code($code, 'domready');
	}

	// defines vars available for the template
	function add_template_vars($vars) {
		if (is_array($vars)) $this->template_vars = array_merge($this->template_vars, $vars);
	}

	function _ci_object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}

	// renders the template
	function show($title, $content_view, $view_vars = NULL, $return = FALSE) {
		// from now on the code is standard
		$view_folder = $this->view_folder ? $this->view_folder.'/' : '';
		$this->title = $title;
		$this->content = array(
			'view' => $view_folder.$content_view,
			'vars' => $view_vars
		);
		// add self reference to be available from the template view
		$this->template_vars['TC'] = & $this;
		$view_loader_args = array(
			'_ci_view' => $this->template_file,
			'_ci_vars' => $this->_ci_object_to_array($this->template_vars),
			'_ci_return' => $return,

		);
		// if outside of view folder, set path to file
		if ($this->template_outside) $view_loader_args['_ci_path'] = $this->template_file;
		//workaround for the new version of code cignitor
		$method = new ReflectionMethod('CI_Loader', '_ci_load');
		$method->setAccessible(true);
		return $method->invokeArgs($this->CI->load, array($view_loader_args));
		//return $this->CI->load->_ci_load($view_loader_args);
	}

	// renders main content (body)
	function show_content() {
		$this->add_html('<div id="layout_content">');
		$this->CI->load->view($this->content['view'],$this->content['vars']);
		$this->add_html('</div>');
	}

	// renders page title
	function show_title() {
		$this->add_html('<title>'.$this->title.'</title>');
	}

	// used inside a template to check if there are modules for a specific position
	function has_modules($position) {
		if (isset($this->modules[$position]))
			return TRUE;
		return FALSE;
	}

	// adds HTML to output buffer
	function add_html($html) {
		ob_start();
		echo $html;
		ob_end_flush();
	}

	/** RENDER FUNCTIONS **/

	// renders head info
	function show_head() {
		$html = '';
		$html .= '<title>'.$this->title.'</title>';
		if ($this->meta_info) {
			if (is_array($this->meta_info))
				$html .= implode('',$this->meta_info);
			else
				$html .= $this->meta_info;
		}
		$html .= $this->show_css_files(TRUE);
		$html .= $this->show_js_files(TRUE);
		if ( ! empty($this->js_code))
			$html .= '<script type="text/javascript">'.$this->js_code.'</script>';
		$this->add_html($html);
	}

	// renders the modules of a position
	function show_modules($position, $separator_before = FALSE, $separator_after = FALSE) {
		if ( ! isset($this->modules[$position])) return FALSE;
		// add separator before if defined
		if ($separator_before) $this->add_html('<div class="layout_sep"></div>');
		foreach ($this->modules[$position] as $index => $module) {
			// add separator between modules
			if ($index > 0) $this->add_html('<div class="layout_sep"></div>');
			// add container and module
			$this->add_html('<div id="'.$module['id'].'" class="module_'.$position.'">');
			$this->CI->load->view($module['view'],$module['vars']);
			$this->add_html('</div>');
		}
		// add separator after if defined
		if ($separator_after) $this->add_html('<div class="layout_sep"></div>');
	}

	// renders or returns an URL address (without path returns current url)
	function url($relative_path = FALSE, $return = FALSE) {
		if ( ! function_exists('base_url')) $this->CI->load->helper('url');
		if ( ! $relative_path)
			$url = current_url();
		else
			$url = base_url() . $relative_path;
		if ($return) return $url;
		$this->add_html($url);
	}

	// renders or returns an URL address based on the template path
	function rel_url($relative_path, $return = FALSE) {
		$template_path = dirname($this->template_file);
		$path = $template_path . "/$relative_path";
		return $this->url($path, $return);
	}

	// returns an URL to a controller task
	function task($task = FALSE, $args = FALSE, $return = TRUE) {
		$url = $this->CI->task_url($task, $args);
		if ($return) return $url;
		$this->add_html($url);
	}

	// renders a view inside another view
	// can be used for ajax requests so only a view is rendered without the template
	function view($view, $view_vars = array(), $return = FALSE) {
		$view_folder = $this->view_folder?$this->view_folder.'/':'';
		$view_vars['TC'] = & $this;
		return $this->CI->load->view($view_folder.$view, $view_vars, $return);
	}

	// view function to return a translation
	// without args, returns current system language
	// with one arg, return translation to passed key
	// if more args are passed, return translation with sprintf
	function lang() {
		$arg_count = func_num_args();
		if ($arg_count === 0) return $this->CI->config->item('language');	// no args, returns current language
		// get all function args
		$args = func_get_args();
		if ($arg_count === 1) return $this->CI->lang->line($args[0]);	// 1 arg, just return translation
		// multiple args, return a sprintf
		$args[0] = $this->CI->lang->line($args[0]);	// translate key
		// return processed string
		return call_user_func_array('sprintf', $args);
	}

	// returns language code in short format (ex: en / fr)
	function lang_short() {
		return $this->CI->language_model->langcode;
	}

	// renders a language selector
	function lang_selector($spacing = TRUE) {
		// language model must be available
		if ( ! isset($this->CI->language_model)) return FALSE;
		// show all available languages except currently selected
		$current_lang = $this->CI->language_model->langcode;
		$languages = $this->CI->language_model->languages;
		$first_lang = TRUE;
		foreach ($languages as $langcode => $language) {
			if ($current_lang !== $langcode) {
				$image = '<img border="0" src="'.$this->url('images/flag_'.$langcode.'.gif',TRUE).'" />';
				$link = $this->url("admin/home/langcode/$langcode", TRUE);
				$html = '<a href="'.$link.'" alt="'.$language->name_native.'">'.$image.'</a>';
				if ( ! $first_lang) $html.= '&nbsp;';
				$this->add_html($html);
				$first_lang = FALSE;
			}
		}
	}

	// checks if a given field has validation errors
	function field_has_error($field_name) {
		if (isset($this->CI->form_validation->_field_data[$field_name])) {
			$field = $this->CI->form_validation->_field_data[$field_name];
			if ( ! empty($field['error']))
				return TRUE;
		}
		return FALSE;
	}

	/** FORM TOOLS **/

	// inside function to render a form
	function _form($method, $form_id, $action, $options, $hidden_fields) {
		$this->form_validation = FALSE;
		$this->form_id = $form_id;
		$html = '<form id="'.$form_id.'"';
		if ($action) $html .= ' action="'.$action.'"';
		if ($options) $html .= ' '.$options;
		$html .= ' method="'.$method.'">';
		if ($hidden_fields && is_array($hidden_fields)) {
			$html .= '<div>';
			foreach ($hidden_fields as $field => $value)
				$html .= '<input type="hidden" id="'.$field.'" name="'.$field.'" value="'.$value.'" />';
			$html .= '</div>';
		}
		$this->add_html($html);
	}

	// renders a form start with post method
	function form_post($form_id, $action = FALSE, $options = '', $hidden_fields = FALSE) {
		$this->_form('post', $form_id, $action, $options, $hidden_fields);
	}

	// renders a form start with get method
	function form_get($form_id, $action = FALSE, $options = '', $hidden_fields = FALSE) {
		$this->_form('get', $form_id, $action, $options, $hidden_fields);
	}

	// renders a form end
	// accepts JS code to include before form closes (can be used to apply JS to form elements)
	function form_end($js_code = '') {
		$html = '';
		if ($this->form_validation) {
			$js_code .= "var fc_$this->form_id;";
			$js_code .= "window.addEvent('domready', function() {";
			$js_code .= "fc_$this->form_id = new FormCheck('$this->form_id', { fieldErrorClass: 'error', display: { addClassErrorToField: 1, scrollToFirst: false }});";
			// add and show post submit errors
			if ( ! empty($this->CI->form_validation->_error_array)) {
				$js_code .= "var fc_field;";
				foreach ($this->CI->form_validation->_error_array as $field => $error) {
					$js_code .= "fc_field = $('$field');";
					$js_code .= "fc_field.errors = [\"$error\"];";
					$js_code .= "fc_$this->form_id.addError(fc_field);";
					$js_code .= "fc_field.addEvent('blur', function() { fc_$this->form_id.removeError(fc_field) });";
				}
			}
			$js_code .= "});";
		}
		if ( ! empty($js_code))
			$html .= '<script type="text/javascript">'.$js_code.'</script>';
		$html .= '</form>';
		$this->add_html($html);
		$this->form_id = NULL;
	}

	// helper function to add tags to an element
	function _add_option(& $options, $option, $value) {
		$option_tag = $option.'="';
		if (strpos($options, $option_tag) !== FALSE) {
			$new_tag = $option_tag . $value . ' ';
			$options = str_replace($option_tag, $new_tag, $options);
		} else
			$options .= ' '.$option_tag.$value.'"';
	}

	// renders a label for a input field
	function label($field_name, $label, $options = '') {
		// if ($this->field_has_error($field_name)) $this->_add_option($options, 'class', 'error');
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		$html = '<label for="'.$field_name.'"'.$options.'>'.$label.'</label>';
		$this->add_html($html);
	}

	// renders a input with error class if needed
	// added validators arg wich receives rules for formcheck js validator
	function input($type, $field_name, $value = '', $options = '', $validators = FALSE) {
		if ($this->field_has_error($field_name)) $this->_add_option($options, 'class', 'error');
		if ($validators && is_array($validators)) {
			$this->_add_option($options, 'class', "validate['".implode("','", $validators)."']");
			$this->form_validation = TRUE;
		}
		if ($this->post_auto_fill && $type == 'text') {
			$postvalue = $this->CI->input->post($field_name);
			if ($postvalue !== FALSE) $value = $postvalue;
		}
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		$html = '<input type="'.$type.'" id="'.$field_name.'" name="'.$field_name.'" value="'.$value.'"'.$options.' />';
		$this->add_html($html);
	}

	// renders a submit button
	function submit($value = '', $options = '') {
		if ($this->form_validation) $this->_add_option($options, 'class', "validate['submit']");
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		$html = '<input type="submit" value="'.$value.'"'.$options.' />';
		$this->add_html($html);
	}

	// uses glossy.js and image generator to render a button with custom font and "glossiness"
	// type is submit (default) / link / onclick
	// action is URL for link (use url|target if target is needed) / JS Code for onclick
	// params:
	// font - font name to use (ttf)
	// font_size - int
	// font_color - hex
	function glossy_button($id, $value, $type = FALSE, $action = FALSE, $params = FALSE, $options = '', $cache = TRUE) {
		$src = FALSE;
		if ($cache) {
			$cache_key = md5($value.serialize($params));
			$cache_file = "imggen/cache/$cache_key.png";
			if (file_exists(realpath($cache_file))) {
				$cache_file_url = $this->url($cache_file, TRUE);
				$src = $cache_file_url;
			}
		}
		if ( ! $src) {
			$src = base_url()."imggen/image.php?text=$value";
			if ($cache) $src .= "&ck=$cache_key";
			// use and overwrite default params
			if ($params && is_array($params))
				$params = array_merge($this->glossy_button_defaults, $params);
			else
				$params = $this->glossy_button_defaults;
			if ( ! empty($params)) {
				foreach ($params as $param_name => $param_value)
				$src .= "&$param_name=$param_value";
			}
		}
		if ( ! $type) $type = 'submit';
		$html = '';
		if ($type == 'submit') {
			$submit_class = $this->form_validation ? ' class="validate[\'submit\']"' : '';
			$html .= '<input type="submit" id="'.$id.'_submit" style="display:none"'.$submit_class.' />';
		}
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		$this->_add_option($options, 'class', 'tct_glossy_button');
		if ($type == 'link') {
			$components = explode('|', $action);
			$link = $components[0];
			$target = isset($components[1]) ? ' target="'.$components[1].'"' : '';
			$html .= '<a id="'.$id.'" href="'.$action.'"'.$target.$options.'>';
		} else {
			$html .= '<span id="'.$id.'"'.$options.'>';
		}
		$html .= '<img border="0" id="'.$id.'_image" src="'.$src.'" />';
		$html .= ($type == 'link') ? '</a>' : '</span>';
		// add JS code
		$click_action = FALSE;
		if ($type == 'onclick') $click_action = $action;
		elseif ($type == 'submit') $click_action = "$('{$id}_submit').click()";
		$imagevar = $id . '_image';
		$container = $id . '_container';
		// specifies on which event the glossy effect will be applied; if is an ajax request, we can't apply on window load
		$apply_event = $this->CI->is_ajax ? 'domready' : 'load';
		$html .= '<script type="text/javascript">';
		$html .= "window.addEvent('$apply_event', function() {";
		$html .=	"cvi_glossy.add($('$imagevar'), { radius:100, shade:30, noshadow:true });";
		$html .=	"var $container = $('$id');";
		$html .=	"var $imagevar = $('$imagevar');";
		$html .=	"if ( ! Browser.Engine.trident) {";
		$html .=		"$container.addEvent('mouseover', function() { cvi_glossy.modify($imagevar, { shade:60 }) });";
		$html .=		"$container.addEvent('mouseout', function() { cvi_glossy.modify($imagevar, { shade:30 }) });";
		$html .=	"}";
		if ($click_action) $html .=	"$container.addEvent('click', function() { $click_action });";
		$html .= "});";
		$html .= '</script>';
		$this->add_html($html);
	}

	// sets the default glossy button options for this template
	function set_glossy_button_defaults($params) {
		$this->glossy_button_defaults = $params;
	}

	// renders a checkbox field
	// remember to add [] to name if you want to group checkboxes
	function checkbox($field_name, $field_id = '', $value = '', $selected = FALSE, $options = '', $validate = FALSE) {
		if ($this->field_has_error($field_name)) $this->_add_option($options, 'class', 'error');
		if ($validate) {
			$this->_add_option($options, 'class', "validate['checkbox','required']");
			$this->form_validation = TRUE;
		}
		if ($this->post_auto_fill) {
			$postvalue = $this->CI->input->post($field_name);
			if ($postvalue !== FALSE && ((is_array($postvalue) && in_array($value, $postvalue)) || $postvalue == $value))
				$selected = TRUE;
		}
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		if ( ! empty($field_id)) $field_id = ' id="'.$field_id.'"';
		$checked = $selected ? (' checked="checked"') : '';
		$html = '<input type="checkbox"'.$field_id.' name="'.$field_name.'" value="'.$value.'"'.$options.$checked.' />';
		$this->add_html($html);
	}

	// renders a radio field
	function radio($field_name, $field_id = '', $value = '', $selected = FALSE, $options = '', $validators = FALSE) {
		if ($this->field_has_error($field_name)) $this->_add_option($options, 'class', 'error');
		if ($validators && is_array($validators)) {
			$this->_add_option($options, 'class', "validate['".implode("','", $validators)."']");
			$this->form_validation = TRUE;
		}
		if ($this->post_auto_fill) {
			$postvalue = $this->CI->input->post($field_name);
			if ($postvalue !== FALSE && $postvalue == $value) $selected = TRUE;
		}
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		if ( ! empty($field_id)) $field_id = ' id="'.$field_id.'"';
		$checked = $selected ? (' checked="checked"') : '';
		$html = '<input type="radio"'.$field_id.' name="'.$field_name.'" value="'.$value.'"'.$options.$checked.' />';
		$this->add_html($html);
	}

	//renders a dropdown with error class if needed
	function dropdown($field_name, $items, $selected_item = '', $empty_item = FALSE, $options = '', $validators = FALSE)	{
		if ($this->field_has_error($field_name)) $this->_add_option($options, 'class', 'error');
		if ($validators && is_array($validators)) {
			$this->_add_option($options, 'class', "validate['".implode("','", $validators)."']");
			$this->form_validation = TRUE;
		}
		if ($this->post_auto_fill) {
			$postvalue = $this->CI->input->post($field_name);
			if ($postvalue !== FALSE) $selected_item = $postvalue;
		}
		if ( ! empty($options) && $options[0] !== ' ') $options = ' ' . $options;
		$html = '<select id="' . $field_name . '" name="' . $field_name . '"' . $options . '>';
		if ( ! empty($items)) {
			if ($empty_item !== FALSE)
				$html .= '<option value="">' . $empty_item . '</option>';
			// check if array is associative
			$is_assoc = (bool)array_diff_key($items, array_keys(array_keys($items)));
			foreach($items as $key => $item) {
				if ($is_assoc) {
					$selected = ($selected_item == $key)?' selected="selected"':'';
					$value = $key;
				} else {
					$selected = ($selected_item == $item)?' selected="selected"':'';
					$value = $item;
				}
				$html .= '<option value="' . $value . '"' . $selected . '>' . $item . '</option>';
			}
		}
		$html .= '</select>';
		$this->add_html($html);
	}

	// uses tcborder library to apply shadedborder JS to an element
	function apply_tcborder($element, $options, $return = FALSE) {
		if ( ! isset($this->TC->tcborder)) $this->CI->load->library('TCBorder');
		$html = $this->CI->tcborder->apply($element, $options, TRUE);
		if ($return) return $html;
		$this->add_html($html);
	}

	// renders CSS includes
	function show_css_files($return = FALSE) {
		$html = '';
		if (empty($this->css_files)) return $html;
		foreach ($this->css_files as $css_file)
			$html .= '<link rel="stylesheet" type="text/css" href="'.$css_file.'" />';
		if ($return) return $html;
		$this->add_html($html);
	}

	// renders JS includes
	function show_js_files($return = FALSE) {
		$html = '';
		if (empty($this->js_files)) return $html;
		foreach ($this->js_files as $js_file)
			$html .= '<script type="text/javascript" src="'.$js_file.'"></script>';
		if ($return) return $html;
		$this->add_html($html);
	}

	// returns a number formatted to current language setting
	function number($value, $decimals) {
		if ( ! is_numeric($value)) $value = 0;
		return number_format($value, $decimals, $this->CI->lang->line('tc_decimal_sep'), $this->CI->lang->line('tc_thousand_sep'));
	}

	// returns a number formatted as euro currency (ex: '? 100,50')
	function euro($value, $symbol_before = TRUE) {
		return $symbol_before ? '&#8364;&#0160;'.$this->number($value, 2) : $this->number($value, 2).'&#0160;&#8364;';
	}

	// internal function to help the following functions
	function _date($format, $timestamp) {
		if ($timestamp) {
			// if is a date string, convert to timestamp
			if ( ! is_numeric($timestamp)) $timestamp = strtotime($timestamp);
			return date($format, $timestamp);
		}
		return date($format);
	}

	// returns a date formatted to current language specification
	function date($timestamp = FALSE) {
		$format = $this->CI->lang->line('tc_date_format');
		return $this->_date($format, $timestamp);
	}

	// same as above, but include time
	function datetime($timestamp = FALSE, $show_seconds = FALSE) {
		$format = $this->CI->lang->line('tc_date_format').$this->CI->lang->line('tc_datetime_splitter');
		if ($show_seconds)
			$format .= $this->CI->lang->line('tc_time_format_secs');
		else
			$format .= $this->CI->lang->line('tc_time_format');
		return $this->_date($format, $timestamp);
	}

	// returns a config item
	function config($key) {
		return $this->CI->config->item($key);
	}

}
/* End of file TCTemplate.php */