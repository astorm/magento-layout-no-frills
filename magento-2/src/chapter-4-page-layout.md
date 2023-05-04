# Page Layouts in Magento 2

When it comes to the HTML page layout for a page, so far we've spent a lot of time talking about 

1. Individual components of Magento's layout system and using those components to build up simple examples "from scratch"
2. Starting with a fully rendered Magento page and slightly altering it.  

In this chapter, we're going to dive a bit deeper and take a look at how **Magento** creates its fully designed layouts from scratch.  We'll do this by stripping a Magento MVC endpoint down to a naked, blank, HTML page.

## Getting to a Blank Page

To start with, load the following URL in your browser

    http://magento.example.com/pulsestorm_nofrillslayout/blank/index
    
You should see a one column Magento page with the message *Hello Blank Page*. If you're curious, the page's controller file looks like this

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Blank/Index.php    
    <?php
    namespace Pulsestorm\Nofrillslayout\Controller\Blank;
    use Pulsestorm\Nofrillslayout\Controller\BaseController;
    class Index extends \Magento\Framework\App\Action\Action
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
            $pageObject = $this->resultPageFactory->create();
            return $pageObject;
        }
    }    
    
and its layout handle XML file looks like this

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/pulsestorm_nofrillslayout_blank_index.xml
    <?xml version="1.0"?>
    <page   xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" 
            layout="1column">        
        <body>
            <referenceContainer name="content">
                <block 
                    template="Pulsestorm_Nofrillslayout::blank/hello.phtml" 
                    class="Magento\Framework\View\Element\Template" 
                    name="pulsestorm_nofrillslayout_blank_hello"/>
            </referenceContainer>               
        </body>
    </page>   
    
