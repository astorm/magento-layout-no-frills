# Layout Handles

So far in this book, we've been directly `echo`ing output from a controller action.  While this works, and is a great way to learn, it's **not** how client programmers are meant to use Magento's system.  

Building an entire HTML page *from scratch* does not make for an easy to reuse system. It forces each individual developer to decide how the basic `<html/>` page skeleton will be stored, rendered, and edited.  It forces each individual to decide how common page elements (left sidebar, footer, etc.) should be added to.  

When you're working with Magento 2's "according to hoyle" development rules, you'll want to use a *Results Page Object* to create the base HTML page layout.  In this chapter we'll explore using a *Results Page Object*, as well as using a system called *Layout Handles* to formally share Magento 2 layout elements. 

For Magento 1 developers, result page objects are the end-result of Magento 2's refactoring of the old layout XML system.  You'll see many familiar concepts that are implemented quite differently in Magento 2 -- i.e. you know just enough to be dangerous, so step carefully!

## Returning a Result Page Object

We're going to jump right in.  Load the following URL in your browser

    http://magento.example.com/pulsestorm_nofrillslayout/chapter3
    
Just like our previous chapters, you'll see output similar to the following.

    string 'Pulsestorm\Nofrillslayout\Controller\Chapter3\Index::execute' (length=60)

Lets take a look at that URL's controller file
    
    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter3/Index.php
    <?php
    namespace Pulsestorm\Nofrillslayout\Controller\Chapter3;
    use Pulsestorm\Nofrillslayout\Controller\Base;
    
    class Index extends Base
    {   
        protected $resultPageFactory;
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
        )
        {    
            $this->resultPageFactory = $resultPageFactory;
            return parent::__construct($context);
        }           
        public function execute()
        {
            var_dump(__METHOD__);
        }
    }

In the `execute` method we can see the simple `var_dump` statement that led to our output, but there's also something new. The `__construct` method  uses Magento's automatic constructor dependency injection system to inject a result page factory object (i.e. `Magento\Framework\View\Result\PageFactory`).  If you're not familiar with Magento's dependency injection system, checkout the Magento 2 Dependency Injection appendix at the end of this book.

We're going to use the `resultPageFactory` object to create a response object for our controller.  Change your controller's execute method so it looks like the following

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter3/Index.php
    public function execute()
    {
        $pageObject = $this->resultPageFactory->create();
        return $pageObject;
    }
    
If you reload with the above page we'll see -- another blank page?  However, if we view the **source** of that page using our browser's `View Source` feature, or a CLI program like `curl`

    $ curl 'http://magento.example.com/pulsestorm_nofrillslayout/chapter3'
    <!doctype html>
    <html lang="en-US">
        <head >
            <script>
        var require = {
            "baseUrl": "http://magento.example.com/static/frontend/Magento/luma/en_US"
        };
    </script>
            <meta charset="utf-8"/>
    <meta name="description" content="Default Description"/>
    <meta name="keywords" content="Magento, Varien, E-commerce"/>
    <meta name="robots" content="INDEX,FOLLOW"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
    <title></title>
    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/mage/calendar.css" />
    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/styles-m.css" />
    <link  rel="stylesheet" type="text/css"  media="screen and (min-width: 768px)" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/styles-l.css" />
    <link  rel="stylesheet" type="text/css"  media="print" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/print.css" />
    <link  rel="icon" type="image/x-icon" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico" />
    <link  rel="shortcut icon" type="image/x-icon" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico" />
    <script  type="text/javascript"  src="http://magento.example.com/static/frontend/Magento/luma/en_US/requirejs/require.js"></script>
    <script  type="text/javascript"  src="http://magento.example.com/static/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/user/example-script.js"></script>
    <link  rel="stylesheet" type="text/css"  media="all" href="http://magento.example.com/media/styles.css" />            </head>
        <body data-container="body" data-mage-init='{"loaderAjax": {}, "loader": { "icon": "http://magento.example.com/static/frontend/Magento/luma/en_US/images/loader-2.gif"}}' class="pulsestorm-nofrillslayout-chapter3-index page-layout-admin-1column">
                </body>
    </html>
    
we'll see Magento has added a skeleton HTML page.  So, right off the bat, we already have a shared HTML skeleton for all developers to use. This skeleton pulls in Magento's base javascript and CSS, sets up default `<body/>` tag attributes, and makes sure any boilerplate needed for **all** Magento 2 pages is ready for us to use.

