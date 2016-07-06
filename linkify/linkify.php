<?php
/*
	Plugin Name: Linkify
	Plugin URI: http://labs.danny.gb.net/wordpress/linkify
	Description: Creates attractive links to internal/external pages in a similar way to Facebook.  Stores the title, thumbnail and meta description of a webpage (if available) to an attachment.
	Version: 1.0
	Author: Danny Battison
	Author URI: http://labs.danny.gb.net
	License: GPL2
*/

/* If only everyone wrote perfect XHTML */

if(!defined('LINKIFY_URL')) {
	define('LINKIFY_URL', plugin_dir_url(__FILE__));
}

if(!defined('LINKIFY_DIR')) {
	define('LINKIFY_DIR', plugin_dir_path(__FILE__));
}

require_once LINKIFY_DIR . 'linkify-settings.php';

class linkify {
	private $last_attachment_id, $processor, $settings;
	public $options;
	
	const SERVER = 1, CLIENT = 2;
	
	public function __construct() {
		add_shortcode('linkify', array($this, 'shortcode'));
		add_filter('posts_where', array($this, 'posts_where_post_excerpt'), 10, 2);
		
		$this->settings = new linkify_settings();
		
		$this->options = $this->settings->get();
		
		if(!$this->options['shortcode-only']) {
			add_filter('the_content', array($this, 'the_content'));	
		}
		$this->processor = $this->options['load-server-side'] == linkify::SERVER ? linkify::SERVER : linkify::CLIENT;
		
		add_action('wp_head', array($this, 'register_scripts'));
		add_action('admin_head', array($this, 'register_scripts'));
		
		// Add settings link on plugin page 
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", array($this, 'settings_link'));
	}
	
	function the_content($content) {
		$content = preg_replace_callback('/<p>(.+?)<\/p>/i', array($this, 'linkify_content'), $content);
		return $content;
	}
	
	function shortcode($params, $content) {
		return $this->generate_html(strip_tags($content), $params);
	}
	
	function generate_html($url, $shortcode=null) {
		$unloaded = "unloaded";
		$params = array();
		switch($this->processor) {
			case linkify::SERVER:
				$params = $this->get_information($url, false);
				if($params !== null) {
					$unloaded = "";
				}
				break;
			case linkify::CLIENT:
				$params = array(
					"url" => $url,
					"title" => empty($title) ? $url : $title,
					"description" => $description,
					"image" => LINKIFY_URL . "img/ajax-loader.gif",
				);
				break;
		}
		if($shortcode !== null) {
			$params = array_merge($params, $shortcode);
		}
		$params['title'] = empty($params['title']) ? $url : $params['title'];
		$params['image'] = $unloaded == 'unloaded' ? '' : $params['image'];
		
		$rel = ""; $target = "";
		if(isset($params['rel'])) {
			$rel = "rel='{$params['rel']}'";
		} else if($this->options['rel-nofollow'] == 'all') { 
			$rel = "rel='nofollow'"; 
		}else if($this->options['rel-nofollow'] == 'external') { 
			$local = site_url();
			if(strpos($local, $url) !== 0) {
				$rel = "rel='nofollow'"; 
			}
		}
		
		if(isset($params['target'])) {
			$target = "target='{$params['target']}'";
		} else if($this->options['target-blank'] == 'all') { 
			$target = "target='_blank'"; 
		} else if($this->options['target-blank'] == 'external') { 
			$local = site_url();
			if(strpos($local, $url) !== 0) {
				$target = "target='_blank'"; 
			}
		}
		
		$params = $this->prepare($params);
		$img = empty($params['image']) ? "<img />" : "<img src='{$params['image']}' alt='{$params['title']}' />";
		
		return "<div class='linkify $unloaded'>
			<a href='$url' $rel $target>
				<span>{$params['title']}</span>
				$img
				<p>{$params['description']}</p>
				<span></span>
			</a>
		</div>";
	}
	
	function generate_json($url) {
		return json_encode($this->prepare($this->get_information($url, true)));
	}
	
	function linkify_content($matches) {
		$foo = array();
		preg_match('/(http[s]?:\/\/[^\s]*)/i', strip_tags($matches[1]), $foo);

		if(count($foo) > 0 && strip_tags($matches[0]) == $foo[1]) {
			$url = strip_tags($matches[0]);
			return $this->generate_html($url);
		} else {
			return $matches[0];
		}
	}
	
	function prepare($params) {
		$params['title'] = strip_tags($params['title']);
		$params['description'] = strip_tags($params['description']);
		$params['image'] = strip_tags($params['image']);
		
		$max = $this->options['max-description'];
		$params['description'] = strlen($params['description']) > $max ? (substr($params['description'], 0, $max)) : ($params['description'] . ' ');
		$append = substr(trim($params['description']), strlen(trim($params['description']))-1) == '.' || strlen(trim($params['description'])) == 0 ? '' : '...';
		$params['description'] = substr($params['description'], 0, strrpos($params['description'], " ")) . $append;
		
		return $params;
	}
	
	function get_information($url, $grab=false) {
		$info = $this->get_from_media($url);
		if($info == null && $grab) {
			$info = $this->get_from_url($url);
		}
		
		return $info;
	}
	
