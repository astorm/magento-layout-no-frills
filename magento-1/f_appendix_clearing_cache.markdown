The Hows and Whys of Clearing Magento's Cache
==================================================	
In layman's terms, caching in computer science/software engineering refers to the practice of doing something that's resource intensive once, storing the results somewhere else, and then the next time someone wants it you hand them the stored result.  The most common example of this for web developers is browser caching.  Asset files (images, CSS, Javascript) will be downloaded once, and then stored locally for a period of time.  This results in better network performance, and hair pulling by web developers at 2am wondering why their CSS files aren't being updated.

Magento, like most modern web frameworks, heavily utilizes a caching system to improve performance.  For example, certain configuration files are loaded once from disk once, combined, and then the combined config is stored on disk for later use.  This includes the Layout XML files.  This means if you have caching turned on (CE ships with caching on out of the box), you'll need to clear your cache after making any changes to the layout files.  Otherwise, the old, cached version of the Package Layout will be loaded and Magento won't see your changes, and you'll be left wondering why your new block isn't showing up.

You can control cache settings by navigating to 

	System -> Cache Managment

in the Admin Console, (see *Figure F.1*) 

<img src="images/appendix_f/cache-manage.png" />
	
From this page you can clear out the cache, or turn it off entirely. 	

Occasionally, a cached configuration may prevent you from getting to the Magento Admin Console.  For example, consider an event observer with an invalid class name.  If this happens, you'll want to look in the <code>var/cache</code> folder

	ls -l var/cache
	total 0
	drwxrwxrwx   10 _www  staff   340 Mar 15 16:10 mage--0
	drwxrwxrwx   10 _www  staff   340 Mar 15 16:10 mage--1
	drwxrwxrwx    6 _www  staff   204 Mar 15 16:02 mage--2
	drwxrwxrwx    8 _www  staff   272 Mar 15 16:02 mage--3
	drwxrwxrwx    8 _www  staff   272 Mar 15 16:10 mage--4
	drwxrwxrwx   12 _www  staff   408 Mar 15 16:02 mage--5
	drwxrwxrwx   10 _www  staff   340 Mar 15 16:02 mage--6
	drwxrwxrwx    8 _www  staff   272 Mar 15 16:02 mage--7
	drwxrwxrwx   12 _www  staff   408 Mar 15 16:02 mage--8
	drwxrwxrwx   10 _www  staff   340 Mar 15 16:02 mage--9
	drwxrwxrwx    6 _www  staff   204 Mar 15 16:02 mage--a
	drwxrwxrwx   10 _www  staff   340 Mar 15 16:02 mage--b
	drwxrwxrwx  164 _www  staff  5576 Mar 15 16:02 mage--c
	drwxrwxrwx  172 _www  staff  5848 Mar 15 16:02 mage--d
	drwxrwxrwx    4 _www  staff   136 Mar 15 16:10 mage--e
	drwxrwxrwx   12 _www  staff   408 Mar 15 16:10 mage--f
	
	
This folder is where Magento stores its cached data. Delete everything in this folder to manually clear the Magento cache and restore you store's functionality. 

*Visit http://www.pulsestorm.net/nofrills-layout-appendix-f to join the discussion online.*