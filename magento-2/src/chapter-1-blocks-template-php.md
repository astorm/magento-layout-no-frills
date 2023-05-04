# Block Basics

The atomic unit of an HTML page in Magento 2 is a block.  A block is a PHP object that renders a bit of HTML.  To start with, we're going to use PHP to instantiate a block object, and then output a bit of HTML.  

After installing the `Pulsestorm_Nofrills` module/extension, load the following URL in your browser (substituting the `magento.example.com` URL with your own)

    http://magento.example.com/pulsestorm_nofrillslayout/chapter1
    
You should see a simple `Hello World` message displayed.  The code that creates this page can be found here

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        echo "Hello World";
        exit;
    }        

This file is a *Magento Controller*.  You don't need to understand the specifics here, but Magento implements a version of the popular Model, View, Controller programming pattern.  For Magento, "MVC" means that every specific URL will call the `execute` method of a specific controller file.  We're going to write our sample code inside these controller files.  We're doing this because it's the easiest way to get a PHP enviornment that's bootstrapped with Magento's standard objects and libraries.  If you're interested in learning more about Magento as an MVC framework, the *Magento 2 for PHP MVC developers series*

http://alanstorm.com/category/magento-2/#magento-2-mvc

is a good place to start.

Try changing the code above so it matches the following

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $block = new \Pulsestorm\Nofrillslayout\Block\Chapter1\Hello;
        echo $block->toHtml();
        
        exit;
    }

Above, we've changed the code so it

1. Creates a new PHP object from the class named `Pulsestorm\Nofrillslayout\Block\Chapter1\Hello`

2. Calls that object's `toHtml` method

3. Uses PHP's `exit` statement to halt normal Magento page rendering

If you reload the URL, you should see the following message

    Hello World from a Block
    
Congratulations -- you just instantiated your first block object, and used that block object to render a chunk of HTML.     

## What is a Block

OK, so what just happened up there?  Lets take our code apart line-by-line.  

    $block = new \Pulsestorm\Nofrillslayout\Block\Chapter1\Hello;
    
This line uses PHP's `new` statement to create a new object from the PHP class `\Pulsestorm\Nofrillslayout\Block\Chapter1\Hello`.  If you're confused by the backslashes, those are PHP namespaces.  In PHP versions greater than 5.3, you can organize your classes into a tree of namespaces, sort of like a file system.  

Many modern coding conventions dictate you should use a `use` statement in your class files, which will let you use a class's short name.  This would look something like the following.        

    use Pulsestorm\Nofrillslayout\Block\Chapter1\Hello;
    
    //...
    function execute()
    {
        $block = new Hello;
        //...
    }

The `use` statement imports the PHP class into the current namespace under the short name `Hello`.  While you'll need to understand the `use` statement to work with Magento 2 (and most modern PHP frameworks), we'll try to stick to fully namespaced classes in this book.  

If we take a look at the next line

    echo $block->toHtml();

we see a call to the `toHtml` method of our block object.  Let's take a look at the definition file for the `Pulsestorm\Nofrillslayout\Block\Chapter1\Hello` class, which should contain the definition for the `toHtml` method.  You're probably wondering where you can find that source file.

Most PHP frameworks, Magento included, use PHP's autoloader feature to [automatically load class definition files](http://php.net/autoload).  Most modern PHP frameworks use one of the PSR autoloading standards ([PSR-0](http://www.php-fig.org/psr/psr-0/) or [PSR-4](http://www.php-fig.org/psr/psr-4/)).  

So where's our class file? Magento converts a class name like `Pulsestorm\Nofrillslayout\Block\Chapter1\Hello` into a file path like

    Pulsestorm/Nofrillslayout/Block/Chapter1/Hello.php
    
and then searches a set number of base directories for your class definition file.  If you're interested in learning more, checkout the appendix on PHP autoloaders. In our case, that's the following file.  

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Chapter1/Hello.php
    namespace Pulsestorm\Nofrillslayout\Block\Chapter1;
    class Hello implements \Magento\Framework\View\Element\BlockInterface
    {
        public function toHtml()
        {
            return '<p>Hello World from a Block</p>';
        }   
    }

Here we see the definition of our block class.  All an object needs to do to be a block is

1. Have a method named `toHtml`.  
2. Have the `toHtml` method return a rendered HTML string.  

