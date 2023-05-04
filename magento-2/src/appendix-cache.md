## The Magento Cache
	
The concept of a *cache* in programming is a relatively simple one.  However, as time goes on, the *practice* of caching has become more and more complicated.

Caching is the process of

1. Doing something computationally intensive or time consuming.
2. Saving the result.
3. Using the saved result the next time you need that same thing.

Caching is a popular solution for performance problems because it allows developers to solve their problem without worrying about performance up-front. In isolation, this may seem lazy. However, considered in the life of a working programmer who is writing new algorithms everyday, and who never knows which ones need to be performant and which ones do not, caching is often the only solution that makes sense.  Deciding what to cache, when, and for how long is a fundamental part of modern systems development.

Magento 1 featured an infamous number of caches.  Magento 2 has kept all of these, and added a few new layers on top.

You can see identifiers (also known as *tags*) for each of Magento's cache types by using the `cache:status` CLI command

    $ php bin/magento cache:status
                        config: 1
                        layout: 1
                    block_html: 1
                   collections: 1
                    reflection: 1
                        db_ddl: 1
                           eav: 1
         customer_notification: 1
                     full_page: 0
            config_integration: 1
        config_integration_api: 1
                     translate: 1
             config_webservice: 1

                
Covering what each of these cache types does is beyond the scope of this book.  We will, however, briefly cover a few front end related caches.  We'll also cover some things not on this list, but that are cache-like systems you'll need to be aware of when working with Magento 2.  Finally we'll cover clearing these caches via formal, and informal, means.          

#### layout

The `layout` cache stores pre-built XML configuration trees for *layout related XML files*.  This means files in your module and theme's `view` folder, and **not** files in your `etc` folder. 

#### block_html

Most `phtml` templates in Magento have a corresponding PHP Block class.  A block class author can, if they wish, configure these blocks to be cached.  That is, they'll render once using `phtml` code and block class methods, but the next render will simply pull the previous output from cache.  

Magento's category navigation block is a prominent example of this -- as querying a large category list is often a computationally complex action. 

#### full_page

The full page cache is a new cache type introduced in Magento 2.  The full page cache will cache the results of individual Magento page requests to disk.  This means with full page caching enabled, the first request for a Magento page will use Magento's controllers and layout system to build the page's output and save the page to cache before serving it. The next request will skip these subsystems, opting to serve the page from the cache.

This feature existed in Magento 1's enterprise versions, and Magento 1 CE had many third party full page caching extensions available.  Because this feature ships in all versions of Magento 2, module and theme developers will need to test their feature both with full page caching enabled, and full page caching disabled.

Although this cache type is grouped with Magento's others, and you're able to use standard cache commands to manage (i.e. clear) the full page cache, Magento **does not** store these cached pages in the standard cache storage.  Instead, full page cache files are (by default) stored in the

    var/page_cache
   
folder.  Finally, it's worth noting that some Magento team members have stated the stock full page cache behavior is intended as a demo, and that it shouldn't be used in production.  Regardless of how you decide to deploy, you'll want to be aware of full page cache while you're developing or distributing code to developers who may run with this mode enabled. 

### Full Page Cache with Varnish

By default, Magento's full page cache stores its cached pages on disk, in the `var/page_cache` folder.  However, it's possible to configure Magento to use varnish for its full page cache.  Varnish is a web server proxy whose sole job is to save pre-rendered HTTP responses (HTML pages, XML files, JSON files, etc.)

You can "turn on" varnish by navigating to 

	Stores -> Configuration -> Advanced -> Full Page Cache

Magento's varnish implementation will generate a varnish VCL file for you to use with your varnish system.  It will not setup or run varnish automatically for you.  Magento 1 had no official varnish support, but if you've ever used the Magento 1 Nexcess Turpentine extension you'll feel right at home.  Per our previous comments on Magento not recommending full page caching on disk (the default behavior) for production sites, Magento **does** recommend you run your stores behind varnish proxy servers.

If you're interested in running varnish and don't know how, you'll need to check with your server infrastructure team/partners.

### LessCSS Caches

Magento uses a front end tool called LessCSS to generate its cross browser CSS files.  On the fly rendering of LessCSS files is another place where you can benefit from a caching system.

Magento stores its LessCSS caches in 

    var/view_preprocessed

If you're making changes to LessCSS files, you may need to clear out this folder to see your changes.

There's no `bin/magento` command to clear out these LessCSS caches.  However, Magento does ship with grunt tasks that will clear out *some* of the LessCSS caches.  See the Front End Build System appendix for more details.

### Front End Files (Static Assets)

Magento's front end asset files (CSS, JavaScript, etc.) are, for the most part, generated on the fly.  As previously mentioned, CSS files are generated by LessCSS. JavaScript files, while not generated, are stored with Magento's modules and moved *on demand* to the `pub/static` folder.  By on demand, we mean as a user requests them, or when you deploy your Magento system (using the `setup:static-content:deploy` command).

### Other Cache Entries

Keep in mind that Magento's caching objects are available for third party extension developers to use however they want.  Even if you clear **all** the identifiers listed above, another developer may have tagged a cache entry with their own cache tags and not one of the 13 Magento cache types.  For these cases, Magento offers a Flush Cache Storage option, available at 

    System -> Tools -> Cache Management

or

    $ php bin/magento cache:flush
    
This will clear Magento's primary cache storage engine of all entries.

### Notes on Clearing Cache

Throughout this book, we'll try to remind you when you'll need to clear your Magento cache to see your changes show up. Magento's main cache storage is located (by default) in the 

    var/cache 
   
folder. You can clear your cache by removing this entire folder.  However -- it's also possible (and recommended) to configure Magento to use an in-memory caching solution like redis or memcache.  Because of this, it's probably best to develop the habit of clearing your cache using the built in Magento command line command
 
     $ bin/magento cache:clean
 
This will run through each of Magento's main cache types and clear them out.

Another caveat -- if your full page cache engine is varnish, or another third party full page cache implementation, the above command may not clear this cache.  Also, as previously mentioned, the LessCSS files and front end assets only behave like cached entries, they're not an official part of the Magento cache.  

Whenever you're facing a problem where it *seems* like you've edited the right file, but your changes aren't showing up, it's always worth reviewing which cache and cache-like systems may be in play, and take the additional steps of 

1. Clearing those caches using the official tools
2. Moving on to clearing the storage directly (removing cache folder/files, deleting your redis key entries, etc. )
