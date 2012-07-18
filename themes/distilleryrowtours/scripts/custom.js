jQuery(document).ready(function($){
	$('.dropdown-menu').prev('a').dropdown();
	
	$('.dropdown-menu').prev('a').click(function(e){
		$('.navbar li').removeClass('open');
		$(this).parent('li').addClass('open');
		e.preventDefault();
	});
	
	$('#pitch .pitch-content').each(function(){
		var imgSrc = $(this).find('img').attr('src');
		
		$(this).css({'background-image': 'url('+templateDir+'/images/paper_bg.png), url(' +imgSrc+')'});
		$(this).find('img').css({'opacity':0});
		console.log(templateDir);
	});
	
	jQuery('.flexslider').flexslider({
	  animation: "slide",
	  selector: ".slides > div",
	  controlNav: false,
	  directionNav: false
	});
	
});

jQuery(window).load(function() {
 });