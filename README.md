# Twine

*Code will be uploaded at a later time.*

This is a backend for Vine, primarily made for app version 1.3.4.

## Disclaimers
Made as an experiment and **NOT ready for production use**. If you decide to host your own revival and wish to use this backend, be aware there are vulnerabilities that will need to be repaired, mainly with SQL injection.

I will not start my own Vine revival for a multitude of reasons. Do not contact me asking to start one or for help starting one. 

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
- Adding location to posts (Thanks to [osm2foursquare](https://github.com/savefade/osm2foursquare) by Savefade)
- Revining
- Blocking
- Reporting

## Not working:
- Explore page (not searching for people or tags): `/explore/v2`
- VMs
- Follow requests

Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis)!
