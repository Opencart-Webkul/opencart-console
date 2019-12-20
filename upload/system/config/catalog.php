<?php
// Site
$_['site_url']           = HTTP_SERVER;

// Database
$_['db_autostart']       = true;
$_['db_engine']          = DB_DRIVER; // mpdo, mssql, mysql, mysqli or postgre
$_['db_hostname']        = DB_HOSTNAME;
$_['db_username']        = DB_USERNAME;
$_['db_password']        = DB_PASSWORD;
$_['db_database']        = DB_DATABASE;
$_['db_port']            = DB_PORT;

// Session
$_['session_autostart']  = false;
$_['session_engine']     = 'db';
$_['session_name']       = 'OCSESSID';

// Template
$_['template_engine']    = 'twig';
$_['template_directory'] = '';

// Autoload Libraries
$_['library_autoload']   = array();

// Actions
$_['action_pre_action']  = array(
	'startup/startup',
	'startup/marketing',
	'startup/error',
	'startup/event',
	'startup/sass',
	'startup/maintenance',
	'startup/seo_url'
);

// Action Events
$_['action_event']      = array(
	'controller/*/before' => array(
		'event/language/before',
		//'event/debug/before'
	),
	'controller/*/after' => array(
		'event/language/after',
		//'event/debug/after'
	),
	'view/*/before' => array(
		500 => 'event/theme',
		998 => 'event/language'
	),
	'language/*/after' => array(
		'event/translation'
	)
);