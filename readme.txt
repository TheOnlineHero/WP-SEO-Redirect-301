=== WP SEO Redirect 301 ===
Contributors: MMDeveloper, The Marketing Mix Osborne Park Perth
Tags: seo, redirect, 301, slug
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 2.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

WP-SEO-Redirect-301 is a WordPress plugin that remembers your old urls and redirects users to the updated url, to prevent 404s when urls change

If you click on "SEO Redirect 301" menu link, you will see a list of old urls pointing to the new ones. Its here where you can delete ones you don't want anymore.

Since version 1.9, you now get a sitemap that you can submit to google and bing. After installing the plugin, you can submit http://yoursite/301-sitemap.xml to google or bing.

Since version 1.8.0, you can now create custom urls in your admin post/page edit page. Login to your wordpress site, go to any post/page and scroll down. You should find a Meta Box called SEO Redirect 301s. If you submit a url here and then browse to this url, it will redirect you to the page/post your editing.

Built by The Marketing Mix Perth: http://www.marketingmix.com.au


== Installation ==

1) Install WordPress 4.0 or higher

2) Download the latest from:

http://wordpress.org/extend/plugins/wp-seo-redirect-301

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.


Thats it, you don't need to worry, update your page urls and this plugin will redirect your users to the updated url.


Built by The Marketing Mix Perth: http://www.marketingmix.com.au


== Changelog ==

= 2.0.6 =

* Retain query string from old url.

= 2.0.5 =

* Don't re redirect to same url even if at some point it had changed. This to prevent endless redirects.

= 2.0.4 =

* Changed so it only works for posts and pages.

= 2.0.3 =

* If page is the front page, don't bother checking to see if its a 404.

= 2.0.2 =

* Updated 404 detection code.

= 2.0.0 =

* Tom M8te was blocked by Wordpress as they no longer accept framework plugins. So I've had to drastically change the plugin to work.

= 1.9.1 =

* Noticed an issue where url has a query string. The fix is to remove query strings from the urls.

= 1.9 =

* Able to generate sitemap that you can submit to google or bing.

= 1.8.2 =

* Increased compatibility across multiple themes.

= 1.8.1 =

* I think I may have fixed template conflictions once and for all. I've placed the redirect on the shutdown event which is the last wordpress event that executes. I think before it tried to redirect before all other code could execute, breaking most templates. Now that this executes on the last event, I think it should create less template issues.

= 1.8.0 =

* Allows you to create custom urls to redirect to a page.

= 1.7.4 =

* I noticed that SEO Redirect 301 didn't want to play nice with some paid themes. I've come up with a solution to SmartStart and the fix could potentially fix other themes that work closely with Wordpress Theme code. 

= 1.7.3 =

* Fixed small bug with dependency checker.

= 1.7.2 =

* Better dependency installer. Fixed issue with empty redirect table.

= 1.7.1 =

* Added more Tom M8te functions to make it easier to manage.

= 1.6.4 =

* After looking at logs, I found duplicate entry errors, which didn't create them, just warned about them. This patch removes the chance of the warning.

= 1.6.3 =

* Small bug fix, happens rarely. If the database is corrupt and post id didn't exist it produced an error. This patch fixes it.

= 1.6.2 =

* Fixed bug with redirect. Sometimes the system redirects to an attachment, not a page or post, which makes no sense. Anyways I put a guard on it so if it tries to redirect to the attachment, it redirects to the page instead.

= 1.6.1 =

* Fixed bug with https urls.

= 1.6 =

* Dependent on plugin Tom M8te to make some features easier to code. There is no new features since 1.5.

= 1.5 =

* Fixed redirect posts to correct urls. Fixed delete redirects which did delete but didn't refresh changes at correct time.

= 1.4 =

* Fixed some typos, whoops.

= 1.3 =

* Better description on table when you haven't yet changed your urls.

= 1.2 =

* UI to show user which old urls are pointing to the new updated urls.

= 1.1 =

* Fixed up updating children urls. So now if parent slug name changes, the child slugs are updated as well. For example, lets say you have a page called http://localhost/cars with a child page http://localhost/cars/holden, if you change the slug name to car and you navigated to http://localhost/cars it will redirect you to http://localhost/car. Similarly if you navigated to http://localhost/cars/holden it will redirect you to http://localhost/car/holden.

= 1.0 =

* Initial Checkin


== Upgrade notice ==

= 2.0.6 =

* Retain query string from old url.

= 2.0.5 =

* Don't re redirect to same url even if at some point it had changed. This to prevent endless redirects.

= 2.0.4 =

* Changed so it only works for posts and pages.

= 2.0.3 =

* If page is the front page, don't bother checking to see if its a 404.

= 2.0.2 =

* Updated 404 detection code.

= 2.0.0 =

* Tom M8te was blocked by Wordpress as they no longer accept framework plugins. So I've had to drastically change the plugin to work.

= 1.9.1 =

* Noticed an issue where url has a query string. The fix is to remove query strings from the urls.

= 1.9 =

* Able to generate sitemap that you can submit to google or bing.

= 1.8.2 =

* Increased compatibility across multiple themes.

= 1.8.1 =

* I think I may have fixed template conflictions once and for all. I've placed the redirect on the shutdown event which is the last wordpress event that executes. I think before it tried to redirect before all other code could execute, breaking most templates. Now that this executes on the last event, I think it should create less template issues.

= 1.8.0 =

* Allows you to create custom urls to redirect to a page.

= 1.7.4 =

* I noticed that SEO Redirect 301 didn't want to play nice with some paid themes. I've come up with a solution to SmartStart and the fix could potentially fix other themes that work closely with Wordpress Theme code. 

= 1.7.3 =

* Fixed small bug with dependency checker.

= 1.7.2 =

* Better dependency installer. Fixed issue with empty redirect table.

= 1.7.1 =

* Added more Tom M8te functions to make it easier to manage.

= 1.6.4 =

* After looking at logs, I found duplicate entry errors, which didn't create them, just warned about them. This patch removes the chance of the warning.

= 1.6.3 =

* Small bug fix, happens rarely. If the database is corrupt and post id didn't exist it produced an error. This patch fixes it.

= 1.6.2 =

* Fixed bug with redirect. Sometimes the system redirects to an attachment, not a page or post, which makes no sense. Anyways I put a guard on it so if it tries to redirect to the attachment, it redirects to the page instead.

= 1.6.1 =

* Fixed bug with https urls.

= 1.6 =

* Dependent on plugin Tom M8te to make some features easier to code. There is no new features since 1.5.

= 1.5 =

* Fixed redirect posts to correct urls. Fixed delete redirects which did delete but didn't refresh changes at correct time.

= 1.4 =

* Fixed some typos, whoops.

= 1.3 =

* Better description on table when you haven't yet changed your urls.

= 1.2 =

* UI to show user which old urls are pointing to the new updated urls.

= 1.1 =

* Fixed up updating children urls. So now if parent slug name changes, the child slugs are updated as well. For example, lets say you have a page called http://localhost/cars with a child page http://localhost/cars/holden, if you change the slug name to car and you navigated to http://localhost/cars it will redirect you to http://localhost/car. Similarly if you navigated to http://localhost/cars/holden it will redirect you to http://localhost/car/holden.

= 1.0 =

* Initial Checkin
