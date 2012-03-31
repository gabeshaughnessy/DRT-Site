<?php
  $product = new Cart66Product();
  // $products = $product->getNonSubscriptionProducts();
  $products = $product->getModels('where id>0', 'order by name');
  if(CART66_PRO && count($products)) {
    $today = date('m/d/Y');
    $salesGrandTotal= 0;
    $incomeGrandTotal = 0;
    ?>
    <table class="Cart66TableMed">
      <tr>
        <th colspan="2">Product Name</th>
        <?php $thisMonth = date('m/1/Y'); ?>
        <?php for ($i=5; $i >= 0; $i--): ?>
          <th colspan="2"><?php echo date('M, Y', strtotime("$thisMonth - $i months")); ?></th>
        <?php endfor; ?>
        <th colspan="2" style="background-color: #EEE;">Total Sales</th>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
        <?php for ($i=5; $i >= 0; $i--): ?>
          <td style="font-weight: bold; border-left: 1px solid #ccc;">sales</td>
          <td style="font-weight: bold; background-color: #eee;">income</td>
        <?php endfor; ?>
        <td style="font-weight: bold; background-color: #ddd; border-left: 1px solid #ccc;">sales</td>
        <td style="font-weight: bold; background-color: #ddd;">income</td>
      </tr>
      <?php foreach($products as $p): ?>
        <tr>
          <td colspan="2" style="border-right: 1px solid #ccc;"><?php echo $p->name; ?></td>
          <?php for ($i=5; $i >= 0; $i--): ?>
            <?php 
              if(!isset($totals) || !is_array($totals)) { $totals = array(); }
              if(isset($totals[$i])) {
                $totals[$i] += $p->getSalesForMonth( date('n', strtotime("$thisMonth - $i months")), date('Y', strtotime("$thisMonth - $i months")) ); 
              }
              else {
                $totals[$i] = $p->getSalesForMonth( date('n', strtotime("$thisMonth - $i months")), date('Y', strtotime("$thisMonth - $i months")) );
              }
            ?>
            <?php 
              if(!isset($income) || !is_array($income)) { $income = array(); }
              if(isset($income[$i])) {
                $income[$i] += $p->getIncomeForMonth( date('n', strtotime("$thisMonth - $i months")), date('Y', strtotime("$thisMonth - $i months")) ); 
              }
              else {
                $income[$i] = $p->getIncomeForMonth( date('n', strtotime("$thisMonth - $i months")), date('Y', strtotime("$thisMonth - $i months")) );
              }
            ?>
            <td style="text-align: right;">
              <?php echo $p->getSalesForMonth( date('n', strtotime("$thisMonth - $i months")), date('Y', strtotime("$thisMonth - $i months")) ); ?>
            </td>
            <td style="text-align: right; background-color: #eee; border-right: 1px solid #CCC;">
              <?php 
                echo CURRENCY_SYMBOL;
                $money = $p->getIncomeForMonth( date('n', strtotime("$thisMonth - $i months")), date('Y', strtotime("$thisMonth - $i months")) ); 
                echo number_format($money, 2);
              ?>
            </td>
          <?php endfor; ?>
          <?php $salesGrandTotal += $p->getSalesTotal(); ?>
          <?php $incomeGrandTotal += $p->getIncomeTotal(); ?>
          <td style="text-align: right; font-weight: bold; background-color: #ddd;"><?php echo $p->getSalesTotal(); ?></td>
          <td style="text-align: right; font-weight: bold; background-color: #ddd;">
            <?php 
              echo CURRENCY_SYMBOL;
              echo number_format($p->getIncomeTotal(), 2); 
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="2">&nbsp;</td>
        <?php for($i=count($income); $i>0; $i--): ?>
          <td colspan="1" style="text-align: center; font-weight: bold; background-color: #ddd;"><?php echo $totals[$i-1]; ?></td>
          <td colspan="1" style="text-align: center; font-weight: bold; background-color: #ddd; border-right: 1px solid #ccc;">
            <?php 
              echo CURRENCY_SYMBOL;
              echo number_format($income[$i-1], 2); 
            ?>
          </td>
        <?php endfor; ?>
        <td style="text-align: right; background-color: #ddd; font-weight: bold;"><?php echo $salesGrandTotal; ?></td>
        <td style="text-align: right; background-color: #ddd; font-weight: bold;">
          <?php 
            echo CURRENCY_SYMBOL;
            echo number_format($incomeGrandTotal, 2); 
          ?>
        </td>
      </tr>
    </table>
    <?php
  }
  else {
    echo '<p>Product sales reports are only available in <a href="http://cart66.com">Cart66 Professional</a></p>';
  }
?>

<?php if(CART66_PRO): ?>
<h3 style="margin-top: 40px;">Daily Income Totals</h3>

<?php
  global $wpdb;
  $data = array();
  for($i=0; $i<42; $i++) {
    $dayStart = date('Y-m-d 00:00:00', strtotime('today -' . $i . ' days'));
    $dayEnd   = date('Y-m-d 00:00:00', strtotime("$dayStart +1 day"));
    $orders = Cart66Common::getTableName('orders');
    $sql = "SELECT sum(`total`) from $orders where ordered_on > '$dayStart' AND ordered_on < '$dayEnd'";
    $dailyTotal = $wpdb->get_var($sql);
    $data['days'][$i] = date('m/d/Y', strtotime($dayStart));
    $data['totals'][$i] = $dailyTotal;
  }
?>
<table class="Cart66TableMed">
  <?php for($i=0; $i<count($data['days']); $i++): ?>
    <?php if($i % 7 == 0) { echo '<tr>'; } ?>
    <td>
      <span style="color: #999; font-size: 11px;"><?php echo date('m/d/Y D', strtotime($data['days'][$i])); ?></span><br/>
      <?php echo CURRENCY_SYMBOL . number_format($data['totals'][$i], 2); ?>
    </td>
    <?php if($i % 7 == 6) { echo '</tr>'; } ?>
  <?php endfor; ?>
</table>
<?php endif; ?>