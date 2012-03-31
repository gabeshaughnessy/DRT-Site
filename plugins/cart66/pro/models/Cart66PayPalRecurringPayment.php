<?php 
class Cart66PayPalRecurringPayment extends Cart66ModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('pp_recurring_payments');
    parent::__construct($id);
  }
  
  /**
   * Log the PayPal recurring payment. 
   * 
   * The $data array paramter is a URL decoded version of the IPN post data.
   *   - Log the data in the pp_recurring_posts table
   *   - Update the account_subscriptions table with the new active_until date
   */
  public function log(array $ipnData) {
    $isLogged = false;
    $subscription = new Cart66AccountSubscription();
    if($subscription->loadByPayPalBillingProfileId($ipnData['recurring_payment_id'])) {
      $data = array(
        'account_id' => $subscription->accountId,
        'recurring_payment_id' => $ipnData['recurring_payment_id'],
        'mc_gross' => $ipnData['mc_gross'],
        'txn_id' => $ipnData['txn_id'],
        'product_name' => $ipnData['product_name'],
        'first_name' => $ipnData['first_name'],
        'last_name' => $ipnData['last_name'],
        'payer_email' => $ipnData['payer_email'],
        'ipn' => serialize($ipnData),
        'next_payment_date' => $ipnData['next_payment_date'],
        'time_created' => date('Y-m-d H:i:s', strtotime($ipnData['time_created']))
      );
      $this->setData($data);
      $id = $this->save();
      if($id >0) {
        $isLogged = true;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Recurring payment logged with ID: $id");
        $subscription->extendActiveUntil($ipnData['next_payment_date']);
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to log recurring payment. " . print_r($data, true));
      }
    }
    else {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to log recurring payment because the paypal billing profile id is unknown: " . $ipnData['recurring_payment_id']);
    }
    return $isLogged;
  }
  
}