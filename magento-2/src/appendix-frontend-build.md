## Front End Build System
	
Although we won't cover it in great detail, Magento uses a "modern" front end build system that's based on NodeJS and the `grunt` build tool.  The build system is modern in the sense that it exists (vs. working with plain, non-preprocessed CSS files).  However, some bleeding edge front end developers might find both `grunt` and LessCSS beneath them.  Regardless of your opinion, these are the systems stock Magento uses, so you'll need to be aware of them. 

To use these front end build tools, you'll need to install NodeJS onto your computer. The node onboarding process is pretty straight forward these days -- just hop over to 

https://nodejs.org
    
for more information.  

You can check if node is installed via the command line and node's `-v` version flag.

    $ node -v
    v6.4.0

NodeJS also comes with a command called `npm`. This originally stood for *Node Package Manager*, but these days `npm` can install all sorts of javascript projects to your computer.  The `npm` command is similar to `composer.phar` in that it manages code packages for you.  However, `npm` handles *javascript* packages.  

The `npm` command, like `node`, has a version flag checker

    $ npm -v
    3.10.6

Once you have NodeJS and `npm` installed, you'll use these tools to install the grunt cli tool globally.  Globally means you'll be able to access grunt no matter which directory your command line is currently at.  A local (non-global) install means you're installing something into a specific node/npm based project.

To install grunt globally, type the following 

    $ npm install -g grunt-cli

Once this command finishes running, you should have a `grunt` command available.

    $ grunt -V
    grunt-cli v1.2.0


### Installing the Local Magento Build Tools

So, you've now got `node`, `npm`, and `grunt` running locally.  The next step is to install the `npm` based project Magento ships withs.  In the Magento root folder, you'll find a file named `package.json.sample` -- copy this file to the name `package.json`

    $ cd /path/to/magento
    $ cp package.json.sample package.json
    
The `package.json` file is `npm`'s version of `composer.json`.  It contains a list of javascript packages to install.  While Composer will install things to the `vendor` folder, `npm` will install its packages to the `node_modules` folder.  

How do you install a node based project?  Just run

    $ npm install
    
Once the command finishes, you'll have a `node_modules` folder.  At this point, we'll be able to run grunt locally from our project folder.  However, when we do this, we'll get an error

    $ grunt 
    A valid Gruntfile could not be found. Please see the getting started 
    guide for more information on how to configure grunt:
        http://gruntjs.com/getting-started
        
    Fatal error: Unable to find Gruntfile.
          
Even though `grunt` has the javascript packages installed locally, `grunt` also needs a configuration file.  Fortunately, Magento ships with that configuration file -- named `Gruntfile.js.sample`.  Just copy this file to the name `Gruntfile.js`

    $ cp Gruntfile.js.sample Gruntfile.js
    
and you'll be able to run `grunt`.  Use the `--help` flag to get a list of the tasks available

    $ grunt --help   
    
For the purposes of this book, the command we'll be running most often is 

    $ grunt clean
    
This will clear out any generated front end files, as well as the cached/preprocessed LessCSS views.