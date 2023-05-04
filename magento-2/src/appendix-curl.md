Downloading URLs with curl
--------------------------------------------------	
Every web page on the internet has a URL

	http://example.com/hello.html

In modern times, URLs often resolve to data documents like XML or JSON files.

	http://magento.example.com/foo.json
	http://magento.example.com/foo.xml

Some of these may be actual documents on a server.  Others may be generated on the fly by a software application (like Magento).  One of the beauties of the internet is web browsers don't care how a document gets made -- as long as the server speaks the *HyperText Transfer Protocol* (or HTTP), web browsers are happy to grab the document or data from the server.

As a human being, you use a program like Internet Explorer, Chrome, Firefox, Opera, Netscape, etc. to browse the web.  These programs fetch the documents for you.  

As a programmer, it's often useful to make an HTTP request **without** a web browser.  Browsers have many layers of caching that happen automatically, some of which are hard to turn off.  Web browsers also try to render documents as HTML by default, and sometimes you want to see the *raw data* returned by the server. Other times, you may be writing a software application that isn't a web browser, but still needs to use pages and data on the web.  Regardless of your motivations, there are many libraries and programs for doing this. One of the most popular and longest standing is a program and library called `curl`.

The `curl` command line program is available for all popular operating systems, and many unpopular ones.  It's based on a C library named `libcurl` that programmers embed in their own C based software applications.  Chances are you use something that uses `libcurl` everyday.

This book will occasionally use the `curl` command line program to fetch a URL.  This appendix is a brief and incomplete tutorial on to use `curl`.  

### Fetching URLs with CURL

In its simplest form, the `curl` command accepts a single argument.  This argument is a URL.  When you pass curl a single url, it will use the HTTP protocol to fetch a response from that URL

    $ curl https://alanstorm.com/
    <!DOCTYPE html>
    <!--[if IE 7]>
    <html class="ie ie7" lang="en-US">
    <![endif]-->
    <!--[if IE 8]>
    <html class="ie ie8" lang="en-US">
    //...

    $ curl https://api.twitter.com/1/statuses/alanstorm.json
    {
        "errors": [{
            "message": "The Twitter REST API v1 is no longer active. Please migrate to API v1.1. https://dev.twitter.com/docs/api/1.1/overview.",
            "code": 64
        }]
    }
   
The results of a `curl` call are sent to the terminal's STDOUT stream.  i.e. they're shown as output to the terminal.  If you want to save these results to a file, you can use standard Unix redirect tools

    $ curl https://alanstorm.com/ > file.html
    $ curl https://api.twitter.com/1/statuses/alanstorm.json > file.json
    
You can also tell `curl` to save a file using the servers file name with the `O` option.

    $ curl -O https://api.twitter.com/1/statuses/alanstorm.json
    $ cat alanstorm.json
   
### Viewing HTTP Headers with CURL

Sometimes you may be surprised to find `curl` returns an empty or unexpected response for a URL that works in the browser

    $ curl http://alanstorm.com
    <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
    <html><head>
    <title>302 Found</title>
    </head><body>
    <h1>Found</h1>
    <p>The document has moved <a href="https://alanstorm.com/">here</a>.</p>

One reason for this **may** be that the server sent an HTTP Location redirect.  You can test for this by looking at the HTTP headers for a response with the `-I` option.

    $ curl -I http://alanstorm.com
    HTTP/1.1 302 Found
    Date: Thu, 29 Mar 2018 00:42:28 GMT
    Server: Apache/2.4.29
    Location: https://alanstorm.com/
    Content-Type: text/html; charset=iso-8859-1

Ah ha!  We did get a `Location:` header redirect.  This is an example of curl being very simple and literal.  It made the HTTP request, and showed you the response.  There are times where this is exactly the behavior you want.  However, sometimes you just want curl to follow the redirects automatically.  In those cases, use the `L` option

	$ curl -L http://alanstorm.com
	//...

Another important header trick: Use the `curl -i` option (lowercase) to fetch both the headers AND the response.

The `curl` command has almost 200 options at its disposal.  With these options, you'll be able to recreate almost any web request you need.  We're not going to cover them all, but the internet is filled with all sorts of `curl` examples. Whether you're looking to set custom request headers, want to read and write  cookies, or plumbing the depths of what HTTP can do, `curl` can probably do it for you. 