Our class does both these things.   You'll also notice our class implements an interface. If you're not sure what an interface is for,  checkout the interfaces appendix.

    implements \Magento\Framework\View\Element\BlockInterface

If we take a look at this interface

    <?php
    #File: vendor/magento/module-cms/Api/Data/BlockInterface.php
    namespace Magento\Framework\View\Element;

    interface BlockInterface
    {
        public function toHtml();
    }

we see a single `toHtml` method.  In addition to supporting the *convention* that block objects need a `toHtml` method, Magento 2 enforces a `toHtml` method  by having all blocks implement this `Magento\Framework\View\Element\BlockInterface` interface.

## Creating your Own Block Class.   

Now that we know what a block class is, lets try creating one of our own. We'll name our block class `Pulsestorm\Nofrillslayout\Block\Chapter1\Hello2`.  So, to start, let's change our execute method to use our new block class. 

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $block = new \Pulsestorm\Nofrillslayout\Block\Chapter1\Hello2;
        echo $block->toHtml();
    }

If we reload our URL with the above in place, we'll see an error that looks something like this

> Fatal error: Class 'Pulsestorm\Nofrillslayout\Block\Chapter1\Hello2' not found in /path/to/magento/app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php on line 17

This is PHP telling us we tried to use a class (`Pulsestorm\Nofrillslayout\Block\Chapter1\Hello2`) with no definition file.  Let's fix that by defining our class.  Create the following file in the following location

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Chapter1/Hello2.php
    <?php
    namespace Pulsestorm\Nofrillslayout\Block\Chapter1;
    class Hello2 implements \Magento\Framework\View\Element\BlockInterface
    {
        public function toHtml()
        {
            return '<p>Hello World from <b>our own</b> Block</p>';
        }   
    }

This is almost exactly the same definition file as the `Pulsestorm\Nofrillslayout\Block\Chapter1\Hello` class -- the only exceptions are

1. The class's short name is `Hello2`
2. We're outputting a different HTML string

After you've done the above, reload the page and you should see your new Hello World message.

## The Magento Object Manager

Before we move on, we need to discuss another object oriented programming feature of Magento 2 -- something called the Object Manager.  The Object Manager is a special object Magento uses to replace PHP's `new` keyword.  

In normal PHP code, you instantiate an object like this

    $block = new \Pulsestorm\Nofrillslayout\Block\Chapter1\Hello2;
    
When Magento 2 instantiates an object, the code looks more like this

    $block = $objectManager->create('Pulsestorm\Nofrillslayout\Block\Chapter1\Hello2');

The reasons for this are myriad, but from a high level point of view, by requiring system developers to use an object manager, Magento gives their objects a number of special properties that PHP objects don't have.  One *negative* consequence of this is many of the Magento core classes are difficult and/or impossible to use **without** the object manager.  For example, if you tried to create a new class that extends a base Magento class like this

    <?php
    namespace Pulsestorm\Nofrillslayout\Block;
    class SomeClass extends \Magento\Framework\View\Element\Template
    {
    }

and then instantiated that class with the `new` keyword

    $block = new \Pulsestorm\Nofrillslayout\Block\SomeClass;
    
You'd end up with a PHP error that looked something like this
    
> 1 exception(s):
Exception #0 (Exception): Recoverable Error: Argument 1 passed to Magento\Framework\View\Element\Template::__construct() must be an instance of Magento\Framework\View\Element\Template\Context, none given

The specific reasons for this are a bit beyond the scope of this book, but if you're interested in learning more *The Magento 2 Object System*

http://alanstorm.com/category/magento#magento_2_object_system

is a great place to get started.  There's also a Dependency Injection appendix in the back of this book that covers adjacent topics. 

For our immediate purposes here's all you need to know.  The base controller file in the `Pulsestorm_Nofrillslayout` module has a special `getObjectManager` method.  This method will return an instance of the Magento object manager for you.  From this point forward, all our code is going to use this object manager to create objects.  In a normal Magento project you'll rarely need to use the object manager -- but in order to teach you why that's true, we'll need to use the object manager.  Programming is crazy like that sometimes.

So, when you see code like this

    $objectManager = $this->getObjectManager();
    $block         = 
        $objectManager->create('Pulsestorm\Nofrillslayout\Block\SomeClass');

all you need to know is this code instantiates a `Pulsestorm\Nofrillslayout\Block\SomeClass` object.

## Creating Text and Template Blocks

