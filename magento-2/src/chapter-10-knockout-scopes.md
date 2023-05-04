# Registering Knockout.js Custom Scopes

To close out the book, we're going to take a deeper look at Magento's javascript based, front end view model system.  Unfortunately, we don't have the time or space to cover every topic in the depth that we've covered Magento's layout XML. In other words, this chapter assumes some knowledge we haven't yet covered in this book.  If you're stuck on a fundamental concept or specific problem remember that help is just a Stack Overflow question away. 

Magento's front end template library is the venerable Knockout.js.  While not the latest and greatest, Knockout is a well documented library with known patterns and behaviors.   However, if you look at the source code of a Magento page looking for a Knockout.js view model, you may be a little confused.  There's no obvious place where Magento uses Knockout's `ko.applyBindings` method to bind a view model to the page.  

That's because Knockout.js's default mechanisms start to fall apart when you have many different developers trying to use the  Knockout.js view model for their own template and business logic.  A single view model is perfect for a three person team building a marketing site.  It's less perfect for a team of thirty, supporting a community thousands of developers strong.

If you load the Magento homepage, you'll see HTML that looks something like this.  

    <li class="greet welcome" data-bind="scope: 'customer'">
        <span data-bind="text: customer().fullname ? $t('Welcome, %1!').replace('%1', customer().fullname) : 'Default welcome msg!'"></span>
    </li>

This looks like standard Knockout.js template code, with one glaring exception

    data-bind="scope: 'customer'"
    
The `scope` binding is unfamiliar, even to experienced Knockout.js developers.  That's because this is a **custom** binding, written by the Magento core team.  When you say `scope: 'customer'`, you're telling Knockout.js that the inner nodes are bound to the "customer" view model.  

In this way, different modules can apply different Knockout.js view models to different areas of the page, and everyone's code can coexist in harmony.  

You're probably wondering **how** to create these scoped view models.  That's what we'll cover in this chapter.

## Getting Started

Load up this chapter's URL in your browser

    http://magento.example.com/pulsestorm_nofrillslayout/chapter10
    
We've set you up with a page that renders the following HTML

    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <p data-bind="text:helloMessage"></p>
    </div>    

This is an HTML `div` that uses Magento's Knockout.js scope binding.  This scope binding binds the view model named `pulsestorm_nofrillslayout_chapter10_viewmodel` to the inner nodes.  The inner nodes render the `helloMessage` property of the view model using Knockout.js's standard `data-bind` attribute.  

There's one problem -- we haven't registered a view model named `pulsestorm_nofrillslayout_chapter10_viewmodel`.  To do that, we'll need to add a bit of `x-magento-init` javascript to the page.  

First, let's change our module to use a custom template.  Open the following layout handle XML file and add these nodes to the bottom

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/layout/pulsestorm_nofrillslayout_chapter10_index.xml
    <?xml version="1.0"?>
    <page xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" 
            layout="1column"> 
        
        <!-- ... leave the existing nodes in place ... -->
    
        <!-- START: add the following new nodes -->
        <referenceBlock name="pulsestorm_nofrillslayout_chapter10_hello">
            <action method="setTemplate">
                <argument name="template" xsi:type="string">Pulsestorm_Nofrillslayout::chapter10/user/main.phtml</argument>
            </action>    
        </referenceBlock>
        <!-- END: add the following new nodes -->
    </page>     

and then create a new template file for our block

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    <h1>Hello Custom Template</h1>
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <p data-bind="text:helloMessage"></p>
    </div>    

Clear your cache and reload the page.  If you see the added title text `Hello Custom Template` then your changes were successful.

## Creating the x-magento-init Script

The new template we created uses the same Knockout.js template code as the default template.  We're going to add an `x-magento-init` script that will register a view model named `pulsestorm_nofrillslayout_chapter10_viewmodel`.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    <h1>Hello Custom Template</h1>
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <p data-bind="text:helloMessage"></p>
    </div>  
    
    <!-- START: add this code -->
    <script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "Magento_Ui/js/lib/core/element/element",
                        "helloMessage":"Hello View Model"
                    }
                }
            }
        }    
    } 
    </script>
    <!-- END:   add this code -->   

There's a lot happening in the above code snippet.  Before we get to it, try reloading your Magento page. You should see the *Hello View Model* text in your page.  Congratulations!  You just created your first scoped Magento Knockout.js view model. 
        
