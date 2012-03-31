<h2>Cart66 Accounts</h2>

<?php
  if(CART66_PRO) {
    require_once(WP_PLUGIN_DIR. "/cart66/pro/admin/accounts.php");
  }
  else {
    echo '<p class="description">Account functionality is only available in <a href="http://cart66.com">Cart66 Professional</a></p>';
  }
