# Front End Starter Kit -- CSS

We've just spent 6 chapters winding our way through the complex world of generating HTML using Magento's domain specific layout XML language. While HTML generation is the layout system's primary purpose, HTML is only one third of a browser based page-or-application's appearance and behavior.  The remainder of this book will be a crash course on Magento's systems for working with Cascading Style Sheets (CSS) and the javascript programming language. 

## Magento, CSS and LessCSS

Magento, like all other browser based applications, uses CSS files to provide style and layout rules for its various pages and systems. You'll see this if you view the source of any Magento page

    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento.example.com/static/version1484770320/frontend/Magento/luma/en_US/css/styles-m.css" />
    <link  rel="stylesheet" type="text/css"  media="screen and (min-width: 768px)" href="http://magento.example.com/static/version1484770320/frontend/Magento/luma/en_US/css/styles-l.css" />
    <link  rel="stylesheet" type="text/css"  media="print" href="http://magento.example.com/static/version1484770320/frontend/Magento/luma/en_US/css/print.css" />

However, where Magento 2 diverges from Magento 1 is in **how** these files are created, and how the URLs for these assets are generated.  

Magento 2, (like Symfony and other PHP frameworks), has a "static content generator" that gathers up all the CSS, Javascript, and "other" front end files bundled with the code modules.  Once gathered, the system creates a final set of front end files in the `pub/static` folder.

Magento 2, **unlike** Symfony and other PHP frameworks, ships with two possible default web root/index.php files -- 

    index.php
    pub/index.php
    pub/static
    
This means that Magento 2 generates static asset URLs differently depending on which webroot you're serving Magento from.  

    http://magento.example.com/pub/static/...
    
    vs.
    
    http://magento.example.com/static/...

In Magento 1 it was possible to ship your own top level CSS folder and generate your own CSS `<link/>` tags.  This is not practical in Magento 2. With Magento 2, you really need to stick to Magento's front end abstractions for adding and linking CSS/javascript. 

## Adding a CSS File

Adding a CSS file to Magento is as simple as adding a layout handle XML file.  Let's add a default handle 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/default.xml
    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <head>
            <css src="Pulsestorm_Nofrillslayout::chapter-7/example.css"/>
        </head>
    </page>

Per previous chapters, we know Magento will run the code in this `default.xml` file on every page for the `frontend` area.  Be sure you use the `page_configuration.xsd` schema for your file -- it's the schema that allows you to use the `<head/>` tag.

As for the XML itself

    <head>
        <css src="Pulsestorm_Nofrillslayout::chapter-7/example.css"/>
    </head>
    
The `<head/>` section tells Magento the sub-nodes are Magento's special, head modifying instructions. In other words, these nodes don't relate directly to building block objects.  The `<css/>` node is the layout handle XML command that tells Magento we want to add an HTML `<link/>` tag to the document's head.

If you clear your cache and reload the page with the above in place, you should see the following HTML (or something very similar to it) in your source

    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento-2-2-x.dev/static/version1514092162/frontend/Pulsestorm/dram/en_US/Pulsestorm_Nofrillslayout/chapter-7/example.css" />

Magento has taken our `src="Pulsestorm_Nofrillslayout::chapter-7/example.css"` URN, and converted it into a full asset URL.   The above URL is from a system that's using the `/pub/index.php` folder/filter as its webroot.  If we were using the root folder as the webroot, we'd see the following instead.

    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento-2-2-x.dev/pub/static/version1514092162/frontend/Pulsestorm/dram/en_US/Pulsestorm_Nofrillslayout/chapter-7/example.css" />

