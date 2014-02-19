<?php
// SENDGRID helper

function spam_view($params = NULL) {
	if (empty($params)) return FALSE;
	// in normal cases the $params is the account details that is recieving the spam reports
	$spam_report_source = 'https://sendgrid.com/api/spamreports.get.json?';
	$http_query = http_build_query(array(
		'api_user' => $params['login'],
		'api_key' => $params['pass']
	));
	$spam_clients_reported = file_get_contents($spam_report_source.$http_query);
	//invalidemails
	$invalid_report_source = 'https://sendgrid.com/api/invalidemails.get.json?';
	$invalid_clients_reported = file_get_contents($invalid_report_source.$http_query);
	$return_clients_reported = array_merge(json_decode($spam_clients_reported), json_decode($invalid_clients_reported));
	var_dump($return_clients_reported);
	if ($return_clients_reported) return $return_clients_reported;
	else return FALSE;
}
function spam_delete($params = NULL) {
	if (empty($params)) return FALSE;
	// in normal cases the $params is the client address to delete in the spam list reported
	$spam_report_source = 'https://sendgrid.com/api/spamreports.delete.json?';
	$http_query = http_build_query(array(
		'api_user' => $params['login'],
		'api_key' => $params['pass'],
		'email' => $params['email']
	));
	$spam_clients_reported = file_get_contents($spam_report_source.$http_query);
	$spam_report_source = 'https://sendgrid.com/api/invalidemails.delete.json?';
	$http_query = http_build_query(array(
		'api_user' => $params['login'],
		'api_key' => $params['pass'],
		'email' => $params['email']
	));
	$spam_clients_reported = file_get_contents($spam_report_source.$http_query);
	if ($spam_clients_reported) return $spam_clients_reported;
	else return FALSE;
}
?>