With this skeleton created, our next trick will be pulling in the default Magento page with a store's layout, branding, and navigation already created.  We'll do this with something called layout handles. 

## Layout Handles

This is where things get a little weird.  We're going to 

1. Add a "handle" to our page object
2. Setup a "Layout Handle XML file" for that handle
3. Use that "Layout Handle XML file" to specify a default layout

If you don't know what a *handle* or *Layout Update XML file* is, don't worry.  These are things Magento has invented.  We're going to walk through the process of creating each, which should help you understand what they are.

First, we'll add a handle (`our_custom_handle`) to our page layout object.
    
    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter3/Index.php    
    public function execute()
    {
        $pageObject = $this->resultPageFactory->create();
        $pageObject->addHandle('our_custom_handle');
        return $pageObject;
    }
    
Next, we'll create the Layout Handle XML file

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/our_custom_handle.xml
    <page xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" layout="1column">        
    </page> 
    
Finally, we'll clear our cache and reload the page.  We should see a fully rendered one column Magento page with no content.
    
Congratulations -- you just created your first Magento layout handle.    

## What are Handles

The best way to think about handles is as a layout specific event system.  When it comes time for Magento to render a results page object, Magento will look at all the configured handles, and then ask the system

> Hey, here are my handles -- does anyone have XML files for me?

Then, as module developers, we can create an XML file with our handle name (the `our_custom_handle.xml` file above).  This is module developers responding with

> Hello layout system!  Thank you for the handle list. Yes!  We have a `our_custom_handle.xml` file with some layout updates inside.

The XML file itself contains further instructions.  Our file was relatively simple.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/our_custom_handle.xml

    <?xml version="1.0"?>
    <page xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" layout="1column">        
    </page> 
    
The root node of all layout handle XML files is `<page/>`, and the `xsi:noNamespaceSchemaLocation` attribute points to the Magento provided XSD schema validation file.  The one piece of non-boilerplate code in our layout handle XML file is the following

    layout="1column"
    
This tells Magento we want to use the "1column" default layout.  We'll get to what using a different *page layout* means in the next chapter.  Before we can talk about page layouts though, we need to talk a bit about default handles.

## Default Handles

During normal run-of-the-mill development, you'll rarely add a custom handle like we did.          

    $pageObject->addHandle('our_custom_handle');
    
Like a lot of what we do in this book, this was a pedagogical shortcut to help you understand the system.  You *might* include custom handles if your features are complex enough to warrant them, but by and large the default Magento handles should be enough to get you by.     

What are the default Magento handles?  Unfortunately -- there's no simple code snippet you can use to view the default handles in Magento 2.  Debugging tools like Commerce Bug

http://store.pulsestorm.net/products/commerce-bug-3)

will display the specific handles for any page in Magento.  

That said, there are always two handles you can rely on being present.  

The first is the `default` handle.  This handle is fired on **every** page load.  You can use `default` to add layout instructions you want to run everywhere in Magento.  Magento uses the default node to build up much of a page's standard HTML structure and needed javascript/css code.  There are plenty of examples where Magento's core modules use the `default` handle.

    $ find vendor/magento -wholename '*/module-*/default.xml'
    vendor/magento/module-admin-notification/view/adminhtml/layout/default.xml
    vendor/magento/module-backend/view/adminhtml/layout/default.xml
    vendor/magento/module-bundle/view/frontend/layout/default.xml
    vendor/magento/module-captcha/view/frontend/layout/default.xml
    vendor/magento/module-catalog/view/base/layout/default.xml
    vendor/magento/module-catalog/view/frontend/layout/default.xml
    vendor/magento/module-catalog-search/view/frontend/layout/default.xml
    vendor/magento/module-checkout/view/frontend/layout/default.xml
    vendor/magento/module-cms/view/frontend/layout/default.xml
    vendor/magento/module-contact/view/frontend/layout/default.xml
    vendor/magento/module-cookie/view/frontend/layout/default.xml
    vendor/magento/module-customer/view/frontend/layout/default.xml
    vendor/magento/module-directory/view/frontend/layout/default.xml
    vendor/magento/module-google-analytics/view/frontend/layout/default.xml
    vendor/magento/module-newsletter/view/frontend/layout/default.xml
    vendor/magento/module-page-cache/view/frontend/layout/default.xml
    vendor/magento/module-paypal/view/frontend/layout/default.xml
    vendor/magento/module-reports/view/frontend/layout/default.xml
    vendor/magento/module-rss/view/frontend/layout/default.xml
    vendor/magento/module-sales/view/frontend/layout/default.xml
    vendor/magento/module-search/view/frontend/layout/default.xml
    vendor/magento/module-security/view/adminhtml/layout/default.xml
    vendor/magento/module-theme/view/frontend/layout/default.xml
    vendor/magento/module-ui/view/base/layout/default.xml
    vendor/magento/module-weee/view/frontend/layout/default.xml
    vendor/magento/module-widget/view/frontend/layout/default.xml
    vendor/magento/module-wishlist/view/frontend/layout/default.xml

