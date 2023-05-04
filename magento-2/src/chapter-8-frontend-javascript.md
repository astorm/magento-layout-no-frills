# Front End Starter Kit -- Javascript

It's hard to escape the fact that the modern web runs on javascript.  This puts Magento in a tricky position. As a traditional PHP MVC framework, it's hard for Magento to take advantage of everything the modern javascript world has to offer.  There's also parts of Magento 2 that are still running on Magento 1 code, and still require the grandparent of all javascript frameworks -- PrototypeJS!

In this chapter we'll explain the basics of what you'll need to know to get started with Javascript development in Magento 2.  

## RequireJS

Javascript files face a set of challenges similar to those faced by CSS files.  Magento considers javascript files static content, so source files and deployed files are two different things.  The `/pub/static` vs. `/static` document root problem also exists.  Fortunately, just like Magento 2 provides a `<css/>` tag for adding CSS files, it also provides a `<script/>` tag  for adding javascript files.  Let's add the following node to our `default.xml` file we created in Chapter 7

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/default.xml
    
    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <head>
            <!-- ... -->
            <script src="Pulsestorm_Nofrillslayout::chapter-8/example-script.js"/>             
            
            <!-- alternate <link/> syntax for the same thing -->            
            <!-- <link src="Pulsestorm_Nofrillslayout::chapter-8/example-script.js"/>              -->
            <!-- ... -->
        </head>
    </page>

Here we've added a `<script/>` tag to the `<head/>` section of our layout handle XML file, with a `src` URN of `Pulsestorm_Nofrillslayout::chapter-8/example-script.js`.  If we clear our cache and reload with the above in place, you should see a `<script></script>` tag added to the source of all the pages in your Magento system.

    <script  type="text/javascript"  src="http://magento.example.com/static/version1514092162/frontend/Pulsestorm/dram/en_US/Pulsestorm_Nofrillslayout/chapter-8/example-script.js"></script>

Similar to our `<css/>` layout commands, Magento's used the URN in our `<script/>` tag to automatically generate a full URL. Also similar to our `<css/>` tag, we'll need to actually create a `chapter-8/example-script.js` file.  

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-8/example-script.js
    alert("Hello World");
    
Reload the page with the above in place, and you should see a "Hello World" alert box.   So we're all set right?  Not quite.  Let's give the following script a try. 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-8/example-script.js
    
    jQuery(function(){
        alert("Hello World");
    });
    
The above javascript program uses jQuery's document ready functionality to hold off on calling out alert until the document DOM has loaded.  However, if you try loading the page with the above in place, you probably **won't** see an alert.  Instead you'll see the following in your javascript error console.

> Uncaught ReferenceError: jQuery is not defined

To the uninitiated, this may look like Magento reneged on its promise to include jQuery with Magento 2.  However, if you paste the above program into your javascript console, javascript will find the global jQuery object without issue.  

How is it possible that an inline bit of javascript in a page can't access jQuery, but a fully loaded page can?

While it's possible to use plain "raw" javascript in your Magento 2 projects, Magento's default front end systems are **heavily** biased towards an open-source, javascript AMD system named RequireJS.  If you're hewing closely to Magento's idea of how javascript should be written, you'll need to become intimately familiar with this system.

## Understanding RequireJS Programs

You run a RequireJS program by passing a list of *module dependencies* to the `require` function (or its alias, `requirejs`), along with a single javascript function that defines your program.

    require([...list of dependencies...], function(){
        //your program code
    });
    
So, the simplest hello world program might look something like this

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-8/example-script.js
    require([], function(){
        alert("Hello RequireJS");
    });

Reload the page with the above code in place, and you should see your "Hello RequireJS" alert displayed.  The above program has **zero** module dependencies -- i.e. an empty array (`[]`).  

