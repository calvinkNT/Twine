# Twine

Code will be uploaded at a later time.

Another backend for Vine. Made as an experiment and **NOT ready for production use**. If you decide to host your own revival and wish to use this backend, be aware there are plenty of vulnerabilities.

I don't intend to start my own Vine revival since Ivy's community was absolutely terrible, and don't have the time or energy to deal with some people in the LegacyJailbreak or similar revival communities right now.

## Setup
I use the latest version of XAMPP to host the server.

To make the app point to the server, you can use bag.xml's VineRedirect, or patch the app directly:
1. Download the IPA of Vine 1.3.3 (the version I'm using, but I'm pretty sure most versions of 1.x.x work)
2. Rename the extension from `.ipa` to `.zip`
3. Unzip the folder
4. In Payload folder, there is another `.app` file. Reveal its contents and navigate inside.
5. Find the "iphone" file. That's the app's binary.
6. Drop the binary into https://hexed.it
7. Change all URLs to point to your server. Be aware that the length of the URLs must match, i.e. `api.vineapp.com` (15 characters) or whichever URL you're replacing must be the same length as your new URL.
8. Save the binary
9. Drop it back into the `.app`'s contents
10. Zip the `Payload` and `iTunesArtwork` files
11. Change the extension from `.zip` to `.ipa`
12. Sideload!

## Working:
- Registering new account
- Logging in
- Making & deleting posts
- Modifying user settings:
  - Profile photo
  - Username, description, location, email, phone number
- Timelines:
  - Main feed
  - Per-user/profile
  - User's likes
- Explore users
- Following/unfollowing
- Follower and following pages

## Not working:
- Revining
- Reporting
- Blocking
- Explore page (not searching for people or tags)
- Follow requests
- VMs
- More stuff I probably forgot to mention... whatever's not on the "working" list.

Big thanks to [bruhdude's clematis](https://github.com/bruhdudeisdead/clematis)!
