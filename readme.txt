=== Instafeed ===
Contributors: bjornjohansen, toringe, joakimandersen
Donate link: http://www.kiva.org
Tags: instagram, widget, jquery
Author URI: http://leidar.com/
Requires at least: 3.5
Stable tag: 0.1.3
Tested up to: 3.8.1
License: GPLv2

Stream of photos from Instagram on your WordPress site

== Description ==

Fetch and display the latest Instagrams from either a specified Instagram username or a tag.

* Really easy to configure widget.
* No OAuth required.
* Works on HTTPS.
* No CSS provided (yet). Do what you want, create a slider, lightbox gallery, whatever …
* Full styling freedom
* Optimized for full page caching. Widget content is fetched via an AJAX GET request for easy cache tuning.
* Results from Instagram is cached on server for fast loading.
* Results from server is cached in browser for super-fast loading (and less stress on server) on repeated/multiple page views.
* i18n ready. POT file and nb_NO translation included.


== Installation ==
1. Download plugin
2. Upload the plugin through the 'Plugins' > 'Add new' > 'Upload' page in WordPress,
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Widget settings
2. Widget output (in the twentytwelve theme)
3. HTML output

== Changelog ==

= Version 0.1.3 =
* Added filters for modifying HTML output
* Closed the ul element

= Version 0.1.2.1 =
* Changed default num of entires to 6
* Added banner for wordpress.org repo

= Version 0.1.2 =
* Added client side caching with localStorage
* Added filters for modifying cache times
* Renamed plugin to Instafeed

= Version 0.1.1 =
* User feed works
* Caching results
* Scheme-relative image URLs
* Added POT and translation for nb_NO
* Minified JS

= Version 0.1 =
* Created 2014-01-21
* Tag feed works

