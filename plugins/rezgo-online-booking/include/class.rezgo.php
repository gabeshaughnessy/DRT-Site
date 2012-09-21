<?php

	/*
		This is the Rezgo parser class, it handles processing for the Rezgo XML.
		
		VERSION:
				1.6
		
		- Documentation and latest version
				http://support.rezgo.com/developers/rezgo-open-source-php-parser.html
		
		- Finding your Rezgo CID and API KEY
				http://support.rezgo.com/developers/xml-api-key
		
		- Discussion and Feedback
				http://getsatisfaction.com/rezgo/products/rezgo_rezgo_open_source_php_parser
		
		AUTHOR:
				Kevin Campbell
		
		Copyright (c) 2012, Rezgo (A Division of Sentias Software Corp.)
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

	class RezgoSite {
	
		var $version = '1.6';
	
		var $xml_path;
		
		var $contents;
		var $get;
		var $xml;
		var $secure = 'http://';
		var $obj;
		
		var $country_list;
		
		// indexes are used to split up response caches by different criteria
		var $tours_index = 0; // split up by search string
		var $company_index = 0; // split up by CID (for vendors only, suppliers use 0)

		var $currency_values;
		
		var $tour_limit;
		
		var $refid;
		var $promo_code;
		
		var $pageTitle;
		var $metaTags;
		
		// calendar specific values
		var $calendar_name;
		var $calendar_com;
		var $calendar_active;
		
		var $calendar_prev;
		var $calendar_next;
		
		var $calendar_months = array();
		var $calendar_years = array();
		
		// xml result caches improve performance by not hitting the gateway multiple times
		// searches that have differing args sort them into arrays with the index variables above
		var $headers_response;
		var $company_response;
		var $about_response;
		var $tags_response;		
		var $search_items_response;
		var $tour_availability_response;
		var $search_bookings_response;
		var $search_items_total;
		var $commit_response;
		var $contact_response;
		
		var $tour_prices;
		var $tour_forms;
		
		var $all_required;
		
		// debug and error stacks
		var $error_stack;
		var $debug_stack;
		
		// ------------------------------------------------------------------------------
		// if the class was called with an argument then we use that as the object name
		// this allows us to load the object globalls for included templates.
		// ------------------------------------------------------------------------------
		function __construct($secure=null) {
			if(!$this->config('REZGO_SKIP_BUFFER')) ob_start();
		
			// check the config file to make sure it's loaded
			if(!$this->config('REZGO_CID')) $this->error('REZGO_CID definition missing, check config file', 1);
		
			// assemble XML address
			$this->xml_path = REZGO_XML.'/xml?transcode='.REZGO_CID.'&key='.REZGO_API_KEY;
			
			// assemble template and url path
			$this->path = REZGO_DIR.'/templates/'.REZGO_TEMPLATE;
			$this->base = REZGO_URL_BASE;
			
			if(!defined(REZGO_PATH)) define("REZGO_PATH", $this->path);
			
			// it's possible to define the document root manually if there is an issue with the _SERVER variable
			if(!defined(REZGO_DOCUMENT_ROOT)) define("REZGO_DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);
			
			// set the secure mode for this particular page
			$this->setSecure($secure);
			
			// perform some variable filtering
			if($_REQUEST['start_date']) {
				if(strtotime($_REQUEST['start_date']) == 0) unset($_REQUEST['start_date']);
			}
			if($_REQUEST['end_date']) {
				if(strtotime($_REQUEST['end_date']) == 0) unset($_REQUEST['end_date']);
			}
		
		
			// handle the refID if one is set
			if($_REQUEST['refid'] || $_REQUEST['ttl'] || $_COOKIE['rezgo_refid_val'] || $_SESSION['rezgo_refid_val']) {
				if($_REQUEST['refid'] || $_REQUEST['ttl']) {
					$new_header = $_SERVER['REQUEST_URI'];
					
					// remove the refid information wherever it is
					$new_header = preg_replace("/&?refid=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					$new_header = preg_replace("/&?ttl=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					if($new_header{(strlen($new_header)-1)} == '?') $new_header{(strlen($new_header)-1)} = '';
					
					$refid = $_REQUEST['refid'];
					$ttl = ($_REQUEST['ttl']) ? $_REQUEST['ttl'] : 7200;
				} 
				elseif($_SESSION['rezgo_refid_val']) { $refid = $_SESSION['rezgo_refid_val']; $ttl = $_SESSION['rezgo_refid_ttl']; }
				elseif($_COOKIE['rezgo_refid_val']) { $refid = $_COOKIE['rezgo_refid_val']; $ttl = $_COOKIE['rezgo_refid_ttl']; }
				
				setcookie("rezgo_refid_val", $refid, time() + $ttl, '/', $_SERVER['SERVER_NAME']);
				setcookie("rezgo_refid_ttl", $ttl, time() + $ttl, '/', $_SERVER['SERVER_NAME']);
					
				// we need to set the session here before we header the user off or the old session will override the new refid each time
				if($ttl > 0) {
					$_SESSION['rezgo_refid_val'] = $refid;
					$_SESSION['rezgo_refid_ttl'] = $ttl;
				} else {
					unset($_SESSION['rezgo_refid_val']);
					unset($_SESSION['rezgo_refid_ttl']);
				}
				
				if(isset($new_header)) $this->sendTo((($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$new_header);
			}		
			
			// handle the promo code if one is set
			if(isset($_REQUEST['promo']) || $_COOKIE['rezgo_promo'] || $_SESSION['rezgo_promo']) {
				$ttl = 1209600; // two weeks is the default time-to-live for the promo cookie
				
				if(isset($_REQUEST['promo']) && !$_REQUEST['promo']) {
					$_REQUEST['promo'] = $_COOKIE['rezgo_promo']; // force a request
					$ttl = -1; // set the ttl to -1, removing the promo code
				}
				
				if($_REQUEST['promo']) {
					$new_header = $_SERVER['REQUEST_URI'];
					
					// remove the promo information wherever it is
					$new_header = preg_replace("/&?promo=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					if($new_header{(strlen($new_header)-1)} == '?') $new_header{(strlen($new_header)-1)} = '';
					
					$promo = $_REQUEST['promo'];
				} 
				elseif($_SESSION['rezgo_promo']) { $promo = $_SESSION['rezgo_promo']; }
				elseif($_COOKIE['rezgo_promo']) { $promo = $_COOKIE['rezgo_promo']; }
				
				setcookie("rezgo_promo", $promo, time() + $ttl, '/', $_SERVER['SERVER_NAME']);
				
				// we need to set the session here before we header the user off or the old session will override the new refid each time
				if($ttl > 0) {
					$_SESSION['rezgo_promo'] = $promo;
				} else {
					unset($_SESSION['rezgo_promo']);
				}
				
				if(isset($new_header)) $this->sendTo((($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$new_header);				
			}
			
			// registering global events, these can be manually changed later with the same methods
			$this->setRefId($_COOKIE['rezgo_refid_val']);
			$this->setPromoCode($_COOKIE['rezgo_promo']);
		}
		
		function config($arg) {
			if(!defined($arg)) {
				return 0;
			} else {
				if(constant($arg) == '') { return 0; }
				else { return constant($arg); }
			}
		}
		
		function error($message, $exit=null) {
			$stack = debug_backtrace();
			$stack = $stack[count($stack)-1]; // get the origin point
			
			$error = '<b>[Rezgo error:</b> '.$message.' for <b>'.$stack['class'].'::'.$stack['function'].'</b> in <b>'.$stack['file'].'</b> on line <b>'.$stack['line'].']</b>';
			if($this->config('REZGO_FIREBUG_ERRORS')) echo '<script>if(window.console != undefined) { console.error("'.strip_tags($error).'"); }</script>';
			if($this->config('REZGO_DISPLAY_ERRORS')) echo ($this->config('REZGO_DIE_ON_ERROR')) ? die($error) : $error;
		
			if($exit) exit;
		}
		
		function debug($message, $i=null) {
			$stack = debug_backtrace();
			$stack = $stack[count($stack)-1]; // get the origin point
			
			$message = '[XML backtrace: '.$message.' for '.$stack['class'].'::'.$stack['function'].' in '.$stack['file'].' on line '.$stack['line'].']';
			if($this->config('REZGO_FIREBUG_XML')) {
				if($i == 'commit' && $this->config('REZGO_SWITCH_COMMIT')) { if($this->config('REZGO_STOP_COMMIT')) { echo 'STOP::'.$message.'<br><br>'; } }
				else { echo '<script>if(window.console != undefined) { console.info("'.addslashes($message).'"); }</script>'; }
			}
			if($this->config('REZGO_DISPLAY_XML'))  {
				if($i == 'commit' && $this->config('REZGO_SWITCH_COMMIT')) { die('STOP::'.$message); }
				else { echo '<textarea rows="2" cols="25">'.$message.'</textarea>'; }
			}
		}
		
		function secureURL() {
			if($this->config('REZGO_FORWARD_SECURE')) {
				// forward is set, so we want to direct them to their .rezgo.com domain	
				$secure_url = $this->getDomain().'.rezgo.com';
			} else {
				// forward them to this page or our external URL
				if($this->config('REZGO_SECURE_URL')) {
					$secure_url = $this->config('REZGO_SECURE_URL');
				} else {
					$secure_url = $_SERVER["HTTP_HOST"];
				}	
			}
			return $secure_url;
		}
		
		function isVendor() {
			$res = (strpos(REZGO_CID, 'p') !== false) ? 1 : 0;
			return $res;
		}
		
		// clean slashes from the _REQUEST superglobal if escape strings is set in php
		function cleanRequest() {
			array_walk_recursive($_REQUEST, create_function('&$val', '$val = stripslashes($val);'));
		}

		
		// ------------------------------------------------------------------------------
		// read a tour item object into the cache so we can format it later
		// ------------------------------------------------------------------------------
		function readItem(&$obj) {
			$this->obj = $obj;
		}
		
		function getItem() {
			$obj = $this->obj;
			if(!$obj) $this->error('no object found, expecting read object or object argument');
			return $obj;
		}
		
		// ------------------------------------------------------------------------------
		// format a currency response to the standards of this company
		// ------------------------------------------------------------------------------
		function formatCurrency($num, &$obj=null, $hide=null) {
			if(!$obj) $obj = $this->getItem();
			return (($hide) ? '' : $obj->currency_symbol).number_format((float)$num, (int)$obj->currency_decimals, '.', (string)$obj->currency_separator);			
		}
		
		// ------------------------------------------------------------------------------
		// Check if an object has any content in it, for template if statements
		// ------------------------------------------------------------------------------
		function exists($string) {
			$str = (string) $string;
			return (strlen(trim($str)) == 0) ? 0 : 1;	
		}
		
		// ------------------------------------------------------------------------------
		// Direct a user to a different page
		// ------------------------------------------------------------------------------
		function sendTo($path) {
			@header("location: ".$path);
			exit;	
		}
		
		// ------------------------------------------------------------------------------
		// Format a string for passing in a URL
		// ------------------------------------------------------------------------------
		function seoEncode($string) {
			$str = trim($string);
			$str = str_replace(" ", "-", $str);
			$str = preg_replace('/[^A-Za-z0-9\-]/','', $str);
			$str = preg_replace('/[\-]+/','-', $str);
			return strtolower($str);	
		}
		
		// ------------------------------------------------------------------------------
		// Save tour search info
		// ------------------------------------------------------------------------------
		function saveSearch() {
			setcookie("rezgo_search", $_SERVER['REQUEST_URI'], strtotime('now +1 week'), '/', $_SERVER['SERVER_NAME']);
		}
		
		// ------------------------------------------------------------------------------
		// Toggles secure (https) or insecure (http) mode for XML queries. Secure mode
		// is required when making all commit or modification requests.
		// ------------------------------------------------------------------------------		
		function setSecureXML($set) {
			if($set) { $this->secure = 'https://'; }
			else { $this->secure = 'http://'; }
		}
		
		function setSecure($set) {
			$this->setSecureXML($set);
			
			if($set) { 
				if($_SERVER['HTTPS'] != 'on') {
					if($this->config('REZGO_FORWARD_SECURE')) {
						// since we are directing to a white label address, clean the request up
						$request = '/book?'.$_SERVER['QUERY_STRING'];
					} else {
						$request = $_SERVER['REQUEST_URI'];
					}
					$this->sendTo($this->secure.$this->secureURL().$request); 
				} 
			} else { 
				// switch to non-https on the current domain
				if($_SERVER['HTTPS'] == 'on') { 			
					$this->sendTo($this->secure.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				} 
			}
		}
		
		// ------------------------------------------------------------------------------
		// fetch a requested template from the templates directory and load it into a
		// variable for display.  If fullpath is set, fetch it from that location instead.
		// ------------------------------------------------------------------------------
		function getTemplate($req, $fullpath=false) {
			reset($GLOBALS);
			foreach($GLOBALS as $key => $val) {
				if(($key != strstr($key,"HTTP_")) && ($key != strstr($key, "_")) && ($key != 'GLOBALS')) {
					global $$key;
				} 
			}
			
			// wordpress document root includes the install path so we change the path for wordpress installs			
			$path = ($this->config('REZGO_USE_ABSOLUTE_PATH')) ? REZGO_DOCUMENT_ROOT : REZGO_DOCUMENT_ROOT.REZGO_DIR;
			$path .= '/templates/'.REZGO_TEMPLATE.'/';
			
			$ext = explode(".", $req);
			$ext = (!$ext[1]) ? '.php' : '';
			
			$filename = ($fullpath) ? $req : $path.$req.$ext;
			
			if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
    	} else {
    		$this->error('"'.$req.'" file not found'.(($fullpath) ? '' : ' in "'.$path.'"'));
    	}
    	return $contents;
		}
		
		// ------------------------------------------------------------------------------
		// general request functions for country lists
		// ------------------------------------------------------------------------------
		function countryName($iso) {
			$path = ($this->config('REZGO_USE_ABSOLUTE_PATH')) ? REZGO_DOCUMENT_ROOT : REZGO_DOCUMENT_ROOT.REZGO_DIR;
			
			if(!$this->country_list) {
				if($this->config('REZGO_COUNTRY_PATH')) {
					include(REZGO_COUNTRY_PATH);
				} else {
					include($path.'/include/countries_list.php');	
				}
				$this->country_list = $countries_list;
			}
			$iso = (string)$iso;
			return ($this->country_list[$iso]) ? ucwords($this->country_list[$iso]) : $iso; 
		}
		
		function getRegionList($node=null) {
			$path = ($this->config('REZGO_USE_ABSOLUTE_PATH')) ? REZGO_DOCUMENT_ROOT : REZGO_DOCUMENT_ROOT.REZGO_DIR;
		
			if($this->config('REZGO_COUNTRY_PATH')) {
				include(REZGO_COUNTRY_PATH);
			} else {
				include($path.'/include/countries_list.php');	
			}
			
			if($node) {
				$n = $node.'_state_list';
				if($$n) {
					return $$n;
				} else {
					$this->error('"'.$node.'" region node not found');
				}
			} else {
				return $countries_list;
			}
		}
		
		// ------------------------------------------------------------------------------
		// encode scripts for trans numbers
		// ------------------------------------------------------------------------------
		function encode($enc_text, $iv_len = 16) {
			$var = base64_encode($enc_text);
			return str_replace("=", "", base64_encode($var.'|'.$var));
		}
	
		function decode($enc_text, $iv_len = 16) {
			$var = base64_decode($enc_text.'=');
			$var = explode("|", $var);
			return base64_decode($var[0]);
		}
		
		// ------------------------------------------------------------------------------
		// Make an XML request to Rezgo. $i supports all arguments that the XML gateway
		// supports for pre-generated queries, or a full query can be passed directly
		// ------------------------------------------------------------------------------
		function getPage($url) {
			include('fetch.rezgo.php');
			return $result;
		}
		
		function fetchXML($i) {
			$file = $this->getPage($i);
			
			// sanity check for response, ensure the XML response is valid
			if(strpos($file, '<!0!>') !== false) return false;
			
			// attempt to filter out any junk data
			$file = strstr($file, '<response');
			
			$this->get = utf8_encode($file);
			
			$res = $this->xml = simplexml_load_string($this->get);
			
			if(!$res && strpos($i, 'i=headers') === false) {
				// there has been a fatal error with the XML, report the error to the gateway
				$this->getPage($i.'&action=report');
				
				// send the user to the fatal error page
				if(REZGO_FATAL_ERROR_PAGE) {
					$this->sendTo(REZGO_FATAL_ERROR_PAGE);
				}
			}
		
			return $res;
		}
		
		function XMLRequest($i, $arguments=null) {
		
			if($i == 'headers') {
				if(!$this->headers_response) {
					$query = $this->secure.$this->xml_path.'&i=headers';
					
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->headers_response = $xml;	
					}
				}
				// if a doctype request is sent then echo the doctype immediately
				// this way we won't have an issue if we are outputting XML logs
				if($arguments == 'doctype' && $xml) echo (string) $xml->doctype;
			}
			// !i=company
			if($i == 'company') {
				if(!$this->company_response[$this->company_index]) {	
					$arg = ($this->company_index) ? '&q='.$this->company_index : '';	
					$query = $this->secure.$this->xml_path.'&i=company'.$arg;
				
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->company_response[$this->company_index] = $xml;	
					}
				}
			}
			// !i=about
			if($i == 'about') {
				if(!$this->about_response) {
					$query = $this->secure.$this->xml_path.'&i=about';
				
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->about_response = $xml;	
					}
				}
			}
			// !i=tags
			if($i == 'tags') {
				if(!$this->tags_response) {
					$query = $this->secure.$this->xml_path.'&i=tags';
				
					$xml = $this->fetchXML($query);
					
					if($xml) {
						if($xml->total > 1) {
							foreach($xml->tag as $v) {
								$this->tags_response[] = $v;
							}
						} else {
							$this->tags_response[] = $xml->tag;
						}	
					}
					
				}
			}	
			// !i=search_items
			if($i == 'search_items') {		
				if(!$this->search_items_response[$this->tours_index]) {
					$query = $this->secure.$this->xml_path.'&i=search_items&'.$this->tours_index;
				
					$xml = $this->fetchXML($query);
					
					$this->search_items_total = $xml->total;
					
					$c = 0;
					if($xml && $xml->total != 0) {
						if($xml->total > 1) {
							foreach($xml->item as $v) {
								$this->search_items_response[$this->tours_index][$c] = $v;
								$this->search_items_response[$this->tours_index][$c++]->index = $this->tours_index;
							}
						} else {
							$this->search_items_response[$this->tours_index][$c] = $xml->item;
							$this->search_items_response[$this->tours_index][$c++]->index = $this->tours_index;
						}	
					}
				
				}
			}
			// !i=search_bookings
			if($i == 'search_bookings') {
				if(!$this->search_bookings_response[$this->bookings_index]) {
					$query = $this->secure.$this->xml_path.'&i=search_bookings&'.$this->bookings_index;
				
					$xml = $this->fetchXML($query);
					
					$c = 0;
					if($xml && $xml->total != 0) {
						if($xml->total > 1) {
							foreach($xml->booking as $v) {
								$this->search_bookings_response[$this->bookings_index][$c] = $v;
								$this->search_bookings_response[$this->bookings_index][$c++]->index = $this->bookings_index;
							}
						} else {
							$this->search_bookings_response[$this->bookings_index][$c] = $xml->booking;
							$this->search_bookings_response[$this->bookings_index][$c++]->index = $this->bookings_index;
						}	
					}
				
				}
			}
			// !i=commit
			if($i == 'commit') {
				$query = 'https://'.$this->xml_path.'&i=commit&'.$arguments;
				
				$xml = $this->fetchXML($query);
				
				if($xml) {
					foreach($xml as $k => $v) {
						$this->commit_response->$k = trim((string)$v);	
					}
				}
			}
			// !i=contact
			if($i == 'contact') {
				$query = 'https://'.$this->xml_path.'&i=contact&'.$arguments;
			
				$xml = $this->fetchXML($query);
				
				if($xml) {
					foreach($xml as $k => $v) {
						$this->contact_response->$k = trim((string)$v);	
					}
				}
			}
			
			if(REZGO_TRACE_XML) {
				if(!$query && REZGO_INCLUDE_CACHE_XML) $query = 'called cached response';
				if($query) {
					$message = $i.' ('.$query.')';
					$this->debug($message, $i); // pass the $i as well so we can freeze on commit
				}
			}
		}
		
		// ------------------------------------------------------------------------------
		// Set specific data
		// ------------------------------------------------------------------------------
		function setTourLimit($limit, $start=null) {
			$str = ($start) ? $start.','.$limit : $limit;
			$this->tour_limit = '&limit='.$str;
		}
		
		function setRefId($id) {
			$this->refid = $id;
		}
		
		function setPromoCode($id) {
			$this->promo_code = urlencode($id);
		}
		
		function setPageTitle($str) {
			$this->pageTitle = $str;
		}
		
		function setMetaTags($str) {
			$this->metaTags = $str;
		}
		
		// ------------------------------------------------------------------------------
		// Fetch specific data
		// ------------------------------------------------------------------------------
		function getSiteStatus() {
			$this->XMLRequest(headers);
			return $this->headers_response->site_status;
		}
		
		function getDoctype() {
			// the doctype is outputted automatically by this argument
			$this->XMLRequest(headers, 'doctype');
		}
		
		function getHeader() {
			$this->XMLRequest(headers);
			$header = $this->headers_response->header;
			// handle the tags in the template
			return $this->tag_parse($header);
		}
		
		function getFooter() {
			$this->XMLRequest(headers);
			return $this->headers_response->footer;
		}
		
		function getStyles() {
			$this->XMLRequest(headers);
			return $this->headers_response->styles;
		}
		
		function getAnalytics() {
			$this->XMLRequest(headers);
			return $this->headers_response->analytics_general;
		}
		
		function getAnalyticsConversion() {
			$this->XMLRequest(headers);
			return $this->headers_response->analytics_convert;
		}
		
		function getTriggerState() {
			$this->XMLRequest(headers);
			return $this->headers_response->triggers;
		}
		
		function getBookNow() {
			$this->XMLRequest(headers);
			return $this->headers_response->book_now;
		}
		
		function getTwitterName() {
			$this->XMLRequest(headers);
			return $this->headers_response->social->twitter_name;
		}
		
		function getPaymentMethods($val=null, $a=null) {
			$this->company_index = ($a) ? (string) $a : 0; // handle multiple company requests for vendor
			$this->XMLRequest(company);
			
			if($this->company_response[$this->company_index]->payment->method[0]) {
				foreach($this->company_response[$this->company_index]->payment->method as $v) {
					$ret[] = array('name' => (string)$v, 'add' => (string)$v->attributes()->add);
					if($val && $val == $v) { return 1; }	
				}
			} else {
				$ret[] = array(
					'name' => (string)$this->company_response[$this->company_index]->payment->method, 
					'add' => (string)$this->company_response[$this->company_index]->payment->method->attributes()->add
				);
				if($val && $val == (string)$this->company_response[$this->company_index]->payment->method) { return 1; }				
			}
			
			// if we made it this far with a $val set, return false
			if($val) { return false; }
			else { return $ret; }
		}
		
		function getPaymentCards($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			$split = explode(",", $this->company_response[$this->company_index]->cards);
			foreach((array) $split as $v) {
				if(trim($v)) $ret[] = strtolower(trim($v));
			}
			return $ret;
		}
		
		function getCVV($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return (int) $this->company_response[$this->company_index]->get_cvv;
		}
		
		function getGateway($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return (int) $this->company_response[$this->company_index]->using_gateway;
		}
		
		function getDomain($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->domain;
		}
		
		function getTerms($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->terms;
		}
		
		function getCompanyName($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->company_name;
		}
		
		function getCompanyCountry($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->country;
		}
		
		function getCompanyPaypal($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->paypal_email;
		}
		
		function getCompanyDetails($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index];
		}
		
		function getAboutUs() {
			$this->XMLRequest(about);
			return (string)$this->about_response->about;
		}
		
		function getIntro() {
			$this->XMLRequest(about);
			return (string)$this->about_response->intro;
		}
		
		// get a list of calendar data		
		function getCalendar($item_id, $date=null) {
			if(!$date) { // no date? set a default date (today)
				$date = $default_date = strtotime(date("Y-m-15"));
				$available = ',available'; // get first available date from month XML
			} else {
				$date = date("Y-m-15", strtotime($date));
				$date = strtotime($date);
			}
			
			$promo = ($this->promo_code) ? '&trigger_code='.$this->promo_code : '';	
			
			$query = $this->secure.$this->xml_path.'&i=month&q='.$item_id.'&d='.date("Y-m-d", $date).'&a=group'.$available.$promo;	
			
			$xml = $this->fetchXML($query);
			
			// update the date with the one provided from the XML response
			// this is done in case we hopped ahead with the XML search (a=available)
			$date = $xml->year.'-'.$xml->month.'-15';
			
			$year = date("Y", strtotime($date));
			$month = date("m", strtotime($date));
			
			$date = $base_date = date("Y-m-15", strtotime($date));
			$date = strtotime($date);
				
			$next_partial = date("Y-m", strtotime($base_date.' +1 month'));
			$prev_partial = date("Y-m", strtotime($base_date.' -1 month'));
				
			$this->calendar_next = $next_date = date("Y-m-d", strtotime($base_date.' +1 month'));
			$this->calendar_prev = $prev_date = date("Y-m-d", strtotime($base_date.' -1 month'));
			
			
			$this->calendar_name = (string) $xml->name;
			$this->calendar_com = (string) $xml->com;
			$this->calendar_active = (int) $xml->active;
			
			$days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
			
			$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"); 
			
			$start_day = 1;
			$end_day = date("t", $date);
			
			$start_dow = date("D", strtotime(date("Y-m-1", $date)));
			
			$n = 0;
			foreach($months as $k => $v) {
				$this->calendar_months[$n]->selected = ($v == date("F", $date)) ? 'selected' : '';
				$this->calendar_months[$n]->value = $year.'-'.$v.'-15';
				$this->calendar_months[$n]->label = $k;
				$n++;
			}
		
			for($y=date("Y", strtotime(date("Y").' -1 year')); $y<=date("Y", strtotime(date("Y").' +4 years')); $y++) {
				$this->calendar_years[$n]->selected = ($y == date("Y", $date)) ? 'selected' : '';
				$this->calendar_years[$n]->value = $y.'-'.$month.'-15';
				$this->calendar_years[$n]->label = $y;
				$n++;
			}
						
			$c = 0;
			foreach($days as $v) {
				$c++;
				if($start_dow == $v) $start_offset = $c;
			}
			
			if($start_offset) {
				// this will display the lead-up days from last month
				$last_display = date("t", strtotime($prev_date)) - ($start_offset-2);
				
				for($d=1; $d<$start_offset; $d++) {
					$obj->day = $last_display;
					$this->calendar_days[] = $obj;				
					$last_display++;
					unset($obj);
				}	
			}
			
			$w = $start_offset;
			for($d=1; $d<=$end_day; $d++) {		
				$xd = $d - 1;			
				$obj->type = 1;
				
				if($xml->day->$xd) {
					$obj->cond = $cond = (string) $xml->day->$xd->attributes()->condition;
					if($xml->day->$xd->item[0]) {
						// we want to convert the attribute to something easier to use in the template
						$n=0;
						foreach($xml->day->$xd->item as $i) {
							if($i) {
							$obj->items[$n]->uid = $i->uid;
							$obj->items[$n]->name = $i->name;
							$obj->items[$n]->availability = $i->attributes()->value;
							$n++;
							}
						}
					} else {
						if($xml->day-$xd->item) {
						$obj->items[0]->uid = $xml->day->$xd->item->uid;
						$obj->items[0]->name = $xml->day->$xd->item->name;
						$obj->items[0]->availability = $xml->day->$xd->item->attributes()->value;
						}
					}
				}
				
				$obj->date = strtotime($year.'-'.$month.'-'.$d);
				
				$obj->day = $d;		
				$this->calendar_days[] = $obj;			
				unset($obj);
				
				if($w == 7) { $w = 1; } else { $w++; }
			}
				
			if($w != 8 && $w != 1) {
				$d = 0;
				// this will display the lead-out days for next month
				while($w != 8) {
					$d++;
					$w++;
					
					$obj->day = $d;			
					$this->calendar_days[] = $obj;
					unset($obj);
				}
			}
		}
		
		function getCalendarActive() {
			return $this->calendar_active;
		}
		
		function getCalendarPrev() {
			return $this->calendar_prev;
		}
		
		function getCalendarNext() {
			return $this->calendar_next;
		}
		
		function getCalendarMonths() {
			return $this->calendar_months;
		}
		
		function getCalendarYears() {
			return $this->calendar_years;
		}
		
		function getCalendarDays($day=null) {
			if($day) {
				foreach($this->calendar_days as $v) {
					if((int)$v->day == $day) {
						$day_response = $v; break;
					}
				}
				
				return (object) $day_response;
			} else {
				return $this->calendar_days;
			}
		}
		
		function getCalendarId() {
			return $this->calendar_com;
		}
		
		function getCalendarName() {
			return $this->calendar_name;
		}
		
		
		// get a list of tour data
		function getTours($a=null, $node=null) {
			// generate the search string
			// if no search is specified, find searched items (grouped)
			if(!$a || $a == $_REQUEST) {
				if($_REQUEST['search_for']) $str .= ($_REQUEST['search_in']) ? '&t='.urlencode($_REQUEST['search_in']) : '&t=smart';	
				if($_REQUEST['search_for']) $str .= '&q='.urlencode(stripslashes($_REQUEST['search_for']));
				if($_REQUEST['tags']) $str .= '&f[tags]=*'.urlencode($_REQUEST['tags']).'*';
				
				if($_REQUEST['cid']) $str .= '&f[cid]='.urlencode($_REQUEST['cid']); // vendor only
				
				// details pages
				if($_REQUEST['com']) $str .= '&t=com&q='.urlencode($_REQUEST['com']);
				if($_REQUEST['uid']) $str .= '&t=uid&q='.urlencode($_REQUEST['uid']);
				if($_REQUEST['option']) $str .= '&t=uid&q='.urlencode($_REQUEST['option']);
				if($_REQUEST['date']) $str .= '&d='.urlencode($_REQUEST['date']);
				
				$a = ($a) ? $a : 'a=group'.$str;
			}
			
			$promo = ($this->promo_code) ? '&trigger_code='.$this->promo_code : '';		
			
			// attach the search as an index including the limit value and promo code
			$this->tours_index = $a.$promo.$this->tour_limit;
			
			$this->XMLRequest(search_items);
			
			$return = ($node === null) ? (array) $this->search_items_response[$this->tours_index] : $this->search_items_response[$this->tours_index][$node];
			
			return $return;
		}
		
		function getTourAvailability(&$obj=null, $start=null, $end=null) {
			if(!$obj) $obj = $this->getItem();
			
			// check the object, create a list of com ids
			// search the XML with those ids and the date search
			// create a list of dates and relevant options to return
		
			$loop = (string) $obj->index;
			
			$d[] = ($start) ? date("Y-M-d", strtotime($start)) : date("Y-M-d", strtotime($_REQUEST[start_date]));
			$d[] = ($end) ? date("Y-M-d", strtotime($end)) : date("Y-M-d", strtotime($_REQUEST[end_date]));
			if($d) { $d = implode(',', $d); } else { return false; }
			
			if(!$this->tour_availability_response[$loop])  {
				if($this->search_items_response[$loop]) {
					foreach((array)$this->search_items_response[$loop] as $v) {
						$uids[] = (string)$v->com;
					}
					
					$this->tours_index = 't=com&q='.implode(',', array_unique($uids)).'&d='.$d;
					
					$this->XMLRequest(search_items);
					
					$c=0;
					foreach((array)$this->search_items_response[$this->tours_index] as $i) {
						if($i->date) {
							if($i->date[0]) {
								foreach($i->date as $d) {
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->id = $c;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c]->name = (string)$i->time;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c]->availability = (string)$d->availability;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c]->hide_availability = (string)$d->hide_availability;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c++]->uid = (string)$i->uid;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->date = strtotime((string)$d->attributes()->value);
								}
							} else {
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->id = $c;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c]->name = (string)$i->time;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c]->availability = (string)$i->date->availability;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c]->hide_availability = (string)$i->date->hide_availability;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c++]->uid = (string)$i->uid;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->date = strtotime((string)$i->date->attributes()->value);
							}
							
							// sort by date so the earlier dates always appear first, the xml will return them in that order
							// but if the first item found has a later date than a subsequent item, the dates will be out of order
							ksort($res[(string)$i->com]);
						}
					}
					$this->tour_availability_response[$loop] = $res;	
				}
			}
			
			return (array) $this->tour_availability_response[$loop][(string)$obj->com];
		}
		
		function getTourPrices(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$com = (string) $obj->com;
			
			if(!$this->tour_prices[$com]) {	
				$c=0;
				$all_required = 1;
				if($this->exists($obj->date->price_adult)) {
					$ret[$c]->name = 'adult';
					$ret[$c]->label = (string) $obj->adult_label;
					$ret[$c]->required = (string) $obj->adult_required;
					if(!$ret[$c]->required) $all_required = 0;
					($obj->date->base_prices->price_adult) ? $ret[$c]->base = (string) $obj->date->base_prices->price_adult : 0;
					$ret[$c]->price = (string) $obj->date->price_adult;
					$ret[$c++]->total = (string) $obj->total_adult;
				}
				if($this->exists($obj->date->price_child)) {
					$ret[$c]->name = 'child';
					$ret[$c]->label = (string) $obj->child_label;
					$ret[$c]->required = (string) $obj->child_required;
					if(!$ret[$c]->required) $all_required = 0;
					($obj->date->base_prices->price_child) ? $ret[$c]->base = (string) $obj->date->base_prices->price_child : 0;
					$ret[$c]->price = (string) $obj->date->price_child;
					$ret[$c++]->total = (string) $obj->total_child;
				}
				if($this->exists($obj->date->price_senior)) {
					$ret[$c]->name = 'senior';
					$ret[$c]->label = (string) $obj->senior_label;
					$ret[$c]->required = (string) $obj->senior_required;
					if(!$ret[$c]->required) $all_required = 0;
					($obj->date->base_prices->price_senior) ? $ret[$c]->base = (string) $obj->date->base_prices->price_senior : 0;
					$ret[$c]->price = (string) $obj->date->price_senior;
					$ret[$c++]->total = (string) $obj->total_senior;
				}	
				
				for($i=4; $i<=9; $i++) {
					$val = 'price'.$i;
					if($this->exists($obj->date->$val)) {
						$ret[$c]->name = 'price'.$i;
						$val = 'price'.$i.'_label';
						$ret[$c]->label = (string) $obj->$val;
						$val = 'price'.$i.'_required';
						$ret[$c]->required = (string) $obj->$val;
						if(!$ret[$c]->required) $all_required = 0;
						$val = 'price'.$i;
						($obj->date->base_prices->$val) ? $ret[$c]->base = (string) $obj->date->base_prices->$val : 0;
						$ret[$c]->price = (string) $obj->date->$val;
						$val = 'total_price'.$i;
						$ret[$c++]->total = (string) $obj->$val;
					}
				}
				
				$this->all_required = $all_required;
				
				$this->tour_prices[$com] = $ret;
			}
			
			return (array) $this->tour_prices[$com];
		}
		
		function getTourRequired() {
			return ($this->all_required) ? 0 : 1;
		}
		
		function getTourPriceNum(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			for($n=1; $n<=$_REQUEST[$obj->name.'_num']; $n++) {
				$ret[] = $n;
			}
			return (array) $ret;
		}
		
		function getTourTags(&$obj=null) {
			if(!$obj) $obj = $this->getItem();			
			if($this->exists($obj->tags)) {
				$split = explode(',', $obj->tags);
				foreach((array) $split as $v) {
					$ret[] = trim($v);
				}
			}
			return (array) $ret;
		}
		
		function getTourLocations(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$c = 0;
			if($obj->additional_locations->location) {
				if(!$obj->additional_locations->location[0]) {
					$ret[$c]->country = $obj->additional_locations->location->loc_country;
					$ret[$c]->state = $obj->additional_locations->location->loc_state;
					$ret[$c++]->city = $obj->additional_locations->location->loc_city;
				} else {
					foreach($obj->additional_locations->location as $v) {
						$ret[$c]->country = $v->loc_country;
						$ret[$c]->state = $v->loc_state;
						$ret[$c++]->city = $v->loc_city;
					}
				}
			}
			return (array) $ret;
		}
		
		function getTourMedia(&$obj=null) {
			if(!$obj) $obj = $this->getItem();			
			$c = 0;
			if($obj->image_gallery->attributes()->value != 0) {
				if($obj->image_gallery->attributes()->value == 1) {
					$ret[$c]->image = $obj->image_gallery->image->attributes()->value;
					$ret[$c]->path = $obj->image_gallery->image->attributes()->value;
					$ret[$c]->caption = $obj->image_gallery->image->caption;
					$ret[$c++]->type = 'image';
				} else {
					foreach($obj->image_gallery->image as $v) {
						$ret[$c]->image = $v->attributes()->value;
						$ret[$c]->path = $v->attributes()->value;
						$ret[$c]->caption = $v->caption;
						$ret[$c++]->type = 'image';
					}
				}
			}

			if($obj->video_gallery->attributes()->value != 0) {			
				if($obj->video_gallery->attributes()->value == 1) {
					$ret[$c]->image = $obj->video_gallery->video->image;
					$ret[$c]->path = $obj->video_gallery->video->attributes()->value;
					$ret[$c]->caption = $obj->video_gallery->video->caption;
					$ret[$c++]->type = 'video';
				} else {
					foreach($obj->video_gallery->video as $v) {
						$ret[$c]->image = $v->image;
						$ret[$c]->path = $v->attributes()->value;
						$ret[$c]->caption = $v->caption;
						$ret[$c++]->type = 'video';
					}
				}
			}
			
			return (array) $ret;		
		}
		
		function getTourForms($type, &$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$com = (string) $obj->com;
			
			$type = strtolower($type);
			if($type != 'primary' && $type != 'group') $this->error('unknown type specified, expecting "primary" or "group"');

			if(!$this->tour_forms[$com]) {
				if($obj->forms) {
					if($obj->forms->form[0]) {
						foreach($obj->forms->form as $f) {
							$res[(string)$f->show][(string)$f->id]->id = (string)$f->id;
							$res[(string)$f->show][(string)$f->id]->type = (string)$f->type;
							$res[(string)$f->show][(string)$f->id]->label = (string)$f->label;
							$res[(string)$f->show][(string)$f->id]->require = (string)$f->require;
							$res[(string)$f->show][(string)$f->id]->value = (string)$f->value;
							$res[(string)$f->show][(string)$f->id]->comments = (string)$f->comments;
							
							if((string)$f->price) {
								if(strpos((string)$f->price, '-') !== false) { 
									$res[(string)$f->show][(string)$f->id]->price = str_replace("-", "", (string)$f->price);
									$res[(string)$f->show][(string)$f->id]->price_mod = '-';
								} else {
									$res[(string)$f->show][(string)$f->id]->price = str_replace("+", "", (string)$f->price);
									$res[(string)$f->show][(string)$f->id]->price_mod = '+';
								}
							}
							
							if((string)$f->options) {
								$opt = explode(",", (string)$f->options);
								foreach((array)$opt as $v) {
									$res[(string)$f->show][(string)$f->id]->options[] = $v;
								}
							}
						}				
					} else {
						$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->id = (string)$obj->forms->form->id;
						$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->type = (string)$obj->forms->form->type;
						$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->label = (string)$obj->forms->form->label;
						$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->require = (string)$obj->forms->form->require;
						$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->value = (string)$obj->forms->form->value;
						$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->comments = (string)$obj->forms->form->comments;
						
						if((string)$obj->forms->form->price) {
							if(strpos((string)$obj->forms->form->price, '-') !== false) { 
								$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->price = str_replace("-", "", (string)$obj->forms->form->price);
								$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->price = '-';
							} else {
								$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->price = str_replace("+", "", (string)$obj->forms->form->price);
								$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->price = '+';
							}
						}
					
						if((string)$obj->forms->form->options) {
							$opt = explode(",", (string)$obj->forms->form->options);
							foreach((array)$opt as $v) {
								$res[(string)$obj->forms->form->show][(string)$obj->forms->form->id]->options[] = $v;
							}
						}	
					}
				}
				
				$this->tour_forms[$com] = $res;
			}
			
			return (array) $this->tour_forms[$com][$type];
		}
		
		function getTagSizes() {
			$this->XMLRequest(tags);
			
			foreach($this->tags_response as $v) {
				$valid_tags[((string)$v->name)] = (string) $v->count;
			}
			// returns high [0] and low [1] for a list()
			rsort($valid_tags);
			$ret[] = $valid_tags[0];
			sort($valid_tags);
			$ret[] = $valid_tags[0];
						
			return (array) $ret;
		}
		
		function getTags() {
			$this->XMLRequest(tags);
			return (array) $this->tags_response;
		}
		
		// get a list of booking data
		function getBookings($a=null, $node=null) {
			if(!$a) $this->error('No search argument provided, expected trans_num or formatted search string');
			
			if(strpos($a, '=') === false) $a = 'q='.$a; // in case we just passed the trans_num by itself
			
			$this->bookings_index = $a;
			
			$this->XMLRequest(search_bookings);
			
			$return = ($node === null) ? (array) $this->search_bookings_response[$this->bookings_index] : $this->search_bookings_response[$this->bookings_index][$node];
			
			return $return;
		}
		
		function getBookingPrices(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			$c=0;
			if($obj->adult_num >= 1) {
				$ret[$c]->name = 'adult';
				$ret[$c]->label = (string) $obj->adult_label;
				($obj->prices->base_prices->price_adult) ? $ret[$c]->base = (string) $obj->prices->base_prices->price_adult : 0;
				$ret[$c]->price = ((string) $obj->prices->price_adult) / ((string) $obj->adult_num);
				$ret[$c]->number = (string) $obj->adult_num;
				$ret[$c++]->total = (string) $obj->prices->price_adult;
			}
			if($obj->child_num >= 1) {
				$ret[$c]->name = 'child';
				$ret[$c]->label = (string) $obj->child_label;
				($obj->prices->base_prices->price_child) ? $ret[$c]->base = (string) $obj->prices->base_prices->price_child : 0;
				$ret[$c]->price = ((string) $obj->prices->price_child) / ((string) $obj->child_num);
				$ret[$c]->number = (string) $obj->child_num;
				$ret[$c++]->total = (string) $obj->prices->price_child;
			}
			if($obj->senior_num >= 1) {
				$ret[$c]->name = 'senior';
				$ret[$c]->label = (string) $obj->senior_label;
				($obj->prices->base_prices->price_senior) ? $ret[$c]->base = (string) $obj->prices->base_prices->price_senior : 0;
				$ret[$c]->price = ((string) $obj->prices->price_senior) / ((string) $obj->senior_num);
				$ret[$c]->number = (string) $obj->senior_num;
				$ret[$c++]->total = (string) $obj->prices->price_senior;
			}	
			
			for($i=4; $i<=9; $i++) {
				$val = 'price'.$i.'_num';
				if($obj->$val >= 1) {
					$ret[$c]->name = 'price'.$i;
					$val = 'price'.$i.'_label';
					$ret[$c]->label = (string) $obj->$val;
					$val = 'price'.$i;
					$val2 = 'price'.$i.'_num';
					($obj->prices->base_prices->$val) ? $ret[$c]->base = (string) $obj->prices->base_prices->$val : 0;
					$ret[$c]->price = ((string) $obj->prices->$val) / ((string) $obj->$val2);
					$val = 'price'.$i.'_num';
					$ret[$c]->number = (string) $obj->$val;
					$val = 'price'.$i;
					$ret[$c++]->total = (string) $obj->prices->$val;
				}
			}
		
			return (array) $ret;
		}
		
		function getBookingLineItems(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->line_items) {
				if($obj->line_items->line_item[0]) {
					foreach($obj->line_items->line_item as $v) {
						$ret[] = $v;
					}
				} else {
					$ret[] = $obj->line_items->line_item;
				}
			}
			
			return (array) $ret;				
		}
		
		function getBookingFees(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->triggered_fees->triggered_fee[0]) {
				foreach($obj->triggered_fees->triggered_fee as $v) {
					$ret[] = $v;
				}
			} else {
				$ret[] = $obj->triggered_fees->triggered_fee;
			}
			
			return (array) $ret;				
		}
		
		function getBookingPassengers(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			$c=0;
			if($obj->passengers->passenger[0]) {
				foreach($obj->passengers->passenger as $v) {
					// do the forms on the passenger only have one question? if so, fix the formatting so it matches multiple questions
					if($v->total_forms > 0) {
						if(!$v->forms->form[0]) {
							$t = $v->forms->form;
							unset($v->forms->form); // remove the string value
							$v->forms->form[] = $t; // replace it with array value
						}
					} else {
						// no forms, fill the value with a blank array so the templates can use it
						// we add a supress @ modifier to prevent the complex-types error
						@$v->forms->form = array();
					}
					
					$ret[$c] = $v;
					@$ret[$c]->num = $v->type->attributes()->num;
					$val = $v->type.'_label';
					$ret[$c++]->label = $obj->$val;
				}
			} else {
				// do the forms on the passenger only have one question? if so, fix the formatting so it matches multiple questions
				if($obj->passengers->passenger->total_forms > 0) {
					if(!$obj->passengers->passenger->forms->form[0]) {
						$t = $obj->passengers->passenger->forms->form;
						unset($obj->passengers->passenger->forms->form); // remove the string value
						$obj->passengers->passenger->forms->form[] = $t; // replace it with array value
					}
				} else {
					// no forms, fill the value with a blank array so the templates can use it
					@$obj->passengers->passenger->forms->form = array();
				}
				
				$ret[$c] = $obj->passengers->passenger;
				@$ret[$c]->num = $obj->passengers->passenger->type->attributes()->num;
				$val = $obj->passengers->passenger->type.'_label';
				$ret[$c++]->label = $obj->$val;
			}
			
			// unset it if the value is empty because we have no group info	
			$count = count($ret);
			if($count == 1 && !(string)$ret[0]->num) unset($ret);
			
			
			
			// check to make sure the entire array isn't empty of data
			if($count > 1) {
				foreach((array)$ret as $k => $v) {
					
					
					if((string)$v->first_name) { $present = 1; break; }
					if((string)$v->last_name) { $present = 1; break; }

					if((string)$v->phone_number) { $present = 1; break; }
					if((string)$v->email_address) { $present = 1; break; }
					if((string)$v->total_forms > 0) { $present = 1; break; }
				}
				if(!$present) unset($ret);
			}
			
			return (array) $ret;				
		}
		
		function getBookingForms(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->primary_forms->total_forms > 0) {
				if($obj->primary_forms->form[0]) {
					foreach($obj->primary_forms->form as $v) {
						$ret[] = $v;
					}
				} else {
					$ret[] = $obj->primary_forms->form;
				}
			}
						
			return (array) $ret;
		}
		
		function getBookingCounts(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			$list = array('adult', 'child', 'senior', 'price4', 'price5', 'price6', 'price7', 'price8', 'price9');
			$c=0;
			foreach($list as $v) {
				$num = $v.'_num';
				$label = $v.'_label';
				if($obj->$num > 0) {
					$ret[$c]->label = $obj->$label;
					$ret[$c++]->num = $obj->$num;
				}
			}
			return (array) $ret;
		}
		
		function getBookingCurrency(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			return (string) $obj->currency_base;
		}
		
		function getPaxString() {
			if($_REQUEST['adult_num']) $pax_list .= '&adult_num='.$_REQUEST['adult_num'];
			if($_REQUEST['child_num']) $pax_list .= '&child_num='.$_REQUEST['child_num'];
			if($_REQUEST['senior_num']) $pax_list .= '&senior_num='.$_REQUEST['senior_num'];
			if($_REQUEST['price4_num']) $pax_list .= '&price4_num='.$_REQUEST['price4_num'];
			if($_REQUEST['price5_num']) $pax_list .= '&price5_num='.$_REQUEST['price5_num'];
			if($_REQUEST['price6_num']) $pax_list .= '&price6_num='.$_REQUEST['price6_num'];
			if($_REQUEST['price7_num']) $pax_list .= '&price7_num='.$_REQUEST['price7_num'];
			if($_REQUEST['price8_num']) $pax_list .= '&price8_num='.$_REQUEST['price8_num'];
			if($_REQUEST['price9_num']) $pax_list .= '&price9_num='.$_REQUEST['price9_num'];
			
			return $pax_list;
		}
		
		// ------------------------------------------------------------------------------
		// Handle parsing the rezgo pseudo tags
		// ------------------------------------------------------------------------------
		function tag_parse($str) {
			$val = ($GLOBALS['pageHeader']) ? $GLOBALS['pageHeader'] : $this->pageTitle;
			$tags = array('[navigation]', '[navigator]');
			$str = str_replace($tags, $val, $str);	
			
			$val = ($GLOBALS['pageMeta']) ? $GLOBALS['pageHeader'] : $this->metaTags;
			$tags = array('[meta]', '[meta_tags]', '[seo]');
			$str = str_replace($tags, $val, $str);
			
			return (string) $str;
		}
		
		// ------------------------------------------------------------------------------
		// Create an outgoing commit request based on the _REQUEST data
		// ------------------------------------------------------------------------------
		function sendBooking($var=null, $arg=null) {
			$r = ($var) ? $var : $_REQUEST;
			
			if($arg) $res[] = $arg; // extra XML options
			
			($r['date']) ? $res[] = 'date='.urlencode($r['date']) : $this->error('commit element "date" is empty', 1);
			($r['book']) ? $res[] = 'book='.urlencode($r['book']) : $this->error('commit element "book" is empty', 1);
			
			($r['trigger_code']) ? $res[] = 'trigger_code='.urlencode($r['trigger_code']) : 0;
			
			($r['adult_num']) ? $res[] = 'adult_num='.urlencode($r['adult_num']) : 0;
			($r['child_num']) ? $res[] = 'child_num='.urlencode($r['child_num']) : 0;
			($r['senior_num']) ? $res[] = 'senior_num='.urlencode($r['senior_num']) : 0;
			($r['price4_num']) ? $res[] = 'price4_num='.urlencode($r['price4_num']) : 0;
			($r['price5_num']) ? $res[] = 'price5_num='.urlencode($r['price5_num']) : 0;
			($r['price6_num']) ? $res[] = 'price6_num='.urlencode($r['price6_num']) : 0;
			($r['price7_num']) ? $res[] = 'price7_num='.urlencode($r['price7_num']) : 0;
			($r['price8_num']) ? $res[] = 'price8_num='.urlencode($r['price8_num']) : 0;
			($r['price9_num']) ? $res[] = 'price9_num='.urlencode($r['price9_num']) : 0;
			
			($r['tour_first_name']) ? $res[] = 'tour_first_name='.urlencode($r['tour_first_name']) : 0;
			($r['tour_last_name']) ? $res[] = 'tour_last_name='.urlencode($r['tour_last_name']) : 0;
			($r['tour_address_1']) ? $res[] = 'tour_address_1='.urlencode($r['tour_address_1']) : 0;
			($r['tour_address_2']) ? $res[] = 'tour_address_2='.urlencode($r['tour_address_2']) : 0;
			($r['tour_city']) ? $res[] = 'tour_city='.urlencode($r['tour_city']) : 0;
			($r['tour_stateprov']) ? $res[] = 'tour_stateprov='.urlencode($r['tour_stateprov']) : 0;
			($r['tour_country']) ? $res[] = 'tour_country='.urlencode($r['tour_country']) : 0;
			($r['tour_postal_code']) ? $res[] = 'tour_postal_code='.urlencode($r['tour_postal_code']) : 0;
			($r['tour_phone_number']) ? $res[] = 'tour_phone_number='.urlencode($r['tour_phone_number']) : 0;
			($r['tour_email_address']) ? $res[] = 'tour_email_address='.urlencode($r['tour_email_address']) : 0;
			
			if($r['tour_group']) {
				foreach((array) $r['tour_group'] as $k => $v) {
					foreach((array) $v as $sk => $sv) {
						$res[] = 'tour_group['.$k.']['.$sk.'][first_name]='.urlencode($sv['first_name']);
						$res[] = 'tour_group['.$k.']['.$sk.'][last_name]='.urlencode($sv['last_name']);
						$res[] = 'tour_group['.$k.']['.$sk.'][phone]='.urlencode($sv['phone']);
						$res[] = 'tour_group['.$k.']['.$sk.'][email]='.urlencode($sv['email']);
					
						foreach((array) $sv['forms'] as $fk => $fv) {
							if(is_array($fv)) $fv = implode(", ", $fv); // for multiselects
							$res[] = 'tour_group['.$k.']['.$sk.'][forms]['.$fk.']='.urlencode(stripslashes($fv));
						}
					}		
				}
			}
			
			if($r['tour_forms']) {
				foreach((array) $r['tour_forms'] as $k => $v) {
					if(is_array($v)) $v = implode(", ", $v); // for multiselects
					$res[] = 'tour_forms['.$k.']='.urlencode(stripslashes($v));
				} 
			}
			
			($r['payment_method']) ? $res[] = 'payment_method='.urlencode($r['payment_method']) : 0;
			
			($r['payment_method_add']) ? $res[] = 'payment_method_add='.urlencode($r['payment_method_add']) : 0;
			
			($r['payment_method'] == 'Credit Cards' && $r['tour_card_token']) ? $res[] = 'tour_card_token='.urlencode($r['tour_card_token']) : 0;
			($r['payment_method'] == 'PayPal' && $r['paypal_token']) ? $res[] = 'paypal_token='.urlencode($r['paypal_token']) : 0;
			($r['payment_method'] == 'PayPal' && $r['paypal_payer_id']) ? $res[] = 'paypal_payer_id='.urlencode($r['paypal_payer_id']) : 0;
			
			($r['agree_terms']) ? $res[] = 'agree_terms='.urlencode($r['agree_terms']) : 0;
			
			// add in external elements
			($this->refid) ? $res['refid'] = '&refid='.$this->refid : 0;
			($this->promo_code) ? $res['promo'] = '&trigger_code='.$this->promo_code : 0;
			
			$request = '&'.implode('&', $res);
			
			$this->XMLRequest(commit, $request);
			
			return $this->commit_response;
		}
		
		// this function is for sending a partial commit request, it does not add any values itself
		function sendPartialCommit($var=null) {
			$request = '&'.$var;
			
			$this->XMLRequest(commit, $request);
			
			return $this->commit_response;
		}
		
		function sendContact($var=null) {
			$r = ($var) ? $var : $_REQUEST;
			
			// we use full_name instead of name because of a wordpress quirk
			($r['full_name']) ? $res[] = 'name='.urlencode($r['full_name']) : $this->error('contact element "full_name" is empty', 1);
			($r['email']) ? $res[] = 'email='.urlencode($r['email']) : $this->error('contact element "email" is empty', 1);
			($r['body']) ? $res[] = 'body='.urlencode($r['body']) : $this->error('contact element "body" is empty', 1);
			
			($r['phone']) ? $res[] = 'phone='.urlencode($r['phone']) : 0;
			($r['address']) ? $res[] = 'address='.urlencode($r['address']) : 0;
			($r['address2']) ? $res[] = 'address2='.urlencode($r['address2']) : 0;		
			($r['city']) ? $res[] = 'city='.urlencode($r['city']) : 0;
			($r['state_prov']) ? $res[] = 'state_prov='.urlencode($r['state_prov']) : 0;
			($r['country']) ? $res[] = 'country='.urlencode($r['country']) : 0;
			
			$request = '&'.implode('&', $res);
			
			$this->XMLRequest(contact, $request);
			
			return $this->contact_response;
		}
	
	}
	
?>