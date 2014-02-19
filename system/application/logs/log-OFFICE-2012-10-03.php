<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

OFFICE - 2012-10-03 15:07:26 --> set_mass_sender:INSERT INTO setup (var, value, host_id) VALUES ('MASS_SENDER', '0', '3') ON DUPLICATE KEY UPDATE var = 'MASS_SENDER', value = '0'
OFFICE - 2012-10-03 15:07:26 --> settings_model:UPDATE `settings` SET `value` = 'smtp.gmail.com' WHERE `field` =  'host' AND `host_id` =  '3' AND `type` =  'smtp'
OFFICE - 2012-10-03 15:07:26 --> settings_model:UPDATE `settings` SET `value` = '587' WHERE `field` =  'port' AND `host_id` =  '3' AND `type` =  'smtp'
OFFICE - 2012-10-03 15:07:26 --> settings_model:UPDATE `settings` SET `value` = '10' WHERE `field` =  'timeout' AND `host_id` =  '3' AND `type` =  'smtp'
OFFICE - 2012-10-03 15:07:26 --> settings_model:UPDATE `settings` SET `value` = '' WHERE `field` =  'headers' AND `host_id` =  '3' AND `type` =  'smtp'
OFFICE - 2012-10-03 15:07:26 --> settings_model:UPDATE `settings` SET `value` = 'tls' WHERE `field` =  'service_flags' AND `host_id` =  '3' AND `type` =  'smtp'
OFFICE - 2012-10-03 15:07:26 --> settings_model:INSERT INTO `settings` (`host_id`, `type`, `field`, `value`) VALUES ('3', 'imap', 'host', 'imap.gmail.com')
OFFICE - 2012-10-03 15:07:26 --> settings_model:INSERT INTO `settings` (`host_id`, `type`, `field`, `value`) VALUES ('3', 'imap', 'port', '993')
OFFICE - 2012-10-03 15:07:26 --> settings_model:INSERT INTO `settings` (`host_id`, `type`, `field`, `value`) VALUES ('3', 'imap', 'service_flags', '/imap/ssl')
OFFICE - 2012-10-03 15:07:26 --> settings_model:INSERT INTO `settings` (`host_id`, `type`, `field`, `value`) VALUES ('3', 'imap', 'inbox', 'INBOX')
OFFICE - 2012-10-03 15:07:26 --> settings_model:INSERT INTO `settings` (`host_id`, `type`, `field`, `value`) VALUES ('3', 'imap', 'timeout', '10')
OFFICE - 2012-10-03 15:07:26 --> rules_model:UPDATE `rules` SET `flood_refresh` = '10', `flood_interval` = '10', `flood_sleep` = '10', `bounce_interval` = '10', `send_interval` = '10', `send_limit` = '10', `country` = 'us' WHERE `host_id` =  '3'
