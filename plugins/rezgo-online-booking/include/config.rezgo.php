<?php
	
	// These settings are from the WordPress settings page for Rezgo, adjust them there
	define(	"REZGO_CID", 								get_option('rezgo_cid') 											);
	define(	"REZGO_API_KEY", 						get_option('rezgo_api_key') 									);
	
	define(	"REZGO_CAPTCHA_PUB_KEY",		get_option('rezgo_captcha_pub_key')						);
	define(	"REZGO_CAPTCHA_PRIV_KEY",		get_option('rezgo_captcha_priv_key')					);
	
	// we don't want the docroot to be attached since the class does that itself
	// that way, this directive is usable for the full path and the local path
	define(	"REZGO_DIR",								strstr(preg_replace('/(https?\:\/\/)/', '', WP_PLUGIN_URL), '/').'/rezgo-online-booking');
		
	// this directive is defined in rezgo.php rezgo_set_globals() to capture the current dir
	//define( "REZGO_URL_BASE",				""																						);
	
	define( "REZGO_TEMPLATE", 					get_option('rezgo_template') 									);
		
	// this directive is defined in rezgo.php rezgo_set_globals() to capture the current dir
	//define(	'REZGO_FATAL_ERROR_PAGE', '/error.php'																	);
	
	define(	"REZGO_RESULTS_PER_PAGE",		get_option('rezgo_result_num')								);
	
	define(	"REZGO_FORWARD_SECURE",			get_option('rezgo_forward_secure')						);
	
	define( "REZGO_SECURE_URL",					get_option('rezgo_secure_url')								);

	define( "REZGO_XML",								'xml.rezgo.com'																);
	
	define( "REZGO_XML_VERSION",				'current'																			);
	
	define(	"REZGO_HIDE_HEADERS",				1																							);
		
	/* 
		---------------------------------------------------------------------------
			Error and debug handling 
		---------------------------------------------------------------------------
	*/
	
	// Send errors to firebug via console (get firebug: http://getfirebug.com/)
	define(	"REZGO_FIREBUG_ERRORS",			0																							);
	
	// Display errors if they occur, disabled if you just want to send errors to firebug
	define(	"REZGO_DISPLAY_ERRORS",			1																							);
	
	// Stop the page loading if an error occurs
	define(	"REZGO_DIE_ON_ERROR",				0																							);
	
	// Output all XML transactions. THIS MUST BE SET TO 1 TO USE THE SETTINGS BELOW
	define(	"REZGO_TRACE_XML",					0																							);
	
	// Include calls to the XML Cache (repeat queries) in the XML output
	define(	"REZGO_INCLUDE_CACHE_XML",	0																							);
	
	// Send the XML requests to Firebug, to avoid disrupting the page design
	define(	"REZGO_FIREBUG_XML",				1																							);
	
	// Switch the commit XML debug for one more suited for AJAX
	define(	"REZGO_SWITCH_COMMIT",			1																							);
	
	// Stop the commit request so booking AJAX responses can be checked
	define(	"REZGO_STOP_COMMIT",				0																							);
	
	// Display the XML inline with the regular page content
	define(	"REZGO_DISPLAY_XML",				0																							);
	
?>