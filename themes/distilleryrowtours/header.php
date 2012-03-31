<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title(); ?> <?php bloginfo( 'name' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<!-- Twitter Bootstrap -->
<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/bootstrap/css/bootstrap.min.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class('custom'); ?>>
<div id="wrapper" class="container">
	<div id="header" class="row">
		
		<div id="title_area">
			<div id="title" class="span6"><h1><a href="<?php echo get_bloginfo('url'); ?>" title="Home"><?php echo get_bloginfo('name'); ?></a></h1>
			</div>
			<div id="description"  class="span6">
			<h3><?php echo get_bloginfo('description'); ?></h3>
			</div>
		</div>
	<div id="main_menu" class="navbar span12">
	<div class="navbar-inner">
	<div class="container">
	<?php wp_nav_menu(
	array( 
	'theme_location' => 'main_menu',
	'menu_class' => 'nav'
	 ) ); ?>
	 <form class="navbar-search pull-left">
	   <input type="text" class="search-query" placeholder="Search">
	 </form>
	 </div>
	 </div>
	 </div>
	</div><!-- end of row -->

	