<?php

$f3 = require('lib/base.php');
$f3->config('config/config.ini');
$f3->config('config/route.ini');
$f3->route('GET /debug',
    function() {
        //header('Content-type: application/json');
		$test = new \Webmin('128.199.162.70','4d3aku010395');
		$result = $test->find();
		var_dump($result);
    }
);
$f3->run();