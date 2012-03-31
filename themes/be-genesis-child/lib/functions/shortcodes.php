<?php

// Use shortcodes in widgets
add_filter('widget_text', 'do_shortcode');

// Returns Site URL
add_shortcode('url','url_shortcode');

function url_shortcode($atts) {
	return get_bloginfo('url');
}


// Returns Parent Theme Directory
add_shortcode('parent', 'template_shortcode');

function template_shortcode($atts) {
	return get_bloginfo('template_url');
}


// Returns Child Theme Directory
add_shortcode('child', 'child_shortcode');

function child_shortcode($atts) {
	return get_bloginfo('stylesheet_directory');
}


// Opens a div (useful for column classes)
add_shortcode('div', 'be_div_shortcode');

function be_div_shortcode($atts) {
	extract(shortcode_atts(array('class' => '', 'id' => '' ), $atts));
	if ($class) $show_class = ' class="'.$class.'"';
	if ($id) $show_id = ' id="'.$id.'"';
	return '<div'.$show_class.$show_id.'>';
}


// Closes a div
add_shortcode('end-div', 'be_end_div_shortcode');

function be_end_div_shortcode($atts) {
	return '</div>';
}