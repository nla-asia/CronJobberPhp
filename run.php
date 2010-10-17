<?php
//No time limit and a bunch of memory
//Change these settings if you'd like a bit stricter rules
set_time_limit(0);
ini_set('memory_limit', '256M');

require_once dirname(__FILE__).'/CronJobber.php';
require_once dirname(__FILE__).'/Job.php';

array_shift($argv);
$params = array();
foreach( $argv as $argument )
{
	$keyValue = explode('=',$argument);
	$params[$keyValue[0]] = $keyValue[1];
}

$jobber = new CronJobberPhp_CronJobber($params);

$jobber->run();