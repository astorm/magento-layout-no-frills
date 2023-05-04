# Advanced Front End Topics

Now that you're grounded in the basics of Magento's CSS and Javascript systems, there's a few advanced topics to cover.  First, we'll cover everything you can do in a layout handle XML file's `<head/>` section.  Second, we'll show you how to add arbitrary code to your HTML page's head sections.  Third, we'll explain how those Magento CSS and Javascript URLs are served during development mode, which should help you debug missing file problems.

## All `<head/>` directives

In earlier chapters we showed you how to add javascript and CSS/LessCSS files via a layout handle XML file's `<head/>` node.

    <head>
        <script src="Packagename_Modulename::path/to/file.js"/>
        <css src="Packagename_Modulename::path/to/file.css"/>
    </head>

There are a few other tags you can use inside `<head/>` that will influence your HTML page's output.

### Head Tag Attributes

The `<attribute/>` tag will let you set an attribute on **your page's** `<head/>` tag.  i.e. the following layout handle XML

    <head>
        <attribute name="foo" value="bar" />
    </head>

will give you an HTML page that looks something like this

    <html>
        <head foo="bar">
        </head>
        <body>
        </body>
    </html>

### Page title

The `<title/>` tag will let you give your page a title -- i.e., you can set the text value of the HTML title node.  Layout handle XML that looks like this

    <head>
        <title>Ten Reasons Magento 2 isn't Right for your Business</title>
    </head>

will give you an HTML page that looks something like this

    <html>
        <head>
            <title>Ten Reasons Magento 2 isn't Right for your Business</title>
        </head>
        <body>
        </body>
    </html>

### Meta Tags

Similar to the `<title/>` tag, you'll use a layout update XML's `<meta/>` to create `<meta/>` tags in your HTML page.  Layout update XML that looks like this

    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
    </head>

will produce an HTML page that looks something like this

    <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1"/>
        </head>
        <body>
        </body>
    </html>

The `<meta/>` tag also has a second form that uses a `property` attribute instead of a `name` attribute.

    <head>
        <meta property="viewport" content="width=device-width, initial-scale=1"/>
    </head>

In this second form, `property` is simply an alias for `name`.  The above layout update XML will produce the same HTML output.

### CSS, Link, and Script Tags

We've already discussed the `<css/>` tag in earlier chapters.  You use it to add a CSS file, or a LessCSS file compiled to CSS, to your page

    <head>
        <css src="path/to/file.css"/>
        <css src="path/to/file.less"/>
    </head>

Layout handle XML files also support a `<link/>` tag.

    <head>
        <link src="path/to/file.xxx" />
    </head>

You can use the `<link/>` to create HTML `<link/>` elements to files that are not CSS files (an RSS file, for example).

And of course, layout handle XML files also support a `<script/>` tag for adding javascript files to your page.

    <head>
        <script src="path/to/file.js" />
    </head>

Together, these three tags are known as *asset* tags in Magento 2.  Asset tags all share a few extra abilities we haven't yet discussed.  For example, by default, the `src` attribute is a path to a file, or a Magento URN

    <script src="path/to/file.js" />
    <script src="Package_Module::path/to/file.js" />

In both these cases, Magento will generate a final URL that reflects the location of Magento's static asset folder relative to the root and the current area/theme.  If you want to use a full URL instead of these local file paths, you can specify this via the `src_type` attribute.

    <script src="https://example.com/path/to/file.js" src_type="url" />
    <css src="https://example.com/path/to/file.js"    src_type="url" />
    <link src="https://example.com/path/to/file.js"   src_type="url"  />

A `src_type` of URL will allow you to set a hard-coded full URL path.  The default is `src_type="resource"`, which treats the `src="..."` value as a Magento asset resource (and expands the path accordingly).  The final (if little used) valid value for `src_type` is `src_type="controller"`.  This allows you to use a Magento MVC controller path as the `src="..."` value.  If you don't understand what an MVC controller is, don't worry.  It won't be on the test.

If you use an attribute name other than `src_type` in these three asset tags Magento will pass that attribute along to the final rendered HTML.  For example, the following layout update XML

    <css src="css/styles-l.css" media="screen and (min-width: 768px)"/>

will pass along the `media` attribute to the final rendered HTML.

    <link  rel="stylesheet"
           type="text/css"
           media="screen and (min-width: 768px)"
           href="http://magento.example...css/styles-l.css" />

