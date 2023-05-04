## Magento 2 Components
	
In Magento 1, the idea of modules, themes, libraries, and language packs all got a little blurry around the edges.  Magento 2 attempts to make these distinctions a bit clearer.  Specifically, while Magento 2 still has code modules, themes, code libraries, and language packs, there's a higher level idea of a Magento Component that sits above all of them.  

### What is a Component

To start, a "naming things is hard" note.  Magento Components and UI Components are two different things.  We're talking about Magento Components here, so if you know anything about the UI Component system, check that knowledge at the door.  Don't worry, it'll be there when you get back. 

A Magento Component is, simply speaking, a logical collection of files in a folder hierarchy.  A Magento Component is identified by a `registration.php` file in the top level folder of this hierarchy.

For example, consider the following file

    #File: vendor/magento/theme-frontend-blank/registration.php
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::THEME,
        'frontend/Magento/blank',
        __DIR__
    );
    
The files in `vendor/magento/theme-frontend-blank` are part of the blank theme that ships with Magento 2.  This `registration.php` identifies the files in this folder as part of this theme.  Each `registration.php` contains a call to the static `Magento\Framework\Component\ComponentRegistrar::register` method which 

1. Identifies the Component type 
2. Provides an identifier for the Component
3. Provides a path to the Component's files      

So, above, the Component is identified as a theme via the class constant

    \Magento\Framework\Component\ComponentRegistrar::THEME
    
Its identifying string is `frontend/Magento/blank`, and the code uses the `__DIR__` magic constant to identify the current directory as the folder that contains this Component's files.  Regarding `__DIR__`, while it's technically possible to point to another folder, the Magento convention is to always use this constant.  Veering from this may result in unexpected behavior.

### A Module Component

Let's look at another Component to make sure we understand `registration.php`.  Consider the following file

    #File: vendor/magento/module-catalog/registration.php 
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'Magento_Catalog',
        __DIR__
    );

This folder contains files for the catalog code module.  We know this Component is a module because of the following class constant. 

    \Magento\Framework\Component\ComponentRegistrar::MODULE
    
This module's identifier is `Magento_Catalog`, and (as recommended) the location of its files are the current directory, indicated via `__DIR__`.  

You can see an example of a code library here

    #File: vendor/magento/framework/registration.php
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
        'magento/framework',
        __DIR__
    );    

and a language/translation pack here

    #File: vendor/magento/language-de_de/registration.php
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::LANGUAGE,
        'magento_de_de',
        __DIR__
    );

Magento treats the files in each of these folders differently -- but they're all Magento Components.

### How Magento loads Components

This, of course, leads to the $64,000 question: How does Magento see, or load, these Components.  In Magento 1, modules were loaded via files in 

    app/etc/modules
    
and themes were loaded by editing values in the `System -> Configuration -> Design` sections and added to via layout XML files configured in a module's `config.xml` file.   Magento 1's single code library lived in `lib/`, and Magento scanned for language packs in a hard coded folder.  Like we said -- things get blurry at the edges. 

One of the goals of the Magento Component system is to remove that blurriness.  Another is to enable distribution of these Components via PHP Composer.  If you downloaded Magento 2 as an archive, you may wonder why most of its code files are located in the `vendor/magento` folder.  The `vendor` folder is where Composer installs files to by default.  

So, if Magento's code files are (mostly) in `vendor` -- how does Magento see them?  That happens thanks to the magic of Composer's "file autoload" feature.  