The above code uses the `x-magento-init` mechanism to run the program in the `Magento_Ui/js/core/app` RequireJS module.  The `Magento_Ui/js/core/app` RequireJS module is responsible for registering scoped Knockout.js view models based on the configuration passed in.  Let's look at that configuration, piece by piece. 

    {
        "components": {
            /* ... */
        }
    }

The JSON object we pass to `Magento_Ui/js/core/app` has a single key, named `components`.  Magento's internal name for  *scoped Knockout.js view models* is "components".  Magento also calls other things components, so we're going to stick to calling them *scoped Knockout.js view models*.  

Inside this `components` key is another JSON object

    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
        /* ... */
    }

This is another object of key/value pairs.  The key (`pulsestorm_nofrillslayout_chapter10_viewmodel`) is the name of the view model you want to register.  The value 

    {
        "component": "Magento_Ui/js/lib/core/element/element",
        "helloMessage":"Hello View Model"
    }

is the configuration for the specific RequireJS module that Magento will load and use as a constructor function for your new view model.  The `component` key is the RequireJS module, (`Magento_Ui/js/lib/core/element/element` above).  This RequireJS module is Magento's *base* constructor function for view models.  

Magento will use the other keys at this level  (`helloMessage` above) as data properties for your view model object.  Because we said

    "helloMessage":"Hello View Model"

in our JSON, Knockout.js has access to a `helloMessage` variable in its HTML template

    <p data-bind="text:helloMessage"></p>
    
## An Aside on uiElement Objects
    
The `Magento_Ui/js/lib/core/element/element` module is the full Magento path to a RequireJS module.  However, this module is one that Magento has aliased using the RequireJS `map` feature.  You'll usually see this module referenced as `uiElement`.  In other words, the following configuration

    {
        "component": "uiElement",
        "config":{
            "helloMessage":"Hello View Model"
        }
    }
    
will behave the same as one with a `Magento_Ui/js/lib/core/element/element` identifier.  The `uiElement` objects are actually part of a complex and powerful javascript system for giving constructor functions class-like inheritance.  While we'll get into this system a little bit in this chapter, it's beyond our scope to describe it in full.  What you need to know for now is when Magento instantiates your view model, it does so with pseudo-code that's equivalent to  something like this

    requirejs(['uiElement'], function(UiElement){
        var viewModel = new UiElement;
        viewModel.helloMessage = "Hello View Model";
        return viewModel;
    });
    
Magento uses the function returned by the `uiElement`/`Magento_Ui/js/lib/core/element/element` module as a javascript constructor function.  Magento then assigns each data key in `config` to the object as a value. 

The actual inner workings of this `uiElement` object system are much more complex.  Fortunately, there's articles covering this system online

    Magento 2: Advanced Javascript
    http://alanstorm.com/series/magento2-advanced-javascript/
    
    UI Components
    http://alanstorm.com/series/magento-2-ui/
    
    UiElement Internals
    http://alanstorm.com/series/magento-2-uielement-internals/
    
While you can get by without understanding all the nitty gritty details of this object system, if you're interested in becoming a true Magento 2 master, the above articles are an important step in that journey.       

## Magento and Knockout.js Templates

