<?php
class Cart66Log {
  
  public static function getLogFilePath() {
    $logFilePath = CART66_PATH . '/log.txt';
    return $logFilePath;
  }
  
  /**
   * Attempt to create a log file in the plugins/cart66 directory
   * Returns the path to the log file. If the file could not be created a Cart66Exception is thrown.
   *
   * @return string
   * @throws Cart66Exception on failure to create log file
   */
  public static function createLogFile() {
    $logDirPath = CART66_PATH;
    $logFilePath = self::getLogFilePath();
    
    if(file_exists($logDirPath)) {
      if(is_writable($logDirPath)) {
        @fclose(fopen($logFilePath, 'a'));
        if(!is_writable($logFilePath)) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to create log file. $logFilePath");
          throw new Cart66Exception("Unable to create log file. $logFilePath");
        }
      }
      else {
        throw new Cart66Exception("Log file directory is not writable. $logDirPath");
      }
    }
    else {
      throw new Cart66Exception("Log file directory does not exist. $logDirPath");
    }
    
    
    return $logFilePath;
  }
  
  public static function exists() {
    $exists = false;
    $logFilePath = self::getLogFilePath();
    if(file_exists($logFilePath) && filesize($logFilePath) > 0) {
      $exists = true;
    }
    return $exists;
  }
  
  public static function getCartSettings() {
    global $wpdb;
    $out = "\n=====================\nCART SETTINGS\n=====================\n\n";
    $cartTable = Cart66Common::getTableName('cart_settings');
    $sql = "SELECT * from $cartTable order by `key`";
    $results = $wpdb->get_results($sql, OBJECT);
    foreach($results as $row) {
      $out .= $row->key . ' = ' . $row->value . "\n";
    }
    return $out;
  }
  
}