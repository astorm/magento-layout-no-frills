# Magento 2 Theming

Theming systems occupy a strange space in web application frameworks.  From a user facing perspective, it may seem obvious what themes are -- they determine how a site looks and feels.  However, on a technical level, a theming system is something that allows developers *limited* access to the system that outputs HTML and CSS for web pages.  The question becomes **how** limited is that access.

Themes in Magento 2 are, technically speaking, pretty straight forward.  A Magento theme allows theme developers to **replace** or **add to** assets added by Magento modules, or by their parent themes.  These assets include

- Layout Handle XML files
- CSS/LessCSS files, 
- Javascript source files
- Knockout.js html templates
- Email templates
- Additional requirejs-config.js configuration
- Additional translation strings

In this chapter we're going to create a new child theme, and then use that theme to replace/extend a few of Magento's stock layout handle xml files, phtml files, and LessCSS files. 

## Creating a Child Theme

Most developers will rarely create a new theme in Magento 2.  Instead, you'll have your theme **inherit** from a theme that already exists.  When you create a child theme in Magento 2, you're telling Magento 

> I want my theme to behave exactly the same as this other theme.  

After you've created your child theme, you can customize it and make it behave *slightly* differently than the parent theme.  This may mean some additional LessCSS rules, a different phtml template for one section, etc.

We're going to dive right in and create a new theme.  The first thing we need to do is come up with a name and identifier for our theme.  A theme's unique identifier is made up of three different parts.

    [area]/[package-name]/[theme-name]
    
A theme's `area` is the Magento area this theme will apply to (See the appendix on areas if you're unfamiliar with the concept).  The package name is a string that identifies the person or organization that created the theme.  The theme's name is, well, the theme's name!  Here's an example of an actual theme identifier for Magento's Luma theme

    frontend/Magento/luma
    
The area, `frontend`, indicates this is the theme for the front end cart application.  The package name, `Magento`, indicates this is a theme that comes from Magento Inc.  The theme's name, `luma`, is (again), the theme's name.

A theme is a Magento component, which means a theme is meant to be distributed by Composer.  This is why you can find the luma theme in the `vendor/magento/theme-frontend-luma` folder.  In addition to distributing themes via Composer, Magento will also scan any folder that matches the following `glob` pattern for theme registration files 

    #File: app/etc/NonComposerComponentRegistration.php
    
    /* ... */
    $pathList[] = dirname(__DIR__) . '/design/*/*/*/registration.php';    
    /* ... */

If you're unfamiliar with components, checkout the component appendix for more information. 

We're going to use the theme identifier `frontend/Pulsestorm/dram`.  We're also going to create our theme in the `app/design` folder.  We'll also have our theme inherit from the Luma theme.  To create your new theme, first create a `registration.php` file in the following location 

    #File: app/design/frontend/Pulsestorm/dram/registration.php
    <?php
        \Magento\Framework\Component\ComponentRegistrar::register(
            \Magento\Framework\Component\ComponentRegistrar::THEME,
            'frontend/Pulsestorm/dram',
            __DIR__
        );

Then, create a `theme.xml` file    

    #File: app/design/frontend/Pulsestorm/dram/theme.xml
    <?xml version="1.0"?>
    <theme xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/theme.xsd">
        <title>Pulsestorm Dram</title>
        <parent>Magento/luma</parent>
        <media>
            <preview_image/>
        </media>
    </theme>

The `registration.php` file is required by all Magento components.  You'll notice we're using the `ComponentRegistrar::THEME` theme constant to tell Magento we're registering a theme, and the `frontend/Pulsestorm/dram` identifier we previously discussed.  

Every theme file needs a `theme.xml` file.  This file identifies the theme's parent, gives the theme a plain english title, and allows you to configure an (optional) preview image.  With the above files in place, clear your cache, and navigate to the `Content -> Design -> Themes` section.  You should see your theme listed in the theme grid.

    Theme Title         Parent Theme        Theme Path
    ------------------------------------------------------------    
    Magento Luma        Magento Blank       Magento/luma
    Pulsestorm Dram     Magento Luma        Pulsestorm/dram
    Magento Blank                           Magento/blank
    
One important thing to note about this section:  The first time Magento sees a new theme, it will parse the theme information and save it to the `theme` database table

    mysql> select * from theme\G

    /* ... */

    *************************** 4. row ***************************
         theme_id: 4
        parent_id: 2
       theme_path: Pulsestorm/dram
      theme_title: Pulsestorm Dram
    preview_image: NULL
      is_featured: 0
             area: frontend
             type: 0
             code: Pulsestorm/dram

