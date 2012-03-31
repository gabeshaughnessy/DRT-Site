<?php
// Look for constant contact opt-in
$ccIds = Cart66Common::postVal('constantcontact_subscribe_ids');

if(isset($ccIds) && is_array($ccIds)) {
  
  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to register for Constant Contact newsletter");
  $cc = new Cart66ConstantContact();
  
  if(isset($_POST['payment']) && isset($_POST['billing'])) {
    // Process from on-site checkout forms
    $email = $_POST['payment']['email'];
    $extraFields = array(
  		'FirstName' => $_POST['billing']['firstName'],
  		'LastName'  => $_POST['billing']['lastName']
  	);
  }
  elseif( isset($_POST['constantcontact_email']) && isset($_POST['constantcontact_first_name']) && isset($_POST['constantcontact_last_name']) ) {
    // Process from PayPal Express Checkout
    $email = Cart66Common::postVal('constantcontact_email');
    $extraFields = array(
  		'FirstName' => $_POST['constantcontact_first_name'],
  		'LastName'  => $_POST['constantcontact_last_name']
  	);
  }
  
  if(isset($email) && !empty($email)) {
    $contact = $cc->query_contacts($email);
  	$cc->set_action_type('contact');
  	if($contact) {
  	  $status = $cc->update_contact($contact['id'], $email, $ccIds, $extraFields);
  	  if($status) {
  	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Constant Contact newsletter registration updated. Contact Info: " . 
  	      print_r($contact, true) . ' Email:' . $email . print_r($ccIds, true) . print_r($extraFields, true));
  	  }
  	  else {
  	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Constant Contact newsletter registration update failed: " . 
  	      $cc->http_get_response_code_error($cc->http_response_code) . $cc->http_response_body .
  	      "\nEmail:" . $email . " Status: $status " . print_r($ccIds, true) . print_r($extraFields, true));
  	  }
  	}
  	else {
  	  $newId = $cc->create_contact($email, $ccIds, $extraFields);
  	  if($newId) {
  	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Constant Contact newsletter registration created" . ' Email:' . $email . print_r($ccIds, true) . print_r($extraFields, true));
  	  }
  	  else {
  	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Constant Contact newsletter registration creation failed: " . 
  	      $cc->http_get_response_code_error($cc->http_response_code) . $cc->http_response_body . 
  	      ' Email:' . $email . print_r($ccIds, true) . print_r($extraFields, true));
  	  }
  	}
  }
  
}
