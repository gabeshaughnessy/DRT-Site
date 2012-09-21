<?php if(isset($data['featureLevels'])): ?>
<div class="inside">
  <p style="margin:0; padding: 3px 0px;"><strong>Require Feature Level To Access</strong></p>
  <p style="line-height: 1.5em;">
    <?php if(!empty($data['featureLevels'])): ?>
    
      <?php foreach($data['featureLevels'] as $featureLevel): ?>
        <?php
          $checked = in_array($featureLevel, $data['saved_feature_levels']) ? 'checked="checked"' : '';
        ?>
        <input type="checkbox" name="feature_levels[]" value="<?php echo $featureLevel ?>" <?php echo $checked ?> /> 
        <?php echo $featureLevel; ?><br/>
      <?php endforeach; ?>
  </p>
  
  <p style="margin:0; padding: 3px 0px; font-weight: bold;">Your Active Memberships &amp; Subscriptions</p>
  <p>
      <?php foreach($data['plans'] as $plan => $featureLevel): ?>
        <?php echo $plan ?> (<?php echo $featureLevel ?>) <br/>
      <?php endforeach; ?>
  
    <?php endif; ?>
  </p>
  
  <p class="description">Note: You may have more subscription plans than feature levels. Cart66 uses feature levels, not the names of plans to manage permissions.</p>
  <input type="hidden" name="cart66_spreedly_meta_box_nonce" value="<?php echo wp_create_nonce('spreedly_meta_box') ?>" />
</div>
<?php else: ?>
  <div class="inside">
    <p>You have not configured any feature levels</p>
  </div>
<?php endif; ?>