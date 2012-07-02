	<div id="footer" class="row">
	<ul id="footer_left" class="span4">
	<?php dynamic_sidebar( 'Footer Left' ); ?>
	</ul>
	<ul id="footer_middle" class="span4">
	<?php dynamic_sidebar( 'Footer Middle' ); ?>
	</ul>
	<ul id="footer_right" class="span4">
	<?php dynamic_sidebar( 'Footer Right' ); ?>
	</ul>
	
	
	<?php wp_footer(); ?>
	</div>
</div><!-- end of the wrapper -->
<div id="bottom">
<img src="<?php echo of_get_option('default_bg', get_template_directory_uri().'/images/bg3.jpg'); ?>" width="100%"  id="bg_image"/>
</div>
</body>

</html>