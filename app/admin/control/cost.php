<?php
require_once( ROOT . '/data/cache/audit_type.cache.php' );
require_once( ROOT . '/data/cache/mark.cache.php' );
require_once( ROOT . '/data/cache/iso.cache.php' );
require_once( ROOT . '/data/cache/cost_type.cache.php' );
$et		= load( 'enterprise' );
$ct		= load( 'contract' );
$ctc	= load( 'cost' );

if($a=='add')
	$a='edit';
//控制器调度
$action = CTL_DIR . $c . '/' . $a . '.php';
if (file_exists($action)) {
    include_once ($action);
    exit;
} 