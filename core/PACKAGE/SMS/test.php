<?php

require_once dirname(__FILE__).'/SMS.php';

$sms = new _SMS(null);
$sms->send($argv[1], $argv[2]);


