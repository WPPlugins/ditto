=== Ditto ===
Contributors: lordspace,orbisius
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A
Tags: admin, test, theme, themes, test drive, testdrive, sandbox, theme test drive, test theme,test plugin, wordpress sandbox, wordpress, test environment, staging environment
Requires at least: 3.0.0
Tested up to: 4.1
Stable tag: 1.0.1
License: GPLv2 or later

This plugin allows you to copy a plugin and (TODO:) theme to a test/sandbox WordPress (local for now) installation.

== Description ==

This plugin allows you to copy a plugin and (TODO:) theme to a test/sandbox WordPress (local for now) installation.

This plugin is intended to be used by developers/designers who need a quick way to copy their plugins and themes to another (clean) copy of WordPress
so they can test their work.

Note: In future versions of the plugin it will support activation and deactivation of the deployed plugin/theme.
Currently, target plugin's code is copied (from the current WordPress installation assuming that that's the development environment) to a target WordPress test/sandbox site.
The plugin must be activated on the target WordPress test/sandbox site.

= Why is the plugin needed? =
When developers and designs work they work on one instalation they may not necessarily catch some glitches.
That's why it is important to test the work on a fresh installation and also on a WordPress installation that has many plugins.

= Demo =
http://www.youtube.com/watch?v=vuRFblOKD8c

= Usage =

You need to go to *Ditto* > Deploy > Select the plugin that you want to deploy and then Parent Target Directory of a local
WordPress installation. The plugin will be copied in the sub-folder of the Parent Target Directory.

In the next releases the plugin will be able to deploy a theme to a test location as well as to a remote location such as
<a href="http://qsandbox.com/?utm_source=ditto&utm_medium=readme&utm_campaign=product" target="_blank" title="Free Test/Sandbox WordPress Site">http://qsandbox.com</a>

= Author =

Svetoslav Marinov (Slavi) | <a href="http://orbisius.com" title="Wordpress Plugin Development St. Catharines, Niagara" target="_blank">Custom Web Programming and Design by orbisius.com</a>

== Installation ==

= Automatic Install =
Please go to Wordpress Admin &gt; Plugins &gt; Add New Plugin &gt; Search for: ditto and then press install

= Manual Installation =
1. Upload ditto.zip into to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

Run into issues or have questions/suggestions? 

If you have suggestions or run into an issue please use our support forum at http://club.orbisius.com/support/


== Screenshots ==
1. Plugin's dashboard page
2. Plugin's deploy page

== Upgrade Notice ==
n/a

== Changelog ==

= 1.0.1 =
* Added code to remember the last used data.
* Tested WP 4.1
* Added themes to be copied as well + updated uninstall to delete the new setting variable.
* Improved data sanitization

= 1.0.0 =
* Initial Release
