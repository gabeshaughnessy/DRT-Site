<?php 
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'main_settings';
?>
<div class="wrap">
  <div id="cart66-settings-icon" class="icon32" style="background:url(<?php echo CART66_URL . '/images/cart66_logo_sm.gif' ?>);background-size:32px 32px;background-repeat:no-repeat;"><br></div>
  <h2 class="nav-tab-wrapper">
  <?php foreach($data['setting']->getSettingsTabs() as $tab_key => $tab_caption): ?>
    <?php $active = $tab == $tab_key ? 'nav-tab-active' : ''; ?>
    <a class="nav-tab <?php echo $active; ?>" href="?page=cart66-settings&tab=<?php echo $tab_key; ?>"><?php echo $tab_caption['tab']; ?></a>
  <?php endforeach; ?>
    <span class="settings-version-number"><?php echo CART66_VERSION_NUMBER; ?></span>
  </h2>
  <?php do_settings_sections($tab); ?>
</div>