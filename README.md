# Twine

**Code will be uploaded at a later time, once it's in a more finished state.**

This is the most feature-complete open-source backend reimplementation for Vine. ([Demo Video](https://www.youtube.com/watch?v=puNC5kEVflo))

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
  - Editor's Pick
  - Popular Now
  - On The Rise
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
- Exploring
  - Exploring users
  - Exploring tags
  - Explore page with trending tags, Editor's Pick, Popular Now, and On The Rise buttons

## Not working:
- VMs
- Setting your account to private or sensitive doesn't do anything
- Integration with FaceBook and Twitter (I won't repair this)

## Compatible Versions

| Version | Status |
| ------- | ------ | 
| 1.x.x | Fully supported |
| 2.5.1 | Planned |
| 5.7.0 | Planned |
| Other | Unknown |

## Setup
**If you're not comfortable managing an Apache web server and handling basic management tasks without step-by-step guidance, you should not host an instance.** Please do not contact me for support in regards to hosting an instance.

I use Apache to host the server, with the `SQLite3` and `PDO_SQLITE` PHP extensions installed (XAMPP will automatically have these enabled). Be sure to also have `ffmpeg` usable through your system's command line.

Drop the server in `/var/www/html` (or `htdocs` if using XAMPP), write your DMCA contact email in `_config.php`, make sure your app is pointed to the server, and register a new account through the app. The db file will be created automatically.

[How to make the app point to the server](https://github.com/bruhdudeisdead/clematis/wiki/Patching)

## Disclaimer
This project does not claim ownership of, or affiliation with, the Vine trademark or any official Vine clients or applications.

If you choose to host an instance of this backend, you acknowledge and agree that:
- You accept all risks and responsibilities associated with operating the instance.
- You have configured a valid and reachable DMCA contact email in `$emailForDmca` within `_config.php`.
- You are solely responsible for content moderation, DMCA compliance, security, and legal obligations related to your instance.

## Attribution
Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis), [bag.xml's VineX](https://github.com/bag-xml/VineX), and [Savefade's osm2foursquare](https://github.com/savefade/osm2foursquare).
