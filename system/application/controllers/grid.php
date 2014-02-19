<?php
class Grid extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('log');
		$this->load->helper('array');
		$this->load->helper('debug');
	}
	function view($table = false, $id = false)
	{
		if($table === false) return false;
		$model = $this->load->model($table.'_model','model');

		$page = 1;
		$perpage = 10;
		$pagination = false;

		$pagination = $this->input->post('page')?$this->input->post('page'):false;
		$page = $this->input->post('page')?$this->input->post('page'):1;
		$perpage = $this->input->post('perpage')?$this->input->post('perpage'):10;
		$sorton = $this->input->post('sorton')?$this->input->post('sorton'):'id';
		$sortby = $this->input->post('sortby')?$this->input->post('sortby'):'asc';

		$n = ( $page -1 ) * $perpage;

		$total = $this->model->db->count_all($this->model->table);
		if ($pagination) $ret = $this->model->view(array($perpage,$n),array($sorton,$sortby), $id);
		else $ret = $this->model->view(false,array($sorton,$sortby), $id);

		$ret = array("page" => $page, "total" => $total, "data" => $ret);
		die(json_encode($ret));
	}
	function set($table = false)
	{
		if($table === false) return false;

		$data = $_POST;
		$model = $this->load->model($table.'_model','model');
		$this->model->set($data);
	}
	function get($table = false)
	{
		if($table === false) return false;

		$result = array();
		$id = $this->input->post('id');
		$model = $this->load->model($table.'_model','model');
		if(!$id) $result = $this->model->getAll();
		else $result = $this->model->get(array('id' => $id));

		die(json_encode($result));
	}
	function del($table = false)
	{
		if($table === false) return false;

		$id = $this->input->post('id');
		$ids = explode(",",$id);
		$model = $this->load->model($table.'_model','model');
		foreach($ids as $value)
		{
			$this->model->delete($value);
		}

	}

}
