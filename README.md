# Linkify

Creates links to internal/external pages in a similar way to Facebook.  Stores key information from a webpage to an attachment.

## Description

Creates attractive links to internal/external pages in a similar way to Facebook.  Stores the title, thumbnail and meta description of a webpage (if available) to an attachment.

Example usage:
 - Paste a URL on its own line in the editor.  Linkify will do all the work!
 - Shortcode syntax: `[linkify title='Title' description='Full description.']http://example.com[/linkify]`
 
Technical specs:
 - Automatically linkifies links which are on their own paragraph, whether the editor automatically encased it in a link or not.
 - Picks up the `<title>` element if available, uses the URL if not.
 - Picks up the meta description if available, otherwise finds the first `<p>` on the page.
 - Picks up the meta image_src if available, otherwise finds the first `<img>` on the page.
 - If server-side storage is disabled, the plugin will use jQuery to grab the site information client-side.

## Installation 

1. Upload & activate plugin
2. Type/paste a URL onto a SEPARATE LINE in a post/page
3. *OPTIONAL* Edit the attachment description/image after viewing the post/page for the first time (this will fetch the link details)

## Changelog 

#### 1.0 
 - Initial release!