A RequireJS module is a javascript object, function, or string, returned by a unique namespace path.  One of the goals of RequireJS is to abstract away the loading of individual javascript files, and let the javascript programmer think about their code in terms of functionality.  For example, instead of referencing a global `jQuery` object, RequireJS provides a `jquery` module.  Consider the following program

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-8/example-script.js

    require(['jquery'], function(jqueryModule){
        //the jquery module returns the object we normally
        //think about as the global jQuery/$ obejct
        var body = jqueryModule('body')[0];
        jqueryModule(body).html('<p>Hello jQuery</p>')
    });

If you reload your page with the above in place, you'll find that the contents of the `<body/>` tag have been replaced with the `<p>Hello jQuery</p>` HTML.  The key to understanding this program is the following 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-8/example-script.js
    require(['jquery'], function(jqueryModule){
        //...
    });

Here, we've told RequireJS that our program needs to use the jQuery module (`['jquery']`).  Because we listed the module named `jquery` as the first item in in our dependency list, RequireJS passed the jQuery module object to our program function as the first parameter (`jqueryModule` above).  RequireJS will pass any module specified in the module list as parameters to your program.

For example, in an imaginary system that had modules named `one`, `two`, and `three`, you'd be able to include them in a program like this.

    require(['one','two','three'], function(oneModule, twoModule, threeModule){
    });

## Creating your Own RequireJS Modules

Magento 2's javascript systems are strongly biased towards third party developers creating new RequireJS modules for their code.  Unless you already know what you're doing, you will not want to fight this. 

Magento provides a RequireJS configuration that allows each Magento module to have a RequireJS namespace.  For example, give the following program a try.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter-8/example-script.js

    require(
        ['jquery', 'Pulsestorm_Nofrillslayout/messages'], 
        function(jQuery, messages){
            var body = jQuery('body')[0];
            jQuery(body).html(messages.getMessage())
        }
    );

The program above has two module dependencies -- `jquery` and `Pulsestorm_Nofrillslayout/messages`.  The former is a Magento core provided module for accessing jQuery -- the second is a module we've provided as part of the `Pulsestorm_Nofrillslayout` Magento module.  

Magento's RequireJS bootstrap allows us to use a RequireJS name like this

    [Magento Module Name]/some-string
    Pulsestorm_Nofrillslaoyut/messages

and have that RequireJS module correspond to a **Magento module**  (`Pulsestorm_Nofrillslaoyut` above).  You can see the RequireJS module definition here

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/messages.js
    define([], function(){
        var moduleObject = {};
        moduleObject.getMessage = function(){
            return "Hello RequireJS Custom Module";
        };
        return moduleObject;
    });
    
Put another way, Magento knows to look for this RequireJS module in `path/to/Pulsestorm/Nofrillslayout` because the first portion of the module name is `Pulsestorm_Nofrillslayout`.  

Magento takes the second portion of the module name (`messages` above), and turns that into a `view/[area]/web/...` file path (`view/frontend/web/messages.js` above).  If you're not familiar with the specifics of defining a RequireJS module, the official RequireJS documentation is a good place to start. 

http://requirejs.org/docs/api.html#define

## RequireJS Bootstrap

