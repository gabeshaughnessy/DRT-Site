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
    if(isset($_SESSION['Cart66Cart']) && is_object($_SESSION['Cart66Cart'] == 'Cart66Cart')) {
      $this->_items = $_SESSION['Cart66Cart']->getItems();
      $data['items'] = $this->_items;
    }
    $data['cartPage'] = get_page_by_path('store/cart');
    $data['checkoutPage'] = get_page_by_path('store/checkout');
    $data['numItems'] = $this->countItems();
    $data['cartWidget'] = $this;
    $data['beforeWidget'] = $before_widget;
    $data['afterWidget'] = $after_widget;
    $data['beforeTitle'] = $before_title;
    $data['afterTitle'] = $after_title;
    
    echo Cart66Common::getView('views/cart-sidebar.php', $data);
  }
  
  public function update($newInstance, $oldInstance) {
    $instance = $oldInstance;
    $instance['title'] = strip_tags($newInstance['title']);
    return $instance;
  }
  
  public function getItems() {
    if(isset($_SESSION['Cart66Cart'])) {
      return $_SESSION['Cart66Cart']->getItems();
    }
  }
  
  public function countItems() {
    if(isset($_SESSION['Cart66Cart'])) {
      return $_SESSION['Cart66Cart']->countItems();
    }
  }
  
  public function getSubTotal() {
    if(isset($_SESSION['Cart66Cart'])) {
      return $_SESSION['Cart66Cart']->getSubTotal();
    }
  }
  
  public function getDiscountAmount() {
    if(isset($_SESSION['Cart66Cart'])) {
      return $_SESSION['Cart66Cart']->getDiscountAmount();
    }
  }
  
  public function form($instance) {
    $title = esc_attr($instance['title']);
    ?>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cart66-cart'); ?>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
    <?php
  }
}