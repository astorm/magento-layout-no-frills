Let's consider a Magento front end asset URL, broken out by segment.

    http://magento.example.com      #domain name
    /pub/static                     #base public asset path
    /version1514092162              #version slug
    /frontend/Magento/luma          #magento area (frontend, adminhtml, etc.)
    /en_US                          #theme identifier
    /Package_Module                 #module identifier (optional)
    /path/to/file.css               #asset path

### Domain Name

The domain name is your website's configured domain name.  It may seem obvious and redudant to mention this, but Magento does allow you to configure a domain name to use site wide at 

    ...

Subtle differences between what you think your site's URL is and what it actually is (https vs. http, www vs. non-www) can sometimes cause problems with your assets, so be sure you know what's going on with your system here.

### Base Public Path

Next we have the base publiasset c path.  As mentioned throughout this book, this path will vary dependingo on whether you've configured your webserver to use Magento root `index.php` file, or the `pub/index.php` file. 

### Version Slug

The version slug may be a little confusing, and almost certainly features a different number in your specific Magento system.  This is here strictly as a "cache busting" URL.  Modern browsers can be aggressive about caching CSS, Javascript, and other frontend assets.  This caching is problematic if you're launching new versions of these files on a regular basis.  By injecting this extra portion in an asset's URL, Magento tricks the browser into thinking its a new file, and the old cached version will be bypassed. 

### Magento Area

The portion of the URL is the Magento area.  Different areas (the `frontend` cart, the back office `adminhtml`) may have a javascript/css file with the same name and path, but with different contents. 

### Theme Identifier

The portion of the URL is your theme's package name (`Magento` above) and your theme name (`luma`).  This segment needs to be here since Magento's theming system allows you to replace specific frontend asset files.

### Locale

The portion of the URL is the locale, or language, identifier for a site.  It may seem a little excessive to allow different locales to have different CSS and Javascript files, but if you cnosider things like CSS `:after` text, or the need to localize string constants in UI libraries, it makes sense.

### Asset Path

Finally, we have the path to the asset

### Module Name (optional)

If your file is a part of a module (as opposed to being a part of a theme), this portion of the URL is the `Packagename_Modulename` module identifier for the module where the asset is located.  

## Turning URLs into Paths

Magento will take the above URL and transform it into to a file path.  For example, this URL for the stock Magento `style-m.css` file

    http://magento.example.com
    /pub/static
    /version1514092162
    /frontend
    /Magento
    /luma
    /en_US
    /css
    /styles-m.css
    
corresponds to the following file path 

    $ ls -lh ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css
    -rw-rw-r--  1 _www  staff   334K Dec 23 21:09 ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css
    
That is, Magento takes each segment (minus the version slug) and treats it as a path on your file system.

Some of you may be scratching your head -- we never created a `styles-m.css` file and putting it in the `pub/static` folder.  How did these files get here?         

Here's a quick experiment that will help clear that up.  You'll need to be in developer mode for this to work.

First, lets url `curl` to check Magento can download `styles-m.css`.  We'll use `curl`'s `-I` modifier to look at the HTTP response headers, but you can also just load the URL directly in a browser

    curl -I 'http://magento-2-2-x.dev/pub/static/version1514092162/frontend/Pulsestorm/dram/en_US/css/styles-m.css'
    HTTP/1.1 200 OK
    //...
    
So, that's a `200 OK` code -- that means the file's there.  Next, let's **remove** `styles-m.css`   

    $ rm ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css

With the file removed, lets try downloading it again.  We'd expect to get a `404 Not Found` error -- except

    $ curl -I 'http://magento.example.com/pub/static/version1514092162/frontend/Pulsestorm/dram/en_US/css/styles-m.css'
    HTTP/1.1 200 OK
    //...

The URL still serves the file!  What's going on.

You first though may be that we've told you an un-ture thing, and that the `styles-m.css` file we pointed you at isn't the file that Magento's serving.  At least, that's what I thought the first time I encountered this behavior.  However, if you take a look at your file system again

    $ ls -lh ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css
    -rw-rw-r--  1 _www  staff   334K [today] ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css
    
You'll see that **something** has recreated the file for us!

Static Asset Serving
--------------------------------------------------
When you deploy a Magento system to production, you need to run the following command

    $ php bin/magento setup:static-content:deploy
    
This command looks though all Magento's module's and themes for frontend assets, and creates them in the `pub/static` folder file hierarchy we discussed above.

While this is an OK solution for a production system -- **developing features** under this system would be incredibly tedious and time consuming.  Change a file, deploy site, wait, whoops that wan't it, repeat.  To combat this problem, Magento deploys frontend assets **on demand** when you're running in developer mode.  

