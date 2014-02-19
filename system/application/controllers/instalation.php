<?php

class Instalation extends Controller {

	function __construct()
	{
		parent::Controller();
		$this->load->dbforge();
	}

	function index()
	{

		$tables = array();
		$tables['host'] = array(
			'protocol' => array(
				'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'smtp_host' => array(
				 'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'smtp_user' => array(
				 'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'smtp_pass' => array(
				 'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'flags' => array(
				 'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'smtp_port' => array(
				 'type' => 'INT',
				 'constraint' => 10
			),
			'smtp_timeout' => array(
				'type' => 'INT',
				'constraint' => 10
			),
			'priority' => array(
				'type' => 'INT',
				'constraint' => 10,
				'defaultValue' => 3
			)
        );
		$tables['accounts'] = array(

			'name' => array(
				'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'email' => array(
				 'type' => 'VARCHAR',
				 'constraint' => '100'
			),
			'count' => array(
				 'type' => 'INT',
				 'constraint' => 10
			),
			'created' => array(
				 'type' => 'TIMESTAMP'
			),
			'modified' => array(
				 'type' => 'TIMESTAMP'
			),
			'erased' => array(
				 'type' => 'TIMESTAMP'
			)
		);


		foreach($tables as $index => $value)
		{
			$this->dbforge->drop_table($index);
			$this->dbforge->add_field('id');
			$this->dbforge->add_field($value);
			$this->dbforge->create_table($index);
		}



	}
	

}

/* End of file email.php */
/* Location: ./system/application/controllers/index.php */