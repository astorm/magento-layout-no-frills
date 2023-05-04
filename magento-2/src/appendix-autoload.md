## PHP Autoloading
	
Autoloading is the PHP system that ensures when a developer attempts to instantiate an object from a class, that the correct class definition file is loaded.  

All programming languages have some sort of mechanism for this -- what makes PHP distinct is the autoloading system is end-user-programmer configurable.  That is to say, anyone writing PHP code can register an autoloader that might run when another programmer attempts to instantiate a class.

Taking the long historic view, this has led to autoloaders from early PHP frameworks not playing nice with each other when used in the same program.  An autoloader from framework A might fail when a programmer attempts to load a class from framework B.  

In recent years, the PHP community has standardized its autoloading mechanism using the PSR-0 and PSR-4 standards. These standards were adopted by Composer, which means most modern Composer distributed frameworks (Magento 2 included) use the PSR based Composer autoloaders.  

However, as a working PHP programmer, it's still necessary to understand the mechanics of autoloading.  Older PHP frameworks still use older, non PSR based autoloaders in their Composer packages.  Additionally -- Composer's autoloader API offers an irresistible temptation for meta-programmers who want to hook into class instantiation in PHP based systems.

This appendix will describe Magento's PSR-4 based autoloaders. It will also describe a few ways in which Magento uses the autoloader system to implement functionality unrelated to class autoloading. 

### PSR-4 Autoloading

A PSR-4 autoloader allows a user to configure a class prefix with a path name.  When PHP needs to instantiate classes with this prefix, the autoloader will look for class files in the configured folder.  The autoloader uses the non prefixed portion of the class to build the path's full class.

Here's an example.  A user might configure the class prefix `Foo\Bar` with the path `path/to/src`.  Then, if someone tried to instantiate a class like this
 
     $object = new \Foo\Baz\Bar\Bap;
     
The PSR autoloader would look for the class's definition file in 

    path/to/class/Bar/Bap.php
    
The autoloaders would not try to load a class named `Baz\Bar\Foo`, because this class does not start with the configured prefix `Foo\Bar`.   

A PSR-4 autoloader creates the path to the class file by starting with the configured path name, and transforming the non-prefix part of the class name (`Bar\Bap`).  This transformation includes turning the namespace separator characters into file path separators, and appending a `.php` file extension.
 
There's some additional subtleties and rules to PSR-4 autoloading.  If you're interested in learning more you should checkout the PSR-4 specification

http://www.php-fig.org/psr/psr-4/
 
###  PSR-4 Autoloading in Composer and Magento

Here's how PSR-4 autoloading works in Magento 2 with Composer.  In most cases, a Magento 2 module will configure a PSR-4 autoloader in its composer.json file.   For example, consider the `Magento_Catalog` module

    #File: vendor/magento/module-catalog/composer.json 

    "autoload": {
        /* ... */,
        "psr-4": {
            "Magento\\Catalog\\": ""
        }
    }
   
As you can see above, Magento's configured a PSR-4 autoloader with a class prefix of `Magento\Catalog`, and an empty path.  This is a standard Composer PSR-4 autoloader configuration.  The empty path setting means *from this Composer package's root folder*. In other words, Magento 2 will load the `Magento\Catalog\Model\Product` class from 
   
    vendor/magento/module-catalog/Model/Product.php
    
This is the pattern Magento follows for all its core modules.  Third party developers are not required to follow this pattern, but it's strongly recommended they do so. 

### Autoloader Module Registration

Earlier we mentioned some frameworks, Magento included, use the autoloader and autoloader related systems to implement other functionality.  

Magento needs a way to know which modules are available in a system.  Rather than keep a list of modules somewhere in application state, or scan common module directories on every system bootstrap, Magento uses registration.php files.  These files are located in the root of each Magento module folder (as well as theme, library, and language pack folders)

Rather than perform a search for these files on every bootstrap, Magento taps into the power of Composer's `files` autoload feature.  Consider, again, the `Magento_Catalog` module.

    #File: vendor/magento/module-catalog/composer.json 

    "autoload": {
        "files": [
            "registration.php"
        ],
        /* ... */
    }

The original intent of the `files` autoloader is that developers would use a file autoload to manually setup PHP autoloading for the framework with `__autoload` or `spl_autoload_register`, or that they would simply list the class files that should be automatically loaded here.  

However, because PHP loads a file configured in the `files` autoloader on every PHP request, this is a tempting place for framework authors to put other initializations and registration scripts.  Magento could not resist this temptation themselves -- if you look inside a registration.php file

    #File: vendor/magento/module-catalog/registration.php 
    <?php
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'Magento_Catalog',
        __DIR__
    );

You'll see the code a module developer writes to let Magento know their module's name, and that the module is available for the system to use.
 
### Code Generation Autoloader

The other meta-programming autoloader Magento uses is their code generation autoloader.  Magento 2's programming style requires many classes, and many of those classes have boilerplate code that's the same thing over and over again.  Rather than force Magento developers to manually create these classes, Magento will automatically create them for us whenever it sees them mentioned.  For example, if you mention a Factory class in a constructor

    public function __construct(SomeObjectFactory $factory)
    {
        /* ... */
    }

**and** that constructor class does not exist already, Magento will automatically create a class with a base factory implementation for you in either the `var/generation/` or (in Magento 2.2+) `generation/` folders

The "whenever it sees them" line probably piqued your engineering brain.  In order to implement this functionality, Magento registers a **custom** autoloader named `Magento\Framework\Code\Generator\Autoloader::load`.  Its implementation is beyond the scope of this appendix, but Magento registers this autoloader here

    #File: vendor/magento/framework/ObjectManager/DefinitionFactory.php
    public function createClassDefinition()
    {
        $autoloader = new Autoloader($this->getCodeGenerator());
        spl_autoload_register([$autoloader, 'load']);
        return new Runtime();
    }

and you can start following the autoloader's execution here

    #File: vendor/magento/framework/Code/Generator/Autoloader.php
    public function load($className)
    {
        if (!class_exists($className)) {
            return Generator::GENERATION_ERROR != $this->_generator->generateClass($className);
        }
        return true;
    }
	
There's a number of different file types Magento will generate for you with this autoloader

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <argument name="generatedEntities" xsi:type="array">
        <item name="factory" xsi:type="string">\Magento\Framework\ObjectManager\Code\Generator\Factory</item>
        <item name="proxy" xsi:type="string">\Magento\Framework\ObjectManager\Code\Generator\Proxy</item>
        <item name="interceptor" xsi:type="string">\Magento\Framework\Interception\Code\Generator\Interceptor</item>
        <item name="logger" xsi:type="string">\Magento\Framework\ObjectManager\Profiler\Code\Generator\Logger</item>
        <item name="mapper" xsi:type="string">\Magento\Framework\Api\Code\Generator\Mapper</item>
        <item name="persistor" xsi:type="string">\Magento\Framework\ObjectManager\Code\Generator\Persistor</item>
        <item name="repository" xsi:type="string">\Magento\Framework\ObjectManager\Code\Generator\Repository</item>
        <item name="convertor" xsi:type="string">\Magento\Framework\ObjectManager\Code\Generator\Converter</item>
        <item name="searchResults" xsi:type="string">\Magento\Framework\Api\Code\Generator\SearchResults</item>
        <item name="extensionInterface" xsi:type="string">\Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator</item>
        <item name="extension" xsi:type="string">\Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator</item>
    </argument>	
    
However, be careful.  Just because Magento shipped a code generating autoloader doesn't mean that the autoloader is ready for the outside world (or even the inside world) to use.