The only exceptions to this are the `content_type="..."` and `ie_condition="..."` attributes.  Magento used the `content_type` tag in a previous version to set whether an asset was a CSS or Javascript file.  Similarly, Magento used the `ie_condition` attribute to surround the linked asset in Internet Explorer conditional comments.  Magento 2 uses neither of these tags in its current set of layout handle XML files, and their futures are uncertain.  Because of that uncertain future, we recommend you avoid using them.

### Removing an Asset

The final `<head/>` tag to cover is the `<remove/>` tag

    <head>
        <remove src="path/to/file.css" />
    </head>

The `<remove/>` tag allows you to remove a file added by a different layout handle XML file.  All you need to do is pass in the `src` of the file (matching the `src` from the other layout handle XML file) and Magento will skip adding that particular asset to the page.

## The head.additional Block

In addition to using Magento 2's layout XML system to automatically add front end asset URLs to your project, you can also create these URLs via PHP using a `Magento\Framework\View\Asset\Repository` object.

We'll show you how to do this below, as well as how to add arbitrary HTML to the `<head/>` of a Magento HTML page.

Starting with the later, add the following node to our previously created `default.xml` layout handle XML file

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/default.xml
    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <body>
            <referenceBlock name="head.additional">
                <block  template="Pulsestorm_Nofrillslayout::chapter-9/head.phtml"
                        class="Pulsestorm\Nofrillslayout\Block\Head"
                        name="pulsestorm_javascriptcssexample_block_head" />
            </referenceBlock>
        </body>
        <!-- ... -->
    </page>


The above code

1. Gets a reference to the `head.additional` block
2. Creates a new `Pulsestorm\Nofrillslayout\Block\Head` block named `pulsestorm_javascriptcssexample_block_head` that uses the `Pulsestorm_Nofrillslayout::chapter-9/head.phtml` template.
3. Adds that new block to the `head.additional` block using the reference from #1

The `head.additional` block is a special block. Any block added to `head.additional` will automatically be output into the `<head/>` area of a Magento page. It's a little weird that we're operating on the `<head/>` of a the document in the `<body/>` section of the layout update xml file, but sometimes Magento's a little weird.

Regardless, once we've got the layout XML in place, we'll want to create our new `Head` block class

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Head.php
    <?php
    namespace Pulsestorm\Nofrillslayout\Block;
    class Head extends \Magento\Framework\View\Element\Template
    {
    }

As well as a template

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter-9/head.phtml
    <!-- Hello There -->

With the above in place, clear your Magento cache and reload your page. You should see the `<!-- Hello There -->` comment in your page's `<head/>` node.

    <!-- Hello There -->
    </head>

With a new template rendered in `<head/>`, we're ready to render an asset URL using the asset repository.

## The Asset Repository

The `Magento\Framework\View\Asset\Repository` object allows us to create asset objects. Asset objects can convert a file identifier like `foo/test.js` or `Pulsestorm_Nofrillslayout::test.js` into a full URL.

Magento blocks come with an asset repository ready for us to use, but we need to do something a little weird to use it.  Change your `Head.php` file so it matches the following

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Head.php
    <?php
    namespace Pulsestorm\Nofrillslayout\Block;
    class Head extends \Magento\Framework\View\Element\Template
    {
        public $assetRepository;
        public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            array $data = []
        )
        {
            $this->assetRepository = $context->getAssetRepository();
            return parent::__construct($context, $data);
        }
    }

Magento block objects have so many dependencies that the Magento core team created a single `Magento\Framework\View\Element\Template\Context` object to manage them all.  This includes the asset repository object.

We've grabbed the asset repository from the context object, and assigned it to the `assetRepository` property of our block object. The other parameters in `__construct` and the call to `parent::__construct` are there for compatibility with the base template block class. Also, notice we made `assetRepository` a **public** property. This means we'll be able to access it in our `phtml` template.

Edit your `head.phtml` file so it matches the following.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter-9/head.phtml
    <?php
        $asset_repository = $this->assetRepository;
        $asset  = $asset_repository->createAsset('Pulsestorm_Nofrillslayout::test.js');
        $url    = $asset->getUrl();
    ?>
    <!-- Hello There -->
    <script src="<?php echo $url; ?>"></script>


With the above in place, clear your cache, delete the files in `var/generate/*` (because you changed an automatic constructor dependency injection constructor), and reload the page. If you view the raw HTML source, you should see a new `<script></script>` tag rendered with a full asset URL.

    <script src="http://magento.example.com/static/version1514092162/frontend/Pulsestorm/luma/en_US/Pulsestorm_Nofrillslayout/test.js"></script>

