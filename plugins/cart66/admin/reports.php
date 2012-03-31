<h2>Cart66 Reports</h2>

<div class='wrap'>
  
  <h3 style="margin-top: 50px;">Product Sales</h3>
  
  <?php
  if(CART66_PRO) {
    require_once(WP_PLUGIN_DIR. "/cart66/pro/admin/reports.php");
  }
  else {
    echo '<p class="description">Sales reports are only available in <a href="http://cart66.com">Cart66 Professional</a>.</p>';
  }
  ?>
  
  <br/>
  
  <h3>Export Orders</h3>
  
  <?php
    $firstDayLastMonth =  date("m/1/Y", strtotime('-1 month'));
    $lastDayLastMonth =  date("m/d/Y", strtotime('-1 day', strtotime('+1 month', strtotime($firstDayLastMonth))));
  ?>
  <form action="" method="post" style="margin-bottom: 25px;">
    <input type="hidden" name="cart66-action" value="export_csv" />
    <table class="">
      <tr>
        <th style="text-align: left; padding: 0px 5px;">Start Date</th>
        <th style="text-align: left; padding: 0px 5px;">End Date</th>
        <th>&nbsp;</th>
      </tr>
      <tr>
        <td><input type="text" name="start_date" value="<?php echo $firstDayLastMonth; ?>" id="start_date" /></td>
        <td><input type="text" name="end_date" value="<?php echo $lastDayLastMonth; ?>" id="end_date" /></td>
        <td><input type="submit" name="submit" value="Export" id="submit" class="button-secondary" /></td>
      </tr>
      <tr>
        <td style="text-align: left; padding: 0px 5px;"><span class="description">mm/dd/yyyy</span></td>
        <td style="text-align: left; padding: 0px 5px;"><span class="description">mm/dd/yyyy</span></td>
        <td style="text-align: left; padding: 0px 5px;">&nbsp;</td>
      </tr>
    </table>
  </form>
</div>