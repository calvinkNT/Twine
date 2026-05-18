# Twine

*Code will be uploaded at a later time.*

Currently, this is the most feature-complete open-source backend reimplementation for Vine. If you host an instance of this server without significant modification to the code, please credit me. 

[Demo Video](https://www.youtube.com/watch?v=puNC5kEVflo)

## Setup
I use the latest version of XAMPP to host the server. If hosting using standalone Apache, [enable/install SQLite](https://serverfault.com/questions/32982/how-do-i-enable-sqlite-on-linux-apache-php). Be sure to also have `ffmpeg` usable through your system's command line.

To make the app point to the server, I use bag.xml's VineRedirect.

## Working:
- Registering new account
- Logging in
- Modifying settings
  - Profile photo
  - Username, description, location, email, phone number
  - Setting or unsetting profile to private or sensitive
- Timelines
  - Main feed
  - Per-user/profile
  - Liked vines from user
- Posting
  - Making & deleting posts
  - Adding location/venue
  - Adding to channel
- Interactions
  - Revining
  - Notifications
  - Blocking
  - Reporting
  - Following/unfollowing
- Explore users

## Not working:
- Explore page w/ trending tags `/explore/v2`
  - Seeing vines in specific channels
- Explore tags
- Follow requests
- Setting account to sensitive doesn't really do anything
- VMs (will probably never be implemented by me)

## Attribution
Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis), [bag.xml's VineX](https://github.com/bag-xml/VineX), and [Savefade's osm2foursquare](https://github.com/savefade/osm2foursquare).
