Page Out Of Date Reminder
=========================

This WordPress plugin can automatically alert a page owner when a page
has not been edited in a while.

### Page indicators
* Fresh - page has been updated recently (time elapsed < minimum threshold) and all links work
* Stale - page has not been updated recently (time elapsed >= minimum threshold), or some links are broken
* Frozen - age and link validity are irrelivant. page will never be updated (it is archived or historical)

### Planned Features (all user-configurable):
#### Detection of stale pages
* minimum page time threshold - time before a page is considered stale
* reminder interval - time between reminders about a stale page, after threshold has been reached 
 * ex if threshold is 1 year and interval is 6 months,
   owner will get alerted at 1 year, 1 year & 6 months, 2 years, etc
   until the page is updated (freshened) or marked as frozen.
 * Possibly also allow diminishing interval that gets more persistent over time
* Allow post specific overrides for threshold and interval
* Allow global default as well as site-specific setting
* Allow post type or tag to have custom thresholds and intervals
* Allow options for stale pages to simply not alert the user ever (page is historical and will never be updated)
 * Perhaps these pages could be referred to as 'archived' or 'preserved' or 'frozen'

#### Detection of 'page owner'
* Default to Author
* Option to detect page owner based on last X number of edits
 * for example, if Author is ABC, but the last 4/5 edits were by DEF, assume owner is currently DEF
* Allow manually set page owner
 * Would be neat to allow non-WordPress users to be owners, such as a group or mailing list
* Allow multiple owners (or at least multiple contacts for when a page goes stale)

#### Multiple alert methods
* email
* dashboard page listing all stale pages
 * hopefully just listing stale pages that the logged-in user can actually edit
* text message (if they _really_ want to know)

#### Broken link detection
* If a broken link is detected on the daily scan of all pages, mark page as stale and flag broken link somehow
* Will scan all links and images and try to get the headers for that link.
  If the server returns an error, mark that link as broken.

#### Page freshened
* After becoming stale, a user can simply mark the page as fresh to reset all timers for that page.
* If a link is broken, the page will immediately become stale until the link is fixed

Page frozen
* Override ability for pages to prevent age limits and link validity. Used for pages that will never be updated.