What we've done above is use the `createAsset` method of the asset repository object to create an asset object. Then, we use the `getUrl` method of the asset object to fetch the url of the asset. All we need to know is the file identifier -- Magento handles the grunt work of pulling together the correct URL path parameters for us.

## Static Files Serving

To close out this chapter, we're going to take an in depth look at the `static`/`pub/static` issue. We've already mentioned that Magento 2 ships with **two** `index.php` files.

    /path/to/magento2/index.php
    /path/to/magento2/pub/index.php


One is at the top level of Magento 2's distribution folder. The second is inside the "`pub`" folder. There's also separate, but similar, `.htaccess` files in each folder.

The file you **want** to use for your Magento system's root folder is `pub`. This is a modern PHP framework convention, and is one layer in a layered approach to protecting your PHP and configuration files from being accidentally exposed to the world. However, Magento still ships with the root level `index.php` because many hosting companies make changing your web root difficult, or impossible.

Whatever folder you end up using will have consequences for the paths Magento generates to front end asset files. For the purposes of this chapter, unless we explicitly state otherwise, assume we've setup our web server to point to the `pub` folder.  We're also assuming you're still running your system in `developer` mode.

## Serving a Front End Asset File

Let's take a look at each segment of a Magento front end asset URL.

    http://magento.example.com      #domain name
    /pub/static                     #base public asset path
    /version1514092162              #version slug
    /frontend/Magento/luma          #magento area (frontend, adminhtml, etc.)
    /en_US                          #theme identifier
    /Package_Module                 #module identifier (optional)
    /path/to/file.css               #asset path

### Domain Name

The domain name is your website's configured domain name.  It may seem obvious and redundant to mention this, but Magento does allow you to configure a domain name to use site wide at both

    Stores -> Configuration -> Web -> Base URLs
    Stores -> Configuration -> Web -> Base URLs (Secure)

Subtle differences between what you think your site's URL is and what it actually is (https vs. http, www vs. non-www) can sometimes cause problems with your assets, so be sure you know what's going on with your system here.

### Base Public Path

Next we have the base static asset path.  As mentioned throughout this book, this path will vary depending on whether you've configured your web server to use Magento's root `index.php` file, or the `pub/index.php` file.

### Version Slug

The version slug may be a little confusing, and almost certainly features a different number in your Magento system.  This is here to "cache bust" the URLs when you deploy a new set of assets.  Web browsers can be aggressive about caching CSS, Javascript, and other front end assets.  This caching is problematic if you're changing these files on a regular basis.  By injecting this extra portion in an asset's URL, Magento tricks the browser into thinking it's a new file that needs to be downloaded and re-cached.

### Magento Area

This portion of the URL is the Magento area.  Different areas (the `frontend` cart, the back office `adminhtml`) may have a javascript/css file with the same name and path, but with different contents.

### Theme Identifier

This portion of the URL is your theme's package name (`Magento` above) and your theme name (`luma`).  This segment needs to be here since Magento's theming system allows you to replace specific front end asset files.

### Locale

This portion of the URL is the locale, (usually thought of as language), identifier for a site.  It may seem a little excessive to allow different locales to have different CSS and Javascript files, but if you consider things like CSS `:after` text, or the need to localize string constants in UI libraries, it makes sense.

### Asset Path

Finally, we have the path to the asset file.

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

Some of you may be scratching your head -- we've never created or mentioned a `styles-m.css` file in the `pub/static` folder.  Where did this file come from?

Here's a quick experiment that will help clear that up.  You'll need to be in developer mode for this to work.

First, lets use `curl` to make sure there's a `styles-m.css` file.  We'll use `curl`'s `-I` modifier to look at the HTTP response headers, but you can also just load the URL directly in a browser.  Remember, the `version...` segment of your URL will be different from the one below.  You also may or may not need the `pub` segment on your URL.

    curl -I 'http://magento.example.com/pub/static/version1514092162/frontend/Magento/luma/en_US/css/styles-m.css'
    HTTP/1.1 200 OK
    //...

So, that's a `200 OK` code -- that means the file's there.  Next, let's **remove** `styles-m.css`

    $ rm ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css

With the file removed, lets try downloading it again.  We'd expect to get a `404 Not Found` error -- except

    curl -I 'http://magento.example.com/pub/static/version1514092162/frontend/Magento/luma/en_US/css/styles-m.css'
    HTTP/1.1 200 OK
    //...

The URL still serves the file!  What's going on?!

