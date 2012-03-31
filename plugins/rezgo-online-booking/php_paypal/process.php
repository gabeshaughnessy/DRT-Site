<?php
/*
 * process.php
 *
 * PHP Toolkit for PayPal v0.51
 * http://www.paypal.com/pdn
 *
 * Copyright (c) 2004 PayPal Inc
 *
 * Released under Common Public License 1.0
 * http://opensource.org/licenses/cpl.php
 *
 */

//Configuration Files
include_once('includes/config.inc.php'); 
include_once('includes/global_config.inc.php');
?> 

<html>
<head><title>Pay with a PayPal account</title></head>
<body onLoad="document.paypal_form.submit();">
<form method="post" name="paypal_form" action="<?=$paypal[url]?>">

<?php
//show paypal hidden variables
//$paypal[site_url]="https://".$domain.".booking-server.com/";
//$paypal[success_url]="booking_complete_2.php";
//echo "<br>success url = ".$paypal[success_url];
showVariables();
?> 
<center><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="333333">Processing Transaction . . . </font></center>

</form>

<script>document.paypal_form.submit();</script>

</body>   
</html>
