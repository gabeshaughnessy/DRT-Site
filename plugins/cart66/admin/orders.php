<?php 
  global $wpdb;
  $order = new Cart66Order();
  $status = '';
  if(isset($_GET['status'])) {
    $status = $_GET['status'];
  }
?>
<h2><?php _e('Cart66 Orders', 'cart66'); ?></h2>

<div class='wrap' style='margin-bottom:60px;'>
  
 <?php 
    $setting = new Cart66Setting();
    $stats = trim(Cart66Setting::getValue('status_options'));
    if(strlen($stats) >= 1 ) {
      $stats = explode(',', $stats);
  ?>
      <p style="float: left; clear: both; margin-top:0; padding-top: 0;"><?php _e( 'Filter Orders by Status' , 'cart66' ); ?>:
        <?php
          foreach($stats as $s) {
            $s = trim(strtolower($s));
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order status query: WHERE status='$s'");
            $tmpRows = $order->getOrderRows("WHERE status='$s'");            
            $n = count($tmpRows);
            if($n > 0) {
              $url = Cart66Common::replaceQueryString("page=cart66_admin&status=$s");
              echo "<a href=\"$url\">" . ucwords($s) . " (" . count($tmpRows) . ")</a> &nbsp;|&nbsp; ";
            }
            else {
              echo ucwords($s) ." (0) &nbsp;|&nbsp;";
            }
          }
        ?>
        <a href="?page=cart66_admin">All (<?php echo count($order->getOrderRows()) ?>)</a>
      </p>
  <?php
    }
    else {
      echo "<p style=\"float: left; clear: both; color: #999; font-size: 11px; both; margin-top:0; padding-top: 0;\">" .
        __("You should consider setting order status options such as new and complete on the 
        <a href='?page=cart66-settings'>Cart66 Settings page</a>.","cart66") . "</p>";
    }
  ?>
</div>
<div class="wrap">
  
  <table class="widefat Cart66HighlightTable" id="orders_table">
    <tr>
      <thead>
      	<tr>
      	  <th><?php _e('ID', 'cart66'); ?></th>
    			<th><?php _e( 'Order Number' , 'cart66' ); ?></th>
    			<th><?php _e( 'Name' , 'cart66' ); ?></th>
    			<th><?php _e( 'Name' , 'cart66' ); ?></th>
      		<th><?php _e( 'Amount' , 'cart66' ); ?></th>
      		<th><?php _e( 'Date' , 'cart66' ); ?></th>
          <th><?php _e( 'Delivery' , 'cart66' ); ?></th>
      		<th><?php _e( 'Status' , 'cart66' ); ?></th>
      		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
      	</tr>
      </thead>
      <tfoot>
      	<tr>
      		<th><?php _e('ID', 'cart66'); ?></th>
    			<th><?php _e( 'Order Number' , 'cart66' ); ?></th>
    			<th><?php _e( 'Name' , 'cart66' ); ?></th>
    			<th><?php _e( 'Name' , 'cart66' ); ?></th>
      		<th><?php _e( 'Amount' , 'cart66' ); ?></th>
      		<th><?php _e( 'Date' , 'cart66' ); ?></th>
          <th><?php _e( 'Delivery' , 'cart66' ); ?></th>
      		<th><?php _e( 'Status' , 'cart66' ); ?></th>
      		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
      	</tr>
      </tfoot>
    </tr>
  </table>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      var orders_table = $('#orders_table').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "bPagination": true,
        "iDisplayLength": 30,
        "aLengthMenu": [[30, 60, 150, -1], [30, 60, 150, "All"]],
        "sPaginationType": "bootstrap",
        "bAutoWidth": false,
        "sAjaxSource": ajaxurl + "?action=orders_table",
        "aaSorting": [[5, 'desc']],
        "aoColumns": [
          { "bVisible": false },
          { "bSortable": true, "fnRender": function(oObj) { return '<a href="?page=cart66_admin&task=view&id=' + oObj.aData[0] + '">' + oObj.aData[1] + '</a>' }},
          { "fnRender": function(oObj) { return oObj.aData[2] + ' ' + oObj.aData[3] }},
          { "bVisible": false },
          { "fnRender": function(oObj) { return '<?php echo CART66_CURRENCY_SYMBOL ?>' + oObj.aData[4] }},
          { "bSearchable": false },
          { "bSearchable": false },
          null,
          { "bSearchable": false, "bSortable": false, "fnRender": function(oObj) { return oObj.aData[8] != "" ? '<a href="#" onClick="printView(' + oObj.aData[0] + ')" id="print_version_' + oObj.aData[0] + '"><?php _e( "Receipt" , "cart66" ); ?></a> | <a href="?page=cart66_admin&task=view&id=' + oObj.aData[0] + '"><?php _e( "View" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66_admin&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "cart66" ); ?></a> | <a href="#" class="Cart66ViewOrderNote" rel="note_' + oObj.aData[0] + '"><?php _e( "Notes" , "cart66" ); ?></a><div class="Cart66OrderNote" id="note_' + oObj.aData[0] + '"><a href="#" class="Cart66CloseNoteView" rel="note_' + oObj.aData[0] + '" alt="Close Notes Window"><img src="<?php echo CART66_URL ?>/images/window-close.png" /></a><h3>' + oObj.aData[1] + '</h3><p>' + oObj.aData[8] + '</p></div>' : '<a href="#" onClick="printView(' + oObj.aData[0] + ')" id="print_version_' + oObj.aData[0] + '"><?php _e( "Receipt" , "cart66" ); ?></a> | <a href="?page=cart66_admin&task=view&id=' + oObj.aData[0] + '"><?php _e( "View" , "cart66" ); ?></a> | <a class="delete" href="?page=cart66_admin&task=delete&id=' + oObj.aData[0] + '"><?php _e( "Delete" , "cart66" ); ?></a>'; },"aTargets": [ 9 ] }
        ],
        "oLanguage": { 
          "sZeroRecords": "<?php _e('No matching Orders found', 'cart66'); ?>", 
          "sSearch": "<?php _e('Search', 'cart66'); ?>:", 
          "sInfo": "<?php _e('Showing', 'cart66'); ?> _START_ <?php _e('to', 'cart66'); ?> _END_ <?php _e('of', 'cart66'); ?> _TOTAL_ <?php _e('entries', 'cart66'); ?>", 
          "sInfoEmpty": "<?php _e('Showing 0 to 0 of 0 entries', 'cart66'); ?>", 
          "oPaginate": {
            "sNext": "<?php _e('Next', 'cart66'); ?>", 
            "sPrevious": "<?php _e('Previous', 'cart66'); ?>", 
            "sLast": "<?php _e('Last', 'cart66'); ?>", 
            "sFirst": "<?php _e('First', 'cart66'); ?>"
          }, 
          "sInfoFiltered": "(<?php _e('filtered from', 'cart66'); ?> _MAX_ <?php _e('total entries', 'cart66'); ?>)", 
          "sLengthMenu": "<?php _e('Show', 'cart66'); ?> _MENU_ <?php _e('entries', 'cart66'); ?>", 
          "sLoadingRecords": "<?php _e('Loading', 'cart66'); ?>...", 
          "sProcessing": "<?php _e('Processing', 'cart66'); ?>..." 
        }
      }).css('width','');
      $('.Cart66ViewOrderNote').live('click', function () {
        $(".Cart66OrderNote").hide();
        var id = $(this).attr('rel');
        $('#' + id).show();
        return false;
      });
      $('.Cart66CloseNoteView').live('click', function () {
        var id = $(this).attr('rel');
        $('#' + id).hide();
        return false;
      });
      $('.delete').live('click', function() {
        return confirm('Are you sure you want to delete this item?');
      });
      orders_table.fnFilter( '<?php echo $status ?>', 7 );
    } );    
  })(jQuery);
  function printView(id) {
    var url = ajaxurl + '?action=print_view&order_id=' + id
    myWindow = window.open(url,"Your_Receipt","resizable=yes,scrollbars=yes,width=550,height=700");
    return false;
  }
</script>
