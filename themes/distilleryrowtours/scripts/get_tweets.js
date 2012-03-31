//Global Variables
var tweets = [];
function got_tweets(data) {
tweets = data;
console.log(tweets[0].text);
}


jQuery(document).ready(function($){
$('#tweets').append('<p class="status">' + tweets[0].text + '</p>');
//addfollow link -<a href=\"http://twitter.com/daveclay1\" class=\"follow_link\"><i class=\"icon-star-empty\"></i>Visit Dave on Twitter</a> 
}); 