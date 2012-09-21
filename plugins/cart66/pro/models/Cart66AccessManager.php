<?php
class Cart66AccessManager {

  /**
   * Return link to the page with the custom field
   *   cart66_member = denied
   * If no such page exists, return link to homepage of site
   */
  public static function getDeniedLink() {
    $deniedLink = get_bloginfo('url');
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=denied');
    if(count($pgs)) {
      $deniedLink = get_permalink($pgs[0]->ID);
    }
    return $deniedLink;
  }

  /**
   * Return an array of page ids with the custom field 
   *   cart66_access = private
   */
  public static function getPrivatePageIds() {
    $private_posts = array();
    $query = new WP_Query(array(
      'post_type' => 'any', 
      'meta_key' => 'cart66_access', 
      'meta_value' => 'private', 
      'posts_per_page' => -1, 
      'ignore_sticky_posts' => 1,
      'tax_query' => array(
        'include_children' => 1
      )
    ));
    foreach($query->posts as $post) {
      $private_posts[] = $post->ID;
    }
    return $private_posts;
  }

  /**
   * If the visitor is not a logged in, check if the page that is being accessed is private. 
   * If so, redirect to the login page or the access denied page. 
   */
  public static function verifyPageAccessRights($pageId) {
    if(!Cart66Common::isLoggedIn()) {
      $privatePages = self::getPrivatePageIds();
      $deniedLink = self::getDeniedLink();
      if(in_array($pageId, $privatePages)) {
        Cart66Session::set('Cart66AccessDeniedRedirect', Cart66Common::getCurrentPageUrl());
        wp_redirect($deniedLink);
        exit;
      }
    }
  }
  
  public static function getRequiredFeatureLevelsForPage($pageId) {
    $requiredFeatureLevels = array();
    $featureLevels = get_post_meta($pageId, '_cart66_subscription', true);
    if(!empty($featureLevels)) {
      $requiredFeatureLevels = explode(',', str_replace(' ', '', $featureLevels));
    }
    return $requiredFeatureLevels;
  }

  /**
   * Return an array of page ids where the custom field is set to 
   *   cart66_access = guest only
   * If no such pages exist, return an empty array
   *
   * @return array
   */
  public static function getGuestOnlyPageIds() {
    $guestPageIds = array();
    $pages = get_pages(array('meta_key'=>'cart66_access', 'meta_value' => 'guest', 'hierarchical' => 0));
    foreach($pages as $pg) {
      $guestPageIds[] = $pg->ID;
    }
    return $guestPageIds;
  }

  /**
   * Hide pages that the logged in user may not access
   */
  public static function hideSubscriptionPages($featureLevel, $activeAccount=false) {
    global $wpdb;
    $hiddenPages = array();
    $posts = Cart66Common::getTableName('posts', '');
    $meta = Cart66Common::getTableName('postmeta', '');
    $sql = "SELECT post_id, meta_value from $meta where meta_key='_cart66_subscription'";
    $results = $wpdb->get_results($sql);
    if(count($results)) {
      foreach($results as $m) {
        $requiredFeatureLevels = explode(',', $m->meta_value);
        if(!in_array($featureLevel, $requiredFeatureLevels) || !$activeAccount) {
          $hiddenPages[] = $m->post_id;
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Excluding page: " . $m->post_id);
        }
      }
    }
    return $hiddenPages;
  }
  
  /**
   * Return the link to the member home page as designated with the custom field:
   *   cart66_member = home
   * If no home page is set, return false.
   * 
   * @return string Link to member home page or false
   */
  public static function getMemberHomePageLink() {
    $url = false;
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=home');
    if(count($pgs)) {
      $url = get_permalink($pgs[0]->ID);
    }
    return $url;
  }
  
  /**
   * Return the link to the member log in page as designated with the custom field:
   *   cart66_member = login
   * If no log in page is set, return false.
   * 
   * @return string Link to member log in page or false
   */
  public static function getLogInLink() {
    $url = false;
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=cart66_member&meta_value=login');
    if(count($pgs)) {
      $url = get_permalink($pgs[0]->ID);
    }
    return $url;
  }

}