Covering RequireJS in full is beyond the scope of this book.  However, what **is** in the scope of this book is covering how Magento bootstraps its RequireJS instance.  Once you understand this, you should be able to track down anything you need by using the official RequireJS docs. [http://requirejs.org/docs](http://requirejs.org/docs).

There are **three** `<script/>` tags you'll want to be aware of w/r/t the RequireJS bootstrap

    <script  type="text/javascript"  src="http://magento.example.com/static/version1514092162/frontend/Magento/luma/en_US/requirejs/require.js"></script>
    <script  type="text/javascript"  src="http://magento.example.com/static/version1514092162/frontend/Magento/luma/en_US/mage/requirejs/mixins.js"></script>
    <script  type="text/javascript"  src="http://magento.example.com/static/version1514092162/_requirejs/frontend/Magento/luma/en_US/requirejs-config.js"></script>

The `requirejs/require.js` file is the main RequireJS source file.  This implements the functions that make RequireJS work. 

The `mage/requirejs/mixins.js` file is a special Magento file.  This file **redefines** several RequireJS source functions.  Magento does this to implement a "mixins" system.  This system allows RequireJS developers to intercept the creation of any RequireJS module and redefine it -- sort of like Magento 1 class rewrites, but for RequireJS javascript code.  The specifics of this system are beyond the scope of this book, but you can read more online. 

https://alanstorm.com/the-curious-case-of-magento-2-mixins/

The final file, `requirejs-config.js`, is the most important one to understand.  If you load this in your web browser directly, you'll see a number of calls to `require.config`

    (function(require){
    (function() {
    /**
     * Copyright © 2016 Magento. All rights reserved.
     * See COPYING.txt for license details.
     */

    var config = {
        /* ... */
    };

    require.config(config);
    })();

This file is **not** a static javascript file.  It's dynamically generated from the contents of each module's `requirejs-config.js` file(s). 

    $ find vendor/magento/ -name requirejs-config.js
    vendor/magento//module-admin-notification/view/adminhtml/requirejs-config.js
    vendor/magento//module-authorizenet/view/frontend/requirejs-config.js
    /*...*/
    vendor/magento//module-weee/view/frontend/requirejs-config.js
    vendor/magento//module-wishlist/view/frontend/requirejs-config.js

These files are Magento's way of letting each individual module author call RequireJS's built-in configuration method 

http://requirejs.org/docs/api.html#config

in order to specify the behavior of RequireJS.  Magento's core code uses these files for a variety of reasons -- including setting up aliases for modules, configuring dependencies for legacy modules, setting jQuery's no conflict mode, and more.

The specifics of how all this is implemented are beyond the scope of this book, 
but if you're curious, this Magento 2 and RequireJS article is a good place to start. 

http://alanstorm.com/magento\_2\_and\_requirejs/

## Module Dependencies

One important bit of Magento's Require.js configuration we **will** cover is here.

    #File: vendor/magento/module-theme/view/frontend/requirejs-config.js
    var config = {    
        deps: [
            "jquery/jquery.mobile.custom",
            "js/responsive",
            "mage/common",
            "mage/dataPost",
            "js/theme",
            "mage/bootstrap"
        ]    
    }
        
    #File: vendor/magento/module-theme/view/adminhtml/requirejs-config.js
    var config = {
        "deps": [
            "js/theme",
            "mage/backend/bootstrap",
            "mage/adminhtml/globals"
        ],    
    };
    
When you see `deps` as a top level key in a RequireJS configuration object, it means Magento will load the listed modules once it's finished loading RequireJS -- i.e. on every HTML page load.  The `mage/bootstrap` and `mage/backend/bootstrap` are particularly interesting, as they enable some important functionality we'll mention below.  However, **all** these modules are worth investigating, as they often set global state or configuration that your javascript application may depended on.  i.e. if things are acting weird, it may be because of something in one of these modules.

## Running RequireJS Programs in Magento

Earlier, we used the global `require` function to run a RequireJS based program.  While this is a perfectly adequate way to run a javascript program in Magento 2, Magento provides us with **two** other ways to kick-off a RequireJS based program.

The first of these two methods are "`x-magento-init`" script tags.  You can see these tags on most Magento pages.  Here's one example

    <script type="text/x-magento-init">
        {
            "*": {
                "mage/cookies": {
                    "expires": null,
                    "path": "/",
                    "domain": ".magento.example.com",
                    "secure": false,
                    "lifetime": "3600"
                }
            }
        }
    </script>
    
This may look like a standard javascript tag, but take a closer look.   The `type` attribute is `text/x-magento-init`.  

    <script type="text/x-magento-init">

This means the browser **will not** execute code in the `script` tag on its own.  Also, if you take a look at this `<script/>` tag's content

    {
        "*": {
            "mage/cookies": {
                "expires": null,
                "path": "/",
                "domain": ".magento.example.com",
                "secure": false,
                "lifetime": "3600"
            }
        }
    }
        
you'll see there's nothing to execute -- there's just a JSON object.  What gives?

The key to understanding this are the `mage/backend/bootstrap` and `mage/bootstrap` modules we saw earlier. The javascript in these modules parses every Magento page for these `x-magento-init` tags, loads each RequireJS module listed in the nested object (`mage/cookies` above), and then executes the function returned by that module, passing in the configuration.  Here's what that looks like in pseudo code

    var moduleFunction = requirejs('mage/cookies');
    moduleFunction({"expires": null,
                    "path": "/",
                    "domain": ".magento.example.com",
                    "secure": false,
                    "lifetime": "3600"});


The intention of these `x-magento-init` blocks is to allow PHP developers to generate a JSON string via PHP, and securely pass it to a RequireJS program for execution.  

## The Other Initialization

There's a second way to initialize a RequireJS program in Magento 2, and that's via a `data-mage-init` attribute in an HTML node.  Again, here's an example from some Magento generated HTML

    <ul id="main-dropdown" class="dropdown switcher-dropdown" 
        data-mage-init='{"dropdownDialog":{
            "appendTo":"#switcher-currency > .options",
            "triggerTarget":"#switcher-currency-trigger",
            "closeOnMouseLeave": false,
            "triggerClass":"active",
            "parentClass":"active",
            "buttons":null}}'>       

The `data-mage-init` attribute accepts a JSON object. The object's key (`dropdownDialog` above) is the name of a RequireJS module.  When Magento's bootstrapping code encounters a `data-mage-init` attribute, it will load this RequireJS module, and call the function returned from this module, passing in the configuration object provided in the `data-mage-init` script.  Again, in pseudo-code, here's what Magento will do when it sees a `data-mage-init` attribute.

    var moduleFunction = requirejs('dropdownDialog');
    moduleFunction('{
            "appendTo":"#switcher-currency > .options",
            "triggerTarget":"#switcher-currency-trigger",
            "closeOnMouseLeave": false,
            "triggerClass":"active",
            "parentClass":"active",
            "buttons":null}', jQuery('#main-dropdown'));

Finally, there's also a form of `x-magento-init` which behaves just like `data-mage-init` -- just replace the `*` from the earlier `data-mage-init` with a `jQuery`/CSS selector. 

    <script type="x-magento-init">
        {
            ".switcher-dropdown":{
                "dropdownDialog":{
                    "appendTo":"#switcher-currency > .options",
                    "triggerTarget":"#switcher-currency-trigger",
                    "closeOnMouseLeave": false,
                    "triggerClass":"active",
                    "parentClass":"active",
                    "buttons":null                
                }
            }
        }
    </script>
    
The only difference here is `data-magento-init` allows us to target a **specific** DOM element, while `x-magento-init` allows us to target any number of DOM elements with generic selectors, **or** to target no DOM elements with the special `"*"` selector.  

\pagebreak

## Next Steps

We've only touched the surface of what you can do with javascript in Magento 2.  Below these initialization scripts, Magento 2 contains a completely custom javascript framework that includes its own javascript object system.  Here's some online resources that will help you get to the core of what's going on in Javascript in Magento 2

<dl>
    <dt>**Magento 2: Advanced Javascript**</dt>
    <dd>https://alanstorm.com/category/magento-2/#magento2-advanced-javascript<dd>
</dl>

<dl>
    <dt>**Magento 2 UI Components**</dt>
    <dd>https://alanstorm.com/category/magento-2/#magento-2-ui</dd>
</dl>

<dl>
    <dt>**Magento 2: uiElement Internals**</dt>
    <dd>https://alanstorm.com/category/magento-2/#magento-2-uielement-internals</dd>
</dl>