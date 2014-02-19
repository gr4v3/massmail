<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

OFFICE - 2012-10-09 23:48:22 --> **************************
OFFICE - 2012-10-09 23:48:22 -->             host_id:3
OFFICE - 2012-10-09 23:48:22 --> max_accounts_per_ip:10
OFFICE - 2012-10-09 23:48:22 -->     max_ips_per_day:10
OFFICE - 2012-10-09 23:48:22 -->     limit:8
OFFICE - 2012-10-09 23:48:22 --> SELECT `ip`, `country`, `owner`
FROM (`available_ips`)
WHERE `owner` =  '127.0.0.1'
AND `active` =  1
AND `status_id` =  1
AND DATEDIFF(CURRENT_TIMESTAMP,access) >10
ORDER BY `access` asc
LIMIT 8
