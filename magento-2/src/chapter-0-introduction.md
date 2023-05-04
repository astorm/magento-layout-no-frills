# Introduction

The kindest thing I can say about writing this book is that it's been a challenge. The best way to introduce this book is to explain why it's been so challenging. 

Writing the first edition of No Frills Magento Layout was no picnic.  Even in 2011, the level of knowledge around Magento's HTML-generating domain-specific-language was scant.  However, in the world of web development, there **was** a consensus on how we should be building web applications.

1. HTML delivered to the browser, possibly generated with server-side languages like PHP, ruby, python, java, etc.
2. CSS for layout and styles, with an occasional image or background-image if you wanted to get fancy
3. Javascript to add interactive elements to the page
4. AJAX requests if those interactive elements needed additional server resources/information

Even in 2018, these four principles remain a solid and proven way to build software that's delivered to users via a web browser.

Unfortunately, in the years since Magento's release and my writing the first edition of this book, **a lot** has changed in the world of web development.  

The biggest change has been the rise of mobile and the stagnation of the mobile web.  Chrome and Safari on Android and iOS based phones are wonders of software engineering, but the web browser remains a second class citizen in the battery constrained world of mobile devices.  Where the aughties culminated with web-browsers recognized as the superior method of delivering cross platform software, the mobile computing world took a few steps backwards into device specific software and locked-in file formats. In this enviornment "the web" became a transport layer for data, which in turn led to the server side world becoming a specialized, UI-less island. 

CSS has also seen its fair share of changes.  Again, the aughties saw a wealth of clever CSS design and development techniques arise, often on a grassroots level. These techniques allowed web developers to tame the complicated  world of cascading style sheets.  In 2018, these techniques still exist, but they're locked into specific frameworks (Bootstrap) or preprocessing programs (LessCSS, Sass).  When you consider a mobile web that has given up on a stable "CSS API" it's clear that writing your style sheets without one of these tools is a dicey proposition.  By itself this might not be a problem, but these tools are often developed inside of agency culture, a culture that's more interested in short term results than they are in long term sustainability.  This makes for complicated toolchains that change dramatically year-to-year.

All this bleeds over to the world of Javascript, where it's both the best of times and worst of times.  The ubiquity of javascript in the browser has brought a **tremendous** amount of engineering resources to bear on the language, runtimes, and third party libraries.  There's now a school of thought that says the **only** HTML you need to render on the server is the HTML that bootstraps your javascript runtime.  However, despite (or because of?) all this attention, javascript based applications tend to be built-on hundreds of thinly sliced libraries with no dominant paradigm from application to application. 

The PHP landscape has also seen its fair share of changes -- the most important of which is Composer.  While Composer modestly bills itself as a dependency manager, in reality it's taken off (and over) as PHP's de-facto package manager, autoloader bootstrap enviornment, and distribution channel for PHP code. 

Another change has been PHP 7.0, 7.1, and 7.2's making a Zeno like march towards explicitly typed language semantics.  PHP 7 lets developers familiar with java like semantics ply their wares like never before, and the tension between done-quick vs. done-correct has never been higher within the PHP community.

While some would chalk all this up as progress, it's balkanized the developer community.  *The Right* way to develop a web application is much less clear. There's a tension as to how much attention we should pay to the web application vs. just building an API for mobile support.  

Magento 2 jumped into a web development world that's dramatically changed, and it's not clear which direction (if any) Magento core is leaning with regards to to the chaos.

## Magento 2

With Magento 2, Magento Inc.'s committed hard to PHP's trends.  Composer's fully embraced, Magento 2's PHP semantics favor multi-level class based abstractions, and the XML configuration files have multiplied like guinea pigs.  While all this does confer the Magento system with certain enterprise  market advantages, it's also created a system where getting started is a tad more complicated than dropping your PHP files into a `public_html` folder.

Magento's Layout XML files are a *domain specific language* that use PHP classes and templates to render HTML.  In this way, Magento 2 is a traditional server side framework, similar to the version developed in the aughties.  

However: Magento 2 has **also** revamped its API. Formerly a tool for data transport, Magento 2's new API puts REST services front and center, including browser based sessions and permissions.  It's the sort of API you can call directly from javascript. 

To support building javascript applications using this API, Magento 2 also features a RequireJS module system, a significant extension of Knockout.js for AJAX based template rendering, and a **new** domain specific language that enables pure javascript based UIs with **no server side rendered HTML**.  In this way, Magento seems to be forward thinking.

However however: While Magento 2 has these newer systems in place, the Magento 2  application remains a *mix* of pure javascript UI, and the older server side rendered HTML *enhanced* by javascript. 

Finally, in the realm of CSS, the Magento application ships with a heavily integrated LessCSS system and a grunt build enviornment.  There's been community led efforts to enable both Sass and gulp for Magento 2 development workflows, but the core system itself has LessCSS as a hard dependency. This means developers targeting **all** Magento systems face some hard choices with regards to to their CSS build pipelines. 

## What We'll Cover

All of which is a preamble to saying: This book is not a top to bottom course in Magento's front end systems.  Also, while you can get something out of this book if you're coming in fresh to Magento, you'll be best served if you already have an inkling of where Magento 2 came from. To help with this we've included a copy of the original No Frills Magento Layout with your purchase.

We'll cover using Magento's *layout handle XML files* to render HTML content at a module level.  We'll also cover what *themes* are in Magento 2, how their handling of layout handle XML files has changed, and how you can use LessCSS via themes to style Magento pages.  Finally, we'll take a brief survey of how Magento uses new front end technologies like RequireJS, Knockout.js, and LessCSS and show you how to get your front end files loaded into Magento's application context.

## Conventions

There's a few last bits of intro business before we can get on with the book.

When you downloaded your copy of this book you also received a `Pulsestorm_Nofrillslayout` Magento module.  You'll want to install this single module into your system, as many of the code samples included in this book start with this module as their base.  If you're unsure how to install a stand alone module in Magento 2, read the "Installing the `Pulsestorm_Nofrillslayout` Module" appendix.

Once installed, you'll want to flip your system into `developer` mode.  The simplest way to do this is to run the following command

    $ php bin/magento deploy:mode:set developer
    
If you're not familiar with Magento's various modes, or not familiar with the command line, we have an appendix that covers each of those as well.  

The appendixes are quick primers on topics that aren't *quite* related to Magento's layout system, but are still required knowledge for working with the system.  We've broken them off into appendixes to avoid breaking flow in our tutorials. 

Alright, let's get to it!
