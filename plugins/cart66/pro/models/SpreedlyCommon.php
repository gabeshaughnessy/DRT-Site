<?php
class SpreedlyCommon {
  
	public static $siteName;
  public static $apiToken;
	public static $baseUri;
  
  
  /**
   * This function must be called before any others to initialize the Spreedly account api details
   */
  public static function init($siteName, $apiToken) {
    self::$siteName = $siteName;
    self::$apiToken = $apiToken;
    self::$baseUri = "https://spreedly.com/api/v4/$siteName";
  }
  
  /**
   * Convert an array into XML
   * 
   * Example use: echo buildXml($products,'products');
   * 
   * @param array $array       - The array you wish to convert into a XML structure.
   * @param string $name       - The name you wish to enclose the array in, the 'parent' tag for XML.
   * @param bool $standalone   - This will add a document header to identify this solely as a XML document.
   * @param bool $beginning    - INTERNAL USE... DO NOT USE!
   * @param int $nested        - INTERNAL USE... DO NOT USE! The nest level for pretty formatting
   * @return Gives a string output in a XML structure
  */
 
  public static function buildXml($array, $name, $space='', $standalone=false, $beginning=true, $nested=0) {
    $output = '';
    if ($beginning) {
      if($standalone) header("content-type:text/xml;charset=utf-8");
      if(!isset($output)) { $output = ''; }
      if($standalone) $output .= '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>' . "\n";
      if(!empty($space)) {
        $output .= '<' . $name . ' xmlns="' . $space . '">' . "\n";
      }
      else {
        $output .= '<' . $name . '>' . "\n";
      }
      $nested = 0;
    }

    // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
    $ArrayNumberPrefix = 'ARRAY_NUMBER_';

     foreach ($array as $root=>$child) {
      if (is_array($child)) {
        $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
        $nested++;
        $output .= self::buildXml($child,NULL,NULL,NULL,FALSE, $nested);
        $nested--;
        $output .= str_repeat(" ", (2 * $nested)) . '  </' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
      }
      else {
        if(!isset($output)) { $output = ''; }
        $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' .
          $child . '</' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
      }
    }

    if ($beginning) $output .= '</' . $name . '>';

    return $output;
  }
  
  
  public static function curlRequest($url, $method="get", $data=null) {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] curl request info: $url\nMethod: $method\nData: $data");
		$ch = curl_init(self::$baseUri.$url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, self::$apiToken.":X");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: text/xml",
				"Accept: text/xml"
			));

		switch ($method) {
		case "post":
			if ($data) {
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			} else {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			}
			break;
		case "delete":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
		case "put":
			$fh = fopen("php://memory", "rw");
			fwrite($fh, $data);
			rewind($fh);
			curl_setopt($ch, CURLOPT_INFILE, $fh);
			curl_setopt($ch, CURLOPT_INFILESIZE, strlen($data));
			curl_setopt($ch, CURLOPT_PUT, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Content-Type: text/xml",
					"Accept: text/xml",
					"Expect:"
				));
			break;
		default:
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			break;
		}

		$result = new StdClass();
		$result->response = curl_exec($ch);
		$result->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		return $result;
	}
	
	public static function camelToDash($val) {
    return preg_replace_callback('/[A-Z]/', 
      create_function('$match', 'return "-" . strtolower($match[0]);'),
      $val);
  }
	
  /**
   * Send XML content via post to the vault and receive an xml string response
   * 
   * @param string XML to send to the vault
   * @return string XML received from the vault
   */
   /*
  public static function post($xml, $url) {
    $domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'http://oliver.phpoet.com';
    $header = "POST $url HTTP/1.1\n"; 
    $header.= "Host: $domain\n"; 
    $header.= "Content-Length: " . strlen($xml) . "\n"; 
    $header.= "Content-type: text/xml; charset=UTF8\n"; 
    $header.= "Connection: close; Keep-Alive\n\n"; 
    $header.= $xml;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);
    $response = curl_exec($ch); 
    curl_close($ch);
    return $response;
  }
  */
}