Knockout.js has a [template data-binding](http://knockoutjs.com/documentation/template-binding.html).  Try editing our `phtml` file so the HTML portion matches the following

    <h1>Hello Custom Template</h1>
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <!-- ko template: "ourKnockoutTemplateId"--><!-- /ko -->
    </div>  

    <script type="text/html" id="ourKnockoutTemplateId">
        <h2>Rendered in a Knockout.js Template</h2>
        <p data-bind="text:helloMessage"></p>
    </script>

    <!-- leave x-magento-init in place -->
    
Reload the page, and you should see the slightly modified HTML output.
        
We've replaced the text inside our `scope` div with a call to the tag-less form of the Knockout.js template binding.  

    <!-- ko template: "ourKnockoutTemplateId"--><!-- /ko -->
    
This tells Knockout to render the template named `ourKnockoutTemplateId`.  We define this template via a `script` tag

    <script type="text/html" id="ourKnockoutTemplateId">
        <h2>Rendered in a Knockout.js Template</h2>
        <p data-bind="text:helloMessage"></p>
    </script>      
    
Notice the `id="ourKnockoutTemplateId"` -- this identifies the template, and is the `id` we we pass to the template binding.  

While neat, the template system suffers from the fact that you need to render the template you want to use as separate HTML DOM nodes (a `<script/>` with a  `type` of `text/html`).  With this default Knockout.js template handling, there's no easy way to create a library of client side templates.  Magento's solved this problem by extending Knockout.js to work with templates loaded via URLs.  This allows you to store your Knockout.js templates in plain `.html` files, and only load in the ones you need via the template binding.  

If that didn't make sense, a sample should make it clear.

Change the HTML section of `main.phtml` so it matches the following

    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <p>
            We're going to try rendering our view model's template.
        </p>
        <div>
            <!-- ko template: 'Pulsestorm_Nofrillslayout/chapter10/remote-template' --><!-- /ko -->
        </div>
    </div>

    <!-- leave x-magento-init in place -->

We've replaced the template identifier in the template binding with `Pulsestorm_Nofrillslayout/chapter10/remote-template`. This identifier is actually a static Knockout.js template URN that corresponds with the file at 

    app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/remote-template.html
    
The first portion of the URN (`Pulsestorm_Nofrillslayout`) indicates the module folder where Magento can find your template.  The second portion (`chapter10/remote-template`) indicates the folder path and file, **from the base** `web/[area]` folder, and appended with the `.html` file extension.  In other words, the following file (which we've included as part of the book's default code)

    view/frontend/web/template/chapter10/remote-template.html
    
Because this folder/file is in our module's `web` folder, this template is also available via a URL something like the following (remember, the `version...` string will be different on your system).

    http://magento.example.com/static/version1514092162/frontend/Pulsestorm/dram/en_US/Pulsestorm_Nofrillslayout/template/chapter10/remote-template.html     

If you reload our page with the edited `main.phtml`, you'll see the contents of `remote-template.html` rendered.  Behind the scenes, Magento translates `Pulsestorm_Nofrillslayout/chapter10/remote-template` into a URL like the one above, loads the `.html` file via AJAX, and then uses the loaded HTML as a Knockout.js template.  

This template has the same functionality of any standard Knockout.js template loaded via a `text/html` `<script/>` tag.  For example, lets create our own template that references the `helloMessage` view variable.

    #File: view/frontend/web/template/chapter10/user/remote-template.html
    <h2>This is a user generated Magento 2 remote Knockout.js template.</h2>
    <p data-bind="text:helloMessage"></p>

and then change our template binding to reference the new template URN

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    <div>
        <!-- ko template: 'Pulsestorm_Nofrillslayout/chapter10/user/remote-template' --><!-- /ko -->
    </div>

If you reload the page with the above in place, you should see the `helloMessage` variable rendered inside the `<p></p>` tags.
    
The `remote-template.html` file uses our view model because we loaded it inside our `data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'"` node.  If we'd loaded it inside another node, it would be bound to that view model.     

While this approach comes with its own set of tradeoffs (less transparency, more individual network requests to load templates) its main advantage is a server agnostic way to organize templates into files, and the ability to reduce the size of your initial page load (i.e. it's not bloated with `<script type="text/html"/>` tags your code may or may not use).
    
## Using uiRegistry to Debug View Models

One thing that can be frustrating with Knockout.js, particularly with Magento's scoped view models, is the inability to examine your view model object.  While tools like Google Chrome's Knockout.js Context Debugger
   https://chrome.google.com/webstore/detail/knockoutjs-context-debugg/oddcpmchholgcjgjdnfjmildmlielhof?hl=en
    
can help, the lack of interactive debugging is a hindrance.

Fortunately, if you're comfortable with your browser's javascript debugger, there is a solution.  When you create a scoped view model with the `Magento_Ui/js/core/app` application, Magento registers and stores each view model via the `uiRegistry` RequireJS module.  You can use the following javascript to peek at any named view model.

    //normally you wouldn't use requirejs directly like this
    //but for debugging purposes it's safe.  
    > var uiRegistry  = requirejs('uiRegistry');
    > var viewModel   = uiRegistry.get('pulsestorm_nofrillslayout_chapter10_viewmodel');    
    > console.log(viewModel);

The above code uses the `uiRegistry`'s `get` method to fetch our named view model.  You can use this to fetch **any** named view model used in a `scope` binding, and then examine the fetched model with your javascript debugger.   
    
## Rendering Multiple Templates/View Models

There may be times where you want to render multiple templates/view models.  For this, Magento created a special `uiCollection` view model constructor.  Let's try a `main.phtml` that looks like the following

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    
    <h1>Hello Collection of  Template</h1>
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <div>
            <!-- ko template: getTemplate() --><!-- /ko -->
        </div>
    </div>

    <!-- START: add this code -->
    <script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "Magento_Ui/js/lib/core/collection"
                    }
                }
            }
        }    
    } 
    </script>
    