1. Each Composer package has a composer.json file
2. One of the things this file lets you do is run a small PHP script on every page load for a Composer based project
3. The original intent of this script was to allow packages to setup their own PHP Autoloader (via PHP's `spl_autoload_register` function)
4. Magento has hijacked this functionality to register Components

If you're interested in learning more about autoloading, the autoloading appendix is a good place to start, and this online article can provide you with an in-depth exploration of Composer's autoloading mechanisms

http://alanstorm.com/laravel\_composer\_autoloading/

Let's take another look at the catalog module -- but this time, we'll look at its `composer.json` file.

    #File: vendor/magento/module-catalog/composer.json
    {
        /* ... */
        "autoload": {
            "files": [
                "registration.php"
            ],
            /* ... */
        }
    }

We've highlighted the section we're interested in.  This `autoload.files` configuration tells Composer to load the `registration.php` files located in the root folder of this module.  This is the same `registration.php` we viewed earlier.

By creating their `composer.json` files like this, Magento has ensured that as soon as Composer's autoloader finishes loading, that PHP will have called `Magento\Framework\Component\ComponentRegistrar::register` for all the Components that are currently installed via Composer.  

While clever, and powerful, this does mean that Magento 2 is tied to Composer in a way that other PHP projects with traditional architectures aren't.  

### Non-Composer Components

This also raises a few important questions -- is it possible to install Magento Components **without** relying on Composer?  Can we work on projects without needing to redeploy code to a Composer package every time we want to see a change?

Fortunately, the answer to both is yes.  In addition to the above `composer.json` mechanism, Magento will scan certain folders for `registration.php` files, and load those as well.  This scanning happens in the following file

    #File: app/etc/NonComposerComponentRegistration.php
    <?php
    $pathList[] = dirname(__DIR__) . '/code/*/*/cli_commands.php';
    $pathList[] = dirname(__DIR__) . '/code/*/*/registration.php';
    $pathList[] = dirname(__DIR__) . '/design/*/*/*/registration.php';
    $pathList[] = dirname(__DIR__) . '/i18n/*/*/registration.php';
    $pathList[] = dirname(dirname(__DIR__)) . '/lib/internal/*/*/registration.php';
    $pathList[] = dirname(dirname(__DIR__)) . '/lib/internal/*/*/*/registration.php';
    foreach ($pathList as $path) {
        // Sorting is disabled intentionally for performance improvement
        $files = glob($path, GLOB_NOSORT);
        if ($files === false) {
            throw new \RuntimeException('glob() returned error while searching in \'' . $path . '\'');
        }
        foreach ($files as $file) {
            include $file;
        }
    }
    
If you're having trouble reading that, Magento will search for `registration.php` files at

    app/code/[ANYFOLDER]/[ANYFOLDER]/registration.php   
    app/design/[ANYFOLDER]/[ANYFOLDER]/[ANYFOLDER]/registration.php   
    app/i18n/[ANYFOLDER]/[ANYFOLDER]/registration.php
    lib/internal/[ANYFOLDER]/[ANYFOLDER]/registration.php
    lib/internal/[ANYFOLDER]/[ANYFOLDER]/[ANYFOLDER]/registration.php

Where `[ANYFOLDER]` is literally any folder.  These paths are included partially for backwards compatibility, but also for a developer's convenience.  For example, because Magento still scans `app/code` for `registration.php` files, we can have you drop the sample code included with this book into this folder and have it work **without** the need for setting up a Composer module. 

Magento 1 developers will want to be careful: There's a few quirks to the system that make these file path based Components behave a little differently than their Magento 1 counter parts.  

For example -- it's the constant in `registration.php` that controls the type of Component you're loading.  This means it's technically possible (although not recommended) to include a theme in `app/code`.

    app/code/base/default/registration.php
    app/code/base/default/...other theme files...

or a module in `app/design`

    app/design/somefolder/Package/Module/registration.php    
    app/design/somefolder/Package/Module/...other module files...    

Also -- if you're in the habit of renaming folders to `.bak`, or something similar, as a quick backup/testing mechanism, you're in for a surprise.  Magento will still scan the following folder and try to load a module

    app/code/Packagename/Modulename.bak/registration.php    
    
Also, don't forget that a module or theme's name comes from the `registration.php` file and **not** the file path. It's theoretically possible to setup a `registration.php` file where the two don't match.   

    #File: app/code/Packagename/Modulename/registration.php  
      
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'Someotherpackage_Module',
        __DIR__
    );

While this might "work" -- it will likely lead to confusion and *may* cause unexpected bugs.  If you're going to use the `app/code` folders it's best to stay within the guide rails of the system (unless you enjoy deep code safari's -- in which case, have fun!)