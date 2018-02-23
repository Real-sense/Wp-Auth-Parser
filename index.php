<?php

	include_once './lib/curl.php';
	include_once './config.php';

	$data = getConfig();

	$c = Curl::app($data['url'])
		->headers(1)
		->post($data['user'])
		->follow(1)
		->cookie('1.txt');

	$r = $c->request($data['path']);

	echo $r['html'];