Based on what we've learned so far, the above code creates a scoped view model.  The following DOM code lists this view model as the `scope` for its inner nodes.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <div>
            <!-- ko template: getTemplate() --><!-- /ko -->
        </div>
    </div>
    
and calls that view model's `getTemplate` method to render a Knockout.js template.  

If we take a look at the view model configuration

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    
    <!-- START: add this code -->
    <script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "Magento_Ui/js/lib/core/collection"
                    }
                }
            }
        }    
    } 
    </script>      

We see the `pulsestorm_nofrillslayout_chapter10_viewmodel` view model is a `Magento_Ui/js/lib/core/collection`.  

**Note:** This RequireJS module is another one that's aliased.  The `Magento_Ui/js/lib/core/collection` module is also available with the `uiCollection` name.  i.e., the above is equivalent to 

    <script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "uiCollection"
                    }
                }
            }
        }    
    } 
    </script> 

The "collection" here has nothing to do with Magento 2's backend collection models.  Instead, the `uiCollection` module *collects* together child view models.

If you reload the page -- you'll only see the HTML that the server renders.  There's nothing from the Knockout.js template. If we peek at the `pulsestorm_nofrillslayout_chapter10_viewmodel` view model and call its `getTemplate` method

    //from your javascript console
    uiRegistry = requirejs('uiRegistry');
    viewModel  = uiRegistry.get('pulsestorm_nofrillslayout_chapter10_viewmodel');
    console.log(viewModel.getTemplate());

The output should be
    
    ui/collection

This means Magento's rendering the `ui/collection` template URN.  This corresponds to the following stock template file.

    #File: vendor/magento//module-ui/view/base/web/templates/collection.html
    <each args="data: elems, as: 'element'">
        <render if="hasTemplate()"/>
    </each>

So, there's a number of different things going on here.  First -- the reason we didn't see any output for our `uiCollection` model?  We didn't configure any children.  A `uiCollection` view model works because the `collection.html` template will `foreach` over all the child templates and render them. Since we didn't configure any children, there was nothing to render.

Second, even to an experienced Knockout.js developer, the above template looks super weird.  Knockout.js doesn't have an `<each/>` tag or a `<render/>` tag. Those `if` and `args` attributes look weird as well -- Knockout.js uses the `data-bind` attribute for that sort of thing.  What gives?

If turns out that, while these remote `.html` template **are** Knockout.js templates, Magento has **enhanced** these template with an advanced syntax that replaces many of Knockout.js's tag-less bindings with tag versions.  For example, the above template translates into the following "raw" Knockout.js code

    <!-- ko foreach: {data: elems, as: 'element'} -->
        <!-- ko if: hasTemplate() --><!-- ko template: getTemplate() --><!-- /ko --><!-- /ko -->
    <!-- /ko -->    

Covering all these tags is beyond the scope of this chapter, but you can read about them online 

https://alanstorm.com/design-problems-with-magentos-knockoutjs/

## Adding Child View Models

Configuring children is relatively simple -- just add a `children` node to your configuration

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "uiCollection"
                        "children":{
                            /* ... */
                        }
                    }
                }
            }
        }    
    } 
    
And inside that node add a list of key/value pairs, each one configuring a child view model object.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    
    "*": {
        "Magento_Ui/js/core/app": {
            "components": {
                "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                    "component": "Magento_Ui/js/lib/core/collection",
                    "children":{
                        "child1":{
                            "component":"uiElement",
                            "template":"Pulsestorm_Nofrillslayout/chapter10/child-one"                        
                        },
                        "child2":{
                            "component":"uiElement",
                            "template":"Pulsestorm_Nofrillslayout/chapter10/child-two"
                        }                        
                    }
                }
            }
        }
    }     