Regardless of which URL your system loads, if we look at the CSS URL directly in our browser (or, via `curl` -- see the appendix if you're not familiar with this command), we'll get a 404 error

    $ curl -I 'http://magento-2-2-x.dev/pub/static/version1514092162/frontend/Pulsestorm/dram/en_US/Pulsestorm_Nofrillslayout/chapter-7/example.css'
    HTTP/1.1 404 Not Found
    Date: Tue, 13 Feb 2018 02:56:24 GMT
    Server: Apache/2.4.28 (Unix) PHP/7.0.18
    X-Powered-By: PHP/7.0.18
    X-Content-Type-Options: nosniff
    X-XSS-Protection: 1; mode=block
    X-Frame-Options: SAMEORIGIN
    X-UA-Compatible: IE=edge
    Content-Type: text/plain;charset=UTF-8
  
That's because we need to add the actual CSS file.  Let's do that now!  The `Pulsestorm_Nofrillslayout::chapter-7/example.css` identifier works similarly to the `phtml` template identifiers we've already seen.  However, when used in the `<css/>` tag, the identifier tells Magento to look in the following folder for CSS files.

    app/code/[Namespace]/[Module]/view/[area]/web

This means we'll want to create our CSS file here

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-7/example.css
    body {
        background-color:#f00;
    }

With the above in place, reload/re-download your CSS URL and you should see our file.  Reload the main page, and these CSS rules will be included, just like any other.

## Magento and LessCSS

Adding a traditional CSS file to a page is just the beginning of Magento 2's CSS improvements.  The next thing we'll want to talk about is LessCSS.  LessCSS is one of the first CSS preprocessors to gain traction in the front end web development world.  The default themes in Magento's CSS build systems are deeply ingrained with LessCSS workflows and concepts. 

While there's lots of efforts out there to create alternatives using the Sass preprocessor, or even "headless" systems using the REST API -- the fact that Magento's base themes ship using LessCSS styles that target specific DOM nodes/`class=`/`id=`/etc. means a good chunk of the Magento theme and extension ecosystem will follow along with LessCSS rules.  i.e. Even if you like Sass or headless workflows, chances are you'll need to understand LessCSS if you want to get your Magento job done.  

If you've already made your way through our theming chapter you know how to add a LessCSS stylesheet, but it never hurts to repeat things. The syntax for adding a LessCSS stylesheet to the system is surprisingly similar to adding a CSS file.  Open up our `default.xml` file and add another `<css/>` node.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/default.xml
    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <head>
            <css src="Pulsestorm_Nofrillslayout::chapter-7/example.css"/>
            
            <!-- START: our new node -->
            <css src="Pulsestorm_Nofrillslayout::chapter-7/example-2.css"/>
            <!-- END:   our new node -->
        </head>
    </page>

Adding a LessCSS file uses the exact same syntax as adding a CSS file. All you need to do is add a new `<css/>` node to your layout handle XML file, and add a new `src` URN.  One weird, but important thing?  You'll want your source URL to have a `.css` extension, **even though** we're adding a `.less` file.  

With the above layout handle XML in place, clear your cache and reload the page.  You should see the following `<link/>` tag added to the page.

    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/chapter-7/example-2.css" />
    
Just like before, if we load our CSS URL directly in a web browser, we'll get a 404 error.  Let's add our file.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-7/example-2.less
    @myBlue: #00f;
    body {
        background-color:@myBlue;
    }

Now, load the `example-2.css` URL, and you should see the following

    body {
      background-color: #0000ff;
    }

Also, all the pages in your Magento front end should now have a blue background.

At this high level, Magento's LessCSS implementation is relatively simple -- whenever you add a CSS file to Magento via a layout handle XML file

    <css src="Pulsestorm_Nofrillslayout::chapter-7/example-2.css"/>
    
Magento will first look for a `.less` file with the same name.  If Magento finds a `.less` file, the LessCSS code embedded into Magento's system will transform the LessCSS file immediately to a CSS file.  That's what happened above -- when we loaded the CSS URL, Magento processed our LessCSS file and transformed it into CSS for us.  
    
## Magento LessCSS Performance

Once you have the basic mechanics of Magento's CSS down, the first question that probably comes to mind is

> Isn't it CPU/perf expensive to do this much LessCSS preprocessing on the fly? 

The answer here is both yes **and** no.  It's true that, when creating or editing your LessCSS files, Magento will need to recreate the CSS on every request.  **However**, when Magento's running in production mode, **no** automatic LessCSS generation happens.  Instead, part of deploying a Magento system involves running the `bin/magento setup:static-content:deploy` command.  (If you're not familiar with running Magento commands via the command line, checkout the CLI appendix).

    $ php bin/magento setup:static-content:deploy

One of the things this command does is scan through every layout handle XML file in the system, look for `<css/>` directives with associated LessCSS files, and automatically generate the needed CSS.  The `setup:static-content:deploy` command will generate these files once. Alternately, when Magento's in development mode it will render them on the fly.

## Magento LessCSS Caching

When you're working with LessCSS files in development mode and want to see changes, there's a number of things you'll need to do to make sure your changes show up.

First, obviously, you'll want to edit your file.  Below we're editing our `example-2.less` file to use a less garish blue.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-7/example-2.less
    @myBlue: #00a;
    body {
        background-color:@myBlue;
    }
              
Next, you'll need to remove a cached version of the `.less` file.  This file doesn't live in Magento's normal cache -- instead there's a `var/view_preprocessed` folder

    var/view_preprocessed
    
that contains **all** the preprocessed view files.  You can remove the entire `view_preprocessed` folder **or** find your specific `.less` file and remove it. (if you're not familiar with the unix `find` command we're using below, please see the appendix for a quick tutorial)

    $ find var/view_preprocessed -name example-2.less
    var/view_preprocessed/css/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/chapter-7/example-2.less

    $ find var/view_preprocessed -name example-2.less -exec rm '{}' \;
    $ rm var/view_preprocessed/css/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/chapter-7/example-2.less
    
Once you've removed the `.less` file, you'll **also** need to remove the generated `.css` file from `pub/static`.

    $ find pub/static -name example-2.css
    pub/static/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/chapter-7/example-2.css
    
    $ find pub/static -name example-2.css -
    $ rm pub/static/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/chapter-7/example-2.css
    
Once both these files are deleted, you should be able to reload the CSS file, which will regenerate your LessCSS file.

The Magento CLI tool **does not** ship with commands to automatically clear out the LessCSS cache.  Instead, you'll need to use the `grunt` task runner to manage Magento's LessCSS pipeline.  We have a quick tutorial on this tool in the appendix.  While the `grunt` task runner has commands that let you do the above automatically, we've found it's a good idea to know where these cache files live in case you need to run down sticky style rules yourself. 

## Magento's LessCSS Style Sheets

So, we now know how to add our own LessCSS style sheets to the system, edit them efficiently, and have accepted the production-mode/development-mode differences as the territory we're dealing with.  The last thing we'll need to talk about is how Magento's two default themes use LessCSS.

When Magento 2.0 shipped, there were only **six** top level `.less` files that Magento included via `<css/>` tags. 

    css/print.css
    vendor/magento//theme-frontend-blank/web/css/print.less

    css/styles-l.css
    vendor/magento//theme-frontend-blank/web/css/styles-l.less

    css/styles-m.css
    vendor/magento//theme-frontend-blank/web/css/styles-m.less

    css/styles-old.css
    vendor/magento//theme-adminhtml-backend/web/css/styles-old.less

    css/styles.css
    vendor/magento//theme-adminhtml-backend/web/css/styles.less

    mage/gallery/gallery.css
    vendor/magento//magento2-base/lib/web/mage/gallery/gallery.less

However, this may be a little confusing if you've searched Magento's code base for files with a `.less` extension.  Despite there being only six distinct `<css/>` nodes, there's almost 500 `.less` files.

    $ find vendor/magento/ -name '*.less' | wc -l
     485
    
    $ find vendor/magento/ -name '*.less'
    vendor/magento//framework/Css/Test/Unit/PreProcessor/_files/invalid.less
    vendor/magento//framework/Css/Test/Unit/PreProcessor/_files/valid.less
    ...
    vendor/magento//theme-frontend-luma/web/css/source/_theme.less
    vendor/magento//theme-frontend-luma/web/css/source/_variables.less
    vendor/magento//theme-frontend-luma/web/css/source/components/_modals_extend.less    

What gives?  

Well, if we take a look at `styles-l.less`, the main `.less` file for desktop styles.

    #File: vendor/magento/theme-frontend-blank/web/css/styles-l.less
    
    //...
    
    @import '_styles.less';
    @import (reference) 'source/_extends.less';

    //
    //  Magento Import instructions
    //  ---------------------------------------------

    //@magento_import 'source/_module.less'; // Theme modules
    //@magento_import 'source/_widgets.less'; // Theme widgets
    
    //...
    
Unlike our simple background color setting example, the LessCSS styles for a full Magento site are significantly more complicated.  Rather than jam all the style rules in a single, megalithic file, Magento takes advantage of LessCSS's `@import` command, as well as the special Magento `//@magento_import` command to separate styles into different files.

The LessCSS `@import` directive is relatively simple to understand.  It's a stock LessCSS command, and simply loads in all the style rules from a separate file.           
    
The `//@magento_import` directive is a little less straight forward.  Your first thought may be to ignore this line -- after all, it's prepended with a comment, isn't it?  LessCSS should ignore it, right?

Under normal circumstances, you'd be correct.  However, Magento's custom LessCSS preprocessor code will search any Magento `.less` file for `//@magento_import` (with the comments), and replace it with the contents of a number of different files.  Specifically, Magento will search *all modules and themes* for the provided `.less` files, and if found, add them to the final generated CSS.  For example, in the code above, when Magento sees

    //@magento_import 'source/_module.less';
    
It will search **every** module for file that matches 

    path/to/component/[Namespace]/[Module]/view/[area]/web/css/source/_module.less
    
Additionally, since these are considered view files by Magento, the same rules that apply to merging layout handle XML in themes also applies to LessCSS files pulled in via `magento_import`.  i.e. **all** the following `_module.less` files are merged into the final generated CSS files when you're using the Luma theme.

    $ find vendor/magento/ -wholename '*theme-frontend-luma*web/css/source/_module.less*'
    vendor/magento//theme-frontend-luma/Magento_AdvancedCheckout/web/css/source/_module.less
    vendor/magento//theme-frontend-luma/Magento_AdvancedSearch/web/css/source/_module.less
    //...
    vendor/magento//theme-frontend-luma/Magento_Vault/web/css/source/_module.less
    vendor/magento//theme-frontend-luma/Magento_Wishlist/web/css/source/_module.less    

You can see a working example of `magento_import` in the `Pulsestorm_Nofrills` module.  We've included the following LessCSS files with the module

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/css/source/_module.less    
    #someWeirdRuleInModule{
        color:#fff;
    }    
    
    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/css/source/widgets.less    
    #someWeirdRuleInWidget{
        color:#fff;
    }

