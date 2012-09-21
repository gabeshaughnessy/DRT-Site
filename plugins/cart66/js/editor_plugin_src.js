/**
 * @author Andre Fredette
 * @version 1.0 October 2009
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('cart66');

	tinymce.create('tinymce.plugins.cart66', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mcephproduct', function() {
				ed.windowManager.open({
					file : wpurl + '?cart66dialog=1', // wpurl is home_url( '/' )
					width : 500,
					height : 255 + (tinyMCE.isNS7 ? 20 : 0) + (tinyMCE.isMSIE ? 0 : 0),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register example button
			ed.addButton('cart66', {
				title : 'cart66.cart66_button_desc',
				cmd : 'mcephproduct',
				image : url + '/img/cart66.gif'
			});

		},

		/**
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Cart66',
				author : 'Reality66',
				authorurl : 'http://cart66.com/',
				infourl : 'http://cart66.com/',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('cart66', tinymce.plugins.cart66);
})();
