=== Plugin Name ===
Contributors: dannygbnet
Tags: links, thumbnails
Requires at least: 3.0.1
Tested up to: 3.6
Stable tag: 1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Creates links to internal/external pages in a similar way to Facebook.  Stores key information from a webpage to an attachment.

== Description ==

Creates attractive links to internal/external pages in a similar way to Facebook.  Stores the title, thumbnail and meta description of a webpage (if available) to an attachment.

Example usage:
 - Paste a URL on its own line in the editor.  Linkify will do all the work!
 - Shortcode syntax: [linkify title='Your Title Here' description='Full description of link goes here.']http://example.com[/linkify]
 
Technical specs:
 - Automatically linkifies links which are on their own paragraph, whether the editor automatically encased it in a link or not.
 - Picks up the <title> element if available, uses the URL if not.
 - Picks up the meta description if available, otherwise finds the first <p> on the page.
 - Picks up the meta image_src if available, otherwise finds the first <img> on the page.
 - If server-side storage is disabled, the plugin will use jQuery to grab the site information client-side.

== Installation ==

1. Upload & activate plugin
2. Type/paste a URL onto a SEPARATE LINE in a post/page
3. *OPTIONAL* Edit the attachment description/image after viewing the post/page for the first time (this will fetch the link details)

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. img/screenshot-linkify.jpg
2. img/screenshot-editor.jpg
3. img/screenshot-settings.jpg

== Changelog ==

= 1.0 =
* Initial release!

== Upgrade Notice ==

= 1.0 =
Initial release!