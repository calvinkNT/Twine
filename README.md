# Twine

WIP open-source backend reimplementation for Vine (NOT READY FOR PROD!)

[Demo Video](https://www.youtube.com/watch?v=puNC5kEVflo)

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
  - Blocking/unblocking
  - Reporting (profiles and vines)
  - Following/unfollowing
- Exploring
  - Exploring users
  - Exploring tags
  - Explore page
  - Looking at channels
- Activity (all types)

## Not working: 
- VMs 
- Integration with FaceBook and Twitter
- Password reset through app
- Follow requesting immediately is approved; I can't figure out the `/users/:id/notifications/followRequests` endpoint for the life of me

## Compatible Versions

| Version | Status |
| ------- | ------ | 
| 1.0.x | Mostly supported<sup>1</sup> |
| 1.1.x | Fully supported |
| 1.2.x | Fully supported |
| 1.3.x | Fully supported |
| 1.4.x | Fully supported |
| 2.5.1 | Planned |
| 5.7.0 | Planned |
| Other | Unknown, not planned<sup>0</sup> |

<sup>0</sup> If you'd like to improve on the project, please make a pull request.  
<sup>1</sup> Can't upload vines or see Explore page when using VineRedirect  
<sup>2</sup> Crashes on viewing timelines

## Setup
**If you're not comfortable managing an Apache web server and handling basic management tasks without step-by-step guidance, you should not host an instance.** Please do not contact me for support in regards to hosting an instance.

I use Apache to host the server, with the `SQLite3` and `PDO_SQLITE` PHP extensions installed (XAMPP will automatically have these enabled). Be sure to also have `ffmpeg` usable through your system's command line.

Drop the server in `/var/www/html` (or `htdocs` if using XAMPP), write your DMCA contact email in `_config.php`, make sure your app is pointed to the server, and register a new account through the app. The db file will be created automatically.

To make the app point to the server, I use [bag.xml's VineRedirect](https://cydia.bag-xml.com/), which works for jailbroken iOS 5-7 devices. A guide on patching for Android and other platforms can be found [here](https://github.com/bruhdudeisdead/clematis/wiki/Patching).

When patching domains in the binary, make sure that when you need different length domains (i.e. `api12.example.com` and `api1234.example.com`), they both redirect to your same server.

## Disclaimer
This project does not claim ownership of, or affiliation with, the Vine trademark or any official Vine clients or applications.

If you choose to host an instance of this backend, you acknowledge and agree that:
- You accept all risks and responsibilities associated with operating the instance.
- You have configured a valid and reachable DMCA contact email in `$emailForDmca` within `_config.php`.
- You are solely responsible for content moderation, DMCA compliance, security, and legal obligations related to your instance.

## Attribution
Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis), [bag.xml's VineX](https://github.com/bag-xml/VineX), and [Savefade's osm2foursquare](https://github.com/savefade/osm2foursquare).