Lets get back to Magento blocks.  Hard coding HTML or text values in a `toHtml` method isn't a great best practice.  Fortunately, Magento 2 ships with two block types that can help us work around this: Text blocks, and Template blocks.  

First, let's talk about text blocks, and by talk about, we mean use!  Change the Chapter 1 controller so it matches the following

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $block         = $objectManager->get('Magento\Framework\View\Element\Text');
        $block->setText('Hello text block.');
        echo $block->toHtml();
    }
    
With the above code in place, reload the page and you should see a page that contains the `Hello text Block.` text.  

What we've done above is use Magento's Object Manager to create a `Magento\Framework\View\Element\Text` object, use that object's `setText` method to set the text on the block, and then called that block's `toHtml` method.  When a `Magento\Framework\View\Element\Text` object renders itself, it looks for the value set with `setText`, and renders that value as HTML text.  While the above example is simple, a text block gives you the ability to create a block that renders **any** text you'd like, from any source.  

## Template Blocks

While Text Blocks are powerful, the real workhorse of Magento 2 layouts are template blocks.  Template blocks are similar to text blocks in that they allow you to render any HTML you want -- however, rather than render arbitrary strings set via PHP code, a template block will use a `phtml` PHP template file for that rendering.  Lets give template blocks a try.  

First, we'll create our `.phtml` template file.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/hello.phtml
    <h1>Hello Template!</h1>
    
Then, we'll change our controller action file to instantiate a template object, assign that template object a path, and then render that template object

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {        
        $objectManager = $this->getObjectManager();
        $block         = $objectManager->get('Magento\Framework\View\Element\Template');
        $block->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/hello.phtml');
        echo $block->toHtml();    
    }

Reload the URL, and you should see the rendered text from your `.phtml` template.
    
Congratulations, you just rendered your first Magento 2 template block!  There's a little more to unpack here that in our previous code samples, so lets get to it.

First off, there's our `.phtml` template file. 

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/hello.phtml
    <h1>Hello Template!</h1>
      
In Magento 2, a module's `phtml` template files ship **with the module**.  Every Magento 2 module can have a `view` folder 

    app/code/Pulsestorm/Nofrillslayout/view

This folder contains assets related to rendering a module's front end HTML, CSS, and Javascript.  The next portion of a view's path 

    frontend
    
should be the *Magento Area* you want to use the view in.  Areas are a tricky topic to cover in full, but for the purposes of this book, the `frontend` area means the user facing Shopping Cart application, and the `adminhtml` area means the backend Magento Admin Console.  You can also checkout the areas appendix in the back of this book.

The third portion of a template's path is the word `templates`.  The folder indicates the type of view asset inside the folder.  

Finally, within the `templates` folder, you can use any directory hierarchy you'd like to organize your `.phtml` templates.  We've chosen to place our template in the `chapter1/user` folder -- you'd probably choose something else for a real module.  

Next is the code that instantiates and renders the template block.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php

    $objectManager = $this->getObjectManager();
    $block         = $objectManager->get('Magento\Framework\View\Element\Template');
    $block->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/hello.phtml');
    echo $block->toHtml(); 

The `Magento\Framework\View\Element\Template` class is Magento's base template class. All Magento 2 blocks inherit from this base template class.  The bit of code we're most interested in though is this one

    $block->setTemplate('Pulsestorm_Nofrillslayout::user/hello.phtml');

A Magento 2 programmer uses the `setTemplate` method to tell Magento where a block's template is located -- the `Pulsestorm_Nofrillslayout::chapter1/user/hello.phtml` syntax (A "Template URN") contains two parts -- the first is the module where your template is.  Behind the scenes Magento will 

1. Expand this module name into your module's `view` folder (`app/code/Pulsestorm/Nofrillslayout/view`)

2. Append the current area (`frontend`)

3. Append the word template (`template`)

4. Append the file path on the *right* side of the `::`.  

So, with `Pulsestorm_Nofrillslayout::user/chapter1/hello.phtml`, Magento uses those four parts 

- `app/code/Pulsestorm/Nofrillslayout/view`

- `frontend`

- `template`

- `Pulsestorm_Nofrillslayout::chapter1/user/hello.phtml`

crete a full template path of 
    app/code/Pulsestorm/Nofrillslayout/view/frontend/template/chapter1/user/hello.phtml

## Creating your Own Template Blocks