Because these paths match the above mentioned rules, you should find the `#someWeird...` rules in your generated `styles-l.css` file. 

## PHP Code for Magento Imports

If you're curious how the `magento_import` preprocessor loads its files, you'll find the code in the following class/file

    vendor/magento/framework/Css/PreProcessor/Instruction/MagentoImport.php
    
If you're curious how Magento's LessCSS processor itself works, you'll find the code that kicks off preprocessing here

    vendor/magento/framework/Css/PreProcessor/Adapter/Less/Processor.php     
    
## The Problem With Sassy Alternatives    

For folks working for a certain sort of interactive agency, Magento 2's choice of LessCSS was somewhat disappointing.  Other preprocessors like Sass and Stylus seem more popular with this set of developers. Because of this, we've seen some efforts to create alternate CSS workflows using these systems.

While these are interesting projects, and do create a workable alternative for folks heavily invested in these stacks, these projects (along with things like "headless" API only implementations) end up sacrificing a huge chunk of Magento's value as a software ecosystem. 

There's no such thing as a preprocessor standard -- each of these toolsets is capable of different things.  So far, each alternative preprocessor project ends up outputting CSS that's different from the LessCSS generated CSS.  They also often need to (or want to) implement changes to the layout handle files or generated HTML.

While all of this is OK on an individual system, the tradeoff is the wealth of third party code (open source and commercial) that relies on the existing LessCSS rules, the existing layout structure, or the existing HTML structure may end up not working.  It's hard to overstate the value that consistency in the available layout blocks, style rules, and HTML code brings for folks making redistributable Magento code.  

Even if these alternate preprocessors somehow manage to maintain compatibility with the LessCSS versions of Magento's themes -- extension developers are placed in an odd position: Do they adopt LessCSS rules and provide Sass/Stylus alternatives?  Do they do this for each individual Sass or Stylus project?

Unfortunately, there aren't easy answers here.  While it's possible to use pure CSS (and therefore your own CSS workflows) by adding new files to Magento, most working Magento developers won't be able to avoid working with LessCSS, and it seems unlikely Magento 2 will rip out the guts of a system that's already taken countless developer hours to build. 