If we take a look inside the `pub/static` folder, we'll find an `.htaccess` file

    #File: pub/static/.htaccess
    # ...
    <IfModule mod_rewrite.c>
        RewriteEngine On

        # Remove signature of the static files that is used to overcome the browser cache
        RewriteRule ^version.+?/(.+)$ $1 [L]

        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-l

        RewriteRule .* ../static.php?resource=$0 [L]
    </IfModule>    
    # ...
    
This rewrite rule takes every request for a file in a `pub/static/version*` folder and, if the file doesn't already exist, routes the request to the program in `static.php`.  In other words, a request for this URL

    http://magento-2-2-x.dev/pub/static/version1514092162/frontend/Pulsestorm/dram/en_US/css/styles-m.css
    
is **actually** a request for
    http://magento-2-2-x.dev/pub/static.php?resource=frontend/Pulsestorm/dram/en_US/css/styles-m.css
    
This `pub/static.php` program is Magento's static asset server.  Its job is to use the `resource` parameter to find a frontend asset file in Magento's themes and modules, save that file to disk for future requests, and return the file.

This `.htaccess` file is also the place where the `version...` slug is stripped out of the URL.

For folks using nginx, Magento has a sample configuration with similar rules

    #...
    
    location ~ ^/static/version {
        rewrite ^/static/(version\d*/)?(.*)$ /static/$2 last;
    }
    
    #...
    
    if (!-f $request_filename) {
        rewrite ^/static/?(.*)$ /static.php?resource=$1 last;
    }

    #...
    
Be careful with your nginx configuration -- it's not uncommon for engineers to remember one of these rules, but forget the other.  If you're not seeing frontend assets, this is the first place to look. 

## The Static Asset Server

If we take a look at `static.php`

    #File: pub/static.php
    require realpath(__DIR__) . '/../app/bootstrap.php';
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
    /** @var \Magento\Framework\App\StaticResource $app */
    $app = $bootstrap->createApplication(\Magento\Framework\App\StaticResource::class);
    $bootstrap->run($app);    

We see that Magneto bootstraps and runs an independent application.  This is similar (but simpler) to what happens in Magento's `index.php` file.  If we take a look at that application's source file, we can see the main work of the application in the `launch` method (which is eventually called after the `run` method above) 

    #File: vendor/magento//framework/App/StaticResource.php
    public function launch()
    {
        // disabling profiling when retrieving static resource
        \Magento\Framework\Profiler::reset();
        $appMode = $this->state->getMode();
        if ($appMode == \Magento\Framework\App\State::MODE_PRODUCTION) {
            $this->response->setHttpResponseCode(404);
        } else {
            $path = $this->request->get('resource');
            $params = $this->parsePath($path);
            $this->state->setAreaCode($params['area']);
            $this->objectManager->configure($this->configLoader->load($params['area']));
            $file = $params['file'];
            unset($params['file']);
            $asset = $this->assetRepo->createAsset($file, $params);
            $this->response->setFilePath($asset->getSourceFile());
            $this->publisher->publish($asset);
        }
        return $this->response;
    }
    
We're not going to dive deep into this program, but here's a few lines worth paying attention to.   

    if ($appMode == \Magento\Framework\App\State::MODE_PRODUCTION) {
        $this->response->setHttpResponseCode(404);
    }  
    
If Magento detects you're in production mode, it automatically returns a 404.  Magento still needs to use the `.htaccess` rules from above to strip the `version...` string from URLs, but doesn't want to dynamically server files in production mode for performance and security reasons. 

These three lines

    $asset = $this->assetRepo->createAsset($file, $params);
    $this->response->setFilePath($asset->getSourceFile());
    $this->publisher->publish($asset);
    
are where the bulk of the work happen.  The `createAsset` method is what does most of the work in using that `resource` path to find an actual file.  The call to `setFilePath` on the response object is what tells Magento how to send the front end file to the browser **for this request only**.  The call to `publish` on the `publisher` object is what saves the file to the `pub/static` folder.  

In an ideal world, you wouldn't need to be aware of this file.  However -- if there's a bug in the static asset serving application, you may need to learn the finer points of debugging to fix it.  Also, if your file permissions aren't set correctly Magento may not be able to create the file in your `pub/static` folder.  Beyond being annoying, the large number of Javascript files Magento needs means there are dozens of requests to this program when you first start using a Magento system.  

Only using the static asset serving application is not a viable development strategy -- if your system is behaving super slow even with Magento caches on, you may want to look into the performance of this application. 