The examples in the last section used Text and Template blocks directly.  While this is possible in Magento 2, it's far more common (and useful) for a developer to create their own block classes that *extend* these base block classes.  

For example, if you create the following PHP class definition file

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Chapter1/User/Template.php
    <?php
    namespace Pulsestorm\Nofrillslayout\Block\Chapter1\User;
    class Template extends \Magento\Framework\View\Element\Template
    {

    }    

you'll be able to use the `Pulsestorm\Nofrillslayout\Block\Chapter1\User\Template` block class in your own code. 

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {        
        $objectManager = $this->getObjectManager();
        $block         = $objectManager->get('Pulsestorm\Nofrillslayout\Block\Chapter1\User\Template');
        $block->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/hello.phtml');
        echo $block->toHtml();    
    }

This probably seems like an unneeded complication for our simple hello world output, but its the real power of this approach becomes apparent when we add a method definition to our block object.

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Chapter1/User/Template.php
    <?php
    namespace Pulsestorm\Nofrillslayout\Block\Chapter1\User;
    class Template extends Magento\Framework\View\Element\Template
    {
        public function getFish()
        {
            return ['one fish', 'two fish', 'red fish', 'blue fish'];
        }
    }  
    
and then **use that method from our template**.  

    <?php #app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/hello.phtml ?>
    
    <?php 
        $all_fish = $block->getFish();
    ?>
    <h1>Hello Fish!</h1>
    <ul>
    <?php foreach($all_fish as $fish):?>
        <li><?php   echo $block->escapeHtml($fish); ?></li>
    <?php endforeach; ?>
    </ul>

If you make the above change to your `Template.php` and `hello.phtml` and then reload the page, you should see the following content rendered.

    Hello Fish!

    - one fish
    - two fish
    - red fish
    - blue fish
    
The above code introduces a few new concepts, so lets cover them one by one. First off, we added a PHP method to our block class

    #File: app/code/Pulsestorm/Nofrillslayout/Block/Chapter1/User/Template.php
    public function getFish()
    {
        return ['one fish', 'two fish', 'red fish', 'blue fish'];
    }

This method returns an array that contains four strings.  Next, in our template file, we **called this method** to fetch the list of fish

    <?php 
        $all_fish = $block->getFish();
    ?>
    
A `.phtml` template file is just a PHP file -- you can write **any** sort of PHP code you'd like in these templates.  All Magento `.phtml` files used by the layout system are tied to a specific block class.  When you say `$block->getFish()`, you're calling the `getFish` method on the template's block class.
           
Once we've fetch the array of fish, we use standard PHP to loop through the array, and echo out each string element as an unordered list. 

    <ul>
    <?php foreach($all_fish as $fish):?>
        <li><?php   echo $block->escapeHtml($fish); ?></li>
    <?php endforeach; ?>
    </ul>
    
The only non-core PHP code in this block is the call to the block's `escapeHtml` method.  All untrusted output should be passed through the  `$block->escapeHtml` method when you're rendering a `.phtml` template.  This will take care of escaping HTML such that you're safe from XSS style attacks.  

The `escapeHtml` method is similar to our `getFish` method, in that it's a method defined on our block object.  However, this method is defined on the base template block's parent class (a `Magento\Framework\View\Element\AbstractBlock`).  

    #File: vendor/magento/framework/View/Element/AbstractBlock.php
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->_escaper->escapeHtml($data, $allowedTags);
    }

Generally speaking, the code in a Block class has two purposes -- first, it's used to fetch data that a template might want to display (using Magento 2's model layer, or however else you might want to fetch data).  Second, they provide helper methods (like `escapeHtml`) so that `phtml` template programmers don't need to write overly complicated template code.  While you *can* write anything in a `phtml` template, the idea is to keep it simple: Call block methods to fetch data, loop over it, and add the occasional conditional `if` before `echo`ing the content out.  

Blocks can make all the difference in the world when/if you need to refactor some a complicated bit of template logic, or you need to hand off the templates to a great front end developer who may not understand the underlying implementation details of where the data used by the template comes from.  

## Magento 2 vs. Magento 1: $this and $block

There's one last thing to mention about `phtml` templates before we move on.  Magento 2 introduces a **new** `$block` variable into every template.  This variable is *almost* interchangeable with `$this` -- i.e. you use it to call your block's methods 

    <?php #app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/hello.phtml ?>
    
    <?php 
        $all_fish = $block->getFish();
        $all_fish = $this->getFish();
    ?>
    
