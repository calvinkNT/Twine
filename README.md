# Twine

**Code will be uploaded at a later time, once it's in a more finished state.**

This is the most feature-complete open-source backend reimplementation for Vine. ([Demo Video](https://www.youtube.com/watch?v=puNC5kEVflo))

## Disclaimer
This project does not claim ownership of, or affiliation with, the Vine trademark or any official Vine clients or applications.

If you choose to host an instance of this backend, you acknowledge and agree that:
- You accept all risks and responsibilities associated with operating the instance.
- You have configured a valid and reachable DMCA contact email in `$emailForDmca` within `_config.php`.
- You are solely responsible for content moderation, DMCA compliance, security, and legal obligations related to your instance.

## Setup
**If you're not comfortable managing an Apache web server and handling basic web development tasks without step-by-step guidance, you should not host an instance.** If you choose to anyways, please do not contact me for assistance.

I use the latest version of XAMPP to host the server for development. If hosting using standalone Apache, [enable/install SQLite](https://serverfault.com/questions/32982/how-do-i-enable-sqlite-on-linux-apache-php). Be sure to also have `ffmpeg` usable through your system's command line.

To make the app point to the server, I use bag.xml's VineRedirect. You can also [patch the app directly](https://github.com/bruhdudeisdead/clematis/wiki/Patching).

## FAQ
> *Can you make a tutorial video on how to set it up?*

No.

> *Frontend?*

No.

> *eta wen???*

Several weeks at best.

## Working:
- Registering new account
- Logging in
- Modifying settings
  - Profile photo
  - Username, description, location, email, phone number
  - Setting or unsetting profile to private or sensitive (see "Not Working")
- Timelines
  - Main feed
  - Per-user/profile
  - Liked vines from user
  - Editor's Pick
  - Popular Now
  - On-The-Rise
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
  - Explore page with trending tags, Editor's Pick, Popular Now, and On-The-Rise buttons

## Not working:
- Although you can set your account to private or sensitive, it doesn't do anything at the moment.
- VMs
- Integration with FaceBook and Twitter (I do not plan to fix)

## Compatible Versions

- 1.0.5: Fully supported
- 1.3.x: Fully supported
- 1.4.5: Fully supported
- Other: Unknown

I don't plan to fully support versions other than 1.3.x, 1.4.x, 2.5.1, and 5.7.0.

## Attribution
Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis), [bag.xml's VineX](https://github.com/bag-xml/VineX), and [Savefade's osm2foursquare](https://github.com/savefade/osm2foursquare).
