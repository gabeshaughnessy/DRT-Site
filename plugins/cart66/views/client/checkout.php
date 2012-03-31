<?php
/**
 * This script requires the following variables from the parent page:
 *   $jqErrors -- an array of jQuery error information
 *   $s -- an array of shipping information
 *   $b -- an array of billing information
 *   $p -- an array of payment information
 */
?>
<script type="text/javascript">
  function setState(kind) {
    $jq('#' + kind + '-state').show();
    $jq('#' + kind + '-state-text').hide();
    
    if($jq('#' + kind + '-country').val() == 'US' || $jq('#' + kind + '-country').val() == null) {
      $jq('#' + kind + '-state').empty(); 
      $jq('#' + kind + '-state').removeAttr('disabled');
      <?php foreach(Cart66Common::getZones('US') as $code => $name): ?>
        $jq('#' + kind + '-state').append('<option value="<?php echo $code ?>"><?php echo $name ?></option>');
      <?php endforeach; ?>
    }
    else if($jq('#' + kind + '-country').val() == 'AU') {
      $jq('#' + kind + '-state').empty(); 
      $jq('#' + kind + '-state').removeAttr('disabled');
      <?php foreach(Cart66Common::getZones('AU') as $code => $name): ?>
        $jq('#' + kind + '-state').append('<option value="<?php echo $code ?>"><?php echo $name ?></option>');
      <?php endforeach; ?>
    }
    else if($jq('#' + kind + '-country').val() == 'CA') {
      $jq('#' + kind + '-state').empty(); 
      $jq('#' + kind + '-state').removeAttr('disabled');
      <?php foreach(Cart66Common::getZones('CA') as $code => $name): ?>
        $jq('#' + kind + '-state').append('<option value="<?php echo $code ?>"><?php echo $name ?></option>');
      <?php endforeach; ?>
    }
	  else if($jq('#' + kind + '-country').val() == 'BR') {
      $jq('#' + kind + '-state').empty(); 
      $jq('#' + kind + '-state').removeAttr('disabled');
      <?php foreach(Cart66Common::getZones('BR') as $code => $name): ?>
        $jq('#' + kind + '-state').append('<option value="<?php echo $code ?>"><?php echo $name ?></option>');
      <?php endforeach; ?>
    }
    else {
      $jq('#' + kind + '-state').attr('disabled', 'disabled');
      $jq('#' + kind + '-state').empty(); 
      $jq('#' + kind + '-state').hide(); 
      $jq('#' + kind + '-state-text').show(); 
    }
  }

  $jq = jQuery.noConflict();
  $jq('document').ready(function() {
    <?php if($_SERVER['REQUEST_METHOD'] == 'GET'): ?>
      $jq('#sameAsBilling').attr('checked', true);
    <?php else: ?>
      <?php
        if(isset($_POST['sameAsBilling']) && $_POST['sameAsBilling'] == '1') {
          ?>
          $jq('#sameAsBilling').attr('checked', true);
          <?php
        }
        else {
          ?>
          $jq('#shippingAddress').css('display', 'block');
          <?php
        }
      ?>
    <?php endif; ?>

    // Dynamically configure state based on country
    $jq('#billing-country').change(function() { setState('billing'); });
    $jq('#shipping-country').change(function() { setState('shipping'); });
    
    $jq('#payment-cardExpirationMonth').val("<?php echo $p['cardExpirationMonth'] ?>");
    $jq('#payment-cardExpirationYear').val("<?php echo $p['cardExpirationYear'] ?>");
    
    <?php if(isset($p['cardType']) && !empty($p['cardType'])): ?>
      $jq('#payment-cardType').val("<?php echo $p['cardType'] ?>");
    <?php endif; ?>
    
    <?php if(isset($jqErrors) && is_array($jqErrors)): ?>
      <?php foreach($jqErrors as $val): ?>
        $jq('#<?php echo $val ?>').addClass('errorField');
      <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if(isset($b['country']) && !empty($b['country'])): ?>
      $jq('#billing-country').val("<?php echo $b['country'] ?>");
    <?php endif; ?>

    <?php if(isset($s['country']) && !empty($s['country'])): ?>
      $jq('#shipping-country').val("<?php echo $s['country'] ?>");
    <?php endif; ?>

    setState('billing');
    setState('shipping');
    
    $jq('#billing-state').val("<?php echo $b['state'] ?>");
    $jq('#shipping-state').val("<?php echo $s['state'] ?>");

  });
  
  $jq('#sameAsBilling').click(function() {
    if($jq('#sameAsBilling').attr('checked')) {
      $jq('#shippingAddress').css('display', 'none');
    }
    else {
      $jq('#shippingAddress').css('display', 'block');
    }
  });
  
  $jq('#changeBillingLink').click(function() {
    $jq('#billingUpdate').toggle();
  });
  
  $jq('#sameAsBillingMember').click(function() {
    if($jq('#sameAsBillingMember').attr('checked')) {
      $jq('#shippingAddressMember').css('display', 'none');
    }
    else {
      $jq('#shippingAddressMember').css('display', 'block');
    }
  });

</script>