<?php 
	header('Content-type: text/css'); 
	require_once "../../../wp-config.php";
	
	$linkify = new linkify();
	$options = $linkify->options;
	
?>

div.linkify {
	padding: 5px;
	border: 1px solid <?php echo $options['border']; ?>;
	background: <?php echo $options['background']; ?>;
	margin: 0 0 24px;
	border-radius: <?php echo $options['border-radius']; ?>px;
}

div.linkify a {
	text-decoration: none;
	display: block;
	clear: both;
	width: 100%;
	height: 100%;
	<?= empty($options['text-colour']) ? "" : "color: {$options['text-colour']}!important;" ?>
}

div.linkify a:hover {
	text-decoration: none;
	<?= empty($options['text-colour-hover']) ? "" : "color: {$options['text-colour-hover']}!important;" ?>
}

div.linkify span:first-child {
	display: block;
}

div.linkify img {
	max-width: 100px;
	float: left;
	margin: 5px 5px 5px 0px;
	border-radius: <?php echo $options['border-radius']; ?>;
}

div.linkify a p {
	font-size: 10pt;
}

div.linkify span:last-child {
	clear: both;
	display:block;
}