<?php
/*
Plugin Name: Distillery Post Types
Plugin URI: http://distilleryrowtours.com
Description: made by gabe for distillery row passport web site
Version: 1.0
Author: Gabe Shaughnessy
Author URI: http://gabesimagination.com
License: GPL2
*/
 
// Set up Post Types
	add_action( 'init', 'be_create_my_post_types' );	
	
	//Set up Taxonomies
	add_action( 'init', 'be_create_my_taxonomies' );

	//Set up Meta Boxes
	add_action( 'init' , 'be_create_metaboxes' );
function be_create_my_post_types() {
	register_post_type( 'drt_partner',
		array(
			'labels' => array(
				'name' => __( 'Partners' ),
				'singular_name' => __( 'Partner' )
			),
			'public' => true,
			'supports' => array('title', 'editor', 'category','excerpt', 'thumbnail'),
		)
	);
	register_post_type( 'drt_spirit',
		array(
			'labels' => array(
				'name' => __( 'Spirits' ),
				'singular_name' => __( 'Spirit' )
			),
			'public' => true,
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
		)
	);
	register_post_type( 'drt_distillery',
		array(
			'labels' => array(
				'name' => __( 'Distilleries' ),
				'singular_name' => __( 'Distillery' )
			),
			'public' => true,
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
		)
	);
	register_post_type( 'drt_call_to_action',
		array(
			'labels' => array(
				'name' => __( 'Calls To Action' ),
				'singular_name' => __( 'CTA' )
			),
			'public' => true,
			'supports' => array('title', 'editor', 'thumbnail'),
		)
	);
		register_post_type( 'drt_pitch_box',
			array(
				'labels' => array(
					'name' => __( 'Pitch Boxes' ),
					'singular_name' => __( 'Pitch Box' )
				),
				'public' => true,
				'supports' => array('title', 'editor', 'thumbnail'),
			)
		);
}

