<?php
// Start the engine
require_once(TEMPLATEPATH.'/lib/init.php');

// Setup the child theme
add_action('after_setup_theme','child_theme_setup');
function child_theme_setup() {
	
	// ** Backend **
	// Remove Unused Backend Pages
	add_action('admin_menu', 'be_remove_menus');
	
	// Remove Unused Page Layouts
	//genesis_unregister_layout( 'full-width-content' );
	//genesis_unregister_layout( 'content-sidebar' );	
	//genesis_unregister_layout( 'sidebar-content' );
	//genesis_unregister_layout( 'content-sidebar-sidebar' );
	//genesis_unregister_layout( 'sidebar-sidebar-content' );
	//genesis_unregister_layout( 'sidebar-content-sidebar' );
	
	// Set up Post Types
	add_action( 'init', 'be_create_my_post_types' );	
	
	// Set up Taxonomies
	//add_action( 'init', 'be_create_my_taxonomies' );

	// Set up Meta Boxes
	//add_action( 'init' , 'be_create_metaboxes' );

	// Setup Sidebars
	//unregister_sidebar('sidebar-alt');
	//genesis_register_sidebar(array('name' => 'Blog Sidebar', 'id' => 'blog-sidebar'));
	
	// Setup Shortcodes
	include_once( CHILD_DIR . '/lib/functions/shortcodes.php');
	
	// ** Frontend **		
	// Remove Edit link
	add_filter( 'edit_post_link', 'be_edit_post_link' );

	// Remove Breadcrumbs
	remove_action('genesis_before_loop', 'genesis_do_breadcrumbs');
}

// ** Backend Functions ** //

function be_remove_menus () {
	global $menu;
	$restricted = array(__('Links'));
	// Example:
	//$restricted = array(__('Dashboard'), __('Posts'), __('Media'), __('Links'), __('Pages'), __('Appearance'), __('Tools'), __('Users'), __('Settings'), __('Comments'), __('Plugins'));
	end ($menu);
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
	}
}

function be_create_my_post_types() {
	register_post_type( 'drt_partner',
		array(
			'labels' => array(
				'name' => __( 'Partners' ),
				'singular_name' => __( 'Partner' )
			),
			'public' => true,
			'supports' => array('title', 'custom-fields', 'editor', 'excerpt', 'thumbnail'),
		)
	);
	register_post_type( 'drt_spirit',
		array(
			'labels' => array(
				'name' => __( 'Spirits' ),
				'singular_name' => __( 'Spirit' )
			),
			'public' => true,
			'supports' => array('title', 'custom-fields', 'editor', 'excerpt', 'thumbnail'),
		)
	);
	register_post_type( 'drt_distillery',
		array(
			'labels' => array(
				'name' => __( 'Distilleries' ),
				'singular_name' => __( 'Distillery' )
			),
			'public' => true,
			'supports' => array('title', 'custom-fields', 'editor', 'excerpt', 'thumbnail'),
		)
	);
}

function be_create_my_taxonomies() {
	register_taxonomy( 
		'poc', 
		'post', 
		array( 
			'hierarchical' => true, 
			'labels' => array(
				'name' => 'Points of Contact',
				'singlular_name' => 'Point of Contact'
			),
			'query_var' => true, 
			'rewrite' => true 
		) 
	);
}


function be_create_metaboxes() {
	$prefix = 'be_';
	$meta_boxes = array();

	$meta_boxes[] = array(
    	'id' => 'rotator-options',
	    'title' => 'Rotator Options',
	    'pages' => array('drt_spirit'), // post type
		'context' => 'normal',
		'priority' => 'low',
		'show_names' => true, // Show field names left of input
		'fields' => array(
			array(
				'name' => 'Instructions',
				'desc' => 'In the right column upload a featured image. Make sure this image is at least 900x360px wide. Then fill out the information below.',
				'type' => 'title',
			),
			array(
		        'name' => 'Display Info',
		        'desc' => 'Show Title and Excerpt from above',
	    	    'id' => 'show_info',
	        	'type' => 'checkbox'
			),
			array(
				'name' => 'Clickthrough URL', 
	            'desc' => 'Where the Learn More button goes',
            	'id' => 'url',
            	'type' => 'text'
			),
		),
	);
 	
 	require_once(CHILD_DIR . '/lib/metabox/init.php'); 
}



// ** Frontend Functions ** //

function be_edit_post_link($link) {
	return '';
}


// ** Unhooked Functions ** //