Each key (`child1`, `child2`) is the name of your child view model, and each object is another scoped view model configuration, the same as your top level configuration.  You'll notice that in addition to setting the `component` property to `uiElement`, we've also set the `template` property 

    "child1":{
        /* ... */,
        "template":"Pulsestorm_Nofrillslayout/chapter10/child-one"                        
    },
    "child2":{
        /* ... */,
        "template":"Pulsestorm_Nofrillslayout/chapter10/child-two"

We've provided the `child-one.html` and `child-two.html` templates in the sample module

    app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/child-one.html
    app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/child-two.html

In a stock system, any **child** of a `uiCollection` view model will  **automatically** have its template rendered.  This is slightly different from the top level view model, which requires an explicit call to Knockout's template binding.

    <!-- ko template: getTemplate() --><!-- /ko -->

A bit lost?  Modify your `main.phtml` template so it matches the following.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    <h1>Hello Collection of  Template</h1>
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <div>
            <!-- ko template: getTemplate() --><!-- /ko -->
        </div>
    </div>

    <!-- START: add this code -->
    <script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "uiCollection",
                        "children":{
                            "child1":{
                                "component":"uiElement",
                                "template":"Pulsestorm_Nofrillslayout/chapter10/child-one"                        
                            },
                            "child2":{
                                "component":"uiElement",
                                "template":"Pulsestorm_Nofrillslayout/chapter10/child-two"
                            }                        
                        }                    
                    }
                }
            }
        }    
    } 
    </script>
    
and reload the page. You'll see each of the `child-one.html` and `child-two.html` templates have rendered.

## Naming Child Elements

When we created our top level element, we gave it a unique-ish name.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml

    "components": {
        "pulsestorm_nofrillslayout_chapter10_viewmodel": {
            "component": "Magento_Ui/js/lib/core/element/element",
            "helloMessage":"Hello View Model"
        }
    }

While not required, by using our module namespace (`pulsestorm_nofrillslayout_`) name as part of the component name, we're (almost) guaranteed that no one else will attempt to create a component with our same name.  This sort of "thrifty namespacing" is necessary when you're creating code that could be deployed into thousands of systems worldwide, or when you're using code from thousands of other developers without everyone being on the same page. 

If you're already on board with that point of view, it may have surprised you to see us use the more generic names of `child1` and `child2`.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml

    "children":{
        "child1":{
            "component":"uiElement",
            "template":"Pulsestorm_Nofrillslayout/chapter10/child-one"                        
        },
        "child2":{
            "component":"uiElement",
            "template":"Pulsestorm_Nofrillslayout/chapter10/child-two"
        }   
    }
    
Normally, you'd want to avoid un-namespaced names like this.  However, a view model's name in Magento is its **full path** in the `x-magento-init`/`Magento_Ui/js/core/app` configuration.  

In other words, the name of the view model with the template of `Pulsestorm_Nofrillslayout/chapter10/child-one` is **not** `child1`.  It's actually `pulsestorm_nofrillslayout_chapter10_viewmodel.child1`, because `child1` is a child of `pulsestorm_nofrillslayout_chapter10_viewmodel`.   

You can test this out for yourself if you're using the `uiRegistry` debugging technique we previously mentioned.  Use the registry's `get` method to grab a view model by name. 

    //normally you wouldn't use requirejs directly like this
    //but for debugging purposes it's safe.  
    var uiRegistry  = requirejs('uiRegistry');
    var viewModel   = uiRegistry.get('pulsestorm_nofrillslayout_chapter10_viewmodel.child1');    
    console.log(viewModel);    
        
## Javascript Objects and Your Own View Models

We now have the the ability to instantiate and register a scoped Magento Knockout.js view model.  We also know how to assign the view model data properties, and how to load a custom template from a `.html` file on the server. What we **don't** have is the ability to use our own custom objects as view models and create our own view model method with complex logic.  In order to do that, we'll need to have a quick talk about object construction in javascript. 

At this point in time, creating new objects in javascript is fraught with a lot of historical baggage.   Creating a basic object in javascript is simple

    var object = {};
    
However, javascript has an alternative syntax for creating objects. This syntax  uses the `new` keyword. 

    var object = new SomeConstructorFunction(); 
    
Where `SomeConstructorFunction` is a javascript function.  This function **definition** does not return a new object.  Instead, when invoked with `new`, the function will assign properties to an object via javascript's magic `this` variable, and javascript will set those new properties on the returned object.

    var SomeConstructorFunction = function(){
        this.foo = "bar";
    }           
    
    var object = new SomeConstructorFunction(); 
    console.log(object.foo);
    
This function is sometimes called a "constructor function" -- although javascript has no internal concept of a constructor function.  Javascript only treats this function as special when you use it with the `new` keyword.     

