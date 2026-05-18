# Twine

*Code will be uploaded at a later time, once it's in a more finished state.*

This is the most feature-complete open-source backend reimplementation for Vine.

[Demo Video](https://www.youtube.com/watch?v=puNC5kEVflo)

## Setup
I use the latest version of XAMPP to host the server. If hosting using standalone Apache, [enable/install SQLite](https://serverfault.com/questions/32982/how-do-i-enable-sqlite-on-linux-apache-php). Be sure to also have `ffmpeg` usable through your system's command line.

To make the app point to the server, I use bag.xml's VineRedirect, which works on jailbroken iOS 5 & 6 on versions <1.4.8. You can also [patch the app directly](https://github.com/bruhdudeisdead/clematis/wiki/Patching).

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
- Explore page w/ trending tags (`/explore/v2` and `/explore/v3`)
  - Seeing vines in specific channels/Channels in general
- Explore tags
- Follow requests
- Setting account to sensitive doesn't really do anything
- VMs

## Compatible Versions

| Version | Status |
| ------------- | --------------- |
| 1.0.5 | Fully supported |
| 1.3.3 & 1.3.4 | Fully supported |
| 1.4.5 & 1.4.8 | Somewhat compatible, frequently crashes |
| Other | Unknown |

I don't plan to fully support versions other than 1.4.x, 2.x.x, and 5.7.0.

## FAQ
> *Frontend?*

No.

> *ETA?*

Several weeks.

## Attribution
Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis), [bag.xml's VineX](https://github.com/bag-xml/VineX), and [Savefade's osm2foursquare](https://github.com/savefade/osm2foursquare) for compatability with 1.0.5 and 1.3.x.
