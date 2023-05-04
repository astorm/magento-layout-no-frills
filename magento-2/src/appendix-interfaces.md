## Interfaces
	
Interfaces are a feature of the PHP programming language.  They originally come from the java programming language, and fall under the broad category of "Object Oriented Programming".  By and large a front end developer working with Magento doesn't need to understand interfaces, but if you want to have a better understanding of *why* certain features work a certain way understanding interfaces is important.  

Also, if this is the first time you've encountered interfaces you probably won't understand them on your first read through.  If you come back in a year you may start to understand their practical application.  Interfaces are a deceptively simple, but advanced topic. Don't be discouraged if you don't get them right away.

### Implementing Interfaces

On their face, PHP's interface feature is a straightforward thing.  A PHP class may *implement* one or many interfaces.

    class Foo extends Bar implements FirstInterface, SecondInterface {...}
 
What does implementing an interface mean?  Well, an interface is a collection of abstract method signatures.
 
     interface FirstInterface
     {
         abstract public function baz();
         abstract public function zip($one, $two);
     }
 
When your class implements an interface, PHP won't run your program unless it has defined all the methods in that interface.  
 
That's all relatively straight forward, and the PHP manual has more information on the specifics of how all this works.

http://php.net/manual/en/language.oop5.interfaces.php
 
What's less clear is *why* anyone would want to use interfaces.  If you're writing code to accomplish a simple task, interfaces don't seem to offer any obvious advantage.  
 
That's because they **don't** offer any advantage. An interface isn't for a programmer writing code to complete a simple task.  Instead, interfaces are a tool that's best used by a programmer *writing code that other programmers will use*.  

An interface allows a system developer to say *ok client programmer, if you want to build your own version of this class, you can.  Just make sure it implements all these methods*.  Interfaces let the client programmer understand which methods on a class are the important ones, and which are the helpers.  By themselves that's all interfaces do, and if you've never encountered them before that can be a tricky concept to get your head around.
 
### Type Systems and Magento 2

Interfaces come to us from the java programming language.  They're one of many programming language features that help systems developers create *type* systems for their application or system.  In the mists of programming past, variable and object types started as a way of telling the computer how much memory it should set aside for certain data.  However, over the years, many smart people have embraced types as a way of writing better, *more correct* programs.  

Whether these systems are worth the time or introduce more problems than they solve is another topic for another day (hint: it depends).  We mention this mainly as a prelude to explaining the extra systems that Magento's build on top of interfaces.

If you've read the dependency injection appendix, you already have some understanding of Magento's custom object system.  Magento doesn't want its developers directly instantiating objects from classes. Instead, they want developers to list their class dependencies in a class's constructor and have Magento's automatic constructor dependency injection system create those objects automatically.

Magento takes this concept one step further with interfaces.  In Magento's ideal version of the world, developers using dependency injection aren't asking the dependency injection system to instantiate a class.  Instead, they're asking the dependency injection system to instantiate an interface.

If you know anything about interfaces this may confuse you -- it's a core tenant of interfaces that you **can't** instantiate an object from them.   So what does it mean to ask Magento's object system to instantiate an interface?  

Magento keeps a list of which concrete classes should be instantiated for each interface.  You can actually add to this list (for your own interfaces), or change this list (for Magento and third party interfaces) via Magento's `di.xml` configuration (a topic for another day and another book).

So if that's the how --  **why** would Magento do something like this?  In practical terms, by developing this system Magento are encouraging developers to think about interfaces for their classes first. Some experts consider this a best practice.  That aside, there is a distinct whiff of 

> Why did they climb the mountain?  Because it's there! 

filtering down from Magento's dependency inject basecamp.

Practically speaking, as a front end developer, you'll be mostly shielded from these sorts of things.  However, the more you're involved with an open-source system like Magento, the more you'll be exposed to its internal code.  Having a general understanding of the hows and whys will always serve you well, even if you never write a line of object oriented PHP code. 