For the remainder of this book we're going to stick to the `$block` convention.

The technical reasons for this are a bit beyond the scope of this book, but at a high level Magento 2 introduced a **new** object (`Magento\Framework\View\TemplateEngine\Php`) that does the actual HTML rendering -- i.e the `include` statement for a block is no longer in the `AbstractBlock` class -- it's in the `Magento\Framework\View\TemplateEngine\Php` class.  If you didn't follow the last paragraph, don't worry, it's mainly intended for folks familiar with Magento 1's internals to help explain the `$block` vs `$this` difference. 

## Parent/Child Blocks

Now that we have a better understanding of what a block object is, we need to discuss how Magento typically uses block objects on a page.  Whenever you want Magento to return an HTML page from a controller, Magento's core code will instantiate a *layout* object.  This object keeps track of which blocks are needed for a particular page, and is the object Magento uses to render the blocks.  The layout object also organizes blocks into a tree structure.  A simplified example of that might look something like this

    - root 
        - sidebar block
        - content block
            - main section section
        - footer block
    
There's a root block at the top -- this block has three child blocks (sidebar, content, footer).  The content block has a main section block.  Real Magento page requests may have dozens, sometimes hundreds, of individual blocks with parent/child hierarchies that go many layers deep.  This allows Magento core developers to isolate a specific bit of HTML and template logic from the rest of the page.

If that didn't make sense, some code should make it clearer.  Replace our controller action with the following code.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager      = $this->getObjectManager();
        $layout             = $objectManager->get('Magento\Framework\View\Layout');
        $block              = $layout->createBlock('Magento\Framework\View\Element\Text');
        $block->setText('<h1>Hello Layout!</h1>');
        echo $block->toHtml();

    }

If you reload our URL with the above code in place, you should see the "Hello Layout!" from our text block rendered.
    
This code introduces two new concepts.  First, there's the Layout object itself, and second, there's the layout object's `createBlock` factory method.

The Layout object (instantiated from a `Magento\Framework\View\Layout` class) is the main object Magento's core code uses to create blocks.  For Magento 1 developers, this object is analogous to the old `Mage::getSingleton('core/layout')` object.  This object has a `createBlock` method.  The system usually uses the `createBlock` method (rather than direct use of the object manager) to instantiate block objects.  It's also necessary to use the `createBlock` method for your block if you want them to have parent/child relationships.  

Next, we're going to create two blocks with a parent child relationship.  First, let's create the blocks.  Replace your controller action code with the following

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $layout        = $objectManager->get('Magento\Framework\View\Layout');
        $blockParent   = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/parent.phtml');
        
        $blockChild    = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockChild->setTemplate('Pulsestorm_Nofrillslayout::chapter1/child.phtml');
        
        echo $blockParent->toHtml();
        echo $blockChild->toHtml();    
    }
    
So far, there's nothing new here.  All we're doing is instantiating two blocks, assigning them templates, and then rendering each block individually.  To make one block a child of another block you need to **append** that block to the first.   Give the following code a try       

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $layout        = $objectManager->get('Magento\Framework\View\Layout');
        $blockParent   = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/parent.phtml');
        
        $blockChild    = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockChild->setTemplate('Pulsestorm_Nofrillslayout::chapter1/child.phtml');
     
        $blockParent->append($blockChild);   
        echo $blockParent->toHtml();    
        #echo $blockChild->toHtml();        
    }

The code above is almost identical to the previous example, except we've

1. Commented out the `echo` for the child block
2. Used the parent block's `append` method to add the child block to the parent block

If you reload the page, **both** blocks are still printed out, even though we're only echoing the first block.  
    
## Rendering a Child Block

The above examples used templates we prepared for this book.  Let's try building a parent/child block relationship from scratch.  First, we'll create a parent template and two children.  

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/parent.phtml    
    <h1>Our Own Parent Block</h1>
    
    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/child1.phtml    
    <p>Some people think having one child is enough</p>
    
    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/child2.phtml            
    <p>Other people think a second child can keep the first company.</p>    
    
