<?php declare(strict_types = 1);
/*
Plugin Name: Randposts
Plugin URI: https://github.com/dougherty-dev/Randposts
Description: Include random posts in WP blog using shortcodes.
Version: 1.0.0
Author: Niklas Dougherty
Author URI: https://github.com/dougherty-dev
License: GNU General Public License v3.0
Copyright: 2021– Niklas Dougherty
*/

version_compare(PHP_VERSION, '8.0', '>=') or exit;
defined('ABSPATH') or exit;
define('RANDPOSTS_VERSION', '1.0.0');
define('RANDPOSTS_NAME', 'randposts');

add_action('init', 'init_randpost');
function init_randpost(): void {
	add_shortcode('randposts', 'get_randposts');
}

function get_randposts(array $attributes, string $content, string $tag): string {
	$randposts = '';

	$attributes = shortcode_atts([
		'post_types' => 'post',
		'post_number' => 1,
		'content_type' => 'title',
	], $attributes, $tag);

	$arguments = [
		'post_type' => explode(',', $attributes['post_types']),
		'numberposts' => $attributes['post_number'],
		'orderby' => 'rand'
	];

	foreach (get_posts($arguments) as $random_content) {
		$randposts .= build_randposts($random_content, $attributes['content_type']);
	}

	return PHP_EOL . $randposts;
}

function build_randposts(WP_Post $post, string $content_type = 'title'): string {
	if (!in_array($content_type, ['url', 'title', 'title_content', 'title_excerpt'])) return '';
	$permalink = get_permalink($post->ID);
	if (in_array($content_type, ['url'])) return $permalink;

	$post_title = $post->post_title;

	if (in_array($content_type, array('title_content', 'content'))) {
		$content = apply_filters('the_content', get_post_field('post_content', $post->ID));
	} elseif (in_array($content_type, ['title_excerpt', 'excerpt'])) {
		$content = get_the_excerpt($post);
	}

	$randposts = <<< EOT
<div class="randposts">
	<a class="randposts_link" href="$permalink">$post_title</a>
	<div class="randposts_content">$content</div><br/>
</div>

EOT;
	return $randposts;
}
