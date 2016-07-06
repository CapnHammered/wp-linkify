<?php
	require_once '../../../../wp-config.php';
	require_once "../linkify.php";
	
	$linkify = new linkify();
	echo $linkify->generate_json(urldecode($_REQUEST['url']));