Then, let's change our controller code to create the blocks, append the children, and `echo` the parent.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $layout        = $objectManager->get('Magento\Framework\View\Layout');
        $blockParent   = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/parent.phtml');
        
        $blockChild1    = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockChild1->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/child1.phtml');

        $blockChild2    = $layout->createBlock('Magento\Framework\View\Element\Template');
        $blockChild2->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/child2.phtml');
             
        $blockParent->append($blockChild1);   
        $blockParent->append($blockChild2);   
        echo $blockParent->toHtml();       
    }
        
With the above code in place, if we reload our URL we'll see

    Our Own Parent Block
    
Huh. That's unexpected.  **Only** the parent block rendered, even though we added the two child blocks. What gives?  

A parent block **does not** echo out its children automatically.  There's one additional thing we'll need to do to make this happen.  Find your parent template file, and add the following code to it. 

    <h1>Our Own Parent Block</h1>
    <?php 
        echo $this->getChildHtml();
    ?>

The `getChildHtml` method will render **all** of a parent's children blocks.  Reload the URL with the above code in place, and you should see both your children rendered.

    Our Own Parent Block

    Some people think having one child is enough

    Other people think a second child can keep the first company.    
    
Once you've digested the above, you may be wondering if it's possible to *selectively* render a specific child block.  This is possible, but we **need to assign a name to our blocks** when we create them.  Let's change our controller code so it matches the following

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $layout        = $objectManager->get('Magento\Framework\View\Layout');
        $blockParent   = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_parent'
        );
        $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/parent.phtml');
        
        $blockChild1    = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_child1'
        );
        $blockChild1->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/child1.phtml');

        $blockChild2    = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_child2'
        );
        $blockChild2->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/child2.phtml');
             
        $blockParent->append($blockChild1);   
        $blockParent->append($blockChild2);   
        echo $blockParent->toHtml();    
        #echo $blockChild->toHtml();        
    }

The big change to the above code is we're passing a second argument to the `createBlock` method. 

    $blockChild2    = $layout->createBlock(
        'Magento\Framework\View\Element\Template',
        'pulsestorm_nofrills_child2'
    );

This second argument (`pulsestorm_nofrills_child2` above) gives our block a **name** in the layout.   Once our blocks have names, we can tell the `getChildHtml` method which block we want to render.  If we edit our `parent.phtml` file so it matches the following

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter1/user/parent.phtml        
    <h1>Our Own Parent Block</h1>
    <?php 
        echo $this->getChildHtml('pulsestorm_nofrills_child1');
    ?>

and reload our page, we'll see that only the first child rendered.  
    
Once your blocks are named, you can add special conditional logic or rules around which child blocks (if any) render for a particular template.    

## Magento 2: Block Containers and the Layout Structure

So far, much of what we've talked about has been a retread of Magento 1 features and concepts.  Magento 2's new object system may have changed the syntax of what we're doing, but conceptually it's very similar to Magento 1.  In this section we're going to cover two new features of the Magento 2 layout system -- containers and the structure.  

A container is conceptually similar to a block, in that it's a node in the layout object.  However, (unlike a block), a container

1. Has no associated class file
2. Has no template
3. Has no content

A container's *only* job in Magento 2 is to hold a reference to other block objects, and then render each and every block added to it.  For Magento 1 developers, a container performs the same job as the old `text/list` blocks.  

With the restrictions above, programmatically interacting with containers becomes a bit trickier than interacting with blocks.  How can we append a container to another block if we can't instantiate a container?  Also -- how do we add the *root* container that sits at the top of the layout tree?

In an attempt to improve the layout system in Magento 2, the core developers inadvertently introduced a new layer of complexity into the rendering of a layout object.  In order to deal with this complexity, the layout object uses a special structure object to keep track of how blocks, containers, and other elements are related.  

Normally, you'll never need to interact with this structure object.  In fact, Magento's built the system in such a way that it's very *difficult* to deal directly with a structure object.  The `Pulsestorm_Nofrillslayout` module that ships with this book contains a special class preference (see the Dependency Injection appendix for more information on class preferences) that will let us get at and use the structure object.  We don't recommend using this in production code, but as a teaching aid it will be invaluable. 

OK, so that's a lot of new concepts all at once.  Let's see if we can clarify things a bit with some code. 

First, let's swap out our controller action with the following.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $layout        = $objectManager->get('Magento\Framework\View\Layout');        
        
        $blockParent   = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_parent'
        );
        $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/parent.phtml');
        $blockChild1    = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_child1'
        );
        $blockChild1->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/child1.phtml');        
        $blockParent->append($blockChild1);
        
        echo $blockParent->toHtml();
    }
        
