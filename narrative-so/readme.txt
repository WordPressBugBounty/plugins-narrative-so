=== Narrative Publisher ===
Contributors: Narrative
Tags: narrative, narrative-app, narrative-publisher, narrative-blog-builder
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Publish your Narrative posts straight to your WordPress site from the Narrative app.

== Description ==
This plugin connects your Wordpress website with your [Narrative App](http://www.narrative.so) allowing you to publish your Narrative posts directly to your Wordpress website. Please contact support@narrative.so for any help.

##Get Started
Sign up with a free trial to Narrative [here](https://my.narrative.so/#/free-trial) and download the app to get started.

== Installation ==
###Plase Note:
This plugin connects your Narrative account with your Wordpress plugin. Please first install the Narrative App via [this link.](http://www.narrative.so/download)

###Installation Video

Please click [here](https://help.narrative.so/articles/2766715-how-to-publish-to-wordpress) for Narrative's installation video.

###From within WordPress

1. Visit 'Plugins > Add New'
2. Search for 'Narrative Publisher'
3. Activate Narrative Publisher from your Plugins page.
4. Return to Narrative and input your activation key.
For any issues see this article [here](https://help.narrative.so/articles/2766715-how-to-publish-to-wordpress)

### Manually

1. Upload the narrative-publisher.zip to the `/wp-content/plugins/` directory
2. Activate the Narrative Publish plugin through the 'Plugins' menu in WordPress
3. Return to Narrative and input your activation key.
For any issues see this article [here](https://help.narrative.so/articles/2766715-how-to-publish-to-wordpress)


###Narrative Support

For any further issues please contact our support team [here](https://help.narrative.so/)

== Frequently Asked Questions ==
Please see our support docs [here](https://help.narrative.so/articles/2766715-how-to-publish-to-wordpress) for help.


== Technical info ==

* This plugin uses [www.narrative.so](https://narrative.so) to input your Narrative Posts into your Wordpress website.
* Narrative has the ability to create and edit posts on your Wordpress website
* When creating a post Narrative will input HTML and JS into your a post.
* For more info please see Narrative's [Terms and Conditions](https://narrative.so/terms-and-conditions) and [Privacy Policy](https://narrative.so/privacy-policy)

== Changelog ==

= 1.1.0 =
* Compatibility with WordPress 7.0 and PHP 8.
* Security: the featured image is now sideloaded through WordPress' own media
  handling, which restricts it to image types. Previously the remote response
  body was written directly into the uploads directory under a filename taken
  from the request.
* Security: the featured image URL is validated before it is fetched, so it can
  no longer be pointed at hosts on the site's local network.
* Security: fixed a stored cross-site scripting issue. The `narrative_post_script`
  post meta was exposed to the REST API with no capability check, and the
  Gutenberg block returned it to the page unfiltered, which let a user without
  the `unfiltered_html` capability store JavaScript that ran for every visitor.
  Writing the meta now requires `unfiltered_html`, the value must be valid
  base64, and both the block and the `[narrative]` shortcode filter their output
  through `wp_kses()`.
* Security: only http(s) URLs are accepted for the story script.
* Security: access codes are compared with `hash_equals()` rather than a loose
  comparison that treated `012345` and `12345` as equal.
* Performance: the rewrite rules are flushed on activation and on upgrade
  instead of on every page load.
* Fixed an undefined index warning raised on every post save.
* Endpoints now send a JSON content type and set status codes through
  `status_header()`.
* Removed unused secret generation code.
* Fixed the Access Key sometimes needing to be entered more than once: the
  confirmation prompt fired on every save and cancelling it discarded the key
  silently.
* Access Keys are now upper cased and stripped of spaces before being stored, so
  a typed key works as well as a pasted one.
* Invalid Access Keys are now rejected with an explanation instead of being
  saved silently, and no longer overwrite a working key.
* The settings page now shows an explicit connection status.
* The text domain now matches the plugin slug.
* Stopped bundling a copy of moment.js; the version in WordPress core is used.

= 1.0.7 =
* Fixed the plugin version reported by the `/narrative/info` endpoint.

= 1.0.6 =
* Tested up to WordPress 6.1.

== Upgrade Notice ==

= 1.1.0 =
Compatibility and security release for WordPress 7.0 and PHP 8. Recommended for
all users.