This form of object creation can be a sore point in certain javascript circles.  Some developers recommend you not use it.  While Knockout.js doesn't **require** you to use constructor functions, their default example

    function AppViewModel() {
        this.firstName = "Bert";
        this.lastName = "Bertington";
    }

    // Activates knockout.js
    ko.applyBindings(new AppViewModel());
    
does use one.  Magento seems to have followed their lead, and built their entire front end object system around constructor functions.  

Remember, when you tell Magento your *component* is a `Magento_Ui/js/lib/core/element/element` (or its alias, `uiElement`)

    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
        "component": "Magento_Ui/js/lib/core/element/element",
        "helloMessage":"Hello View Model"
    }
    
What you're really saying to Magento is

> Hey Magento?  Load the module at `Magento_Ui/js/lib/core/element/element`.  This module should return a function.  Then, create a new object with this function using the `new` keyword.  

Or, in code

    ConstructorFunction = requirejs('Magento_Ui/js/lib/core/element/element');   
    viewModel = new ConstructorFunction({
        "component": "Magento_Ui/js/lib/core/element/element",
        "helloMessage":"Hello View Model"
    });
    
If you didn't follow everything we said about javascript constructors, don't worry.  You don't need to understand these sort of things once you've learned the boilerplate.  If you want a career in software engineering, these fundamentals are good to know.  If you're just looking to get your job done, read on!

## Creating Our Own View Models

From a high level, what we need to do is 

1. Create a new RequireJS module that returns a `uiElement` constructor function
2. Add our view variables to the object created by that constructor function
3. Configure our `x-magento-init` script to use this new component/view-model.  

We'll give our new RequireJS module the name of `Pulsestorm_Nofrillslayout/chapter10/user/view-model`.  With this name, we'll need to create the following file in the following location.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/chapter10/user/view-model.js
    define(['uiElement'], function(UiElement){
        return UiElement;
    });    

Then, we need to configure our `x-magento-init` script to **use** this new RequireJS module as our `x-magento-init` script.  Replace our `main.phtml` with the following

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    <h1>Hello View Model</h1>
    <div data-bind="scope: 'pulsestorm_nofrillslayout_chapter10_viewmodel'">
        <div>
            <!-- ko template: getTemplate() --><!-- /ko -->
        </div>
    </div>

    <!-- START: add this code -->
    <script type="text/x-magento-init">
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "Pulsestorm_Nofrillslayout/chapter10/user/view-model",
                        "template": "Pulsestorm_Nofrillslayout/chapter10/hello-view-model"
                    }
                }
            }
        }    
    } 
    </script>
    
If you reload with the above in place, you should see the `Pulsestorm_Nofrillslayout/chapter10/user/view-model` view model rendered with the (provided by us) `Pulsestorm_Nofrillslayout/chapter10/hello-view-model` template.  

    Hello View Template

    Value of helloWorld variable [].
            
## What Just Happened

Let's start by throwing things into reverse.  If we look at the `x-magento-init` portion of our code, our one big change comes here.  

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    "components": {
        "pulsestorm_nofrillslayout_chapter10_viewmodel": {
            "component": "Pulsestorm_Nofrillslayout/chapter10/user/view-model",
            "template": "Pulsestorm_Nofrillslayout/chapter10/hello-view-model"
        }        
    }
    
Previously, this would have looked like the following     

    "components": {
        "pulsestorm_nofrillslayout_chapter10_viewmodel": {
            "component": "uiElement",
            "template": "Pulsestorm_Nofrillslayout/chapter10/hello-view-model"
        }        
    }
    
We've changed the `component` from a `uiElement` to a `Pulsestorm_Nofrillslayout/chapter10/user/view-model` module.  This tells Magento to instantiate the view model named `pulsestorm_nofrillslayout_chapter10_viewmodel` from the constructor function returned by `Pulsestorm_Nofrillslayout/chapter10/user/view-model` **instead of** from the constructor function returned by `uiElement`/`Magento_Ui/js/lib/core/element/element`. 

If we take a look at the constructor function that `Pulsestorm_Nofrillslayout/chapter10/user/view-model` returns

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/user/view-model.js
    define(['uiElement'], function(UiElement){
        return UiElement;
    });   
    
