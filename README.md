# Twine

*Code will be uploaded at a later time.*

Another backend for Vine, made as an experiment and **NOT ready for production use**. If you decide to host your own revival and wish to use this backend, be aware there are vulnerabilities that will need to be repaired, mainly with SQL injection.

I don't intend to start my own Vine revival for a multitude of reasons. Do what you wish with this.

## Setup
I use the latest version of XAMPP to host the server. To make the app point to the server, I use bag.xml's VineRedirect.

## Working:
- Registering new account
- Logging in
- Making & deleting posts
- Modifying user settings:
  - Settings page
  - Profile photo
  - Username, description, location, email, phone number
  - Setting or unsetting profile to private or "sensitive" (explicit)
- Timelines:
  - Main feed
  - Per-user/profile
  - Liked vines from user
- Explore users
- Following/unfollowing, followers list and following list
- Notifications
- Adding location to posts (credit: [osm2foursquare](https://github.com/savefade/osm2foursquare) by Savefade)
- Revining
- Blocking
- Reporting

## Not working:
- Explore page (not searching for people or tags): `/explore/v2`
- VMs
- More stuff I probably forgot to mention...

Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis)!
