<?php
	// Wordpress hook for Rezgo Settings
	
	add_action( 'admin_init', 'rezgo_register_settings' );
	add_action( 'admin_menu', 'rezgo_plugin_menu' );
	
	function rezgo_register_settings() { 
	  register_setting( 'rezgo_options', 'rezgo_cid' );
	  register_setting( 'rezgo_options', 'rezgo_api_key' );
	  
	  register_setting( 'rezgo_options', 'rezgo_captcha_pub_key' );
	  register_setting( 'rezgo_options', 'rezgo_captcha_priv_key' );
	  
	  register_setting( 'rezgo_options', 'rezgo_result_num' );
	  register_setting( 'rezgo_options', 'rezgo_template' );
	  
	  register_setting( 'rezgo_options', 'rezgo_forward_secure' );
	  register_setting( 'rezgo_options', 'rezgo_secure_url' );
	  
		wp_register_style( 'rezgo_settings_css', plugins_url('/settings.css', __FILE__));
	}
	
	function rezgo_plugin_menu() {
		$icon = REZGO_DIR . '/icon.png';
		$menu_page = add_menu_page( 'Rezgo Settings', 'Rezgo', 'manage_options', 'rezgo-settings', 'rezgo_plugin_settings', $icon );
		add_action( 'admin_print_styles-' . $menu_page, 'rezgo_plugin_admin_styles' );
	}
	
	// Add settings link on plugin page
	function rezgo_plugin_settings_link($links) { 
	  $settings_link = '<a href="admin.php?page=rezgo-settings">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}
	 
	add_filter("plugin_action_links_rezgo/rezgo.php", 'rezgo_plugin_settings_link' );
	
	function rezgo_plugin_admin_styles() {
		 wp_enqueue_style( 'rezgo_settings_css' );
	}
	
	function rezgo_plugin_settings() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		echo '
		<script src="'.REZGO_DIR.'/jquery.tools.min.js" type="text/javascript"></script>
		
		<div class="wrap" id="rezgo_settings">
			<img src="'.REZGO_DIR.'/logo_rezgo.png">';	
		
		if( $_POST && $_POST['rezgo_update'] ) {
			
			if($_POST['rezgo_secure_url']) {
				$_POST['rezgo_secure_url'] = str_replace("http://", "", $_POST['rezgo_secure_url']);
				$_POST['rezgo_secure_url'] = str_replace("https://", "", $_POST['rezgo_secure_url']);
			}
			
			if(!$_POST['rezgo_result_num']) $_POST['rezgo_result_num'] = 20;
			
			update_option( "rezgo_cid", trim($_POST['rezgo_cid']) );
			update_option( "rezgo_api_key", trim($_POST['rezgo_api_key']) );
			
			update_option( "rezgo_captcha_pub_key", $_POST['rezgo_captcha_pub_key'] );
			update_option( "rezgo_captcha_priv_key", $_POST['rezgo_captcha_priv_key'] );
			
			update_option( "rezgo_result_num ", $_POST['rezgo_result_num'] );
			update_option( "rezgo_template", $_POST['rezgo_template'] );
			
			// since we set this value to 1 as default, make sure it's set to 0 if it's off
			if(!$_POST['rezgo_forward_secure']) $_POST['rezgo_forward_secure'] = 0;
			
			update_option( "rezgo_forward_secure", $_POST['rezgo_forward_secure'] );
			update_option( "rezgo_secure_url", $_POST['rezgo_secure_url'] );
		
			echo '
			<p class="success">Your Rezgo options have been updated.</p>
			';
			
		}
		
		echo '		
		<p>
			Rezgo makes it easy for you to accept bookings on your tour or
			activity business WordPress site.  To manage your Rezgo account, <a
			href="http://login.rezgo.com" target="_blank">login here</a>.
		</p>
			<h3>Getting Started</h3>
			
		<p>	
			<ol>
				<li><a href="http://www.rezgo.com">Sign-up for a Rezgo account</a>.
				<li>Setup your inventory and configure your account on Rezgo.
				<li>Complete the Rezgo WordPress Plugin settings below.
				<li>Create a Page and embed the Rezgo booking engine by using the shortcode: [rezgo_shortcode].
				<li>Ensure you are using a non default permalink structure.&nbsp;'.((get_option(permalink_structure) != '') ? 'Your current structure should work!' : '');

					if(get_option(permalink_structure) == '') {
						echo '<div style="border:1px solid #9E0000; padding: 4px; padding-left:6px; padding-right:6px; background-color: #D97F7E; width:-moz-fit-content;">
							<strong>currently using [</strong>default<strong>] which may not work correctly! <a href="/wp-admin/options-permalink.php" style="color:#333333;">Click here</a> to change it.</strong>
						</div>';
					}
					
			echo '</ol>
		</p>
		
		<form method="post" action="">
		';
		
		settings_fields( 'rezgo_options' );
		
		echo '
		<script>
			var cid_value = \''.get_option('rezgo_cid').'\';
			var key_value = \''.get_option('rezgo_api_key').'\';
			function check_values() {
				var cid = $(\'#rezgo_cid\').val();
				var key = $(\'#rezgo_api_key\').val();
			
				// do nothing if we changed nothing
				if(cid_value != cid || key_value != key) {
					cid_value = cid;
					key_value = key;
					
					if(cid && key) {
						$(\'#check_values\').html(\'<img src="'.REZGO_DIR.'/load.gif">\');
						$(\'#check_values\').load(\''.REZGO_DIR.'/settings_ajax.php?cid=\' + cid.trim() + \'&key=\' + key.trim());
					} else {
						reset_check();
					}
				}
			}
			
			function reset_check() {
				$(\'#check_values\').html(\'<span style="required_missing">Information is missing.</span>\');
			}
		</script>
		
		<div class="field_frame">
			<fieldset>
				<legend class="account_info">Account Information</legend>
				
				<dl>
					<dt class=note>Your Company Code and API Key can be found on the Rezgo settings page.</dt>
					<br><br>
				
					<dt>Rezgo Company Code:</dt>
					<dd><input type="text" name="rezgo_cid" id="rezgo_cid" size="10" value="'.get_option('rezgo_cid').'" onkeyup="check_values()" /></dd>
					
					<dt>Rezgo API Key:</dt>
					<dd><input type="text" name="rezgo_api_key" id="rezgo_api_key" size="20" value="'.get_option('rezgo_api_key').'" onkeyup="check_values()" /></dd>
					
					<div class="api_box" id="check_values"> 
					';	
						
						if(get_option('rezgo_cid') && get_option('rezgo_api_key')) {
							
							function getPage($url) {
								include('include/fetch.rezgo.php');
								return $result;
							}
							
							$file = getPage('http://xml.rezgo.com/xml?transcode='.get_option('rezgo_cid').'&key='.get_option('rezgo_api_key').'&i=company');
	
							$result = simplexml_load_string(utf8_encode($file));
							
							if((string)$result->company_name) {
								echo '<span class="ajax_success">XML API Connected</span><br>
								<span class="ajax_success_message">'.((string)$result->company_name).'</span> 
								<a href="http://'.((string)$result->domain).'.rezgo.com" class="ajax_success_url" target="_blank">'.((string)$result->domain).'.rezgo.com</a>';
							} else {
								echo '<span class="ajax_error">XML API Error</span><br>
								<span class="ajax_error_message">'.((string)$result[0]).'</span>';
							}
						} else {
							echo '<span style="required_missing">Information is missing</span>';
						}
						
					echo '	
					</div>
				</dl>
			
			</fieldset>
		</div>
		<div class="field_frame">
			<fieldset>
				<legend class="recaptcha_key">Recaptcha API Keys</legend>
				
				<dl>
					<dt class=note>If you wish to use Recaptcha on your contact page, enter your API credentials here. You can get Recaptcha for free from <a href="http://www.google.com/recaptcha" target="_blank">Google</a></dt>
					<br><br>
				
					<dt>Recaptcha Public Key:</dt>
					<dd><input type="text" name="rezgo_captcha_pub_key" size="50" value="'.get_option('rezgo_captcha_pub_key').'" /></dd>
			
					<dt>Recaptcha Private Key:</dt>
					<dd><input type="text" name="rezgo_captcha_priv_key" size="50" value="'.get_option('rezgo_captcha_priv_key').'" /></dd>
				</dl>
				
			</fieldset>
		</div>
		<div class="field_frame">
			<fieldset>
				<legend class="general_settings">General Settings</legend>
				
				<dl>
					
					<dt class=note>How many results do you want to show on each page? We suggest 20. Higher numbers may have an impact on performance.</dt>
					<br><br>';
					
					$results_num = get_option('rezgo_result_num');
					if(!$results_num) $results_num = 20;
					
					$template_url = str_replace('https://', '', REZGO_DIR);
					$template_url = str_replace('http://', '', $template_url);
					$template_url = str_replace($_SERVER['HTTP_HOST'], '', $template_url);
					
					// if forward secure is not yet set to anything, check it as default
					if (get_option('rezgo_forward_secure') === '' || get_option('rezgo_forward_secure') === false) {
						$forward_secure_checked = 'checked';
					} else {
						$forward_secure_checked = (get_option('rezgo_forward_secure')) ? 'checked' : '';
					}
					
					echo '<dt>Number of results:</dt>
					<dd><input type="text" name="rezgo_result_num" size="5" value="'.$results_num.'" /></dd>		
			
					<dt class=note>The Rezgo template you want to use. Add new templates to '.$template_url.'/templates/</dt>
					<br><br>
			
					<dt>Template:</dt>
					<dd>
						
						<select name="rezgo_template">';
							$handle = opendir('../wp-content/plugins/rezgo-online-booking/templates');
							while (false !== ($file = readdir($handle))) {
					        if ($file != "." && $file != "..") {
					        	$select = ($file == get_option('rezgo_template')) ? 'selected' : '';
					        	echo '<option value="'.$file.'" '.$select.'>'.$file.'</option>';
					        }
					    }
					    closedir($handle);	
						echo '</select>
					</dd>
					
					<dt class=note>If you do not have your own security certificate, you can forward users to the Rezgo white-label for booking (we recommend this).</dt>
					<br><br>
					
					<dt>Forward secure page to Rezgo:</dt>
					<dd><input type="checkbox" name="rezgo_forward_secure" value="1" '.$forward_secure_checked.' onclick="if(this.checked == true) { $(\'#alternate_url\').fadeOut(); } else { $(\'#alternate_url\').fadeIn(); }" /></dd>
					
					<div id="alternate_url" style="display:'.(($forward_secure_checked) ? 'none' : '').';">
						<dt class=note>By default, Rezgo will use your current domain for the secure site.  If you have another secure domain you want to use (such as secure.mysite.com) you can specify it here. Otherwise leave this blank.</dt>
						<br><br>
					
						<dt>Alternate Secure URL:</dt>
						<dd><input type="text" name="rezgo_secure_url" size="50" value="'.get_option('rezgo_secure_url').'" /></dd>
					</div>
					
				</dl>
			</fieldset>
			</div>
			<dl>	
					

			<dd><input type="submit" class="button-primary" value="Save Changes" /></dd>		
		</dl>
		<input type="hidden" name="rezgo_update" value="1" />
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="rezgo_cid,rezgo_api_key,rezgo_uri,rezgo_result_num" />
		
		</form>
		</div>
		';
		
	}

?>