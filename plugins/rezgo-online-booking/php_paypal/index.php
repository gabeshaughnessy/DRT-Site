<?php

// jq 22 jun 2009; index redirect to prevent file listing
header("Location: http://".$_SERVER['HTTP_HOST']);
exit;

?>