<?php
class Cart66GravityReader {
  
  /**
   * An array holding the Gravity Forms field array
   * @var array
   * @access private
   */
  private $_fields;
  
  public function __construct($formId=null) {
    $this->_fields = array();
    if(is_numeric($formId) && $formId > 0) {
      $this->load($formId);
    }
  }
  
  /**
   * Load the form fields for the given form id into the private $_fields array
   */
  public function load($formId) {
    global $wpdb;
    $metaTable = Cart66Common::getTableName('rg_form_meta', '');
    $sql = "select display_meta from $metaTable where form_id = $formId";
    $meta = unserialize($wpdb->get_var($sql));
    if(count($meta['fields'])) {
      $this->_fields = $meta['fields'];
    }
    else {
      throw new Cart66Exception("Unable to load Gravity Form: $formId");
    }
  }
  
  /**
   * Return an array of id/label combos for standard fields
   * 
   * @return array
   * @access public
   */
  public function getStandardFields() {
    $standardFields = array();
    //Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Fields: " . print_r($this->_fields, true));
    if(is_array($this->_fields) && count($this->_fields)) {
      foreach($this->_fields as $field) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Field Data: " . print_r($field, true));
        
        if(!isset($field['inputs']) || !is_array($field['inputs'])) {
          $standardFields[$field['id']] = $field['label'];
        }
        else {
          if($field['type'] == 'product' && (!isset($field['disableQuantity']) || $field['disableQuantity'] != 1) ) {
            if(isset($field['inputs']) && is_array($field['inputs'])) {
              foreach($field['inputs'] as $input) {
                if($input['label'] == 'Quantity') {
                  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Product Quantity ID: " . $input['id']);
                  $standardFields["'" . $input['id'] . "'"] = $field['label'] . ' Quantity';
                  break;
                }
              }
            }
          }
        }
        
      }
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Standard Fields: " . print_r($standardFields, true));
    return $standardFields;
  }
  
  public function updateQuantity($entryId, $qtyFieldId, $qty) {
    global $wpdb;
    $qtyFieldId = (float)$qtyFieldId;
    $entryTable = Cart66Common::getTableName('rg_lead_detail', '');
    self::updateGravityTotal($entryId, $qtyFieldId, $qty);
    $sql = "UPDATE `$entryTable` SET `value` = $qty WHERE `lead_id` = $entryId AND CAST(`field_number` as DECIMAL(5,2)) = $qtyFieldId";
    $wpdb->query($sql);
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Form Update Quantity :: lead_id=$entryId :: field_number=$qtyFieldId");
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Update Query: " . $wpdb->last_query);
    
    
  }
  