	function get_from_media($url) {
		$query = new WP_Query(
			array(
				'post_type' => 'attachment',
				'post_excerpt' => $url,
				'posts_per_page' => 1,
				'post_status' => array('publish', 'private', 'inherit')
			)
		);
		if($query->have_posts()) {
			$img = image_downsize($query->posts[0]->ID, 'thumbnail');
			return array(
				"title" => $query->posts[0]->post_title,
				"image" => $img[0],
				"description" => $query->posts[0]->post_content,
				"url" => $url
			);
		} else {
			return null;
		}
	}
	
	function get_from_url($url) {
		global $post;
		$html = file_get_contents($url);
		
		/* get title. if not available, uses URL */
		$title = array();
		preg_match('/<title(.+)?>(.+)?<\/title>/i', $html, $title);
		$title = empty($title[2]) ? $url : $title[2];
		
		/* get meta description. if not available, gets the first <p> */
		$description = array();
		preg_match_all('/<meta(.+)name=[\'\"]description[\'\"](.+)?(\/?)>/i', $html, $description);
		$foo = trim($description[1][0]);
		$description = empty($foo) ? trim($description[2][0]) : trim($description[1][0]);
		
		if(empty($description)) {
			preg_match('/<p>(.*)<\/p>/i', $html, $description);
			$description = $description[1];
		} else {
			$description = trim(preg_replace('/content=[\'\"](.+)[\'\"]/si', "$1", $description), '/');
		}
		
		/* get meta image_src. if not available, gets the first <img> */
		$image = array();
		preg_match_all('/<link(.+)rel=[\'\"]image_src[\'\"](.+)?(\/?)>/i', $html, $image);
		$foo = trim($image[1][0]);
		$image = empty($foo) ? trim($image[2][0]) : trim($image[1][0]);
		
		if(empty($image)) {
			preg_match_all('/<img(.*?)>/i', $html, $image);
			if(count($image) == 0) {
				$image = plugin_dir_url(__FILE__) . 'no-thumbnail-available.png';
			} else {
				$foo = array();
				$i = 0;
				for($i = 0; $i < count($image[0]); $i++) {
					if(strstr($image[0][$i], 'src=') !== false) { break; }
				}
				$image = preg_match('/src=[\'\"](.+?)[\'\"]/i', $image[0][$i], $foo);
				$image = $foo[1];
			}
		} else {
			$image = trim(preg_replace('/href=[\'\"](.+)[\'\"]/si', "$1", $image), '/');
		}
		
		$data = array(
			"title" => $title,
			"image" => $image,
			"description" => $description,
			"url" => $url
		);
		
		if($this->processor === linkify::SERVER) {
			$id = $this->save_information($data);
			$img = image_downsize($id, 'thumbnail');
			$data["image"] = $img[0];
		}
		
		return $data;
	}
	
	function save_information($params) {
		global $post;
		
		$title = $params["title"];
		$image = $params["image"];
		$description = $params["description"];
		$url = $params["url"];
		
		/* save the image to the media library */
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		add_action('add_attachment', array($this, 'new_attachment'));
		$img = media_sideload_image($image, $post->ID, $title);
		remove_action('add_attachment', array($this, 'new_attachment'));
		
		/* update description / excerpt (latter is used for lookup) */
		if($this->last_attachment_id !== null) {
			$success = wp_update_post(
				array(
					'ID' => $this->last_attachment_id,
					'post_content' => $description,
					'post_excerpt' => $url
				)
			);
			if($success == 0) {
				wp_delete_post($this->attachment_id);
			}
		}
		
		$attachment_id = $this->last_attachment_id;
		$this->last_attachment_id = null;
		
		return $attachment_id;
	}
	
	// extra hooks
	
	function register_scripts() {
		global $wp_version;

		
		wp_enqueue_style('linkify', LINKIFY_URL . 'linkify-style.php');
		echo "<script type='text/javascript'>var LINKIFY_URL = '" . LINKIFY_URL . "';</script>";
		wp_enqueue_script('linkify-script', LINKIFY_URL . 'ajax/linkify.js', array('jquery'), $wp_version, true);
		
		if(is_admin()) {
			wp_enqueue_style('minicolors', LINKIFY_URL . 'minicolors/jquery.minicolors.css');
			echo "<style type='text/css'> 
				input.minicolors { width:135px !important; height:25px !important; } 
				.minicolors-swatch { top:3px !important; left:3px !important; height:19px !important; width:19px !important; }
			</style>";
			wp_enqueue_script('minicolors', LINKIFY_URL . 'minicolors/jquery.minicolors.min.js');
			echo "<script type='text/javascript'><!--
				jQuery(function() {
					jQuery('input.minicolors').each(function() { 
						console.log(jQuery(this));
						jQuery(this).minicolors(); 
					})
				});
			</script>";
		}
	}
	
	function posts_where_post_excerpt($where, &$wp_query) {
		global $wpdb;
		if ($post_excerpt = $wp_query->get('post_excerpt')) {
			$where .= $wpdb->prepare(' AND ' . $wpdb->posts . '.post_excerpt = %s', $post_excerpt);
		}
		return $where;
	}
	
	function new_attachment($id) {
		$this->last_attachment_id = $id;
	}
	
	function settings_link($links) { 
		$settings_link = '<a href="options-general.php?page=linkify-settings-admin">Settings</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
}

$linkify = new linkify();