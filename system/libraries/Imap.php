<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

error_reporting(E_ALL);

// ------------------------------------------------------------------------

class Imap {

	var $CI;
	var $remote_name;
	var $port;
	var $mailbox;
	var $user;
	var $pass;
	var $mbox;
	var $stream;
	var $default_mailbox = 'INBOX';
	var $return;

	function Imap()
	{
		$this->CI =& get_instance();
		log_message('debug', "Imap Class Initialized");
	}

	function connect($user = NULL, $pass = NULL, $server = NULL)
	{

		$this->user		= $this->CI->config->item('imap_user');
		$this->pass		= $this->CI->config->item('imap_pass');
		$this->flags	= $this->CI->config->item('imap_flags');
		$this->port		= $this->CI->config->item('imap_port');
		$this->mailbox	= $this->CI->config->item('imap_mailbox');
		$this->server	= $this->CI->config->item('imap_server');

		if($user)
		{
			$this->user = $user;
		}

		if($pass)
		{
			$this->pass = $pass;
		}

		if($server)
		{
			$this->server = $server;
		}

		// Let's start building the stream's string
		$this->mbox		= '{' . $this->server;

		if($this->port)
		{
			$this->mbox .=  ':' . $this->port;
		}

		if($this->flags)
		{
			$this->mbox .= $this->flags;
		}

		//$this->mbox .= '/user="' . $this->user . '"';
		$this->mbox .= '}';

		if($this->mailbox)
		{
			$this->mbox .= $this->default_mailbox;
		}
		if ( ! function_exists('imap_errors_log')) {
			function imap_errors_log($errno, $errstr) {echo $errstr;}
		}
		set_error_handler("imap_errors_log");
		if ($this->stream = imap_open($this->mbox,$this->user,$this->pass)) {
                    restore_error_handler();
			return TRUE;
		} else {
			restore_error_handler();
			return imap_last_error();
		}
	}

	function msg_count()
	{
		if($this->stream)
		{
			return imap_num_msg($this->stream);
		}

		// Not connected!
		return imap_last_error();
	}

	function msg_list($msg_list = array())
	{
		if($this->stream)
		{
			$return = array();
			if(is_array($msg_list) OR count($msg_list) == 0)
			{
				$count = $this->msg_count();
				for($i = 1; $i <= $count; $i++)
				{
					$header = imap_headerinfo($this->stream, $i);
					foreach($header as $id => $value)
					{
						$header->Msgno = trim($header->Msgno);
						// Simple array
						if(! is_array($value))
						{
							$return[$header->Msgno][$id] = $value;
						}
						else
						{
							/*
							foreach($value as $newid => $array_value)
							{
								foreach($value[0] as $key => $aValue)
								{
									$return[$header->Msgno][$id][$key] = quoted_printable_decode($aValue);
								}
							}
							*/
							foreach($value[0] as $key => $aValue)
							{
								$return[$header->Msgno][$id][$key] = quoted_printable_decode($aValue);
							}
						}

						// Let's add the body
						// We only want the text, not the extra crap
						$return[$header->Msgno]['body'] = imap_fetchbody($this->stream, $header->Msgno,1);
					}
				}
			}
			// We want to search a specific array of messages
			else
			{
				foreach($msg_list as $i)
				{
					$header = imap_headerinfo($this->stream, $i);
					foreach($header as $id => $value)
					{
						// Simple array
						if(! is_array($value))
						{
							$return[$header->Msgno][$id] = $value;
						}
						else
						{
							/*
							foreach($value as $newid => $array_value)
							{
								foreach($value[0] as $key => $aValue)
								{
									$return[$header->Msgno][$id][$key] = $this->_quoted_printable_encode($aValue);
								}
							}
							*/
							foreach($value[0] as $key => $aValue)
							{
								$return[$header->Msgno][$id][$key] = $this->_quoted_printable_encode($aValue);
							}
						}
						// Let's add the body too!
						$return[$header->Msgno]['body'] = imap_fetchbody($this->stream, $header->Msgno, 0);
					}
				}
			}

			#$return['num_of_msgs'] = count($return);
			return $return;
		}

		return imap_last_error();
	}

	function search($params)
	{
		if($this->stream)
		{
			if(is_array($params))
			{
				$search_string = '';
				foreach($params as $field => $value)
				{
					if(is_numeric($field))
					{
						// Make sure the value is uppercase
						$search_string .= strtoupper($value) . ' ';

					}
					else
					{
						$search_string .= strtoupper($field) . ' "' . $value . '" ';
					}
				}

				// Perform the search
				#echo "'$search_string'";
				return imap_search($this->stream, $search_string);
			}
			else
			{
				return imap_last_error();
			}
		}

		return imap_last_error();
	}

	function delete($emails, $delete=FALSE)
	{
		$return = array();
		if($this->stream)
		{
			if(is_array($emails))
			{
				// Let's delete multiple emails
				if(count($emails) > 0)
				{
					$delete_string = '';
					$email_error = array();
					foreach($emails as $email)
					{
						if($delete)
						{
							if(! @imap_delete($this->stream, $email))
							{
								$email_error[] = $email;
							}
						}
					}
					if(! $delete)
					{
						// Need to take the last comma out!
						$delete_string = implode(',', $emails);
						echo $delete_string;
						imap_mail_move($this->stream, $delete_string, "Inbox/Trash");
						imap_expunge($this->stream);
					}
					else
					{
						// NONE of the emails were deleted
						if(count($email_error) === count($emails))
						{
							return imap_last_error();
						}
						else
						{
							$return['status'] = FALSE;
							$return['not_deleted'] = $email_error;
							return $return;
						}
					}
				}
				else
				{
					return imap_last_error();
				}
			}
			else
			{
				if(! $delete)
				{
					return @imap_mail_move($this->stream, $emails, "INBOX/trash");
				}
				else
				{
					// We only want to delete one email
					if(imap_delete($this->stream, $emails))
					{
						return TRUE;	// Success
					}
					else
					{
						return imap_last_error();	// Failed
					}
				}
			}
		}

		// Not connected
		return imap_last_error();
	}

	function switch_mailbox($mailbox = '')
	{
		if($this->stream)
		{
			$this->mbox = '{' . $this->CI->config->item('imap_server');

			if($this->port)
			{
				$this->mbox .=  ':' . $this->port;
			}

			if($this->flags)
			{
				$this->mbox .= $this->flags;
			}

			$this->mbox .= '/user="' . $this->user . '"';
			$this->mbox .= '}';
			$this->mbox .= $this->default_mailbox;

			if($mailbox)
			{
				$this->mbox .= '.' . $mailbox;
			}

			return @imap_reopen($this->stream, $this->mbox);
		}

		// Not connected
		return imap_last_error();
	}

	function current_mailbox()
	{
		if($this->stream)
		{
			$info = imap_mailboxmsginfo($this->stream);
			if($info)
			{
				return $info->Mailbox;
			}
			else
			{
				// There was an error
				return imap_last_error();
			}
		}

		// Not connected
		return imap_last_error();
	}

	function mailbox_info($type='obj')
	{
		if($this->stream)
		{
			$info = imap_mailboxmsginfo($this->stream);
			if($info)
			{
				if($type == 'array')
				{
					$info_array = get_object_vars($info);
					return $info_array;
				}
				else
				{
					return $info;
				}
			}
			else
			{
				// There was an error
				return imap_last_error();
			}
		}

		// Not connected
		return imap_last_error();
	}

	function close()
	{
		if($this->stream)
		{
			imap_errors();
			return @imap_close($this->stream);
		}

		return imap_last_error();
	}
}

?>