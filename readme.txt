=== Security Audit ===
Contributors: IAmNickOrtiz, chris-CSHP, drywallbmb
Tags: security, spam,
Requires at least: 3.9.2
Tested up to: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Audits PHP configuration and codebase with an eye on vulnerabilities

== Description ==

Security Audit is a wrapper around a pair of third-party tools that can help you identify potential vulnerabilities in your site. It does not actually analyze the code of your site, nor does it correct any issues it finds; it simply compares what you've got with publicly-available information regarding security.

Specifically, Security Audit is a wrapper around [PHPSecInfo](http://phpsec.org/projects/phpsecinfo/) and the [WPScan Vulnerability Database](https://wpvulndb.com/) API.

Once installed and activated, you'll have 'Security Audit' as an option in the Tools menu. Navigate there and you'll have tabs for PHPSec Info, Plugin Scanner, Theme Scanner, and WordPress Core Scanner. Click on a tab to initiate a scan of that part of your site. One completed you'll get an overall summary as well as a breakdown of potential security issues.

“The three ‘scanner’ tabs look at the self-reported versions of your software and compare those versions to data in the vulnerabilities database. Resolved, open and undetermined issues will be displayed and color-coded to indicate the level of concern you should probably have.”  This can be useful for determining if a given pending plugin update is a security fix or just bug/feature related; similarly it can also flag known issues with code that has not yet been updated -- always good to know!

The PHPSecInfo tab reports information about your PHP configuration, done by calling the PHPSecInfo library bundled with this plugin. In many cases you may be unable to change your PHP configuration; it depends on the level of control you have over your hosting environment.

== Installation ==

1. Upload the `security-audit` directory to your plugins directory (typically wp-content/plugins)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit Tools > Security Audit to assess your site

== Frequently Asked Questions ==

= Why would I want to do this? =

WordPress' built-in update scanner is great for notifying you of available updates to WordPress and your plugins and themes. But it can't tell you if there are security problems. There's thus no mechanism for differentiating between critical security patch updates and more pedestrian bug/feature updates; It's left up to individual developers to flag in their projects' changelogs if a given update is security-related.

In addition, if there's a plugin that has a security hole but has not yet been updated, there's no convenient way to know about it without leaving your admin to use other tools or scour outside databases. This plugin simplifies this process by providing a convenient window into WPScan's vulnerabilities database.

Furthermore, it helps flag potential issues with your PHP configuration, which can be useful in identifying potential attack vectors, choosing a security-conscious web host, or configuring your own hosting setup.

= Where does the data come from?  =

This plugin uses tools developed and maintained by third-parties to perform its analysis, specifically [PHPSecInfo](http://phpsec.org/projects/phpsecinfo/) and the [WPScan Vulnerability Database](https://wpvulndb.com/). The developers of this plugin can neither endorse nor confirm the accuracy of those systems, and should not be contacted if you dispute any of their findings.

== Changelog ==

= 1.0 =
* Refactoring to utilize AJAX to decrease wait times.

= 0.2 =
* Code reorganization and cleanup.

= 0.1 =
* Initial public release.