Your first thought may be that we've told you an un-true thing, and that the `styles-m.css` file we pointed you at isn't the file that Magento's serving.  At least, that's what I thought the first time I encountered this behavior.  However, if you take a look at your file system again

    $ ls -lh ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css
    -rw-rw-r--  1 _www  staff   334K [today] ./pub/static/frontend/Magento/luma/en_US/css/styles-m.css

You'll see that **something** has recreated the file for us!

## Static Asset Serving

When you deploy a Magento system to production, you need to run the following command

    $ php bin/magento setup:static-content:deploy

This command looks through all of Magento's module's and themes for front end assets, and then creates them in the `pub/static` folder file hierarchy we discussed above.

While this is an OK solution for a production system -- **developing features** under this system would be incredibly tedious and time consuming.  Change a file, deploy the site, wait, whoops that wasn't the right change, repeat.  To combat this problem, Magento deploys front end assets **on demand** when you're running in developer mode.

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

This rewrite rule takes every request for a file in a `pub/static/version*` folder and, **if the file doesn't already exist**, routes the request to the program in `static.php`.  In other words, a request for this URL

    http://magento.example.com/pub/static/version1514092162/frontend/Magento/luma/en_US/css/styles-m.css

is **actually** a request for

    http://magento.example.com/pub/static.php?resource=frontend/Magento/luma/en_US/css/styles-m.css

The `pub/static.php` program is Magento's static asset server.  Its job is to use the `resource` parameter to find a front end asset file in Magento's themes and modules, save that file to disk for future requests, and serve the file.

The `.htaccess` file is also the place where the `version...` slug is stripped out of the URL. If you're using the nginx web server, Magento has a sample configuration with similar rules

    #...

    location ~ ^/static/version {
        rewrite ^/static/(version\d*/)?(.*)$ /static/$2 last;
    }

    #...

    if (!-f $request_filename) {
        rewrite ^/static/?(.*)$ /static.php?resource=$1 last;
    }

    #...

Be careful with your nginx configuration -- it's not uncommon for devops engineers to remember one of these rules, but forget the other.  If you're not seeing front end assets, this is the first place to look.

## The Static Asset Server

If we take a look at `static.php`

    #File: pub/static.php
    require realpath(__DIR__) . '/../app/bootstrap.php';
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
    /** @var \Magento\Framework\App\StaticResource $app */
    $app = $bootstrap->createApplication(\Magento\Framework\App\StaticResource::class);
    $bootstrap->run($app);

We see code that bootstraps and runs an independent application.  This is similar to (but simpler than) what happens in Magento's main `index.php` file.  If we take a look at that application's source file, we can see the main work of the application in the `launch` method (which is eventually called after the `run` method above)

    #File: vendor/magento/framework/App/StaticResource.php
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

    #File: vendor/magento/framework/App/StaticResource.php
    if ($appMode == \Magento\Framework\App\State::MODE_PRODUCTION) {
        $this->response->setHttpResponseCode(404);
    }

If Magento detects you're in production mode, it automatically returns a 404.  Magento still needs to use the `.htaccess` rules from above to strip the `version...` string from URLs, but doesn't want to dynamically serve files in production mode for performance and security reasons.

These three lines

    #File: vendor/magento/framework/App/StaticResource.php

    $asset = $this->assetRepo->createAsset($file, $params);
    $this->response->setFilePath($asset->getSourceFile());
    $this->publisher->publish($asset);

are where the bulk of the work happens.  The `createAsset` method uses the `resource` path to find an actual file.  The call to `setFilePath` on the response object is what tells Magento how to send the front end file to the browser **for this request only**.  The call to `publish` on the `publisher` object is what saves the file to the `pub/static` folder.

In an ideal world, you wouldn't need to be aware of the static serving application.  However -- when there's bugs in this application you may need to go on a deep dive here to diagnose the problem.  Also, if your file permissions aren't set correctly Magento may not be able to create the file in your `pub/static` folder.  Beyond being annoying, the large number of Javascript files Magento needs means there are dozens of requests to this program when you first start using a Magento system.

## Wrap Up

And there we have it.  Everything you ever wanted to know about HTML rendering and front end coding in Magento 2 but were afraid to ask.  You're now ready to start slinging HTML, CSS, and Javascript like it's 2004!

In our final chapter, we're going to take a look at Magento 2's attempt to create a new, more modern javascript development experience.  While far from comprehensive, knowing these newer systems (or knowing how to stay out of their way) is just as important as understanding Magento's Layout XML system.