We see that all we've done is import the `uiElement` module via RequireJS, and then have our module return that module.  i.e. --  we've created a view model that behaves exactly the same as one with a `uiElement` configured component.  If you want a view model with **new** behavior, you'll need to use the `UIElement`'s `extend` method (which we'll cover momentarily).  Also, you probably noticed that this template line

    Value of <code>helloWorld</code> variable [<span data-bind="text:helloWorld"></span>].

rendered without any `helloWorld` value.  Let's kill two birds with one stone by updating our RequireJS module to look like this

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/user/view-model.js
    define(['uiElement'], function(UiElement){
        return UiElement.extend({
            defaults:{
                helloWorld:"Hello World"
            }
        });
    });       
    
If you reload with the above `view-model.js` in place, you should see our view rendered with a value for the `helloWorld` variable.

    Value of helloWorld variable [Hello World!].

## Understanding the extend Method
     
The key to understanding why the above code worked is this block here.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/user/view-model.js
    
    return UiElement.extend({
        defaults:{
            helloWorld:"Hello World"
        }
    });    
    
Previously, we were returning the `UiElement` variable.  This variable contains a function.  Specifically, a constructor function that the system will use to instantiate the `pulsestorm_nofrillslayout_chapter10_viewmodel` view model.  

In javascript, functions are also objects.  This means they can have methods.  Above, we've called the `extend` method on the `UiElement` constructor function.  This is a special, Magento provided method that allows you to create **new** constructor functions.  The object you pass to `extend` allows you to add default properties and methods to your new objects.  

If that didn't make sense, lets take a look by using our browser's javascript console.  First, let's fetch the `uiElement` constructor function, and then look at its source code.  This

    > UiElement = requirejs('uiElement');
    > console.log( UiElement.toString() );

should log something like this
    
    function () 
    {
        var obj = this;

        if (!_.isObject(obj) || Object.getPrototypeOf(obj) !== UiClass.prototype) {
            obj = Object.create(UiClass.prototype);
        }

        obj.initialize.apply(obj, arguments);

        return obj;
    }    
    
When you (or more importantly when Magento or Knockout.js code) uses code like `new UiElement`, **this** is the constructor function that is called.  Covering this object in full is beyond the scope of this chapter, but the previously mentioned uiElement series covers it well. 

http://alanstorm.com/series/magento-2-uielement-internals/

Next, lets use the extend function to create a **new** constructor function. Run the following code from your javascript console.

    > var OurConstructorFunction = UiElement.extend({
        defaults:{
            message:'Hello World!',
        },
        sayHello:function(){
            console.log(this.message);
        }
    });     
    
Above we've passed `extend` an object with a `defaults` key, and a `sayHello` key.  Values in `defaults` will be assigned as *default values to newly constructed objects*, and the non-`defaults` keys will be assigned as methods to the new object.   

In other words, if we use `OurConstructorFunction` to instantiate an object    

    > var object = new OurConstructorFunction;
    
then that object will have a `helloWorld` property

    > console.log(object.message);
    "Hello World"
    
and a `sayHello` method
    
    > object.sayHello();        
    "Hello World"
    
If we return to our RequireJS module

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/user/view-model.js
    define(['uiElement'], function(UiElement){
        return UiElement.extend({
            defaults:{
                helloWorld:"Hello World"
            }
        });
    });     
    
We can see our new view model constructor function 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/user/view-model.js
    define(['uiElement'], function(UiElement){
        return UiElement.extend({
            defaults:{
                helloWorld:"Hello World"
            }
        });
    }); 
    
This view model constructor function will ensure a default value for the `helloWorld` property in our view model.  If we wanted to add a method to our view model we could do the following to add a `getLines` method       

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/web/template/chapter10/user/view-model.js
    define(['uiElement'], function(UiElement){
        return UiElement.extend({
            defaults:{
                helloWorld:"Hello World"
            },
            getLines:function(){
                return ['You say yes', 'I say no', 'You say stop', 'I say go go go'];
            }
        });
    }); 
    
and then we could invoke that method with template code similar to the following

    <ul data-bind="foreach:getLines()">
        <li data-bind="text:$data"></li>
    </ul>    
    
We call `getLines` in the `foreach` binding, and then each time through the loop use the `data-bind="text:$data"` to output the value.  The `$data` variable is a Knockout.js feature/convention, and contains the current value of the loop (similar to `$_` in perl). 

## Non-Default Values

Earlier, we covered the `defaults` property, which lets you set default values for your view models.

    defaults:{
        helloWorld:"Hello World"
    },

With the above configuration, **by default** an instantiated view model will have a `helloWorld` property with a value of `"Hello World"`.  However, it is possible to change these default values.  If we update our `x-magento-init` script to include a new value for `helloWorld`

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter10/user/main.phtml
    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "Pulsestorm_Nofrillslayout/chapter10/user/view-model",
                        "template": "Pulsestorm_Nofrillslayout/chapter10/hello-view-model",
                        "helloWorld":"Hola Muendo"                           
                    }
                }
            }
        }    
    } 

