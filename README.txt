=== CHSIE Events ===
Contributors: Ben Hoverter ()
Requires at least: 3.0.1
Tested up to: 4.9.7
Stable tag: 4.9.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CHSIE Events modifies the Events Calendar and Event Tickets plugins by Modern Tribe, and is dependent upon them.


== Description ==

CHSIE Events modifies the Events Calendar and Event Tickets plugins by Modern Tribe.  It provides the following functionality:

- Modified user event registration on Event pages.  Users are restricted to reservations of a single spot, and registration fields are auto-populated with user data to encourage consistency across the platform. ***WIP: The link provided by Event Tickets at the bottom of Event pages is redirected to an admin-specified page (like a User Dashboard) where the [user-event-registration] shortcode displays data. That page can be specified in Settings > CHSIE Events.***

- ***WIP: The plugin collates user event data to determine number of hours of event attendance.  This is displayed in the [user-event-registration] shortcode below.***

- User event registration info can be displayed with a shortcode: [user-event-registration].  This displays the current user's registered events, as well as all relevant info from those events, ordered by event date.

- The plugin allows admins to associate materials with an Event by selecting media from the Media Library.  Admins can alter the display name, description, and order of materials on the front-end.

- Event materials are displayed on the front-end by the insertion of the [event-materials] shortcode in the Event's text editor field.  When materials are added or deleted, that shortcode is automatically re-added, but it can be moved within the text or deleted altogether.


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `chsie-events.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==


== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot
