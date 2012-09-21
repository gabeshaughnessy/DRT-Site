<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php bloginfo( 'name' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<!-- Twitter Bootstrap -->
<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory' ); ?>/bootstrap/css/bootstrap.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<!-- Google Fonts -->
<link href='http://fonts.googleapis.com/css?family=Josefin+Sans' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Josefin+Slab' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Dancing+Script:400,700' rel='stylesheet' type='text/css'>

<script type="text/javascript">
var templateDir = "<?php bloginfo('template_directory') ?>";
</script>

<?php wp_head(); ?>
</head>

<body <?php body_class('custom'); ?>>
	<div id="wrapper" class="container">
		<div id="header" class="row">
			<div id="title_area">
				<div id="title" class="span7"><h1 class="logo"><a href="<?php echo get_bloginfo('url'); ?>" title="Home"><?php echo get_bloginfo('name'); ?></a></h1>
				</div>
				<div id="description"  class="span5">
				<h3><?php echo get_bloginfo('description'); ?></h3>
				</div>
			</div>
			<div id="main_menu" class="navbar span12">
				<div class="menu-tab left-tab"></div>
				<div class="menu-tab right-tab"></div>
				<div class="navbar-inner">
					<div class="container">
						<?php wp_nav_menu(
						array( 
						'theme_location' => 'main_menu',
						'menu_class' => 'nav',
						'walker' => new My_Walker_Nav_Menu()
						 ) ); ?>
						 <form class="navbar-search pull-right">
						   <input type="text" class="search-query" placeholder="Search">
						 </form>
					 </div>
				 </div>
				 
			 </div>
		</div><!-- end of row -->
	
	