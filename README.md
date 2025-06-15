# Cache Warmer

If the cache for a certain page is not present, the loading time will be higher. A cache warmer aims to preload pages in order to **build up the cache**. This results in faster loading times for your visitors.

### When do I need a cache warmer?

In case you have many pages and the cache is cleared every now and then. It's also very useful if your pages use the 'old' **getThumbnail** method, because those thumbnails are removed when the cache is flushed. By using the cache warmer, these thumbnails will be generated again.

### Benefits

*   Faster loading times after clearing the cache
*   Ability to filter on certain page types
*   Rebuilds thumbnails that were stored in the cache folder

### Requirements

To run Cache Warmer you need:

*   concrete5 5.7.4 or higher
*   PHP 5.3 or higher

### What to know

*   All settings from Cache Warmer are optional
*   You can run the Cache Warmer automatically via a cron job
  
### Reviews

**"Great addon"** - **Martinbuerge** on 1/8/19, 8:48 AM
I have a very large site with about 1000 pages.  
Performance is very good after setting up a cron job.

**"Very handy tool"** - **WillemAnchor** on 4/9/16, 5:16 AM
Excellent add-on that keeps your site up to speed. You can use this job on demand, or schedule it automatically with a CronJob.

**"One of the Best mods for C5. Period."** - **Venderpeg** on 2/6/17, 1:01 AM
This great little add-on is perfect for a little piece of mind. On my site it runs through 145 pages in about a minute and a half. So, I find one thing I need to change on the site and there's no time for maintenance mode. I edit, clear the cache, then run this utility. I now have piece of mind that all visitors are seeing the most current pages and with no need to wait forever for C5 to cache itself. The ability to have late night cron jobs as well are a extra nice + to the above stuff.  
Buy this now while it's cheap.

**"Screaming fast!"** - **Marrow** on 9/21/16, 12:52 PM
I added Cache Warmer and GTmetrix doesn't lie.  
No more waiting for users to access a page before it's cached. This beauty creates the cache files all at once and delivers a smoother, faster user experience. I do suggest utilizing Cron for this add-on to ensure successful up-to-date caching.  
Nice work!
