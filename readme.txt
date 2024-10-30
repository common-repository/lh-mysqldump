=== LH Mysqldump ===
Contributors: shawfactor
Donate link: https://lhero.org/portfolio/lh-mysqldump/
Tags: sql, mysql, database, export, email
Requires at least: 5.4
Tested up to: 6.0
Stable tag: 1.01

A simple plugin to export and backup your database, on an ongoing basis

== Description ==

LH Mysql plugin allow you to create a complete database backup with single click. The entire contents of your databse can be downloaded in a compressed zip file which can also be emailed on an ongoing basis

FEATURES
* A simple interface to download your database
* A complete backup, including custom and non wordpress tables
* The export file is zipped, massively reducing the size of the file downloaded or emailed
* Ongoing backups by email on a weekly basis

**Like this plugin? Please consider [leaving a 5-star review](https://wordpress.org/support/view/plugin-reviews/lh-mysqldump/).**

**Love this plugin or want to help the LocalHero Project? Please consider [making a donation](https://lhero.org/portfolio/lh-mysqldump/).**

Example use:

== Installation ==

1. Upload the `lh-mysqldump` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally navigate to Tools->Export database to dowload the databse immmediately (exports will be done weekly via crooned email regardless).

== Frequently Asked Questions ==

= Why did you write this plugin? =

The existing options were overly complex, bloated, and lacked key functionality. I am a big believer in simple plugins that do one job well

= Can you tell me more about the ongoing backup feature ? =

Sure, on a weekly basis this plugin will export the databse to a zip file and email it to the admin email address. This is done in the backgound via cron. The email address that this backup is sent to can be filtered (see the code to do so)

= Any caveats, or things I should be aware of? =

This plugin may not work for very large large databases and/or very poor hosting. I run it on shared hosting, exporting a database which is over 70 mb and it works fine.

= What is something does not work?  =

LH Mysqldump, and all [LocalHero](https://lhero.org) plugins are made to WordPress standards. Therefore they should work with all well coded plugins and themes. However not all plugins and themes are well coded (and this includes many popular ones). 

If something does not work properly, firstly deactivate ALL other plugins and switch to one of the themes that come with core, e.g. twentyfirteen, twentysixteen etc.

If the problem persists please leave a post in the support forum: [https://wordpress.org/support/plugin/lh-mysqldump/](https://wordpress.org/support/plugin/lh-mysqldump/). I look there regularly and resolve most queries.

= What if I need a feature that is not in the plugin?  =

Please contact me for custom work and enhancements here: [https://shawfactor.com/contact/](https://shawfactor.com/contact/).


== Changelog ==

**1.00 May 10, 2019** 
* Initial release

**1.00 August 07, 2022** 
* Better mysql handling