Although your theme will always need its `theme.xml` file, from now on when Magento needs theme information it will look inside this table. 
            
If you navigate to `Content -> Design -> Configuration`, you can select which store views should use this theme by clicking the `edit` button next to your store view, choosing *Pulsestorm Dram* from the Applied Theme drop down, and then clicking the `Save Configuration` button.  Do this before moving on to the next section.
    
## Adding Layout Handle XML Files

Now that we have a child theme, we can use that child theme to *add* layout handle XML files to our system.  A theme's layout handle XML files work *slightly* differently than the module based layout handle XML files we've dealt with so far.  While the files themselves contain the same XML, and the file name still refers to a specific Magento handle, in a theme 

> Developers create layout handle XML files that are loaded immediately after **a specific module's** layout handle XML file

This means if a layout handle XML file doesn't exist in a module, a theme won't be able to use that handle.  The upside is this new system allows you an unprecedented level of control over when Magento processes your layout handle XML files.

Let's get to the examples!  If you load a category listing page in Magento 2

    http://magento.example.com/gear/fitness-equipment.html
    
You'll see a list of products in a particular category.  This page's full action handle is `catalog_category_view`, and the following XML file is responsible for adding the product listing blocks to a page. 

    vendor/magento/module-catalog/view/frontend/layout/catalog_category_view.xml    

Using our `frontend/Pulsestorm/dram` theme, we can have Magento parse an **additional** layout handle XML file whenever Magento loads the catalog module's layout handle XML file. Just create the following layout handle XML file

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/layout/catalog_category_view.xml
    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <body>
            <referenceContainer name="content">
                <block  name="pulsestorm_magento_catalog_category_view_example"
                        class="Magento\Framework\View\Element\Template"
                        template="hello.phtml"/>
            </referenceContainer>
        </body>
    </page>  
    
along with the following template file

    #File: app/design/frontend/Pulsestorm/dram/templates/hello.phtml
    <h2>Hello Category Listing Page.</h2>    

If you clear your cache, reload the category listing page, and have `frontend/Pulsestorm/dram` selected as the current front end theme, you should see the text *Hello Category Listing Page* below the product listings.
    
## Why This Works

Before we get to the new things here, let's do a quick review of what we already know.  Our layout handle XML file gets a reference to the already created container named `content`

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/layout/catalog_category_view.xml
    <referenceContainer name="content">
        <!-- ... -->
    </referenceContainer>

and then adds a block named `pulsestorm_magento_catalog_category_view_example` to that container.  

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/layout/catalog_category_view.xml
    <block  name="pulsestorm_magento_catalog_category_view_example"
            class="Magento\Framework\View\Element\Template"
            template="hello.phtml"/>

We can name this block anything we like, so long as the name is globally unique in the Magento layout.  

This block's PHP class is `Magento\Framework\View\Element\Template`.  Block objects created with a `Magento\Framework\View\Element\Template` class are simple template blocks.  This block's template URN is `hello.phtml`.

What's new, and might be confusing, is 

1. The layout handle XML file's path
2. The "module-less" template URN.

As previously mentioned, Magento loads a theme's layout handle XML *in addition to* a module's layout handle XML file.  Since we're pairing our layout file with the following core file

    vendor/magento/module-catalog/view/frontend/layout/catalog_category_view.xml 
    
We placed our file in the following theme folder  

    path/to/theme/Magento_Catalog/layout
    
The `Magento_Catalog` portion of this path comes from the catalog module's component identifier.  You can find this identifier by looking in the module's  registration.php file.  

    #File: vendor/magento/module-catalog/registration.php
    
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'Magento_Catalog',
        __DIR__
    );

The `layout` portion of the path is just a hard-coded folder named layout.  As we'll learn later, there are other asset types we can use in themes, so Magento forces us to put our layout handle XML files in the `layout` folder to keep things tidy.  

Our file's name, `catalog_category_view.xml`, should match the name of the module layout handle XML file we're pairing our file with.

The other new thing is our template's URN/file-name.  

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/layout/catalog_category_view.xml
        
    template="hello.phtml"
    
So far we're used to seeing templates with values like `Package_Module::path/to/template`.  While you can use these sort of template URNs in your theme's layout handle XML file, if there's no module name in a template URN Magento will look *in the current theme's templates* folder for the file.  For our example above, that means this file

    #File: app/design/frontend/Pulsestorm/dram/templates/hello.phtml
    <h2>Hello Category Listing Page.</h2>    
  
This allows you to create completely new templates in your themes without needing an extra Magento module.

## Replacing a Layout Handle XML File

