ANONYMOUS POSTING

== Description ==

Local plugin for allowing anonymous posting in forums.

== Configuration ==

Go to "Admin block => Plugins" section for enabling/disabling the plugin and for choosing the default roles for the anonymous user in the course and  the activity.

In a forum activity, there are options in the settings block for enabling and disabling the anonymous posting feature.

It is recommended to create a new custom Student role (Anonymous student) with all the capabilities restricted excepts the forum ones.
The capability for deleting own posts in a forum must be disabled also.
In the global configurations you can choose this new role for beeing used as the default role for the anonymous users in the course and the activity.

Once enabled, users will see new buttons and links for posting or replying as anonymous users.

When an user click in one of these link, is logged in as a special Moodle user account called "Anonymous users", once they have posted a new message they can recover their actual session using a link displayed in the settings block.

== How it works ==

Using javascript some links are added to the standar forum pages:

Add a new anonymous post
Reply as an anonymous user

Once a user click in one of these links, a new page with a notice is displayed

This notice alerts the user that is going to be "login as" an anonymous user to allowing him to post into the forum.

== Privacy and security notice ==

Please note that:

* This plugin creates an user account in your Moodle installation
* This user account has the login blocked
* This user is enrolled in the course where the forum is
* This user is enrolled in the forum
* The navigation of the user is restricted to the forum in the course
* The navigation is restricted using hacks in the navigation and settings block. At least one of this block must be present

== Credits ==

Juan Leyva <http://twitter.com/#!/jleyvadelgado>

http://moodle.org/user/profile.php?id=49568

== See also ==


[[Category: Contributed code]]
