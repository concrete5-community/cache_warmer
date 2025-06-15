# Cache Warmer

If you clear the cache, all pages need to be regenerated. Of course you can wait until your visitors have requested all your pages, but then they will experience a significantly slower page load. **Cache Warmer can automatically generate cache files** for pages that have Full Page Caching enabled.

### When do I need a cache warmer?

In case you have many pages and the cache is cleared every now and then. It's also very useful if your pages use the 'old' **getThumbnail** method, because those thumbnails are removed when the cache is flushed. By using the cache warmer, these thumbnails will be generated again.

### Benefits

*   **Faster loading times** after clearing the cache
*   No **SEO** penalties because a page loads slowly.
*   Ability to filter on certain page types
*   **Rebuilds thumbnails** that were stored in the cache folder

### How it works

Each time you clear the cache, a signal goes to the Cache Warmer so it knows it's needed. To let it do its work, there are two ways:

1\. Run the Cache Warmer job manually from the dashbard, or  
2\. Run the Cache Warmer through a **CLI command**, or  
3\. Run the Cache Warmer job **automatically via a cron job**.

I recommend option 3. You can set the crob job to run **each minute**. It will simply exit if no pages need to be rewarmed.

### What to know

*   All settings from Cache Warmer are optional
*   You can run the Cache Warmer automatically via a cron job

### Requirements

To run Cache Warmer you need:

*   concrete5 5.7.4 or higher
*   PHP 5.3 or higher

### Installation

**Important:**  
At least one of your pages should allow Full Page Caching (FPC). So make sure your global cache settings allow FPC or make sure the individual page(s) allow FPC.
  
To create the cache files, Cache Warmer basically visits your pages one by one. For that reason, the server is going to be busy if you have hundreds of pages! I'd suggest you to schedule the Job at night time, or limit the number of pages per batch. A batch is created when the Job is executed. A batch consists of a series of pages, randomly selected.

**Automated Jobs**  
For small websites, you can run the Job by hand via **Dashboard / Systems & Settings / Optimization / Automated Jobs**. Once you hit the 'Run' button, a popup will appear. While the popup is visible, concrete5 will send various requests to keep the Job going. For each request, Cache Warmer will generate cache files for five pages.

**CLI command**  
The preferred way to run Cache Warmer is via the command line interface. Run it via ````./concrete/bin/concrete5 c5:job cache_warmer````  
  
**Cron job**  
You can automate the Jobs by **scheduling** them. I recommend using a **cron job** for this. If your server doesn't support that, concrete5 can trigger the Job automatically when a someone visits your website.

If you want to set up a cron job but you don't know how to, either ask your System Administrator or ask Google: [https://stackoverflow.com/questions/22358382/execute-php-script-in-cron-job](https://stackoverflow.com/questions/22358382/execute-php-script-in-cron-job)

### FAQ

**Which cache files are generated?**  
The add-on will request and process pages as if it were a normal site visitor. All cache files that would normally be generated will be generated for the targeted pages.

**Which cache settings should be enabled?**  
Only pages with Full Page Cache (FPC) enabled will be processed. If you have FPC disabled on all pages, the add-on won't generate any cache files. There are three scenarios to make Cache Warmer do its thing:

1.  FPC turned off, but enabled for 1 or more pages.
2.  FPC turned on if blocks allow it.
3.  FPC turned on in all cases.

**Could Cache Warmer harm my website?**  
No, it only generates cache files. Files that would normally be created if you manually visited a page. However, Cache Warmer sends several requests to process all pages. This could potentially affect the response time of your server.

**Does it support CLI?**  
Version 2.0.0 introduces a CLI mode with a progress bar. It's available for concrete5 8.3.1 and higher.

**What's the roadmap?**  
I'm thinking of a setting to create a short delay between requests. It'd be handy for larger websites to set a delay between requests to prevent that a server becomes slow. If you have ideas to improve the add-on, please let me know!
  
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
