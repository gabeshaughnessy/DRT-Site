function pad(number, length) {//helper function to create numbers like 001, 0005, etc...
    var str = '' + number;
    while (str.length < length) {
        str = '0' + str;
    }
    return str;
}

function build_sequence(img_prefix, img_suffix, zero_padding, img_count){//helper padding to build the image arrays
var img_seq = new Array();
for (i=1;i<img_count;i+=1){
img_seq[i-1] = [img_prefix + pad(i, zero_padding) + img_suffix];
console.log(img_seq[i]);
}
return img_seq;
}

jQuery(document).ready(function($){
	var images = build_sequence("dave_clay__gabby_1-", ".jpg", 4, 36);//build the image sequence array
	
	$(document).bind('touchmove', function(e) {
	   e.preventDefault();
	}, false);//disable the default touch to scroll the page
	
	$('#talking_head_reel').reel({//this is the function from http://jquery.vostrel.cz/reel
	images: images, //the array of images
	frames: images.length, //number of images in the array
	indicator: 0, //the size of the visual indicator
	revolution:800, //distance the mouse moves for one revolution
	path: "/daveclay/wp-content/themes/davetheme/images/reels/",//prepended before the file name in the images array
	speed: 0, //animation spped in revolutions per second
	brake: .1, //braking force when thrown
	clickfree: true, //Instead of clicking and dragging, binds to mouse enter and exit events
	}); //jquery reel plugin http://jquery.vostrel.cz/reel
	$('#talking_head_reel-reel').disableTextSelect();
	//Make the images draggable by activating these options
	$('.reel').touch({
	    animate: false,
	    sticky: false,
	    dragx: false,
	    dragy: false,
	    rotate: false,
	    resort: false,
	    scale: false,
	});
	
});