Magento will use this value instead.  With the above `x-magento-init`, our template will render with the `Hola Muendo` value instead of hello world.  This works because, behind the scenes, Magento's instantiating this object with javascript code that looks something like

    var viewModel = new UiElement({
        "component": "Pulsestorm_Nofrillslayout/chapter10/user/view-model",
        "template": "Pulsestorm_Nofrillslayout/chapter10/user/hello-view-model",
        "helloWorld":"Hola Muendo"    
    });
    
There's also a second form of this that you'll see in Magento's core code, a top level `config` key    

    {
        "*": {
            "Magento_Ui/js/core/app": {
                "components": {
                    "pulsestorm_nofrillslayout_chapter10_viewmodel": {
                        "component": "Pulsestorm_Nofrillslayout/chapter10/user/view-model",
                        "template": "Pulsestorm_Nofrillslayout/chapter10/user/hello-view-model",
                        "helloWorld":"Hola Muendo",
                        "config":{
                            "helloWorld":"Bonjour le monde"
                        }
                    }
                }
            }
        }    
    }  
    
With the above code, the `helloWorld` value in the `config` object will override the `helloWorld` value set at the top level.  If you're thinking this doesn't make a lot of sense -- you're right.  The reasons for this are complicated and myriad, and likely the result of one engineer responding to another engineer's choices.  If you're writing `x-magento-init` blocks from scratch, we'd recommend staying away from this `config` syntax.  However, you'll need to be aware of it if you're trying to debug how core cart functionality works in Magento 2. 

## Safely Using the uiRegistry

Earlier in this chapter, we used the `uiRegistry` to fetch previously instantiated view models.  

    var uiRegistry  = requirejs('uiRegistry');
    var viewModel   = uiRegistry.get('pulsestorm_nofrillslayout_chapter10_viewmodel');    
    console.log(viewModel);
    
While this worked as a debugging exercise, relying on the `uiRegistry`'s `get` method in production code isn't a great idea.  Magento's system makes no promises about the order it will instantiate view models in.  When you combine that with the asynchronous nature of RequireJS module loading, that means there's no way to know for sure when a particular view model will be available.  

Put another way, just because 

    uiRegistry.get('pulsestorm_nofrillslayout_chapter10_viewmodel'); 
    
works in your dev enviornment doesn't mean it will work in a production enviornment.  Fortunately, the `uiRegistry` object has a solution -- the `get` method also supports a callback syntax.  For example, consider the following program

    requirejs(['uiRegistry'], function(uiRegistry){
        uiRegistry.get('someUniqueKey', function(theRegistryValue){
             console.log("Called the async callback");
             console.log(theRegistryValue);
        });
    });

Here we're attempting to fetch the `uiRegistry`'s `someUniqueKey` value using `get`, with a javascript function as the second argument to get.  Running this program results in **no** output to the console.  That's because there's no value set for `someUniqueKey`.  However, if (in the same console window) we run the following program that **sets** a value

    requirejs(['uiRegistry'], function(uiRegistry){
        uiRegistry.set('someUniqueKey', 'The Value');
        console.log("Finished");
    });  
    
we should see the following in our console.

    Finished
    Called the async callback
    The Value     
    
That is -- Magento invoked the callback function after we set a value.  The callback may not be immediately invoked (i.e. our *Finished* message shows up first), but by using `get` with a callback, we can be certain our code will run **only** when there's a value available in the registry.       

## Wrap Up

Pretty intense, right?  After carefully leading you through nine chapters on layout XML, we feel a little guilty dropping a gigantic javascript anvil on your head.  This chapter only touches the surface of how Magento's new, modern javascript features work.  When you're ready to continue, we strongly recommend taking a look at the three, free, online series on Magento 2's javascript functionality that we mentioned earlier

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

While newer greenfield front end technologies (PWA, vue-storefront, etc.) are making their way to the Magento platform, these Knockout.js based UI Components are what ships with stock Magento.  Understanding them will be just as important as understanding Magento's layout based XML systems for anyone with more than a fleeting interest in Magento development.