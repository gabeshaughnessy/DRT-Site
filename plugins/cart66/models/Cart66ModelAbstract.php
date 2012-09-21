<?php
abstract class Cart66ModelAbstract extends Cart66BaseModelAbstract {
  
  protected $_tableName;
  protected $_db;

  
  public function __construct($id=null) {
    global $wpdb;
    $this->_db = $wpdb;
    $this->_data = array();
    $this->_initProperties();
    if($id > 0 && is_numeric($id)) {
      $this->load($id);
    }
    $this->_errors = array();
  }
  
  public function getDb() {
    return $this->_db;
  }
  
  public function load($id) {
    if(is_numeric($id) && $id > 0) {
      $sql = 'SELECT * from ' . $this->_tableName . " WHERE id='$id'";
      if($data = $this->_db->get_row($sql, ARRAY_A)) {
        $this->setData($data);
        return true;
      }
    }
    return false;
  }
  
  /**
   * Reset all the values to those that are stored in the database
   * 
   * @return void
   */
  public function refresh() {
    $id = $this->id;
    if(is_numeric($id) && $id > 0) {
      $this->load($id);
    }
  }
  
  public function deleteMe() {
    if($this->id > 0) {
      $sql = "DELETE from " . $this->_tableName . " WHERE id='" . $this->id . "'";
      $this->_db->query($sql);
    }
  }
  
  /**
   * Return an array of models
   */
  public function getModels($where=null, $orderBy=null, $limit=null, $tableName=null, $id=null) {
    $models = array();
    global $wpdb;
    if(isset($where)) {
      $where = ' ' . $where;
    }
    if(isset($orderBy)) {
      $orderBy = ' ' . $orderBy;
    }
    if(isset($limit)) {
      $limit = ' limit ' . $limit;
    }
    if($tableName == null){
      $tableName = $this->_tableName;
    }
    if($id == null) {
      $id = 'id';
    }
    $sql = 'SELECT ' . $id . ' FROM ' . $tableName . $where . $orderBy . $limit;
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " getModels: $sql");
    $ids = $wpdb->get_col($sql);
    foreach($ids as $id) {
      $className = get_class($this);
      $models[] = new $className($id);
    }
    return $models;
  }
  
  public function getModelsNoClass($where=null, $orderBy=null, $limit=null, $tableName=null, $id=null) {
    $models = array();
    global $wpdb;
    if(isset($where)) {
      $where = ' ' . $where;
    }
    if(isset($orderBy)) {
      $orderBy = ' ' . $orderBy;
    }
    if(isset($limit)) {
      $limit = ' limit ' . $limit;
    }
    if($tableName == null){
      $tableName = $this->_tableName;
    }
    if($id == null) {
      $id = 'id';
    }
    $sql = 'SELECT ' . $id . ' FROM ' . $tableName . $where . $orderBy . $limit;
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " getModels: $sql");
    $ids = $wpdb->get_results($sql);
    foreach($ids as $id) {
      $models[] = $id;
    }
    return $models;
  }
  
  /**
   * Return the first matching model or false if no model could be found.
   */
  public function getOne($where='where id>0', $orderBy=null) {
    $model = false;
    $models = $this->getModels($where, $orderBy, 1);
    if(count($models)) {
      $model = $models[0];
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Get one is returning: " . count($models));
    }
    return $model;
  }
  
  public function save() {
    foreach($this->_data as $key => $value) {
      if(is_scalar($value)) {
        $this->_data[$key] = stripslashes($value);
      }
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " Saving Data: " . print_r($this->_data, true));
    return $this->_data['id'] >= 1 ? $this->_update() : $this->_insert();
  }
  
  public function getLastQuery() {
    return $this->_db->last_query;
  }
  
  protected function _insert() {
    if(isset($this->_data['created_at'])) {
      $this->_data['created_at'] = date('Y-m-d H:i:s', Cart66Common::localTs());
    }
    if(isset($this->_data['updated_at'])) {
      $this->_data['updated_at'] = date('Y-m-d H:i:s', Cart66Common::localTs());
    }
    $this->_db->insert($this->_tableName, $this->_data);
    $this->id = $this->_db->insert_id;
    return $this->id;
  }
  
  protected function _update() {
    if(isset($this->_data['updated_at'])) {
      $this->_data['updated_at'] = date('Y-m-d H:i:s',  Cart66Common::localTs());
    }
    $this->_db->update($this->_tableName, $this->_data, array('id' => $this->_data['id']));
    return $this->id;
  }
  

  protected function _initProperties() {
  	$query = 'describe ' . $this->_tableName;
  	$metadata = $this->_db->get_results($query);
  	foreach($metadata as $row) {
  	  $colName = $row->Field;
  	  if($colName == 'id') {
  	    $this->_data[$colName] = null;
  	  }
  	  else {
  	    $this->_data[$colName] = '';
  	  }
  	}
  }
  
  public function __wakeup(){
    global $wpdb;
    $this->_db = $wpdb;
  }
  
}