Our goal is to strip away Magento's outer shell and render as simple an HTML page as possible.  Magento's layout system actually has a feature for rendering a empty page.  If we change the `layout="1column"` to `layout="empty"    
    

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/pulsestorm_nofrillslayout_blank_index.xml
    <?xml version="1.0"?>
    <page   xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" 
            layout="empty">        
            <!-- ... -->
    </page>         
    
clear our cache, and then reload the page, we should see an "empty" page with our content. 
    
Observant viewers may know why we put "empty" in skeptical quotes.  While the Magento page branding and navigation is gone, our page itself is still rendered with a non-default type-face, and some non-default indenting.  If we view the source of the document returned from the server, we'll see something that looks similar to the following

    <!doctype html>
    <html lang="en-US">
        <head>
            <script>
                var require = {
                    "baseUrl": "http://magento.example.com/static/frontend/Magento/luma/en_US"
                };
            </script>
            <meta charset="utf-8" />
            <meta name="description" content="Default Description" />
            <meta name="keywords" content="Magento, Varien, E-commerce" />
            <meta name="robots" content="INDEX,FOLLOW" />
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" /> 
            <title></title>
            <link rel="stylesheet" type="text/css" media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/mage/calendar.css" />
            <link rel="stylesheet" type="text/css" media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/styles-m.css" />
            <link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/styles-l.css" />
            <link rel="stylesheet" type="text/css" media="print" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/print.css" />
            <link rel="icon" type="image/x-icon" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico" />
            <link rel="shortcut icon" type="image/x-icon" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico" /> 
            <script type="text/javascript" src="http://magento.example.com/static/frontend/Magento/luma/en_US/requirejs/require.js">
            </script>
            <script type="text/javascript" src="http://magento.example.com/static/frontend/Magento/luma/en_US/mage/requirejs/mixins.js">
            </script>
            <script type="text/javascript" src="http://magento.example.com/static/_requirejs/frontend/Magento/luma/en_US/requirejs-config.js">
            </script>
            <script type="text/javascript" src="http://magento.example.com/static/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/user/example-script.js">
            </script>
            <link rel="stylesheet" type="text/css" media="all" href="http://magento.example.com/media/styles.css" /> 
        </head>
        <body data-container="body" data-mage-init='{"loaderAjax": {}, "loader": { "icon": "http://magento.example.com/static/frontend/Magento/luma/en_US/images/loader-2.gif"}}' class="pulsestorm-nofrillslayout-blank-index page-layout-empty">
            <script>
                require.config({
                    deps: [
                        'jquery',
                        'mage/translate',
                        'jquery/jquery-storageapi'
                    ],
                    callback: function ($) {
                        'use strict';
            
                        var dependencies = [],
                            versionObj;
            
                        $.initNamespaceStorage('mage-translation-storage');
                        $.initNamespaceStorage('mage-translation-file-version');
                        versionObj = $.localStorage.get('mage-translation-file-version');
            
                        if (versionObj.version !== '91c3baf5050b0c1cb0285ace5c5cb45e3f973149') {
                            dependencies.push(
                                'text!js-translation.json'
                            );
            
                        }
            
                        require.config({
                            deps: dependencies,
                            callback: function (string) {
                                if (typeof string === 'string') {
                                    $.mage.translate.add(JSON.parse(string));
                                    $.localStorage.set('mage-translation-storage', string);
                                    $.localStorage.set(
                                        'mage-translation-file-version',
                                        {
                                            version: '91c3baf5050b0c1cb0285ace5c5cb45e3f973149'
                                        }
                                    );
                                } else {
                                    $.mage.translate.add($.localStorage.get('mage-translation-storage'));
                                }
                            }
                        });
                    }
                });
            </script>
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
            <noscript>
                <div class="message global noscript">
                    <div class="content">
                        <p>
                            <strong>
                                JavaScript seems to be disabled in your browser.
                            </strong>
                            <span>
                                For the best experience on our site, be sure to turn on Javascript in your browser.
                            </span>
                        </p>
                    </div>
                </div>
            </noscript>
            <div class="page-wrapper">
                <main id="maincontent" class="page-main">
                    <a id="contentarea" tabindex="-1">
                    </a>
                    <div class="page messages">
                        <div data-placeholder="messages">
                        </div>
                        <div data-bind="scope: 'messages'">
                            <div data-bind="foreach: { data: cookieMessages, as: 'message' }" class="messages">
                                <div data-bind="attr: {
                class: 'message-' + message.type + ' ' + message.type + ' message',
                'data-ui-id': 'message-' + message.type
            }">
                                    <div data-bind="html: message.text">
                                    </div>
                                </div>
                            </div>
                            <div data-bind="foreach: { data: messages().messages, as: 'message' }" class="messages">
                                <div data-bind="attr: {
                class: 'message-' + message.type + ' ' + message.type + ' message',
                'data-ui-id': 'message-' + message.type
            }">
                                    <div data-bind="html: message.text">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script type="text/x-magento-init">
                            {
                                "*": {
                                    "Magento_Ui/js/core/app": {
                                        "components": {
                                                "messages": {
                                                    "component": "Magento_Theme/js/view/messages"
                                                }
                                            }
                                        }
                                    }
                            }
                        </script>
                    </div>
                    <div class="columns">
                        <div class="column main">
                            <input name="form_key" type="hidden" value="QK7qYF0ueevnC66R" />
                            <div id="authenticationPopup" data-bind="scope:'authenticationPopup'" style="display: none;">
                                <script>
                                    window.authenticationPopup = {"customerRegisterUrl":"http:\/\/magento.example.com\/customer\/account\/create\/","customerForgotPasswordUrl":"http:\/\/magento.example.com\/customer\/account\/forgotpassword\/","baseUrl":"http:\/\/magento.example.com\/"};
                                </script>
    <!-- ko template: getTemplate() -->
    <!-- /ko -->
                                <script type="text/x-magento-init">
                                    {
                                        "#authenticationPopup": {
                                            "Magento_Ui/js/core/app": {"components":{"authenticationPopup":{"component":"Magento_Customer\/js\/view\/authentication-popup","children":{"messages":{"component":"Magento_Ui\/js\/view\/messages","displayArea":"messages"},"captcha":{"component":"Magento_Captcha\/js\/view\/checkout\/loginCaptcha","displayArea":"additional-login-form-fields","formId":"user_login","configSource":"checkout"}}}}}            },
                                        "*": {
                                            "Magento_Ui/js/block-loader": "http://magento.example.com/static/frontend/Magento/luma/en_US/images/loader-1.gif"
                                        }
                                    }
                                </script>
                            </div>
                            <script type="text/x-magento-init">
                                {"*":{"Magento_Customer\/js\/section-config":{"sections":{"stores\/store\/switch":"*","directory\/currency\/switch":"*","*":["messages"],"customer\/account\/logout":"*","customer\/account\/loginpost":"*","customer\/account\/createpost":"*","customer\/ajax\/login":["checkout-data","cart"],"catalog\/product_compare\/add":["compare-products"],"catalog\/product_compare\/remove":["compare-products"],"catalog\/product_compare\/clear":["compare-products"],"sales\/guest\/reorder":["cart"],"sales\/order\/reorder":["cart"],"checkout\/cart\/add":["cart"],"checkout\/cart\/delete":["cart"],"checkout\/cart\/updatepost":["cart"],"checkout\/cart\/updateitemoptions":["cart"],"checkout\/cart\/couponpost":["cart"],"checkout\/cart\/estimatepost":["cart"],"checkout\/cart\/estimateupdatepost":["cart"],"checkout\/onepage\/saveorder":["cart","checkout-data","last-ordered-items"],"checkout\/sidebar\/removeitem":["cart"],"checkout\/sidebar\/updateitemqty":["cart"],"rest\/*\/v1\/carts\/*\/payment-information":["cart","checkout-data","last-ordered-items"],"rest\/*\/v1\/guest-carts\/*\/payment-information":["cart","checkout-data"],"rest\/*\/v1\/guest-carts\/*\/selected-payment-method":["cart","checkout-data"],"rest\/*\/v1\/carts\/*\/selected-payment-method":["cart","checkout-data"],"multishipping\/checkout\/overviewpost":["cart"],"paypal\/express\/placeorder":["cart","checkout-data"],"paypal\/payflowexpress\/placeorder":["cart","checkout-data"],"review\/product\/post":["review"],"authorizenet\/directpost_payment\/place":["cart","checkout-data"],"braintree\/paypal\/placeorder":["cart","checkout-data"],"wishlist\/index\/add":["wishlist"],"wishlist\/index\/remove":["wishlist"],"wishlist\/index\/updateitemoptions":["wishlist"],"wishlist\/index\/update":["wishlist"],"wishlist\/index\/cart":["wishlist","cart"],"wishlist\/index\/fromcart":["wishlist","cart"],"wishlist\/index\/allcart":["wishlist","cart"],"wishlist\/shared\/allcart":["wishlist","cart"],"wishlist\/shared\/cart":["cart"]},"clientSideSections":["checkout-data"],"baseUrls":["http:\/\/magento.example.com\/"]}}}
                            </script>
                            <script type="text/x-magento-init">
                                {"*":{"Magento_Customer\/js\/customer-data":{"sectionLoadUrl":"http:\/\/magento.example.com\/customer\/section\/load\/","cookieLifeTime":"3600","updateSessionUrl":"http:\/\/magento.example.com\/customer\/account\/updateSession\/"}}}
                            </script>
                            <script type="text/x-magento-init">
                                {
                                    "body": {
                                        "pageCache": {"url":"http:\/\/magento.example.com\/page_cache\/block\/render\/","handles":["default","pulsestorm_nofrillslayout_blank_index"],"originalRequest":{"route":"pulsestorm_nofrillslayout","controller":"blank","action":"index","uri":"\/pulsestorm_nofrillslayout\/blank\/index"},"versionCookieName":"private_content_version"}        }
                                }
                            </script>
                            <p>
                                Hello Blank Page.
                            </p>
                        </div>
                    </div>
                </main>
                <small class="copyright">
                    <span>
                        Copyright © 2016 Magento. All rights reserved.
                    </span>
                </small>
            </div>
        </body>
    </html>

In other words, we'll see a document that's **far** from *empty*.  The copyright notice, the default styles and javascript, the many javascript blocks Magento relies on for base functionality, the `page-wrapper` `<div/>`, the `<main/>` section, etc.  While an end user may look at this page and consider it empty, a developer knows better.  

## More Empty

There's a way we can get a page that's "more" empty.  If we remove the `layout="empty"` tag, or set it to a value that Magento doesn't recognize, we'll get something that's even **more** empty.  We're going to opt for setting it to `layout="more-empty"`, as some versions of Magento 2 set a default value for this attribute in other layout handles. 
         
    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/pulsestorm_nofrillslayout_blank_index.xml
    <?xml version="1.0"?>
    <page   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xsi:noNamespaceSchemaLocation="../../../../../../../lib/internal/Magento/Framework/View/Layout/etc/page_configuration.xsd"
            layout="more-empty">   
            <!-- ... -->
    </page>             
    
If you clear your cache and reload the browser page with the above in place, we'll see a page that's much more blank.
    
In fact, this page is **so** blank, that even our content is gone!  We'll explain that side effect in a bit, but let's take a look at the HTML source of the page

    <!doctype html>
    <html lang="en-US">
        <head>
            <script>
                var require = {
                    "baseUrl": "http://magento.example.com/static/frontend/Magento/luma/en_US"
                };
            </script>
            <meta charset="utf-8" />
            <meta name="description" content="Default Description" />
            <meta name="keywords" content="Magento, Varien, E-commerce" />
            <meta name="robots" content="INDEX,FOLLOW" />
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" /> 
            <title>
            </title>
            <link rel="stylesheet" type="text/css" media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/mage/calendar.css" />
            <link rel="stylesheet" type="text/css" media="all" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/styles-m.css" />
            <link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/styles-l.css" />
            <link rel="stylesheet" type="text/css" media="print" href="http://magento.example.com/static/frontend/Magento/luma/en_US/css/print.css" />
            <link rel="icon" type="image/x-icon" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico" />
            <link rel="shortcut icon" type="image/x-icon" href="http://magento.example.com/static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico" /> 
            <script type="text/javascript" src="http://magento.example.com/static/frontend/Magento/luma/en_US/requirejs/require.js">
            </script>
            <script type="text/javascript" src="http://magento.example.com/static/frontend/Magento/luma/en_US/Pulsestorm_Nofrillslayout/user/example-script.js">
            </script>
            <link rel="stylesheet" type="text/css" media="all" href="http://magento.example.com/media/styles.css" /> 
        </head>
        <body data-container="body" data-mage-init='{"loaderAjax": {}, "loader": { "icon": "http://magento.example.com/static/frontend/Magento/luma/en_US/images/loader-2.gif"}}' class="pulsestorm-nofrillslayout-blank-index page-layout-more-empty">
        </body>
    </html>         
    
While there's less here, it's still hard to call this page empty.  The `baseUrl` configuration for RequireJS, the `<meta/>` tags, the default styles, the `<script/>` tag pulling in RequireJS, and the attributes of the `<body/>` tag all point to more code archaeology work to be done at deeper levels of the Magento system.

## Magento 2's root.phtml Template

If you're familiar with Magento 1, you know Magento had a number of default "root" templates for a store.     

    $ ls -1 /path/to/magento-1/app/design/frontend/base/default/template/page/
    1column.phtml
    2columns-left.phtml
    2columns-right.phtml
    3columns.phtml
    empty.phtml
    //...
    
Each of these did pretty much what the file name said.  A one column page, a two column page with a left column, a two column page with a right column, etc.  Each of these templates set the base skeleton for a Magento page, and began rendering child blocks for each section and column of the page.  

While Magento 2's layout system is very similar to Magento 1's, this is one area where things have changed substantially.  Magento 2 has **one** `phtml` root template file, and this file **cannot** be changed by the layout system.

This file is located at the following path, and contains the following content.  

    #File: vendor/magento/module-theme/view/base/templates/root.phtml
    <!doctype html>
    <html <?php /* @escapeNotVerified */ echo $htmlAttributes ?>>
        <head <?php /* @escapeNotVerified */ echo $headAttributes ?>>
            <?php /* @escapeNotVerified */ echo $requireJs ?>
            <?php /* @escapeNotVerified */ echo $headContent ?>
            <?php /* @escapeNotVerified */ echo $headAdditional ?>
        </head>
        <body data-container="body" data-mage-init='{"loaderAjax": {}, "loader": { "icon": "<?php /* @escapeNotVerified */ echo $loaderIcon; ?>"}}' <?php /* @escapeNotVerified */ echo $bodyAttributes ?>>
            <?php /* @escapeNotVerified */ echo $layoutContent ?>
        </body>
    </html>

This `phtml` template is responsible for rendering every Magento MVC page.  The `/* @escapeNotVerified */` contents are a flag for Magento's test suite that say "we know these variables aren't escaped, they're probably OK as is, but we haven't verified that".  Let's remove those comments so the file's a little easier to read, as well as jigger the formatting a bit.

    #File: vendor/magento/module-theme/view/base/templates/root.phtml
    <!doctype html>
    <html <?php echo $htmlAttributes ?>>
        <head <?php echo $headAttributes ?>>
            <?php echo $requireJs ?>
            <?php echo $headContent ?>
            <?php echo $headAdditional ?>
        </head>
        <body data-container="body" 
              data-mage-init=
                '{"loaderAjax": {}, "loader": { "icon": "<?php echo $loaderIcon; ?>"}}' 
              <?php echo $bodyAttributes ?>
        >            
            <?php echo $layoutContent ?>
        </body>
    </html>

The first big change for a Magento 1 developer is -- there's no calls to `getChildHtml`.  Instead, Magento `echo`s out eight different variables

    $htmlAttributes
    $headAttributes
    $requireJs
    $headContent
    $headAdditional
    $loaderIcon
    $bodyAttributes
    $layoutContent
    
Each of these variables contains default page elements, and explains why our *more-empty* layout was still not really empty.  Our next logical question is: Where to these variables come from?

## The View Results Page Class    

In Magento 2, the `Magento\Framework\View\Result\Page` class is ultimately responsible for rendering an MVC response.  The reasons for this are, unfortunately, too complicated to go into right now.  The more adventurous among you will need to take that journey on your own.  

For now, we're interested in the `renderPage` method of `Magento\Framework\View\Result\Page`.

    #File: vendor/magento/framework/View/Result/Page.php
    protected function renderPage()
    {
        $fileName = $this->viewFileSystem->getTemplateFileName($this->template);
        if (!$fileName) {
            throw new \InvalidArgumentException('Template "' . $this->template . '" is not found');
        }

        ob_start();
        try {
            extract($this->viewVars, EXTR_SKIP);
            include $fileName;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        $output = ob_get_clean();
        return $output;
    }
    
This method creates a file path from the `$this->template` variable.  

    #File: vendor/magento/framework/View/Result/Page.php

    $fileName = $this->viewFileSystem->getTemplateFileName($this->template);   
    
Then, it uses PHP's `extract` function to pull all the `$this->viewVars` array keys into the local scope as variables.  

    #File: vendor/magento/framework/View/Result/Page.php

    extract($this->viewVars, EXTR_SKIP);
    
This means they'll also be available in `$fileName` when Magento uses PHP's `include` function to load `$fileName`.
   
    #File: vendor/magento/framework/View/Result/Page.php
   
    include $fileName;   
    
This code is also wrapped in output buffering functions and a try/catch block.  If you've ever wondered why Magento 2 exceptions *only* print an error and not any content -- this is why.   

The `$fileName` variable contains the full path to our `root.phtml` template.  Magento populates the `$this->template` variable via automatic constructor dependency injection     

    #File: vendor/magento/framework/View/Result/Page.php
    public function __construct(
        /* ... */
        $template,
        /* ... */
    ) {
        /* ... */
        $this->template = $template;
        /* ... */
    }
    
The value of this variable is `Magento_Theme::root.phtml`, set in the following core `di.xml` file.

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Result\Page">
        <arguments>
            <!-- ... -->
            <argument name="template" xsi:type="string">
                Magento_Theme::root.phtml
            </argument>
        </arguments>
    </type>
    
Don't worry if you didn't follow all of that. The key take away is Magento renders `root.phtml` via a plain old `include`, and the variables `echo`ed inside that template come from the `viewVars` array.

Our next question?  What populates `viewVars`?

## View Variables for the View Results Page Class

The `Magento\Framework\View\Result\Page` class has an `assign` method.

    #File: vendor/magento/framework/View/Result/Page.php
    protected function assign($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $subKey => $subValue) {
                $this->assign($subKey, $subValue);
            }
        } else {
            $this->viewVars[$key] = $value;
        }
        return $this;
    }
    
This method is pretty simple: Pass it a key and a value, and the method sets a value on the `viewVars` array object property.  We mention `assign`, because if we look at the `render` method (the method that itself calls `renderPage` from earlier in the chapter)

    #File: vendor/magento/framework/View/Result/Page.php
    protected function render(ResponseInterface $response)
    {
        /* ... */
        $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
        $requireJs = $this->getLayout()->getBlock('require.js');        
        /* ... */
        $this->assign([
            'requireJs' => $requireJs ? $requireJs->toHtml() : null,
            'headContent' => $this->pageConfigRenderer->renderHeadContent(),
            'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
            'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
            'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
            'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),
            'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif'),
        ]);

        $output = $this->getLayout()->getOutput();
        $this->assign('layoutContent', $output);
        /* ... */
        return $this;
    }    

We see **this** is where, (via `assign`), that Magento sets the variables rendered in `root.phtml`.  In other words `$layoutContent` here

    #File: vendor/magento/module-theme/view/base/templates/root.phtml
    <?php /* @escapeNotVerified */ echo $layoutContent ?>
      
comes from this call here

    #File: vendor/magento/framework/View/Result/Page.php

    $output = $this->getLayout()->getOutput();
    $this->assign('layoutContent', $output);
       
Each of these view variables is set in a different way.  The `$output = $this->getLayout()->getOutput();` looks for output generated after Magento has  processed all the layout handle XML files in the system for a particular request.  

The `$loaderIcon` variable, on the other hand, is a simple rendering of a URL using the `getViewFileUrl` method.

    #File: vendor/magento/framework/View/Result/Page.php

    'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif'),            
    
Magento populates the `$headAdditional` and `$requireJs` variables by rendering a **specific** block from the layout     

    #File: vendor/magento/framework/View/Result/Page.php

    $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
    $requireJs = $this->getLayout()->getBlock('require.js');        
    //...
    'requireJs' => $requireJs ? $requireJs->toHtml() : null,
    'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
    
But values for `$headContent`, `$htmlAttributes`, `$headAttributes`, and `$bodyAttributes` come from calls to the `pageConfigRenderer` property 
       
            'headContent' => $this->pageConfigRenderer->renderHeadContent(),
            'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
            'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
            'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),    
    
The `pageConfigRenderer` property is a `Magento\Framework\View\Page\Config\Renderer` object, set somewhat indirectly in the constructor by a factory that's injected via automatic constructor dependency injection.

We're **not** going to track down the code behind the setting of each of these variables.  That is, again, a job for a more intrepid developer.  We brought you down this deep into Magento's internals to 

1. Show you where the root templates comes from 

2. Make a point about "second system" syndrome. 

While there's no doubt that the individual developer who made these changes to Magento's layout system did so with the best of intentions, we've ended up with a new system that has only **increased** the complexity of the layout system.  

While we *could* keep going in our efforts to produce a true "blank" Magento 2 page, those efforts would only lead to additional instabilities.  We *could* remove the `head.additional` and `requirejs` blocks from the layout, and figure out how to manipulate the `Magento\Framework\View\Page\Config\Renderer` object so it produces empty attributes.  Also, if you're familiar with Magento's automatic constructor dependency injection system we could even **replace** the `root.phtml` template file with one of our own choosing.

However, doing so would likely **break** Magento's default themes in fundamental ways.  Removing the RequireJS `<script/>` tag would break most of Magento's javascript.  Removing default classes would break not only the styling, but javascript code that uses those classes in jQuery selectors, etc. 

Much like the homeowner who turns off everything electric in their house and puzzles over the meter that's still recording usage, the above HTML skeleton is as close as most Magento developers will be able to get to a blank page **without** reimplementing all the functionality of a base Magento store.

That leaves us with one last questions to answer: What the heck happened to our *Hello Blank Page* content?

## Page Layouts

In our layout handle XML file, we have the following code

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/pulsestorm_nofrillslayout_blank_index.xml

    <referenceContainer name="content">
        <block 
            template="Pulsestorm_Nofrillslayout::blank/hello.phtml" 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrillslayout_blank_hello"/>
    </referenceContainer>  
    
This code added our `pulsestorm_nofrillslayout_blank_hello` block to the already created container named `content`.  When we changed our layout to `layout="more-empty"` we **lost** the container named content.  To fix this, we'll need to make our "more-empty" page layout a **real** page layout. 

Page layouts are configured via page layout XML files.  A page layout configuration file looks similar to a layout handle XML file. Magento loads page layout XML files using many of the same classes that load layout handle XML, but Magento loads **page layout** files **separately** from **layout** handle XML files.  

A quick example should help set that in your mind.  Add the following file to the `Pulsestorm_Nofrillslayout` module

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/page_layout/more-empty.xml
    <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_layout.xsd">

        <container name="our_first_container"/>
    </layout>

This is a page layout XML file.  While it looks similar to a layout handle XML file -- notice the root node is named `<layout/>`, and not `<page/>`.  By putting this file in a `page_layout` folder, we've told Magento it's a page layout XML file, and not a layout handle XML file.  By naming this file `more-empty`, we've told Magento that the rules in this file make up the page layout named *more-empty*.

Inside this file, we have a single container node 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/page_layout/more-empty.xml
    
    <container name="our_first_container"/>
    
This means our layout has a single container named `our_first_container`.  Let's update our layout handle XML file to insert our block into **this** container instead of the container named `content`.  In other words, replace `<referenceContainer name="content">` with `<referenceContainer name="our_first_container">`. 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/pulsestorm_nofrillslayout_blank_index.xml

    <referenceContainer name="our_first_container">
        <!-- ... --->
    </referenceContainer>  

With the above in place, clear your cache and reload the page.

> Hello Blank Page
    
Eureka -- our content is restored!    

## Default Layouts

These page layout XML files do **one** of the jobs that Magento 1's
`1column.phtml, 2columns-left.phtml, 2columns-right.phtml, 3columns.phtml, empty.phtml` files did.  A page layout sets up the default container structure for a page.  However, as previously mentioned, in Magento 2 there's a separate system for creating the skeleton HTML.  This is a pattern you'll see repeated all over Magento 2 -- systems that did multiple things in Magento 1 will be broken out and separated into two separate systems in Magento 2. 

When we initially configured `more-empty` as a page layout (i.e. when we created the `more-empty.xml` file), we effectively removed **all** the containers from the page. This meant when our layout handle XML file tried to get a reference to the `content` container (`<referenceContainer name="content">`), there was no container named content, and nowhere to put the block.  Even if you're in developer mode, the `<referenceContainer/>` and `<referenceBlock/>` methods will fail silently when they can't find a block or container.  

Let's take a look at the default/stock page layout files that ship with Magento 2

    $ find vendor/magento/ -wholename '*/page_layout/*'
    module-checkout/view/frontend/page_layout/checkout.xml

    module-theme/view/frontend/page_layout/1column.xml
    module-theme/view/frontend/page_layout/2columns-left.xml
    module-theme/view/frontend/page_layout/2columns-right.xml
    module-theme/view/frontend/page_layout/3columns.xml
    module-theme/view/base/page_layout/empty.xml
        
    module-layered-navigation/view/frontend/page_layout/1column.xml
    module-layered-navigation/view/frontend/page_layout/2columns-left.xml
    module-layered-navigation/view/frontend/page_layout/2columns-right.xml
    module-layered-navigation/view/frontend/page_layout/3columns.xml
    module-layered-navigation/view/frontend/page_layout/empty.xml
    
    module-security/view/adminhtml/page_layout/admin-popup.xml    
    module-theme/view/adminhtml/page_layout/admin-1column.xml
    module-theme/view/adminhtml/page_layout/admin-2columns-left.xml
    module-theme/view/adminhtml/page_layout/admin-empty.xml
    module-theme/view/adminhtml/page_layout/admin-login.xml

Based on the above, we can see Magento 2 has the following page layouts available in the front end cart.

    layout="checkout"
    layout="1column"
    layout="2columns-left"
    layout="2columns-right"
    layout="3columns"
    layout="empty"
    
In the backend admin (the `adminhtml` area) we have the following page layouts available

    layout="admin-1column"    
    layout="admin-2column"        
    layout="admin-empty"            
    layout="admin-login"                
    layout="admin-popup"                    

You may be wondering why the layered navigation module contains its own versions of these page layout files

    vendor/magento/module-theme/view/frontend/page_layout/1column.xml
    vendor/magento/module-layered-navigation/view/frontend/page_layout/1column.xml

Well -- much like layout handle XML files, Magento will load page layout XML files from **all** available modules and combine them.  This allows *any* module to create (as we did with `more-empty`) their own page layout, or **add to** (as the `module-layered-navigation` module does) an existing page layout.  

## Where's The Content Block?

You'll recall our earlier attempt to create an empty layout with the 

    layout="empty" 
    
attribute.  Let's take a look at the XML DSL code for the `empty` page layout.     

    #File: vendor/magento/module-theme/view/base/page_layout/empty.xml
    <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_layout.xsd">
        <container name="root">
            <container name="after.body.start" as="after.body.start" before="-" label="Page Top"/>
            <container name="page.wrapper" as="page_wrapper" htmlTag="div" htmlClass="page-wrapper">
                <container name="global.notices" as="global_notices" before="-"/>
                <container name="main.content" htmlTag="main" htmlId="maincontent" htmlClass="page-main">
                    <container name="columns.top" label="Before Main Columns"/>
                    <container name="columns" htmlTag="div" htmlClass="columns">
                        <container name="main" label="Main Content Container" htmlTag="div" htmlClass="column main"/>
                    </container>
                </container>
                <container name="page.bottom.container" as="page_bottom_container" label="Before Page Footer Container" after="main.content" htmlTag="div" htmlClass="page-bottom"/>
                <container name="before.body.end" as="before_body_end" after="-" label="Page Bottom"/>
            </container>
        </container>
    </layout>
    
As we can see, an "empty" layout actually sets up a number of different containers.  The above sets up a default hierarchy that looks like this

    root
        after.body.start
        page.wrapper
            global.notices
            main.content
                columns.top
                columns
                    main
            page.bottom.container
            before.body.end    

As you can see, an empty layout already contains 10 individual containers for us to insert our blocks into.  However -- where's the content block?

Maybe it's in the layered navigation page layout XML file?  While that would be a little weird, weirder things have happened in the Magento source. However, if we examine this second `empty.xml` file, there's no sign of a content block.

    #File: vendor/magento/module-layered-navigation/view/frontend/page_layout/empty.xml
    <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_layout.xsd">
        <move element="catalog.leftnav" destination="category.product.list.additional" before="-"/>
    </layout>

This brings us to another example of second system syndrome in Magento 2.  Although the page layout system was invented to set the default container structure for a page, the `content` block is actually added by a layout handle XML file.

    #File: vendor/magento/module-theme/view/frontend/layout/default.xml
    <referenceContainer name="main">
        <container name="content.top" label="Main Content Top"/>
        <container name="content" label="Main Content Area"/>
        <container name="content.aside" label="Main Content Aside"/>
        <container name="content.bottom" label="Main Content Bottom"/>
    </referenceContainer>

This `default` handle file (the `default` handle run on every page) gets a reference to the `main` container (this `main` container was created in the `empty.xml` page layout XML file), and adds four new containers `content.top`, `content`, `content.aside`, and `content.bottom`.  

Why was `content`, a block that's available on all page of Magento 2, not added as part of the page layouts?  Unfortunately, only the developers involved know, and while we can sure each individual developer has very good reasons for doing what they did here, the end result is a system where the implicit goal

> Pull page structure into individual page layout files

seems undermined by the actual implementation. 

## Container Features

Before we wrap-up our journey into the page layout system, let's take another look at the HTML source of the `our_first_container` container we rendered.

    <em>Hello Blank Page.</em>
    
Container tags are, by default, just that: Containers to drop our blocks into.  However, there's a few special attributes we can use with our container blocks that will impact the HTML created by our containers.  For example, if we add a `htmlTag` attribute to our container, we can wrap the contents of the container in another tag.  Try editing our `more-empty.xml` file so the `<container/>` looks like this (i.e. we've added a `htmlTag="div"`)

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/page_layout/more-empty.xml
    <container 
            name="our_first_container"
            htmlTag="div"  />
            
Clear the cache, reload the page, and the HTML rendered should now look like this

    <div><em>Hello Blank Page.</em></div>
    
You'll need to be careful with this feature -- Magento's XSD validation files only allow the following wrapper tags: `dd, div, dl, fieldset, main, header, footer, ol, p, section, table, tfoot, ul, nav`.  Trying to wrap something in say, a span, will result in a fatal schema violation exception.  

Once you have a wrapper tag in place, the `htmlId` and `htmlClass` attributes allow you to add an `id` and `class` to your wrapper tags.  In other words, this

    <container 
            name="our_first_container"
            htmlTag="div"  
            htmlId="our_id"
            htmlClass="our_id"
            />
            
results in HTML output that looks like this

    <div id="our_id" class="our_class">
        <em>Hello Blank Page.</em>
    </div>
    
With this new knowledge in mind, if we take another look at the main `empty.xml` page layout file

    #File: vendor/magento/module-theme/view/base/page_layout/empty.xml
    <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_layout.xsd">
        <container name="root">
            <container name="after.body.start" as="after.body.start" before="-" label="Page Top"/>
            <container name="page.wrapper" as="page_wrapper" htmlTag="div" htmlClass="page-wrapper">
                <container name="global.notices" as="global_notices" before="-"/>
                <container name="main.content" htmlTag="main" htmlId="maincontent" htmlClass="page-main">
                    <container name="columns.top" label="Before Main Columns"/>
                    <container name="columns" htmlTag="div" htmlClass="columns">
                        <container name="main" label="Main Content Container" htmlTag="div" htmlClass="column main"/>
                    </container>
                </container>
                <container name="page.bottom.container" as="page_bottom_container" label="Before Page Footer Container" after="main.content" htmlTag="div" htmlClass="page-bottom"/>
                <container name="before.body.end" as="before_body_end" after="-" label="Page Bottom"/>
            </container>
        </container>
    </layout>
    
we'll see that `htmlTag` and `htmlClass` are used liberally throughout the layout.  This explains where all those wrapper tags came from when we switched our layout to `empty.xml`.  

## Certified Backend Layout Developer

Now that we're four chapters in, you should have the basic architectural principles for understanding Magento's page layouts.  

In our next chapter, we'll take a look at Magento's **theme** system.  The intent of themes is to allow traditional HTML/CSS/Javascript developers to modify the look, feel, and behavior of a Magento web page.  As we'll learn though, without a firm grasp of layout fundamentals, what you'll be able to do with themes is extremely limited. 