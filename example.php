<?php
include('class.wxr.php');
include('class.expengine.wxr.php');

$w = new ExpressionEngine_WXR();
$w->export_filename = 'wxr-' . date('Y-m-d') . '.xml';
$w->ee_prefix = 'exp_';
$w->ee_blogname = 'weblog1';
$w->ee_timezone = 'Australia/Adelaide';
$w->dbuser = 'database_user';
$w->dbpass = 'database_password';
$w->dbhost = 'database_host';
$w->dbname = 'database_name';
$w->debug = true;

$w->dbcnx();

$w->_get_blog_settings();
$w->_get_entries( $w->settings->blogid );
$w->_get_categories( $w->items );

// DO XML
$w->do_xml();

?>