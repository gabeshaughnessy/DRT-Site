<?php
$url = trim($url);
$saleAmt = $order->subtotal - $order->discount_amount;
$saleAmt = number_format($saleAmt, 2, '.', '');
$url = str_replace('idev_saleamt=XXX', 'idev_saleamt=' . $saleAmt, $url);
$url = str_replace('idev_ordernum=XXX', 'idev_ordernum=' . $order->trans_id, $url);
$url .= '&ip_address=' . $_SERVER['REMOTE_ADDR'];
Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Commission notification sent to: $url");
Cart66Common::curl($url);