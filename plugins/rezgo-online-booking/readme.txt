=== Rezgo Online Booking ===
Contributors: rezgo
Donate link: http://www.rezgo.com/
Tags: tours, activities, events, attractions, booking, reservation,
ticketing, e-commerce, business, rezgo
Requires at least: 3.0.0
Tested up to: 3.3.1
Stable tag: 1.6

Integrate Rezgo, the leading tour and activity booking platform with your WordPress website.

== Description ==

> This plugin is completely free to use, but it requires a Rezgo account.  <a href="http://www.rezgo.com" rel="nofollow">Try Rezgo today</a> and experience the world's best hosted booking platform.

**Rezgo** is a cloud based software as a service booking system that
gives businesses the ability to manage their tour or activity
inventory, manage reservations, and process credit card payments. This
plugin is a full featured front-end booking engine that connects your
WordPress site to your Rezgo XML API.

The Rezgo WordPress Booking Plugin is not an iframe or javascript
widget based booking engine, it is a completely integrated booking
interface that takes advantage of all the content management
capabilities of WordPress.  Tag, search, tour list, and tour detail
pages are all fully integrated with the WordPress site structure
giving you the ability to link directly to product pages, specific
dates, or apply promotional codes or referral ids.  Every Rezgo
WordPress page is search optimized and index ready, which means your
site gets all the benefit of your Rezgo content.

You get all the features of the regular Rezgo hosted booking engine
plus the flexibility to completely control the look and feel of your
customer booking experience.

= Plugin features include =

* Complete control over look and feel through CSS and access todisplay templates
* Powerful AJAX booking calendar features
* Support for discount and referral codes
* Fully search-ready pages and search engine friendly URLs
* Complete transaction processing on your own site (with secure certificate)*
* Plus all the other features of Rezgo. (http://www.rezgo.com/features)

= Support for your Rezgo Account =

If you need help getting set-up, Rezgo support is only a click or
phone call away:

* Rezgo Support Website (http://support.rezgo.com)
* Customer service forum (http://getsatisfaction.com/rezgo)
* Rezgo on Twitter (http://www.twitter.com/rezgo)
* Rezgo on Facebook (http://www.facebook.com/rezgo.tour.operator.software)
* Pick up the phone and call +1 (604) 983-0083
* Email support AT rezgo.com

== Installation ==

= Install the Rezgo Booking Plugin =

1. Install the Rezgo Booking plugin in your WordPress admin by going
to 'Plugins / Add New' and searching for 'Rezgo' **OR** upload the
'rezgo-online-booking' folder to the `/wp-content/plugins/` directory
2. Activate the Rezgo plugin through the 'Plugins' menu in WordPress
3. Add your Rezgo CID and API KEY in the plugin settings
4. Use the shortcode [rezgo_shortcode] in your page content. Advanced shortcode commands are available here at http://rezgo.me/wordpress
5. Or place `<?php do_action('rezgo_tpl_display'); ?>` in your templates

= Plugin Configuration and Settings =

1. Make sure the Rezgo booking plugin is activated in WordPress.
2. Copy your Company Code and XML API KEY from your Rezgo Settings.
3. If you would like to use the included Rezgo Contact Form, you may
want to get a ReCaptcha API Key.
4. Create a Page and embed the Rezgo booking engine by using the
shortcode: [rezgo_shortcode]
5. Advanced shortcode commands are available here at http://rezgo.me/wordpress

= Important Notes =

1. The Rezgo plug-in requires that you have permalinks enabled in your
WordPress settings. You must use a permalink structure other than the
default structure.  You can update your permalink structure by going
to Settings > Permalinks in your WordPress admin.
2. The Rezgo plug-in is not supported on posts, it will only function on pages.
3. If you DO NOT have a secure certificate enabled on your website,
you should choose the option "Forward secure page to Rezgo".

== Frequently Asked Questions ==

= I'm getting a PHP error when displaying the tour page? =

This is most likely because you are using the default link structure
in WordPress.  The Rezgo plug-in requires that you use permalinks in
order to show the Rezgo content correctly.

= When I click on the book now button I get a page not found or server error? =

This is probably because you do not have a secure certificate
installed correctly on your site.  If this is the case, or if you just
don't know, we recommend you chose the "Forward secure page to Rezgo"
option.

= Can I use the Rezgo WordPress Plugin without connecting to Rezgo? =

The Rezgo WordPress Plugin needs to pull tour and activity data so it
needs to connect to your account via the Rezgo XML API. Your Rezgo
credentials (specifically your Company Code (CID) and API Key) are
used by the Rezgo WordPress Plugin to display your tour and activities
on your WordPress site.

= Can I manage credit card payments on my WordPress site? =

Yes, the Rezgo WordPress plugin has the ability to handle credit card
payments.  Make sure to configure your Rezgo account to connect to
your payment gateway.  Rezgo supports a growing list of Global payment
processors including Authorize.net, PayTrace, Chase Paymentech,
Beanstream, Ogone, Eway, and many others.  In order for your site to
handle payments, you will need to install a secure certificate.  Check
with your web host if you need help installing a secure certificate.
If you do not wish to set-up a secure certificate, you can have the
secure booking complete on your Rezgo hosted booking engine.

== Screenshots ==

1. Your tours and activities will display in a list on your default
tour page.  From the tour page, your customers will be able to search
for available tours and activities by using the date search at the top
of the page, searching your items using keywords, or browsing based on
tags.
2. Detail pages are designed to provide your customers with all the
information they need to make a booking decision.  They can view
detailed information, browse your image gallery, or watch videos.
Visitors can also share your detail page on Twitter, Facebook, TripIt,
Travelmuse, or Duffle.
3. When customers choose a date, they are presented with a list of
options.  Customers can then choose a preferred option in order to
continue the booking process.
4. Once a customer has chosen a date, they are returned to the details
page with the price options available for the option selected.
Customers can enter a promotional code if available, enter the number
of passengers or guests for each price level, and continue on to the
secure booking page.  If the WordPress site is secure, the transaction
will complete on the WordPress site.  If however, there is no secure
certificate, the transaction will complete on your Rezgo hosted
booking engine.

== Changelog ==

= 1.6 =
* Updated payment step of booking page to new async version.
* Updated calendar ajax to new faster version used on white label.
* Updated jQuery in default template to use noConflict() mode.
* Fixed a number of small issues with the checkout process.
* Fixed a bug preventing the calendar from going forward more than 12 months.


= 1.5 =
* Added support for passing variables to the shortcode.
* Added support for new multi-tag searches.
* Improved handling of API keys entered on settings page.
* Switched all remaining file fetching to use configured fetch method.
* Plugin update should no longer remove custom templates.
* Fixed a number of display and instruction errors on settings page.
* Fixed an issue with 'required' field alerts on some browsers.
* Fixed a rare bug with the receipt print button.
* Fixed a bug with smart/keyword searches failing due to bad encoding.
* Fixed an issue with the plugin not returning it's output correctly.


= 1.4.5 =
* Initial release.

== Upgrade Notice ==

= You have the most recent version =