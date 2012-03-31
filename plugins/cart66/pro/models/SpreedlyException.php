<?php
/**
 * Exception code reference
 * 
 * Exception codes from creating an invoice
 * 404 - Not found. The subscription plan with the specified id does not exist
 * 422 - Unprocessable entity. The subscriber info passed in is invalid or the request is malformed
 * 403 - Forbidden. The subscription plan is disabled
 * 
 * Exception codes from paying an invoice
 * 404 - Not found. Invoice with the specified token cannot be found
 * 422 - Unprocessable entity. Some required information is missing or otherwise fails verification
 * 403 - Forbidden. Attempt to pay an already closed invoice, if the payment information fails to authorize, or if a gateway is not configured.
 * 504 - Gateway timeout. The payment gateway fails to respond in a timely manner and the payment is thus not applied.
 * 
 * Expecption codes for spreedly subscriber
 * 403 - Forbidden. No customer id specified or there is already a customer with the given id
 * 422 - Unprocessable entity. Validtion error.
 * 
 * Internal exception codes
 * 66001 - Invalid credit card data used to pay a spreedly invoice
 * 66002 - Trying to pay spreedly invoice without a valid invoice token
 * 66003 - Unable to retrieve remote list of subscriptions
 * 66004 - Unable to add fees to customer
 * 66005 - Unable to assign free trial plan to a subscriber
 * 66006 - Unable to assign free trial plan to a subscriber who does not exist
 * 66007 - Failed to find spreedly subscriber
 */

class SpreedlyException extends Exception {}