  public static function updateGravityTotal($entry_id, $quantity_field_id, $new_quantity) {
    global $wpdb;
    
    $form_id = self::getGravityFormIdForEntry($entry_id);
    $form = RGFormsModel::get_form_meta($form_id);
    
    if($total_field_id = self::getTotalFieldId($form)) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Total field id: $total_field_id :: Quantity field id: $quantity_field_id");
      $lead = RGFormsModel::get_lead($entry_id);
      $quantity = $lead["$quantity_field_id"];
      $total = $lead["$total_field_id"];
      $unit_price = $total/$quantity;
      $new_price = $unit_price * $new_quantity;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] 
        Total: $total
        Quantity: $quantity 
        New Quantity: $new_quantity
        Unit Price: $unit_price
        New Price: $new_price 
        The Lead :: " . print_r($lead, true));
      $entry_table = Cart66Common::getTableName('rg_lead_detail', '');
      $sql = "UPDATE `$entry_table` SET `value` = $new_price WHERE `lead_id` = $entry_id AND CAST(`field_number` as DECIMAL(5,2)) = $total_field_id";
      $wpdb->query($sql);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Update Query: " . $wpdb->last_query);
    }
  }
  
  /**
   * Return the Gravity Form field id for the total column or false if there is no total column.
   */
  public static function getTotalFieldId($form) {
    $total_field_id = false;
    // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Looking For Pricing Total In Gravity Forms Fields: " . print_r($form, true));
    if(isset($form['fields']) && is_array($form['fields'])) {
      $fields = $form['fields'];
      foreach($fields as $field) {
        if($field['type'] == 'total') {
          $total_field_id = $field['id'];
          break;
        }
      }
    }
    return $total_field_id;
  }
  
  public static function getGravityFormIdForEntry($entry_id) {
    global $wpdb;
    $leads = Cart66Common::getTableName('rg_lead', '');
    $sql = "SELECT form_id from $leads where id = %d";
    $query = $wpdb->prepare($sql, $entry_id);
    $form_id = $wpdb->get_var($query);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SQL getting form id from entry id: $query");
    return $form_id;
  }

  public static function getGravityIdForFieldName($form, $name='item-number') {
    $form = unserialize($form);
    $fields = $form['fields'];
    print_r($fields);
    foreach($fields as $field) {
      if(!empty($field['inputs'])) {
        $inputs = $field['inputs'];
        foreach($inputs as $input) {
          if($input['label'] == $name) {
            return $input['id'];
          }
        }
      }
      elseif($field['label'] == $name) {
        return $field['id'];
      }
    }
  }

  public static function displayGravityForm($entryId, $textMode=false, $email=false) {
    $out = '';
    $formId = self::getGravityFormIdForEntry($entryId);

    require_once(GFCommon::get_base_path() . "/entry_detail.php");
    $form = RGFormsModel::get_form_meta($formId);
    $lead = RGFormsModel::get_lead($entryId);

    if(!$textMode) {
      ob_start();
      echo '<table class="form-table entry-details"><tbody>';
      $count = 0;
      $field_count = sizeof($form["fields"]);
      foreach($form["fields"] as $field){
          $count++;
          $is_last = $count >= $field_count ? true : false;

          switch(RGFormsModel::get_input_type($field)){
              case "section" :
                  ?>
                  <tr>
                      <td colspan="2" class="entry-view-section-break<?php echo $is_last ? " lastrow" : ""?>"><?php echo esc_html(GFCommon::get_label($field))?></td>
                  </tr>
                  <?php
              break;

              case "captcha":
              case "html":
                  //ignore captcha field
              break;

              default :
                  if(GFCommon::is_product_field($field["type"])){
                      $has_product_fields = true;
                  }
                  $value = RGFormsModel::get_lead_field_value($lead, $field);
                  
                  $display_value = GFCommon::get_lead_field_display($field, $value, $lead["currency"]);

                  $display_value = apply_filters("gform_entry_field_value", $display_value, $field, $lead, $form);

                  if( (isset($display_empty_fields) && $display_empty_fields) || !empty($display_value) || $display_value === "0"){
                      $count++;
                      if(!isset($has_product_fields)) {
                        $has_product_fields = false;
                      }
                      $is_last = ($count >= $field_count && !$has_product_fields) ? true : false;
                      $last_row = $is_last ? " lastrow" : "";

                      $display_value =  empty($display_value) && $display_value !== "0" ? "&nbsp;" : $display_value;
                      if($email) {
                        $content = '
                        <tr>
                            <td style="color:#555;font-size:12px;padding:4px 7px;vertical-align:top;text-align:right" class="entry-view-field-name">' . esc_html(GFCommon::get_label($field)) . ': </td>
                            <td style="color:#555;font-size:12px;padding:4px 7px;vertical-align:top" class="entry-view-field-value' . $last_row . '">' . $display_value . '</td>
                        </tr>';
                      }
                      else {
                        $content = '
                        <tr>
                          <td colspan="2" class="entry-view-field-name">' . esc_html(GFCommon::get_label($field)) . '</td>
                        </tr>
                        <tr>
                          <td colspan="2" class="entry-view-field-value' . $last_row . '">' . $display_value . '</td>
                        </tr>';
                      }
                      
                      $content = apply_filters("gform_field_content", $content, $field, $value, $lead["id"], $form["id"]);

                      echo $content;

                  }
              break;
          }
      }
      echo '</tbody></table>';
      $out = ob_get_contents();
      ob_end_clean();
    }
    else {
      $count = 0;
      $field_count = sizeof($form["fields"]);

      foreach($form["fields"] as $field){
        $count++;
        $is_last = $count >= $field_count ? true : false;

        switch(RGFormsModel::get_input_type($field)){
            case "section" :
               $out .= "\t" . GFCommon::get_label($field) . "\n";
            break;

            case "captcha":
            case "html":
                //ignore captcha field
            break;

            default :
                $value = RGFormsModel::get_lead_field_value($lead, $field);
                $display_value = strip_tags(str_replace('</li>', "\t\t", GFCommon::get_lead_field_display($field, $value)));
                if(!empty($display_value) || $display_value === "0") {
                  $out .= "\t" . GFCommon::get_label($field) . ': ';
                  $out .= empty($display_value) && $display_value !== "0" ? " " : $display_value;
                  $out .= "\n";
                }
            break;
        }
      }
      $out .= "\n";
    }

    return $out;
  }
  
  public static function dailyGravityFormsOrphanedEntryRemoval() {
    if(class_exists('RGFormsModel')) {
      $forms = RGFormsModel::get_forms();
      $delete_leads = array();
      foreach($forms as $form) {
        $leads = RGFormsModel::get_leads($form->id, 0, 'DESC', '', 0, 30, null, null, false, null, null, $status='unpaid');
        foreach($leads as $lead) {
          if(strtotime($lead['date_created']) < strtotime('24 hours ago', Cart66Common::localTs())) {
            $delete_leads[] = $lead['id'];
          }
        }
      }
      RGFormsModel::delete_leads($delete_leads);
    }
  }
  
  /**
   * Return an array of Gravity Form ids that are linked to Cart66 products
   * 
   * @return array
   */
  public static function getIdsInUse() {
    global $wpdb;
    $products = Cart66Common::getTableName('products');
    $sql = "SELECT gravity_form_id as gfid from $products where gravity_form_id > 0";
    $ids = $wpdb->get_col($sql);
    return $ids;
  }
  
  /**
   * Return title of Gravity form
   *
   *
   * @return string
   */
   public static function getFormTitle($form_id){
     $form = RGFormsModel::get_form($form_id);
     return $form->title;
   }
   
   public static function getFormValuesArray($form_id) {
     $values = array();
     $form = RGFormsModel::get_form_meta($form_id);
     $i=0;
     if(is_array($form['fields'])) {
       foreach($form['fields'] as $field) {
         if(isset($field['choices']) && is_array($field['choices'])) {
           foreach($field['choices'] as $choice) {
             $values[$i][] = str_replace(' ', '', $choice['value']);
           }
           $i++;
         }
       }
     }
     
     return $values;
   }
  
  public static function getPrice($entry_id) {
    $price = null;
    if(class_exists('GFCommon')) {
      $form_id = self::getGravityFormIdForEntry($entry_id);
      $form = RGFormsModel::get_form_meta($form_id);
      $lead = RGFormsModel::get_lead($entry_id);
      $price = GFCommon::get_order_total($form, $lead);
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Product Price: $price :: Form id: $form_id :: Entry id: $entry_id");
    return $price;
  }
  
}