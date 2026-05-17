# Twine

*Code will be uploaded at a later time.*

Currently, this is the most feature-complete open-source backend reimplementation for Vine. If you host an instance of this server without significant modification to the code, please credit me. 

[Demo Video](https://www.youtube.com/watch?v=puNC5kEVflo)

## Setup
I use the latest version of XAMPP to host the server. Just drop the files in `htdocs` and you're good to go.

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
- Interactions
  - Revining
  - Notifications
  - Blocking
  - Reporting
  - Following/unfollowing
- Explore users
- Channels

## Not working:
- Explore page (not searching for people or tags): `/explore/v2`
- Explore tags
- Follow requests (following a private account immedately accepts the follow request)
- Setting account to sensitive doesn't really do anything
- VMs (will probably never be implemented by me)

## Credits
Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis) and [Savefade's osm2foursquare](https://github.com/savefade/osm2foursquare).