The second handle is also issued on every page -- but has a *different name* on every page.  This handle is known as the "full action name" handle.  Every request made to Magento is identifiable by a full action name -- this name combines the three portions of a standard Magento URL

    /pulsestorm_nofrillslayout/chapter3/index
        
into an underscore separated string. 

    pulsestorm_nofrillslayout_chapter3_index    
    
Magento will always add this underscore separated string as a layout handle name.  If you're unsure of a page's full action name, you can peek at it with the following code in your controller's `execute` method.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter3/Index.php
    public function execute()
    {
        var_dump($this->getRequest()->getFullActionName());
    }     

Another example should clarify things further.  First, lets remove the custom layout handle from our controller

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter3/Index.php
    public function execute()
    {
        $pageObject = $this->resultPageFactory->create();
        //$pageObject->addHandle('our_custom_handle');
        return $pageObject;
    }
    
If we reload our page with the above in place, we'll be back to a blank browser window.  Without our custom handle, Magento doesn't know which page layout it should load.  
    
However, we now know Magento will always issue a `pulsestorm_nofrillslayout_chapter3_index` handle on this page.  So, if we rename our layout handle XML file from `our_custom_handle.xml` to `pulsestorm_nofrillslayout_chapter3_index.xml`, clear our cache, and refresh the page we'll see our layout restored.                

## Adding Content via Layout Update XML Files

Layout handles are for more than just creating a default layout. They can also contain any bit of layout update XML -- the sort of things we were doing manually in Chapter 2. Give the following a try in our new `pulsestorm_nofrillslayout_chapter3_index.xml` file.  

    <!-- File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout//pulsestorm_nofrillslayout_chapter3_index.xml --> 
    <page xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" layout="1column">
        <body>
            <referenceContainer name="content">
                <block  name="pulsestorm_nofrills_chapter3_text"
                        class="Magento\Framework\View\Element\Text">
                    <arguments>
                        <argument name="text" xsi:type="string">This is a test.</argument>
                    </arguments>
                </block>        
            </referenceContainer>
        </body>
    </page> 
    
Clear your cache, reload the page, and you should see the phrase

> This is a test

appended to the `content` container. 

The layout update XML should be familiar to you

    <!-- File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout//pulsestorm_nofrillslayout_chapter3_index.xml --> 
    
    <referenceContainer name="content">
        <block  name="pulsestorm_nofrills_chapter3_text"
                class="Magento\Framework\View\Element\Text">
            <arguments>
                <argument name="text" xsi:type="string">This is a test.</argument>
            </arguments>
        </block>        
    </referenceContainer>

This code gets a reference to a preexisting container (added by Magento) named `content`.  Then, it instantiates a text block object, using an `<argument>` node to set the block's text property.  i.e. This code tells Magento to instantiate a text block and add that block to the content container.

More important for us though is *where* this block sits

    <!-- File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout//pulsestorm_nofrillslayout_chapter3_index.xml --> 
    <page xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" layout="1column">
        <body>
            <!-- ... -->
        </body>
    </page> 

In a page layout handle XML file, any layout update XML should go in the `<body/>` section of the document.  This is where Magento will look for layout update blocks to apply.  In addition to being behaviorally enforced (i.e. put those blocks somewhere else and Magento ignores them), the following XML schema helps enforce these rules

    urn:magento:framework:View/Layout/etc/page_configuration.xsd

You can find this schema in the following file 

    vendor/magento/framework/View/Layout/etc/page_configuration.xsd

While beyond the scope of this particular book, it's worth spending some time looking at these XSD files.  In addition to helping developers keep their XML files correct, they also serve as the de-facto documentation on how a certain type of XML file is meant to be structured.

Although a simple example, that's layout handles in a nutshell.  You may be wondering where the `content` block we referenced came from -- in our next chapter we'll take a look at what's averrable in a default page layout for Magento developers, as well as how Magento renders the overall page skeleton.


