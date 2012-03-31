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
      throw new Exception("Unable to load Gravity Form: $formId");
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
    if(is_array($this->_fields) && count($this->_fields)) {
      foreach($this->_fields as $field) {
        if(!is_array($field['inputs'])) {
          $standardFields[$field['id']] = $field['label'];
        }
      }
    }
    return $standardFields;
  }
  
  public function updateQuantity($entryId, $qtyFieldId, $qty) {
    global $wpdb;
    $entryTable = Cart66Common::getTableName('rg_lead_detail', '');
    $wpdb->update($entryTable, array('value' => $qty), array('lead_id' => $entryId, 'field_number' => $qtyFieldId));
  }
  
  public static function getGravityFormIdForEntry($entryId) {
    global $wpdb;
    $leads = Cart66Common::getTableName('rg_lead', '');
    $sql = "SELECT form_id from $leads where id = %d";
    $query = $wpdb->prepare($sql, $entryId);
    $formId = $wpdb->get_var($query);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SQL getting form id from entry id: $query");
    return $formId;
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

  public static function displayGravityForm($entryId, $textMode=false) {
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
                  // Do not display hidden fields
                  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The Field Type: " . $field['type']);
                  if($field['type'] != 'hidden') {
                    $value = RGFormsModel::get_lead_field_value($lead, $field);
                    $display_value = GFCommon::get_lead_field_display($field, $value);
                    if($display_empty_fields || !empty($display_value) || $display_value === "0"){
                        ?>
                        <tr>
                            <td colspan="2" class="entry-view-field-name"><?php echo esc_html(GFCommon::get_label($field))?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="entry-view-field-value<?php echo $is_last ? " lastrow" : ""?>"><?php echo empty($display_value) && $display_value !== "0" ? "&nbsp;" : $display_value ?></td>
                        </tr>
                        <?php
                    }
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
                if($display_empty_fields || !empty($display_value) || $display_value === "0") {
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
  
}