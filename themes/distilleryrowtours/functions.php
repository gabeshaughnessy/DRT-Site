<?php 
// Twitter Bootstrap does some heavy lifting for this theme, take a look over there for Javascript functonality and any styles: http://twitter.github.com/bootstrap

// Theme Location Global variable.
define('THEMELOCATION', get_bloginfo('stylesheet_directory'), true);

// WordPress Post Thumbnail Support
if (function_exists('add_theme_support')) {
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(200, 100, true);
    add_image_size('two-col', 140, 140, true);
    add_image_size('three-col', 280, 220, true);
    add_image_size('four-col', 300, 300, true);
    add_image_size('eight-col', 610, 420, true);
    add_image_size('featured', 375, 600, false);
    add_image_size('pitch', 960, 400, true);
}
//and post thumbnail column in the admin menu
require_once('thumb_column.php');

//Add Theme Options


/*
 * Helper function to return the theme option value. If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * This code allows the theme to work without errors if the Options Framework plugin has been disabled.
 */
if ( !function_exists( 'of_get_option' ) ) {
function of_get_option($name, $default = false) {
    $optionsframework_settings = get_option('optionsframework');
    // Gets the unique option id
    $option_name = $optionsframework_settings['id'];
    if ( get_option($option_name) ) {
        $options = get_option($option_name);
    }
    if ( isset($options[$name]) ) {
        return $options[$name];
    } else {
        return $default;
    }
}
}
//require_once('hh_options.php');
//End Theme Options

//Menu Customizations 

//new menu walker
class My_Walker_Nav_Menu extends Walker_Nav_Menu {
  function start_lvl(&$output, $depth) {
    $indent = str_repeat("\t", $depth);
    $output .= "\n$indent<ul class=\"dropdown-menu\">\n";
  }
 
  // add main/sub classes to li's and links
   function start_el( &$output, $item, $depth, $args ) {
      global $wp_query;
      $indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent
    
      // depth dependent classes
      $depth_classes = array(
          ( $depth == 0 ? 'main-menu-item' : 'sub-menu-item' ),
          ( $depth >=2 ? 'sub-sub-menu-item' : '' ),
          ( $depth % 2 ? 'menu-item-odd' : 'menu-item-even' ),
          'menu-item-depth-' . $depth
      );
      $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );
    
      // passed classes
      $classes = empty( $item->classes ) ? array() : (array) $item->classes;
      $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );
    
      // build html
      $output .= $indent . '<li id="nav-menu-item-'. $item->ID . '" class="dropdown ' . $depth_class_names . ' ' . $class_names . '">';
    
      // link attributes
      $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
      $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
      $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
      $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
     

      
      $item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
          $args->before,
          $attributes,
          $args->link_before,
          apply_filters( 'the_title', $item->title, $item->ID ),
          $args->link_after,
          $args->after
      );
    
      // build html
      $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
  }
  }


//Load Required Theme Scripts
function getsome_js() {
	if (is_admin()) return;
	wp_enqueue_script('jquery');  //Get the latest version of jquery bundled with WordPress
	
	/*Twitter Bootstrap */
	wp_enqueue_script('twitter_bootstrap', THEMELOCATION .'/bootstrap/js/bootstrap.min.js', 'jquery');//The main bootstap
	//wp_enqueue_script('tool_tips', THEMELOCATION .'/scripts/tool_tips.js', 'jquery');//Add bootstrap tooltips to elements
	//wp_enqueue_script('popover', THEMELOCATION .'/scripts/popovers.js', 'jquery');//Add bootstrap tooltips to elements
	
	//wp_enqueue_script('fade', THEMELOCATION . '/scripts/jquery.fade.js', 'jquery'); //The fade in script from Press75.com themes
	
	/* Reel Image Viewers */
	//wp_enqueue_script('reel', THEMELOCATION .'/scripts/jquery.reel.js', 'jquery');//The reel image viewer, for image animations: http://jquery.vostrel.cz/reel
	//wp_enqueue_script('jquery-touch', THEMELOCATION .'/scripts/jquery.touch.js', 'jquery');//jQuery touch to help with thouch screen devices
	//wp_enqueue_script('myReels', THEMELOCATION .'/scripts/myReels.js', 'jquery');//Functions to create the image reels
	//wp_enqueue_script('disable-select', THEMELOCATION .'/scripts/jquery.disable.text.select.js', 'jquery');
	/* Tweets */
	//wp_enqueue_script('get_tweets', THEMELOCATION .'/scripts/get_tweets.js', 'jquery');//Custom Twitter Script to get tweets as a JSON object
	wp_enqueue_script('flexslider', THEMELOCATION .'/scripts/jquery.flexslider-min.js', 'jquery');
	wp_enqueue_script('custom', THEMELOCATION .'/scripts/custom.js', 'jquery');
	
	}
add_action('init', 'getsome_js');//add all these awesome scripts to the init function

/* Menus */
if ( function_exists( 'register_nav_menus' ) ) {
	register_nav_menus(
		array(
		  'main_menu' => 'Main Menu',
		  'footer_menu' => 'Footer Menu'
		)
	);
}

/* Sidebars */
register_sidebar(array(
  'name' => 'Primary Sidebar',
  'id' => 'primary-sidebar',
  'description' => 'Widgets in this area will be shown by default on any page with a sidebar.',
  'before_title' => '<h2>',
  'after_title' => '</h2>',
  'before_widget' => '',
  'after_widget' => ''
));
register_sidebar(array(
  'name' => 'Store Sidebar',
  'id' => 'store-sidebar',
  'description' => 'Widgets in this area will be shown on store pages.',
  'before_title' => '<h2>',
  'after_title' => '</h2>',
  'before_widget' => '',
  'after_widget' => ''
));
register_sidebar(array(
  'name' => 'Footer Left',
  'id' => 'footer-left-sidebar',
  'description' => 'Widgets in this area will be shown on the left side of the footer.',
  'before_title' => '<h2>',
  'after_title' => '</h2>',
  'before_widget' => '',
  'after_widget' => ''
));
register_sidebar(array(
  'name' => 'Footer Middle',
  'id' => 'footer-mid-sidebar',
  'description' => 'Widgets in this area will be shown in the middle side of the footer.',
  'before_title' => '<h2>',
  'after_title' => '</h2>',
  'before_widget' => '',
  'after_widget' => ''
));
register_sidebar(array(
  'name' => 'Footer Right',
  'id' => 'footer-right-sidebar',
  'description' => 'Widgets in this area will be shown on the right side of the footer.',
  'before_title' => '<h2>',
  'after_title' => '</h2>',
  'before_widget' => '',
  'after_widget' => ''
));



?>