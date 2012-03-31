jQuery(document).ready(function($) {
	$(function () {
		$('.fade, .posted-in a, .wp-post-image, input#submit').hover(function() {
			$(this).fadeTo("fast", 0.7);
		}, function() {
			$(this).fadeTo("fast", 1);
		});
	});
});