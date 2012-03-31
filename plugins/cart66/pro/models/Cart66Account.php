<?php
class Cart66Account extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('accounts');
    parent::__construct($id);
  }
  
  /**
   * Login to a Cart66 account by placing the Cart66AccountId into the session.
   * If login is successful the object is loaded from the database.
   * 
   * @param string $email Account holder's email address
   * @param string $password Account holder's plain text password (not yet encrypted)
   * @return integer The account id or NULL if no account id is found
   */
  public function login($email, $password) {
    $accountsTable = Cart66Common::getTableName('accounts');
    $sql = "SELECT id from $accountsTable where username = %s and password = %s";
    $sql = $this->_db->prepare($sql, $email, md5($password));
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Login query: $sql");
    if($accountId = $this->_db->get_var($sql)) {
      $_SESSION['Cart66AccountId'] = $accountId;
      $this->load($accountId);
    }
    return $accountId;
  }
  
  public static function logout($redirectUrl=null) {
    if(isset($_SESSION['Cart66AccountId'])) {
      unset($_SESSION['Cart66AccountId']);
      if(isset($redirectUrl)) {
        $url = str_replace('cart66-task=logout', '', $redirectUrl);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirecting after logout to: $url");
        wp_redirect($url);
        die();
      }
    }
  }
  
  /**
   * Return an array of validation errors
   * If validation fails, an array of the errors is returned  and errors are also stored to the protected $_errors array.
   * If the validation passes, an empty array is returned.
   * 
   * @return array
   */
  public function validate() {
    $this->clearErrors();
    $this->_isEmailValid();
    $this->_isUsernameUnique();
    $this->_isPasswordValid();
    
    // Debugging code to display errors 
    if($this->hasErrors()) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation errors: " . print_r($this->_errors, true) . print_r($this->_data, true));
    }
    
    return $this->_errors;
  }
  
  public function save() {
    if(empty($this->password) && $this->id > 0) {
      // If password is empty, keep the old password
      $dbAccount = new Cart66Account($this->id);
      $this->password = $dbAccount->password;
    }
    return parent::save();
  }
  
  /**
   * Attempt to locate the account based on the passed in email address and reset that password
   * 
   * StdClass Object is returned
   *   $result->success = true/false
   *   $result->message = The message to show the user
   * 
   * @return object $result The result of the password reset attempt
   */
  public function passwordReset() {
    $account = false;
    if(isset($_POST['login']['username'])) {
      $username = $_POST['login']['username'];
      $account = $this->getOne("where username = '$username'");
    }

    $result = new StdClass();
    if($account) {
      $newPwd = Cart66Common::randomString();
      $account->password = md5($newPwd);
      $account->save();
      $email = $account->email;
      $subject = Cart66Setting::getValue('reset_subject');
      $message = Cart66Setting::getValue('reset_intro');
      $message .= "\n\nYour new password is: $newPwd";
      $headers = 'From: '. Cart66Setting::getValue('reset_from_name') .' <' . Cart66Setting::getValue('reset_from_address') . '>' . "\r\n\\";
      Cart66Common::mail($email, $subject, $message, $headers);
      $result->success = true;
      $result->message = "A new password has been emailed to $email";
    }
    else {
      $result->success = false;
      $result->message = "We couldn't find an account with that username.";
    }
    
    return $result;
  }
  
  /**
   * Return the id of the active account subscription or false if the account has no active subscription
   * 
   * If the optional $returnExpired parameter is true the latest subscription id will be returned even if it
   * is an expired susbscription.
   * 
   * @param boolean
   * @return int or false
   */
  public function getCurrentAccountSubscriptionId($returnExpired=false) {
    $id = false;
    if($this->id > 0) {
      $accountSubscriptions = Cart66Common::getTableName('account_subscriptions');
      $sql = "SELECT id from $accountSubscriptions where account_id = %d order by active_until desc";
      $sql = $this->_db->prepare($sql, $this->id);
      $subscriptionId = $this->_db->get_var($sql);
      if($subscriptionId > 0) {
        $sub = new Cart66AccountSubscription($subscriptionId);
        if($sub->isActive() || $returnExpired) {
          $id = $sub->id;
        }
      }
    }
    return $id;
  }
  
  /**
   * Return the Cart66AccountSubscription for this account. 
   * If there are no active subscriptions, return false.
   * 
   * @return Cart66AccountSubscription or false
   */
  public function getCurrentAccountSubscription($returnExpired=false) {
    $sub = false;
    if($id = $this->getCurrentAccountSubscriptionId($returnExpired)) {
      $sub = new Cart66AccountSubscription($id);
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to find a current subscription for account: $this->id");
    }
    return $sub;
  }
  
  /**
   * Return the feature level or false if there is no active account
   * 
   * @return string or false
   */
  public function getFeatureLevel() {
    $level = false;
    if($sub = $this->getCurrentAccountSubscription()) {
      $level = $sub->featureLevel;
    }
    return $level;
  }
  
  public function isActive() {
    $isActive = false;
    if($sub = $this->getCurrentAccountSubscription()) {
      $isActive = $sub->isActive();
    }
    return $isActive;
  }
  
  public function isSpreedlyAccount() {
    $isSpreedlyAccount = false;
    if($id = $this->getCurrentAccountSubscriptionId()) {
      $sub = new Cart66AccountSubscription($id);
      $isSpreedlyAccount = $sub->isSpreedlySubscription();
    }
    return $isSpreedlyAccount;
  }
  
  public function isPayPalAccount() {
    $isPayPalAccount = false;
    if($id = $this->getCurrentAccountSubscriptionId()) {
      $sub = new Cart66AccountSubscription($id);
      $isPayPalAccount = $sub->isPayPalSubscription();
    }
    return $isPayPalAccount;
  }
  
  /**
   * Create a new account with the given details for the new plan. 
   * The account is initially set to be active until one day from creation unless otherwise specified using the $activeUntil parameter.
   * 
   * @param array $details                 The PayPal details from the express checkout details
   * @param string $profileId              The PayPal billing profile id
   * @param Cart66PayPalSubscription $plan The plan for the subscription
   * @param string                         String suitable for use with strtotime
   * @return int                           The id of the new account or FALSE if the account creation failed
   */
  public function attachPayPalSubscription($details, $profileId, $plan, $activeUntil=null) {
    $id = false;
    if($this->id > 0) {
      // Create new account
      $interval = $plan->billingInterval . ' ' . $plan->getBillingIntervalUnit();
      
      // Define initial expiration date
      $activeUntil = isset($activeUntil) ? date('Y-m-d H:i:s', strtotime($activeUntil)) : date('Y-m-d H:i:s', strtotime('+ 1 day'));
      
      $data = array(
        'account_id' => $this->id,
        'billing_first_name' => $details['FIRSTNAME'],
        'billing_last_name' => $details['LASTNAME'],
        'paypal_billing_profile_id' => $profileId,
        'subscription_plan_name' => $plan->name,
        'feature_level' => $plan->featureLevel,
        'active_until' => $activeUntil,
        'billing_interval' => $interval,
        'status' => 'active',
        'active' => 0
      );

      $subscription = new Cart66AccountSubscription();
      $subscription->setData($data);
      $subscription->save();
    }
  }
  
  /**
   * Get the current account subscription, determine what type of subscription it is, then cancel it.
   */
  public function cancelSubscription($note='Your subscription has been canceled per your request.', $expire=false) {
    if($this->id > 0) {
      if($subId = $this->getCurrentAccountSubscriptionId()) {
        $subscription = new Cart66AccountSubscription($subId);
        if($subscription->isPayPalSubscription()) {
          $subscription->cancelPayPalSubscription($note, $expire);
        }
        elseif($subscription->isSpreedlySubscription()) {
          // TODO: Cancel spreedly subscriptions
        }
      }
    }
  }
  
  public function deleteMe() {
    if($this->id > 0) {
      $sub = new Cart66AccountSubscription();
      $subs = $sub->getModels("where account_id=$this->id");
      foreach($subs as $s) {
        $s->deleteMe();
      }
      parent::deleteMe();
    }
  }
  
  protected function _isUsernameUnique() {
    $isUnique = true;
    $username = $this->username;
    
    if(empty($username)) { 
      $this->addError('empty username', 'Username required', 'account-username');
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account username is empty and must be provided");
      $isUnique = false;
    }
    else {
      $accountsTable = $this->_tableName;
      $id = $this->id;
      if(empty($id)) { $id = 0; }
      $sql = "SELECT count(*) as num from $accountsTable where username = %s and id != %d";
      $sql = $this->_db->prepare($sql, $username, $id);
      $num = $this->_db->get_var($sql);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart66 Account: Is username unique:\n$sql\nCount: $num");
      if($num > 0) {
        $this->addError('duplicate username', 'Username unavailable', 'account-username');
        $isUnique = false;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation error: email address is not unique");
      }
    }
    
    return $isUnique;
  }
  

  /**
   * Return true if email address is valid and present
   */
  protected function _isEmailValid() {
    $isValid = true;
    if(!Cart66Common::isValidEmail($this->email)) {
      $isValid = false;
      $this->addError('email', 'Email address is invalid', 'account-email');
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation error: email address is not valid");
      
    }
    return $isValid;
  }
  
  /**
   * Return true if password is not empty
   */
  protected function _isPasswordValid() {
    $isValid = true;
    $pwd = $this->password;
    $emptyMd5 = md5('');
    if($pwd == $emptyMd5) {
      $isValid = false;
      $this->addError('password', 'Account password is required', 'account-password');
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation error: password is required");
    }
    return $isValid;
  }
  
}