## Magento 2 Dependency Injection
	
There's no way around this: If you're going to work with Magento 2, you need to have a high level understanding of dependency injection in order to understand Magento 2's automatic constructor dependency injection system.  This appendix is meant as a general introduction to dependency injection -- if you don't understand everything at first, don't worry.  We'll try to address specific, more complicated concerns in the text itself. 

Come back!  Don't panic!  This will be a gentle introduction. You only need to be passingly familiar with these concepts if you're working with Magento's front end code -- mainly so you can understand what's going on in the `__construct` method of Magento's blocks, controllers, and other layout related classes.  

### Understanding the Problem

In PHP, as in many other programming languages, you can create a new object with syntax that looks something like this

    $object = new SomeClassName;
    $object = SomeClassName::createObject();
    
Instantiating an object is a common task in modern programming.  However, some  programmers have noticed that code like the following

    function someFunction()
    {
        //... does some stuff ...
        $object = new SomeClassName;
        //... does some other stuff with $object ...    
    }
    
presents long term problems when maintaining a code base.  Whenever **anyone** calls this `someFunction` function, the `$object` will always be instantiated as a `SomeClassName` object.  For the original programmer who wrote this code, this isn't a problem.  However, programmers who come along later might not want to instantiate the `SomeClassName` object. These new programmers may want to instantiate a different object that behaves similarly, or they may want to instantiate a mock object because they're writing unit tests.   Because of this, these programmers frown upon writing individual methods or functions that directly instantiate an object.

Instead, the better practice is to pass in the objects your method depends on. 

    //type hint for $object to make sure we get the sort
    //or object this function/method expects
    function someFunction(SomeClasName $object)
    {
        //... does some stuff ...
        
        //... does some other stuff with $object ...    
    }
    
This makes `someFunction` far more flexible.  

Because some programmers like fancy names for things, this is known as *injecting* a dependency into the function, or *dependency injection*.  This technique could just as easily be called "always pass in objects instead of directly instantiating them" -- but that's a lot more to type. 

### Where Should Objects be Instantiated

This does raise a question -- while it's easy to look at a function or method in isolation and say, 

> Hey, we shouldn't instantiate that object here

the objects you inject need to be instantiated **somewhere**.  This is where modern dependency injection systems can get a little complicated.  One approach would be for a program's `main` function (or equivalent in your language/system of choice) to instantiate all a programs objects

    function main()
    {
        $object1 = new SomeClass;
        $object2 = new SomeOtherClass;
        //...
        someFunction($object1);
        
        someOtherFunction($object1, $object2)
    }
    
However, in a complex program (or a complex framework) this approach would end up creating a gigantic initial program which would, itself, end up being super complicated.   This has led many programming frameworks to create systems for **automatically** injecting dependencies.  Magento 2's core team are some of those programmers. 

### Magento's Automatic Constructor Dependency Injection System

Magento's dependency injection system is large, complex, and the sort of thing you could write an entire book on.  

We're going to keep things simple: The following describes how Magento's magic dependency injection system works.  If you're interested in how this system was implemented, or its advanced features, the *Magento 2 Object System* series is a good place to start. 

http://alanstorm.com/series/magento-2-object-system/

Magento 2's core team have extended PHP's basic object construction features.  Whenever Magento 2 instantiates an object, it will look at arguments in the object's constructor function, and **automatically** instantiate objects for these arguments.  Consider a class like this

    class SomeClass
    {
        public function __construct(
            \Some\Other\Class $someOtherClass,
            \Another\Class $anotherClass
        )
        {
            $this->someOtherClass = $someOtherClass;
            $this->anotherClass   = $anotherClass;
        }
    }

When Magento instantiates an object from `SomeClass`, it runs code that looks like this pseudo code

    //looks at the type hint for argument 1, instnatiates an objet        
    $someOtherClass = new \Some\Other\Class;
    
    //looks at the type hint for argument 2, instantiates an object
    $anotherClass   = new \Another\Class;  
    
    $ourObject = new SomeClass($someOtherClass, $anotherClass);
    
The actual code is a bit more complicated, and uses something called the Object Manager, but that's a little beyond our scope today.  Functionally though, that's what happens whenever Magento instantiates an object for you.  These Magento instantiated objects include

1. Controller objects
2. Block objects created via layout handle XML files
3. Objects created via automatic constructor dependency injection

If you directly instantiate a class with the `new` keyword yourself, you won't get this special functionality.  **Only** objects that Magento itself instantiates are subject to automatic constructor dependency injection.  Of course, Magento recommends that you inject all your dependencies and never use the `new` keyword to create objects yourself.  Whether you follow this advice is a topic that, again, is beyond the scope of this article. 

### Instantiating Interfaces

Let's take a look at another Magento class

    #File: vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/Images.php
    use Magento\Catalog\Model\Locator\LocatorInterface;
    
    //...
    
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

If we were debugging this class and wanted to know what sort of object Magento injects into `$this->locator`, we'd look at the type hint and say

> Magento's automatic constructor dependency injection instantiates a `Magento\Catalog\Model\Locator\LocatorInterface` object

However -- `Magento\Catalog\Model\Locator\LocatorInterface` is a PHP interface!  You can't use an interface to instantiate an object!  What's going on?!

Here we've stumbled across another feature of Magento's dependency injection system.  The system allows you to provide an interface as a type hint and then, via configuration, tell Magento which sort of object to instantiate. 
   
Put another way, Magento has a list of class names to use with interfaces it encounters in constructors.  When Magento sees the request to instantiates a `Magento\Catalog\Model\Locator\LocatorInterface`, it looks at its `di.xml` configuration files and finds this

    #File: vendor/magento/module-catalog/etc/adminhtml/di.xml
    <preference 
        for="Magento\Catalog\Model\Locator\LocatorInterface" 
        type="Magento\Catalog\Model\Locator\RegistryLocator"/>
        
This tells Magento when it encounters a `Magento\Catalog\Model\Locator\LocatorInterface` interface that it should instantiate a `Magento\Catalog\Model\Locator\RegistryLocator` object.

Why Magento uses interfaces is a topic that's a bit too far down the OOP rabbit hole. For now, just accept this as one of those weird things Magento 2 does.