Magento's theme system also allows you to **completely replace** a layout handle XML file.  If, for some reason, we wanted to get rid of the catalog module's `catalog_category_view` rules, we could just add the following Layout Handle XML file.

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/layout/override/base/catalog_category_view.xml
    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <body>
        </body>
    </page>
    
Clear any category listing page with the above in place, and Magento will discard all the rules in the       
 
    vendor/magento/module-catalog/view/frontend/layout/catalog_category_view.xml  
    
file and use the rules in `Magento_Catalog/layout/override/base/catalog_category_view.xml` instead.  In other words, the page will no longer have a category listing.

Again, the `Magento_Catalog` portion of this path comes from the original module's registered component name.  The `layout/override/base` path is hard coded.  The `override` folder tells Magento these files should **replace** the existing files.  The `base` path component appears to be an area name, but you're required to use `base` when overriding a file.  In other words, you can't use `frontend` or `area` here.  Finally, the file's name (`catalog_category_view.xml`) is the file we want to replace.

Generally speaking, you'll want to avoid using an override unless it's the only way to achieve your goal.  Many of Magento's core layout files create blocks that **other** layout files expect to find.  While Magento will swallow these sort of layout errors in log files (i.e. Magento won't crash), your system's front end code will probably behave in strange ways with so much expected rendering HTML code missing.

## Replacing a Template

While it's usually a better practice to leave Magento's rendered HTML as is, sometimes the only way to achieve the front end result we want is to replace a template file with our own. Magento's theming system allows you to replace **any** template you'd like with your own. 

For example, consider the product attribute template

    #File: vendor/magento/module-catalog/view/frontend/templates/product/view/attribute.phtml
    /* ... */
    <?php if ($_attributeValue): ?>
    <div class="product attribute <?php /* @escapeNotVerified */ echo $_className?>">
        <?php if ($_attributeLabel != 'none'): ?><strong class="type"><?php /* @escapeNotVerified */ echo $_attributeLabel?></strong><?php endif; ?>
        <div class="value" <?php /* @escapeNotVerified */ echo $_attributeAddAttribute;?>><?php /* @escapeNotVerified */ echo $_attributeValue; ?></div>
    </div>
    <?php endif; ?>

Magento uses this template to output product attribute values on the product details page.  Using blocks, there's no way for us to insert a value between the `class="product` div and the `<div class="value"` div.  It's cases like these where template replacement usually makes sense.

If we create the following file in our theme

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/templates/product/view/attribute.phtml
    
and replace its contents with a slightly modified version of the original

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/templates/product/view/attribute.phtml
    <?php
    /**
     * Copyright © 2016 Magento. All rights reserved.
     * See COPYING.txt for license details.
     */

    // @codingStandardsIgnoreFile

    /**
     * Product view template
     *
     * @see \Magento\Catalog\Block\Product\View\Description
     */
    ?>
    <?php
    $_helper = $this->helper('Magento\Catalog\Helper\Output');
    $_product = $block->getProduct();
    $_call = $block->getAtCall();
    $_code = $block->getAtCode();
    $_className = $block->getCssClass();
    $_attributeLabel = $block->getAtLabel();
    $_attributeType = $block->getAtType();
    $_attributeAddAttribute = $block->getAddAttribute();

    if ($_attributeLabel && $_attributeLabel == 'default') {
        $_attributeLabel = $_product->getResource()->getAttribute($_code)->getFrontendLabel();
    }
    if ($_attributeType && $_attributeType == 'text') {
        $_attributeValue = ($_helper->productAttribute($_product, $_product->$_call(), $_code)) ? $_product->getAttributeText($_code) : '';
    } else {
        $_attributeValue = $_helper->productAttribute($_product, $_product->$_call(), $_code);
    }
    ?>

    <?php if ($_attributeValue): ?>
    <div class="product attribute <?php /* @escapeNotVerified */ echo $_className?>">
        <h2>This is an attribute: </h2>
        <?php if ($_attributeLabel != 'none'): ?><strong class="type"><?php /* @escapeNotVerified */ echo $_attributeLabel?></strong><?php endif; ?>
        <div class="value" <?php /* @escapeNotVerified */ echo $_attributeAddAttribute;?>><?php /* @escapeNotVerified */ echo $_attributeValue; ?></div>
    </div>
    <?php endif; ?>    
    
we'll see the *This is an attribute* text added every time Magento uses the `attriute.phtml` template to output a product's attribute.  

If we examine our file's path, we'll see a pattern similar to our layout handle XML files.  First, of course, is our base theme folder

    app/design/frontend/Pulsestorm/dram
    
This is followed by the `Magento_Catalog` folder
    
    Magento_Catalog