This is a rehash of our code from above.  We've created a parent block, added a child block to the parent, and then `echo`d the content.  Once you've confirmed the above code works, let's replace the `echo $blockParent->toHtml();` so our action looks like this

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();
        $layout        = $objectManager->get('Magento\Framework\View\Layout');        
        
        $blockParent   = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_parent'
        );
        $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/parent.phtml');
        $blockChild1    = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_child1'
        );
        $blockChild1->setTemplate('Pulsestorm_Nofrillslayout::chapter1/user/child1.phtml');        
        $blockParent->append($blockChild1);
        
        $layout->addContainer('top', 'The top level container');        
        
        // Magento\Framework\View\Layout\Data\Structure
        $structure = $layout->getStructure(); //note: not standard magento
        $structure->setAsChild('pulsestorm_nofrills_parent', 'top');
        
        $layout->generateElements();
        echo $layout->getOutput();
    }

If you reload with the above code in place, you should see your blocks rendered out correctly, despite the fact we never called `toHtml` on the parent block.  The new lines we added 

1. Added a root level container named `top`
2. Fetched the layout's structure manager object (`Magento\Framework\View\Layout\Data\Structure`)
3. Used the structure manager to set our parent block as a child of the `top` container
4. Used the layout object's `getOutput` method to render the entire layout

Conceptually, that's a lot to take in.  Let's review each of the above in a little more detail.

First off, the following line

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php
    
    $layout->addContainer('top', 'The top level container');        
    
Uses the Layout Object's `addContainer` method to add a "root level" container to the layout object.  At this point, our layout looks like the following

    - top (a container)
    
That's it -- just a top level container.  If we tried rendering our layout at this point, we'd just get a blank string since our layout is empty.  Next, we need to **add a block** to our container.

Unfortunately -- the Magento layout object doesn't have a publicly accessible method for adding a block to a container.  This may make sense for the core team's goals -- but it gets in the way of our goals: teaching you how the layout system works.  That's why this next line

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php

    // Magento\Framework\View\Layout\Data\Structure
    $structure = $layout->getStructure(); //note: not standard magento

has the "not standard Magento" warning.  We've used Magento's flexible object system to give the layout object a `getStructure` method.  This will return the (standard Magento) `Magento\Framework\View\Layout\Data\Structure` object.  The structure object is the object that keeps track of the parent/child relationships between blocks and containers in a Magento layout.  

The structure object has a `setAsChild` method.  This method allows you to tell Magento

> Make this already-created block a child of a particular container 

So when we said

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php

    $structure->setAsChild('pulsestorm_nofrills_parent', 'top');
    
This was us telling Magento

> Make the already created `pulsestorm_nofrills_parent` block a child of the container named `top`

At this point, our layout looks like this

    - top (a container)
        - pulsestorm_nofrills_parent (a block)
            - pulsestorm_nofrills_child1 (a block)
            
We've added a root level container named `top` and added the block named `pulsestorm_nofrills_parent` to this container.  The `pulsestorm_nofrills_child1` was already a child of `pulsestorm_nofrills_parent` from our previous layout manipulations.

Our final step is outputting the layout.  This is two lines of code 

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter1/Index.php

    $layout->generateElements();
    echo $layout->getOutput();
    
First, we call `generateElements` on the layout object. We'll talk more about this method in later chapters. For now, just think of it as a initialization method for the layout object.  This is us telling Magento

> Hey Magento, get ready, we're about to output the layout

The second line calls the layout object's `getOutput` method.  This is us telling Magento 

> Hey Magento, please give us a final rendered string for this layout object.

When you call `getOutput` on the layout object, Magento will

1. Find every root level container added with `addContainer`

2. Render *every top level block* inside that container.  

So, in our specific case, Magento finds a root level container named `top`.  Inside of top it finds a single block named `pulsestorm_nofrills_parent`, and then calls this block's `toHtml` method.  

## Wrap Up

OK!  Those were lots of words, and some of you may be thinking this is a *crazy* way to build page layouts.  Fortunately, someone who worked at Magento at one point in time agrees with you.  While building layouts in PHP makes it easier to see their structure, for day-to-day HTML work, Magento created its Layout XML system.  

In our next chapter, we're going to take a look at using this layout XML system to create block and page structures identical to what we did above -- all without writing a single line of new PHP code. 