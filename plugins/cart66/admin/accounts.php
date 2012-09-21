<h2>Cart66 Accounts</h2>

<?php
  if(CART66_PRO) {
    require_once(CART66_PATH . "/pro/admin/accounts.php");
  }
  else {
    echo '<p class="description">' . __("Account functionality is only available in <a href='http://cart66.com'>Cart66 Professional</a>","cart66") . '</p>';
  }
