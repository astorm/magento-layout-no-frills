## Viewing HTML Source
    
There will be a number of times in this book where we ask you to view the rendered HTML source of your page.  For long time web developers, this is a simple, obvious task.  However, I've noticed that there's a certain class of young javascript developers who are **only** aware of HTML tags as they relate to their UI abstraction (React, Knockout.js, etc.) of choice.  

Given Magento 2 is still a PHP MVC/MVVM framework that relies on server rendered HTML, a quick primer on the differences between a page's raw source and rendered source seems appropriate. 

### HTTP and the Web Browser

Whenever a user requests a URL from a server (i.e. visits a web page), the web browser connects your computer to server, and gets back an HTML page. In modern web applications, code on the web page itself may make additional requests via ajax (also known as "XHR") for more HTML chunks, or JSON and XML data objects, but that initial visit that returns an HTML page is **always** required.

Most computer based (i.e. not phone or tablets) web browsers still have a "View Source" menu that will let you view the raw source of that initial HTTP request.

For example, in Google Chrome, if you navigate to 

    View -> Developer -> View Source
    
you'll see the HTML file returned by the server.
    
We say "raw source" because most modern browsers also have a way to inspect the *rendered source* of a page.  After a web browser downloads a web page, it will run any javascript programs included on the page.  These javascript programs can change the HTML elements included in a page.  Additionally, these javascript programs can setup event handlers that respond to certain user actions (clicks, hovers, scrolls, taps, etc.), which can further change the initial raw source.

In Google Chrome, if you browse to 
    
    View -> Developer -> Developer Tools
    
and click on the `Elements` tab you're looking at the **rendered** source of a page -- i.e. **after** any javascript has been run. 

### Other Tools for Viewing a Page Source

If you're familiar with the unix command line (and if you're not, you will be after doing Magento development for a bit), there's another way you can view the HTML source of a document.

The command line program `curl`, which ships with most \*nix computers these days (MacOS included), is a program that can fetch the source of *any* URL.  For example, to fetch the raw source of the Google homepage, just type the following command in your unix terminal.

    $ curl 'http://www.google.com'
    <!doctype html><html itemscope="" itemtype="http://schema.o... 
    
If you want to send the source to a file, just use a unix `>` (redirect) character       

    $ curl 'http://www.google.com' > file.html
    
Even the `View Source` menu of a web browser will sometimes add formatting to the HTML document.  The `curl` program can show you **exactly** what's being returned from a web server. This can be a bit fiddly sometimes. For example, if you try to fetch `google.com` (and not `www.google.com`), you'll end up with a result that looks like this

    $ curl 'http://google.com'
    <HTML><HEAD><meta http-equiv="content-type" content="text/html;charset=utf-8">
    <TITLE>301 Moved</TITLE></HEAD><BODY>
    <H1>301 Moved</H1>
    The document has moved
    <A HREF="http://www.google.com/">here</A>.
    </BODY></HTML>

That's because `google.com` redirects to `www.google.com`, and `curl` shows you **exactly** what the server returns (you can work around this specific problem by using the `-L` option).  

A `curl` request may also send different HTTP headers than a browser, which may make certain web servers act differently.  Despite this fiddly business, or maybe **because** of it, `curl` is a tool that should be in every web developer's tool-belt.  

You may start using `curl` to quickly look at a browser's page source, but after a while, you'll start learning the fundamentals of the HTTP protocol, which is the protocol that underlies the entire world wide web. That's a smart thing to do if you're going to make web development your long term home. 