<?php
require_once dirname(__FILE__).'/CronJobber.php';
require_once dirname(__FILE__).'/Job.php';

$jobber = new CronJobberPhp_CronJobber();

$jobber->run();