<?php

	/*
		Plugin Name: Rezgo Online Booking
		Plugin URI: http://wordpress.org/extend/plugins/rezgo-online-booking/
		Description: Connect WordPress to your Rezgo account and accept online bookings directly on your website.
		Version: 1.6
		Author: Rezgo
		Author URI: http://www.rezgo.com
		License: Modified BSD
	*/
	
	/*  
		- Documentation and latest version
				http://support.rezgo.com/developers/rezgo-open-source-php-parser.html
		
		- Finding your Rezgo CID and API KEY
				http://support.rezgo.com/developers/xml-api-key
		
		- Discussion and Feedback
				http://getsatisfaction.com/rezgo/products/rezgo_rezgo_open_source_php_parser
		
		AUTHOR:
				Kevin Campbell, John McDonald
		
		Copyright (c) 2011, Rezgo (A Division of Sentias Software Corp.)
		All rights reserved.
		
		Redistribution and use in source form, with or without modification,
		is permitted provided that the following conditions are met:
		
		* Redistributions of source code must retain the above copyright
		notice, this list of conditions and the following disclaimer.
		* Neither the name of Rezgo, Sentias Software Corp, nor the names of
		its contributors may be used to endorse or promote products derived
		from this software without specific prior written permission.
		* Source code is provided for the exclusive use of Rezgo members who
		wish to connect to their Rezgo XMP API.  Modifications to source code
		may not be used to connect to competing software without specific
		prior written permission.
		
		THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
		"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
		LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
		A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
		HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
		SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
		LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
		DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
		THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
		(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
		OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.	
	*/
	
	// enable output buffering so we can send header information
	ob_start();
	
	// The class must be included by itself here so that it is loaded for all functions
	require('include/page_header.php');
		
	// Settings panel in WP Admin
	include('rezgo_settings.php');

	// flush the rewrite rules and add Rezgo rewrite rules upon plugin activation
	register_activation_hook(__FILE__, 'flush_rewrite_rules');
	add_action('generate_rewrite_rules', 'rezgo_add_rewrite_rules');
	
	function rezgo_add_rewrite_rules($wp_rewrite) {	
		
	  $new_rules = array ( 
	  
	  	// tour details page (general)
			'(.+?)/details/([0-9]+)/([^\/]+)/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=tour_details&com=$matches[2]',
			
			// tour details page (date and option selected)
			'(.+?)/details/([0-9]+)/([^\/]+)/([0-9]+)/([^\/]+)/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=tour_details&com=$matches[2]&option=$matches[4]&date=$matches[5]',
			
			// tag search
			'(.+?)/tag/([^\/]*)/?$'
			=> 'index.php?pagename=$matches[1]&tags=$matches[2]',	
			
			// keyword search
			'(.+?)/keyword/([^\/]*)/?$'
			=> 'index.php?pagename=$matches[1]&search_in=smart&search_for=$matches[2]',		
			
			// supplier search (vendor only)
			'(.+?)/supplier/([^\/]*)/?$'
			=> 'index.php?pagename=$matches[1]&cid=$matches[2]',			
			
			// booking complete print page
			'(.+?)/complete/([^\/]*)/print/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=booking_complete_print&trans_num=$matches[2]',		
			
			// booking complete page
			'(.+?)/complete/([^\/]*)/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=booking_complete&trans_num=$matches[2]',		
			
			// promo code input for /promo/code style
			'(.+?)/(.+)?promo/([^\/]+)/?$'
			=> 'index.php?pagename=$matches[1]&promo=$matches[3]',
			
			// wordpress only, fatal error page
			'(.+?)/error(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=error',
			
			// wordpress only, about us page
			'(.+?)/about(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=about',
			
			// wordpress only, contact us page
			'(.+?)/contact(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=contact', 
			
			// wordpress only, terms and conditions page
			'(.+?)/terms(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=terms',
			
			// wordpress only, booking page
			'(.+?)/book(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=book',
			
			// wordpress only, terms redirect page
			'(.+?)/terms_popup(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=terms_popup',
			
			// wordpress only, shorturl redirect page
			'(.+?)/shorturl_ajax(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=shorturl_ajax',
			
			// wordpress only, calendar redirect page for AJAX
			'(.+?)/calendar(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=calendar',
			
			// wordpress only, booking redirect page for AJAX
			'(.+?)/book_ajax(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=book_ajax',
			
			// wordpress only, payment redirect page for iframe
			'(.+?)/booking_payment(.php)?/?$'
			=> 'index.php?pagename=$matches[1]&rezgo_page=booking_payment'			
			
		);
	
	  //​ add the rewrite rule into top of rules array
	  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	 
	}
	
	global $matched_query;
	global $wp_current_page;
	
	add_action('wp', 'rezgo_set_globals');
	
	function rezgo_set_globals($wp) {
		
		global $matched_query, $wp_current_page;
		
		// set globals parsed from rewrite variables
		parse_str($wp->matched_query, $matched_query);
		
		// the pagename can hide under a couple different names
		$wp_current_page = $wp->query_vars['pagename'];
		
		// register the URL vars as _REQUEST superglobals, this way they act like normal mod_rewrite and
		// we can use regular $_REQUEST for page requests below. This also allows us to additional more 
		// variables in the shortcode, such as rezgo_page.
		foreach($matched_query as $k => $v) {
			$_REQUEST[$k] = urldecode($v);
		}
		
		// extract the wordpress install path, in case we are in a subdirectory
		$res = str_replace('://', '', get_bloginfo('url'));
		$res = strstr($res, '/');
		define("REZGO_URL_BASE",					(($wp_current_page) ? $res."/".$wp_current_page : $res));
		
		// force-define the document root using wordpress's root constant
		// this contains the full path to the rezgo plugin
		
		//define("REZGO_DOCUMENT_ROOT", $_SERVER[DOCUMENT_ROOT]);
		
		define("REZGO_DOCUMENT_ROOT", ABSPATH."wp-content/plugins/rezgo-online-booking/");
		
		define("REZGO_FATAL_ERROR_PAGE",	REZGO_DIR."/error.php");
		
	}
	
	// shortcode [rezgo_shortcode] to use in page or post content
	add_shortcode('rezgo_shortcode', 'rezgo_display_triggers');
	
	// hook to use in templates
	add_action('rezgo_tpl_display', 'rezgo_display_triggers');
	
	function rezgo_display_triggers($args) {
		global $wp, $matched_query;
		
		global $wp_current_page;
		
		
		// Process any arguments on the shortcode that we have into _REQUEST variables
		// we only want arguments that aren't already set as a _REQUEST, so that we don't
		// break the entire booking flow by fixing it to one page or id
		if($args) {
			foreach($args as $k => $v) {
				if(!$_REQUEST[$k]) {
					$_REQUEST[$k] = $v;
				}
			}
		}
		
		
		if($_REQUEST['rezgo_page'] == 'tour_details') {
			return rezgo_display_details();
		} elseif($_REQUEST['rezgo_page'] == 'error') {
			return rezgo_display_error();
		} elseif($_REQUEST['rezgo_page'] == 'about') {
			return rezgo_display_about();
		} elseif($_REQUEST['rezgo_page'] == 'contact') {
			return rezgo_display_contact();
		} elseif($_REQUEST['rezgo_page'] == 'terms') {
			return rezgo_display_terms();
		} elseif($_REQUEST['rezgo_page'] == 'book') {
			return rezgo_display_book();
		} elseif($_REQUEST['rezgo_page'] == 'booking_complete') {
			return rezgo_display_complete();
		} elseif($_REQUEST['rezgo_page'] == 'booking_complete_print') {
			// this print page redirects a standard request to a plugin-specific file
			foreach($_REQUEST as $k => $v) { $string[] = $k.'='.$v; }
			header("location: ".REZGO_DIR.'/booking_complete_print.php?'.implode("&", $string));
			exit;
		} elseif($_REQUEST['rezgo_page'] == 'terms_popup') {
			// this terms popup redirects a standard request to a plugin-specific file
			foreach($_REQUEST as $k => $v) { $string[] = $k.'='.$v; }
			header("location: ".REZGO_DIR.'/terms_popup.php?'.implode("&", $string));
			exit;
		} elseif($_REQUEST['rezgo_page'] == 'shorturl_ajax') {
			// this shorturl page redirects a standard request to a plugin-specific ajax file
			foreach($_REQUEST as $k => $v) { $string[] = $k.'='.$v; }
			header("location: ".REZGO_DIR.'/shorturl_ajax.php?'.implode("&", $string));
			exit;
		} elseif($_REQUEST['rezgo_page'] == 'calendar') {
			// this calendar page redirects a standard request to a plugin-specific ajax file
			foreach($_REQUEST as $k => $v) { $string[] = $k.'='.$v; }
			header("location: ".REZGO_DIR.'/calendar.php?'.implode("&", $string));
			exit;
		} elseif($_REQUEST['rezgo_page'] == 'booking_payment') {
			// this payment form page redirects a standard request to a plugin-specific ajax file
			foreach($_REQUEST as $k => $v) { $string[] = $k.'='.$v; }
			header("location: ".REZGO_DIR.'/booking_payment.php?'.implode("&", $string));
			exit;
		} elseif($_REQUEST['rezgo_page'] == 'book_ajax') {
			rezgo_display_booking();
		} else {
			// if we aren't displaying anything else, show the index
			return rezgo_display_index($hook_args);
		}
		
	}
	
	/*
		-----------------------------------------------------------------------
			Display functions
		-----------------------------------------------------------------------
	*/
		
	function rezgo_display_index($args=null) {
	
		global $site, $item;
		
		global $start;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
		
		// save the current search to a cookie so we can return to it
		if($_REQUEST['search'] != 'restore') {
			$site->saveSearch();
		}
		
		if($_REQUEST['search'] == 'restore' && $_COOKIE['rezgo_search']) {
			$site->sendTo($_COOKIE['rezgo_search']);
		}
		
		// wordpress is adding + to the variables for tags so we must fix it
		$_REQUEST['tags'] = str_replace("+", " ", $_REQUEST['tags']);
		
		// some code to handle the pagination
		if(!$_REQUEST['pg']) $_REQUEST['pg'] = 1;
		
		$start = ($_REQUEST['pg'] - 1) * REZGO_RESULTS_PER_PAGE;
		
		$site->setTourLimit(REZGO_RESULTS_PER_PAGE + 1, $start);
	
		$display .= '<!-- r.'.base64_encode($site->version.' '.$site->getDomain()).' -->';
		
		$display .= $site->getTemplate('header');
		
		$display .= $site->getTemplate('index');
	
		$display .= $site->getTemplate('sidebar_search');
				
		$display .= $site->getTemplate('footer');
		
		return $display;
	}
	
	
	function rezgo_display_details() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
				
		/*
			this query searches for an item based on a com id (limit 1 since we only want one response)
			then adds a $f (filter) option by uid in case there is an option id, and adds a date in case there is a date set	
		*/
		
		$item = $site->getTours('t=com&q='.$_REQUEST['com'].'&f[uid]='.$_REQUEST['option'].'&d='.$_REQUEST['date'].'&limit=1', 0);
		
		// if the item does not exist, we want to generate an error message and change the page accordingly
		if(!$item) { 
			$item->unavailable = 1;
			$item->name = 'Item Not Available'; 
		}
		
		$display = $site->getTemplate('header');
		
			$display .= $site->getTemplate('tour_details');
		
			if($item->unavailable) {
				$display .= $site->getTemplate('sidebar_search');
			} else {
				$display .= $site->getTemplate('sidebar_details');
			}
		
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_error() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
		
		$display = $site->getTemplate('header');
		
		$display .= $site->getTemplate('error');
		
		$display .= $site->getTemplate('sidebar_search');
			
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_about() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
		
		$display = $site->getTemplate('header');
		
		$display .= $site->getTemplate('about');
		
		$display .= $site->getTemplate('sidebar_search');
			
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_terms() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
		
		$display = $site->getTemplate('header');
		
		$display .= $site->getTemplate('terms');
		
		$display .= $site->getTemplate('sidebar_search');
			
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_contact() {
		
		global $site, $item;
		
		// result-> catches the contact response
		global $result;
		
		require('recaptchalib.php');
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
		
		if($_REQUEST['rezgoAction'] == 'contact') {		
			$site->cleanRequest();
			
			if($site->config('REZGO_CAPTCHA_PRIV_KEY')) {
				$resp = recaptcha_check_answer(REZGO_CAPTCHA_PRIV_KEY, $_SERVER["REMOTE_ADDR"], $_REQUEST["recaptcha_challenge_field"], $_REQUEST["recaptcha_response_field"]);
			} else { $resp->is_valid = 1; }
		
		  if (!$resp->is_valid) {
		  	$result->captchaError = 'There was an error with your captcha text, please try again.';
		  } else {
		  	$result = $site->sendContact();
		  }
		
		}
		
		$display = $site->getTemplate('header');
		
		$display .= $site->getTemplate('contact');
		
		$display .= $site->getTemplate('sidebar_search');
			
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_book() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite(secure);
		
		$display = $site->getTemplate('header');
		
		$display .= $site->getTemplate('book');
			
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_complete() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite();
		
		$display = $site->getTemplate('header');
		
		$display .= $site->getTemplate('booking_complete');
			
		$display .= $site->getTemplate('footer');
				
		return $display;
	}
	
	function rezgo_display_booking() {
		
		global $site, $item;
		
		// start a new instance of RezgoSite (in global scope since we are using it in a function)
		$site = new RezgoSite(secure);
		
		// because the booking process uses a large multidimensional array we can't easily redirect
		// it as a _GET string.  So we will run the booking process right here, and forward the script
		// to the result value so it can display correctly.
		
		if($_REQUEST['book']) {
		
			$site->cleanRequest();
		
			$result = $site->sendBooking();
			
			//echo '<pre>'.print_r($result, 1).'</pre>';
		
			if($result->status == 1) {
			
				// start a session so we can save the analytics code
				session_start();
			
				$response = $site->encode($result->trans_num);	
				
				// Set a session variable for the analytics to carry to the receipt's first view
				$_SESSION['REZGO_CONVERSION_ANALYTICS'] = $result->analytics_convert;
				
				// Add a blank script tag so that this session is detected on the receipt
				$_SESSION['REZGO_CONVERSION_ANALYTICS'] .= '<script></script>';
			
			} else {
				// this booking failed, send a status code back to the requesting page
				
				if($result->message == 'Availability Error') {
					$response = 2;
				} else if($result->message == 'Payment Declined') {
					$response = 3;
				} else if($result->message == 'Account Error') {
					// hard system error, no commit requests are allowed if there is no valid payment method
					$response = 5;
				} else {
					$response = 4;
				}
			}
		}
		
		//die('[['.$response.']]');
		
		$site->sendTo(REZGO_DIR.'/book_ajax.php?response='.$response);
	}
	
	
	/* 
		------------------------------------------------------------------------
		These functions are for preserving the custom templates during an update
		------------------------------------------------------------------------
	*/
	
	$_REQUEST['upgrade_id'] = rand(1,100).rand(1,100);	
	
	function rezgo_upgrade_copy($source, $dest) {
	
		// Check for symlinks
		if (is_link($source)) return symlink(readlink($source), $dest);
		
		// Simple copy for a file
		if (is_file($source)) return copy($source, $dest);
		
		// Make destination directory
		if (!is_dir($dest)) mkdir($dest);
			
		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') { continue; }
		
			// Recurse
			rezgo_upgrade_copy("$source/$entry", "$dest/$entry");
		}
		
		// Clean up
		$dir->close();
		return true;
	}
	
	function rezgo_upgrade_rm($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
				rezgo_upgrade_rm($file);
			else
				unlink($file);
		}
		rmdir($dir);
	}
	
	function rezgo_upgrade_backup() {
		
		// make the core backup dir so we can copy directories into it
		mkdir(sys_get_temp_dir().'/rezgo-wp-'.$_REQUEST['upgrade_id']);
		
		$dir = dir(dirname(__FILE__).'/templates/');
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..' || $entry == 'default') continue;
			
			$from = dirname(__FILE__).'/templates/'.$entry;
			$to = sys_get_temp_dir().'/rezgo-wp-'.$_REQUEST['upgrade_id'].'/'.$entry;
			rezgo_upgrade_copy($from, $to);
		}
	}

	function rezgo_upgrade_restore() {
	
		$dir = dir(sys_get_temp_dir().'/rezgo-wp-'.$_REQUEST['upgrade_id']);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') continue;
			
			$from = sys_get_temp_dir().'/rezgo-wp-'.$_REQUEST['upgrade_id'].'/'.$entry;
			$to = dirname(__FILE__).'/templates/'.$entry;
			rezgo_upgrade_copy($from, $to);
		}
		
		// remove the upgrade temp directory
		rezgo_upgrade_rm(sys_get_temp_dir().'/rezgo-wp-'.$_REQUEST['upgrade_id']);
	}
	
	add_filter('upgrader_pre_install', 'rezgo_upgrade_backup', 10, 2);
	add_filter('upgrader_post_install', 'rezgo_upgrade_restore', 10, 2);
	
?>