`Magento_Catalog` comes from the catalog module's registered name in its `registration.php` file.  We're using the catalog module because that's where the core `attribute.phtml` file lives.  Next, we have a 
    
    templates
    
folder.  Like the `layout` folder, this folder exists to separate templates from other file types.  Finally, we have the path to the file we want to replace

    product/view/attribute.phtml

Again, like layout files, this path matches the path to the file in the original module, minus the area.

While our *This is an attribute* text is a somewhat silly example, replacing a template is often the only way to apply subtle changes to Magento's HTML output.  For example, in this HTML chunk

    #File: vendor/magento/module-catalog/view/frontend/templates/product/view/attribute.phtml
    <div class="value" <?php /* @escapeNotVerified */ echo $_attributeAddAttribute;?>>
        <?php /* @escapeNotVerified */ echo $_attributeValue; ?>
    </div>  
    
If a developer (you!) wanted to add an additional CSS class along-side `value`, the only way to do it would be to replace the `attribute.phtml` with your own version.

The downside of template replacement is, if Magento changes a template significantly during a upgrade release, your new template may not work right with the new system.  Prior to an upgrade you'll need to test your theme with the new version of Magento to make sure your changes and Magento's changes play nice together.  

## Replacing CSS and LessCSS Files    

The last file replacement technique we'll discuss is replacing a LessCSS file.  However, to do this, we'll need to discuss how CSS files are handled in a stock version of Magento.  

If you look at a page rendered with Magento's stock Luma theme, you'll see the following `<link/>` tag

    <link   rel="stylesheet" 
            type="text/css"  
            media="screen and (min-width: 768px)" 
            href="http://magento.example.com/.../   \;
                  frontend/Pulsestorm/dram/en_US/css/styles-l.css" />

That is, Magento loads in a CSS file named `styles-l.css`.  Magento does this thanks to the following layout handle XML file.

    #File: vendor/magento/theme-frontend-blank/Magento_Theme/layout/default_head_blocks.xml
    <head>
        <!-- ... --->
        <css src="css/styles-l.css" media="screen and (min-width: 768px)"/>
        <!-- ... --->
    </head>

**However**, if you search the Magento core for a file named `styles-l.css`, you won't find one.  However, you **will** find a file named `styles-l.less`.  

    #File: vendor/magento//theme-frontend-blank/web/css/styles-l.less

    /* ... a very long list of less rules ... */
    
This is the first thing we'll need to cover.  While you can use the `<head/>` section of a layout update XML file to add a traditional CSS file, you can **also** use it to add a LessCSS file.  However, to do so, you don't say     

    <!-- this is wrong -->
    <css src="css/styles-l.less" media="screen and (min-width: 768px)"/>
    
You say

    <css src="css/styles-l.css" media="screen and (min-width: 768px)"/>    
    
Although you've provided a path to a CSS file, Magento knows to check for a `.less` file.   Regardless of whether it's a `.less` file, or a `.css` file, you can replace either file in your theme.  If you wanted to replace the entire `styles-l.less` file, you'd just create a file with one of the following names

    #File: app/design/frontend/Pulsestorm/dram/web/css/styles-l.css
    # or 
    #File: app/design/frontend/Pulsestorm/dram/web/css/styles-l.less
    body{
        background-color:#f00;
    }

If you clear your Magento cache, remove the LESS preprocessed cache folder (`var/view_preprocessed`), remove any generated `style-l.css` file from your `pub` directory

    $ find pub/ -name styles-l.css
    pub/static/frontend/Pulsestorm/dram/en_US/css/styles-l.css

    $ rm pub/static/frontend/Pulsestorm/dram/en_US/css/styles-l.css
    
and then reload the page (or reload the CSS file URL), we'll see our simple file has replaced the stock `styles-l.css`.  

You'll notice that, unlike our layout handle XML files and phtml template files, we **do not** need to put this file in a `Packagename_Modulename` folder

    web/css/styles-l.css    
    
This is because the layout update XML file rules use a file URN without a module name (`css/styles-l.css`).

    <css src="css/styles-l.css" media="screen and (min-width: 768px)"/>
    
If this URN had looked like `Magento_Theme::css/styles-l.css`, then we would have put our file in the following location

    app/design/frontend/Pulsestorm/dram/Magento_Theme/web/css/styles-l.css       
    
Before we move on, be sure to remove either of the files you created previously

    app/design/frontend/Pulsestorm/dram/web/css/styles-l.css
    app/design/frontend/Pulsestorm/dram/web/css/styles-l.less
     
## Practical LessCSS Replacement
    