function be_create_my_taxonomies() {
	register_taxonomy( 
		'business_category', 
		'drt_partner', 
		array( 
			'hierarchical' => true, 
			'labels' => array(
				'name' => 'Business Categories',
				'singlular_name' => 'Business Category'
			),
			'query_var' => true, 
			'rewrite' => true 
		) 
	);
	register_taxonomy( 
		'distillery', 
		array('drt_spirit', 'drt_distillery'),
		array( 
			'hierarchical' => true, 
			'labels' => array(
				'name' => 'Distilleries',
				'singlular_name' => 'Distillery'
			),
			'query_var' => true, 
			'rewrite' => true 
		) 
	);
	register_taxonomy( 
		'featured', 
		array('drt_partner'),
		array( 
			'hierarchical' => true, 
			'labels' => array(
				'name' => 'Features',
				'singlular_name' => 'Featured'
			),
			'query_var' => true, 
			'rewrite' => true 
		) 
	);
	register_taxonomy( 
		'cta_position', 
		array('drt_call_to_action'),
		array( 
			'hierarchical' => true, 
			'labels' => array(
				'name' => 'CTA Positions',
				'singlular_name' => 'CTA Position'
			),
			'query_var' => true, 
			'rewrite' => true 
		) 
	);
}
/* Meta Boxes */
function be_create_metaboxes() {
	$prefix = 'be_';
	$meta_boxes = array();

	$meta_boxes[] = array(
    	'id' => 'partner-details',
	    'title' => 'Partner Details',
	    'pages' => array('drt_partner'), // post type
		'context' => 'normal',
		'priority' => 'low',
		'show_names' => true, // Show field names left of input
		'fields' => array(
			array(
				'name' => 'Discount',
				'desc' => 'What are they offering to passport holders?',
				'id' => 'drt_description',
				'type' => 'text',
			),
			array(
			    'name' => 'Website',
			    'desc' => 'Business Website URL',
			    'id' => 'drt_website',
				'type' => 'text'
			),
			array(
		        'name' => 'Address',
		        'desc' => 'Business Address',
	    	    'id' => 'drt_address',
	        	'type' => 'text'
			),
			array(
				'name' => 'Phone', 
	            'desc' => 'Business Phone Number, with area code, ie (555)555-5555',
            	'id' => 'drt_phone',
            	'type' => 'text'
			),
			array(
				'name' => 'QR Code', 
			    'desc' => 'The URL for the QR Code imagethat links to this parnter profile',
				'id' => 'drt_qrcode',
				'type' => 'text'
			),
			array(
				'name' => 'QR Code link', 
			    'desc' => 'The URL that the qr code links to',
				'id' => 'drt_qrcode_link',
				'type' => 'text'
			),
			array(
				'name' => 'iFrame', 
			    'desc' => 'Embed extra content here',
				'id' => 'drt_iframe',
				'type' => 'textarea'
			),
			array(
				'name' => 'iFrame Title', 
			    'desc' => 'give the extra content a title',
				'id' => 'drt_iframe_title',
				'type' => 'text'
			),
			
		),
	);
	$meta_boxes[] = array(
		'id' => 'partner-hours',
	    'title' => 'Business hours',
	    'pages' => array('drt_partner'), // post type
		'context' => 'normal',
		'priority' => 'low',
		'show_names' => true, // Show field names left of input
		'fields' => array(
			
			array(
		        'name' => 'Monday Open?',
		        'desc' => 'Is the business open on mondays?',
	    	    'id' => 'drt_monday_open',
	        	'type' => 'checkbox'
			),
			array(
				'name' => 'Monday Hours', 
	            'desc' => 'When are they open?',
	        	'id' => 'drt_monday_hours',
	        	'type' => 'text'
			),
			array(
			    'name' => 'Tuesday Open?',
			    'desc' => 'Is the business open on mondays?',
			    'id' => 'drt_monday_open',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Tuesday Hours', 
			    'desc' => 'When are they open?',
				'id' => 'drt_monday_hours',
				'type' => 'text'
			),
			array(
			    'name' => 'Wednesday Open?',
			    'desc' => 'Is the business open on mondays?',
			    'id' => 'drt_monday_open',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Wednesday Hours', 
			    'desc' => 'When are they open?',
				'id' => 'drt_monday_hours',
				'type' => 'text'
			),
			array(
			    'name' => 'Thursday Open?',
			    'desc' => 'Is the business open on mondays?',
			    'id' => 'drt_monday_open',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Thursday Hours', 
			    'desc' => 'When are they open?',
				'id' => 'drt_monday_hours',
				'type' => 'text'
			),
			array(
			    'name' => 'Friday Open?',
			    'desc' => 'Is the business open on mondays?',
			    'id' => 'drt_monday_open',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Friday Hours', 
			    'desc' => 'When are they open?',
				'id' => 'drt_monday_hours',
				'type' => 'text'
			),
			array(
			    'name' => 'Saturday Open?',
			    'desc' => 'Is the business open on mondays?',
			    'id' => 'drt_monday_open',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Saturday Hours', 
			    'desc' => 'When are they open?',
				'id' => 'drt_monday_hours',
				'type' => 'text'
			),
			array(
			    'name' => 'Saturday Open?',
			    'desc' => 'Is the business open on mondays?',
			    'id' => 'drt_monday_open',
				'type' => 'checkbox'
			),
			array(
				'name' => 'Saturday Hours', 
			    'desc' => 'When are they open?',
				'id' => 'drt_monday_hours',
				'type' => 'text'
			),
			
			
		),
	);
	$meta_boxes[] = array(
		'id' => 'distillery-details',
	    'title' => 'Distillery Details',
	    'pages' => array('drt_distillery'), // post type
		'context' => 'normal',
		'priority' => 'low',
		'show_names' => true, // Show field names left of input
		'fields' => array(
			array(
				'name' => 'Discount',
				'desc' => 'What are they offering to passport holders?',
				'id' => 'drt_description',
				'type' => 'text',
			),
			array(
			    'name' => 'Website',
			    'desc' => 'Business Website URL',
			    'id' => 'drt_website',
				'type' => 'text'
			),
			
			array(
		        'name' => 'Address',
		        'desc' => 'Business Address',
	    	    'id' => 'drt_address',
	        	'type' => 'text'
			),
			array(
				'name' => 'Phone', 
	            'desc' => 'Business Phone Number, with area code, ie (555)555-5555',
	        	'id' => 'drt_phone',
	        	'type' => 'text'
			),
			array(
				'name' => 'Logo file', 
			    'desc' => 'The logo of the distillery',
				'id' => 'drt_logo',
				'type' => 'text'
			),
			array(
				'name' => 'QR Code', 
			    'desc' => 'The URL for the QR Code that links to this parnter profile',
				'id' => 'drt_qrcode',
				'type' => 'text'
			),
		),
	);
	$meta_boxes[] = array(
		'id' => 'box-settings',
	    'title' => 'Pitch Box Settings',
	    'pages' => array('drt_pitch_box'), // post type
		'context' => 'normal',
		'priority' => 'low',
		'show_names' => true, // Show field names left of input
		'fields' => array(
			array(
				'name' => 'Filename Prefix',
				'desc' => 'The first part of the file name that is repeated on each image, for example \'image-title\' in the file sequence \'image-title-XXXX.jpg\'',
				'id' => 'drt_prefix',
				'type' => 'text',
			),
			array(
			    'name' => 'Filename Suffix',
			    'desc' => 'The last part of the file name that describes the file type, for example \'.jpg\' in the file sequence \'image-title-XXXX.jpg\'',
			    'id' => 'drt_suffix',
			    'type' => 'text',
			),
			
			array(
			    'name' => 'Zero Padding',
			    'desc' => 'How many digits is the sequence number,for example \'4\' in the file sequence \'image-title-XXXX.jpg\'',
			    'id' => 'drt_zero_padding',
			    'type' => 'text',
			),
			array(
			    'name' => 'Number of Frames',
			    'desc' => 'How many images in the sequence?',
			    'id' => 'drt_image_count',
			    'type' => 'text',
			),
			array(
			    'name' => 'Directory',
			    'desc' => 'What folder or directory are the images in?',
			    'id' => 'drt_image_dir',
			    'type' => 'text',
			),
					),
	);
	
 	
 	require_once('metabox/init.php'); 
}

?>