# Twine

*Code will be uploaded at a later time.*

Currently, this is the most feature-complete open-source backend reimplementation for Vine.

## Setup
I use the latest version of XAMPP to host the server. Just drop the files in `htdocs` and you're good to go.

To make the app point to the server, I use bag.xml's VineRedirect.

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
- Explore tags
- Follow requests
- VMs (will probably never be implemented by me.)

Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis)!