While the above technique works, it is a bit of a cudgel.  Replacing the **entire** `styles-l.less` file with a custom CSS file means replacing **all** the rules for the entire base Magento theme.  While that may be a necessary step if you're creating a new theme that implements a **different** CSS preprocessor (such as Sass), it would be a tremendous amount of work for more simple theme customizations.  

While you can always add a new CSS/Less file via a layout update XML handle, Magento also has a solution for less invasive LessCSS customizations.  To understand this system, we'll need to take a look at the stock `styles-l.less` file.

Throughout `styles-l.less`, you'll see sections that look like the following

    #File: vendor/magento/theme-frontend-blank/web/css/styles-l.less

    //@magento_import 'source/_widgets.less'; // Theme widgets

These lines may *look* like comments, but they're not.  The `//@magento_import` command is a custom LessCSS rule that Magento's parser picks up on.  In plain english, 

> The `//@magento_import` rules tell LessCSS to search through every active Magento library, module, and theme and then run `import` on the Less rules it finds for the passed in file name

So, the source `source/_widgets.less` line above actually loads in the following core LessCSS rules.

    ./vendor/magento/magento2-base/lib/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_AdvancedCheckout/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_Banner/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_Catalog/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_CatalogEvent/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_Cms/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_GiftRegistry/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_MultipleWishlist/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_Reports/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_Sales/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-blank/Magento_VersionsCms/web/css/source/_widgets.less
    ./vendor/magento/theme-frontend-luma/Magento_AdvancedCheckout/web/css/source/_widgets.less

We can add our own files to this list via our theme.  For example, try adding the following file to our theme.

    #File: app/design/frontend/Pulsestorm/dram/web/css/source/_widget.less
    body.example-less-rule-base-css-folder {
      background-color: #f00;
    }

Again, we're creating this file in our theme's `web/css` folder, and then matching the path provided in the `magento_import` rule (`_widget.less`).  If we clear our cache, clear our preprocessed view files, delete the existing `styles-l.css` files from `pub`, and reload the CSS file's URL (which will look something like the following (view your document's source to find your own link).

    http://magento.example.com/static/version1492893430/ \;
           frontend/Pulsestorm/dram/en_US/css/styles-l.css
    
We'll see Magento's added our example rule to the generated file.       

    /* Generated styles-l.css file */
    /* ... */
    @media only screen and (min-device-width: 320px) and (max-device-width: 780px) and (orientation: landscape) {
      .product-video {
        height: 100%;
        width: 81%;
      }
    }
    body.example-less-rule-base-css-folder {
      background-color: #f00;
    }
    @media all and (min-width: 768px), print {
      .abs-product-options-list-desktop dt,

    /* ... */
    
In addition to adding a file to our theme's `web/css` folder, we can also add a rule to **any** valid module folder in our theme.  For example, create the following file at the following location.

    #File: app/design/frontend/Pulsestorm/dram/Magento_Catalog/web/css/source/_widgets.less
    body.example-less-rule-in-mage-catalog{
        background-color:#f00;
    }    
    
Clear the various caches and remove the already generated files, and then reload the styles-l.css URL.  You should see this new CSS rule added to the file

    body.example-less-rule-base-css-folder {
      background-color: #f00;
    }
    body.example-less-rule-in-mage-catalog {
      background-color: #f00;
    }    
    
## Replacing Less Files

Magento's themes do not offer a way to replace/remote files imported via the `//@magento_import` directive.  However, for files imported using the built-in `@import` command

    #File: vendor/magento/theme-frontend-blank/web/css/styles-l.less
    @import '_styles.less';
    //...
    @import 'source/_theme.less';
    
A theme developer *can* replace these files completely by adding a file to their theme's `web/css` folder.

    app/design/frontend/Pulsestorm/dram/web/css/_styles.less
    app/design/frontend/Pulsestorm/dram/web/css/source/_theme.less
    
Be careful when replacing a less file completely.  If you remove an important variable declaration you may prevent the system from successfully compiling your your less files to CSS.  Also, as with any file replacement technique, future Magento upgrades may change the structure of the original file in such a way that the system is no longer compatible with your changes.  When upgrades come down the pipe, be sure to test your changes.

## Wrap Up

That's all we have time for with Magento's themes.  While we haven't covered every possible file type you can replace/extend with a theme, the patterns we've described above will hold true when you're trying to replace other files with a Magento theme. 

Themes are the inflection point where we can transition from backend PHP systems to front end, browser based, code.  Before we jump completely to the front end systems, we want to take a **very** deep dive in layout file loading in Magento 2.  Our next chapter is the most optional one in this book -- feel free to skip ahead to Chapter 7 if you're not quite ready for the deep dive it offers. 