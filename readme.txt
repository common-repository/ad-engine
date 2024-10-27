=== Ad-Engine ===
Contributors: oriontechnologysolutions.com
Donate link: http://www.oriontechnologysolutions.com/web-design/ad-engine/#donate
Tags: ads, rotate, random, adverts, advertisement, custom ads, random ads, rotating ads
Requires at least: 3.0
Stable tag: 0.8
Ad-Engine lets place ads anywhere in wordpress via widget or shortcode. It tracks impressions and clicks.

== Description ==
Ad-Engine lets place ads anywhere in wordpress via widget or shortcode. It tracks impressions and clicks.

<h3>Shortcode usage</h3>
[ad-engine ad_group="GROUP NAME"]

<h3>Shortcode Options</h3>
<ul>
  <li><h4>ad_group</h4></li>
  <li><strong>Mandatory option</strong> Which ad group do you want to pull ads from, copy/paste the name from Ad Groups page</li>
  <li>align</li>
  <li>Should the ad be on the left or the right, applies the alignleft and alignright class, so it should merge in with your theme the same as a captioned image</li>
</ul>


== Installation ==
1. Upload ad-engine.php to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure in the Ad-Engine menu.
1. Place it in your sidebar in the widets section or anywhere you like using [ad-engine ad_group="NAME"]

== Frequently Asked Questions ==
= How do I use the shortcode =

Put [ad-engine ad_group="Category Name"] in a post or text widget

== Changelog ==

= 0.7 =
* Added jQuery as a requirement
* Fixed bug in Total Click percentage

= 0.6 =
* Delete ad now asks for confirmation
* Impressions and clicks are tracked per day
* CSV download capable
* Text links now refer to the target site.

= 0.5 =
* Added align=[left|right] to shortcode support
* Updated description in readme

= 0.4 =
* Fixed bug in tracking url
* Fixed bug where the wrong id was attached to an ad in display

= 0.3 =
* Fixed some function calls with missing 
* Added the ability to delete ads
* Tracks impressions and clicks

= 0.2 =
* Initial release
