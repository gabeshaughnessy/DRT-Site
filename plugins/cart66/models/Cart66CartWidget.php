<?php
class Cart66CartWidget extends WP_Widget {

  private $_items = array();
  
	public function Cart66CartWidget() {
    $widget_ops = array('classname' => 'Cart66CartWidget', 'description' => 'Sidebar shopping cart for Cart66' );
    $this->WP_Widget('Cart66CartWidget', 'Cart66 Shopping Cart', $widget_ops);
  }

	public function widget($args, $instance) {
    extract($args);			
    $data['title'] = $instance['title'];
    $data['shipping'] = isset($instance['shipping']) ? $instance['shipping'] : false;
    
    if(!Cart66Session::get('Cart66Cart')) {
      Cart66Session::set('Cart66Cart', new Cart66Cart());
    }
    $this->_items = Cart66Session::get('Cart66Cart')->getItems();
    $data['items'] = $this->_items;
    
    $data['cartPage'] = get_page_by_path('store/cart');
    $data['checkoutPage'] = get_page_by_path('store/checkout');
    $data['numItems'] = $this->countItems();
    $data['cartWidget'] = $this;
    $data['beforeWidget'] = $before_widget;
    $data['afterWidget'] = $after_widget;
    $data['beforeTitle'] = $before_title;
    $data['afterTitle'] = $after_title;
    
    if (isset($instance['standard_advanced']) && $instance['standard_advanced'] == 'advanced') { 
      echo Cart66Common::getView('views/cart-sidebar-advanced.php', $data); 
    } else {
      echo Cart66Common::getView('views/cart-sidebar.php', $data);
    }
  }

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['standard_advanced'] = $new_instance['standard_advanced'];
		$instance['shipping'] = !empty($new_instance['shipping']) ? 1 : 0;
		return $instance;
	}
  
  public function countItems() {
    if(Cart66Session::get('Cart66Cart')) {
      return Cart66Session::get('Cart66Cart')->countItems();
    }
  }
  
  public function getItems() {
    if(Cart66Session::get('Cart66Cart')) {
      return Cart66Session::get('Cart66Cart')->getItems();
    }
  }
  
  public function getSubTotal() {
    if(Cart66Session::get('Cart66Cart')) {
      return Cart66Session::get('Cart66Cart')->getSubTotal();
    }
  }
  
  public function getDiscountAmount() {
    if(Cart66Session::get('Cart66Cart')) {
      return Cart66Session::get('Cart66Cart')->getDiscountAmount();
    }
  }
  
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'standard_advanced' => 'standard', 'title' => '') );
    $shipping = isset( $instance['shipping'] ) ? (bool) $instance['shipping'] : false;
    $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
	?>
		<p>
		  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'cart66-cart'); ?>:
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('standard_advanced'); ?>"><?php _e('Choose Cart Widget Type:', 'cart66-cart'); ?></label>
			<select name="<?php echo $this->get_field_name('standard_advanced'); ?>" id="<?php echo $this->get_field_id('standard_advanced'); ?>" class="widefat widgetModeSelector">
				<option value="standard"<?php selected( $instance['standard_advanced'], 'standard' ); ?>><?php _e('Standard', 'cart66-cart'); ?></option>
				<option value="advanced"<?php selected( $instance['standard_advanced'], 'advanced' ); ?>><?php _e('Advanced', 'cart66-cart'); ?></option>
			</select>
		</p>
		<div class="CartAdvancedOptions" style="display:<?php echo ($instance['standard_advanced'] != 'advanced') ? 'none;' : 'block;'; ?>">
		  <p>
  	    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('shipping'); ?>" name="<?php echo $this->get_field_name('shipping'); ?>"<?php checked( $shipping ); ?> />
  		  <label for="<?php echo $this->get_field_id('shipping'); ?>"><?php _e( 'Show shipping in widget' ); ?></label><br />
  		  <span class=""><em>For Advanced widget only</em></span>
		  </p>
  	</div>
  	<script type="text/javascript">
          (function($){
            $(document).ready(function(){
              $(".widgetModeSelector").change(function(){
                if($(this).val()=="advanced"){
                  $(".CartAdvancedOptions").show();
                }
                else{
                  $(".CartAdvancedOptions").hide();
                }
              })
            })
          })(jQuery);
        </script>
<?php
	}

}