Building Layouts Programmatically
==================================================	
Before we can understand the layout system in its entirety, we need to understand its individual parts. With that in mind, we'll start with some simple examples.  A Layout can be defined with the following phrase

> A Layout is a collection of blocks in a tree structure

So, let's start by defining what a block is.

A Magento block is an object with a <code>toHtml</code> method defined.  When this <code>toHtml</code> method is called, it returns the string which should be output to the screen. Typically "the screen" means your web browser.  In addition to having a <code>toHtml</code> method, Magento blocks inherit from the <code>Mage\_Core\_Block\_Abstract</code> class. This class contains a number of other useful block helper methods.  We'll get to these eventually, but for now just think of a block as **an object** which has a **<code>toHtml</code>** method, and when this <code>toHtml</code> is called **output is sent to the screen**.

Let's give this a try. If you installed the <code>Nofrills\_Booklayout</code> module that came with this book, you can open the following url on your system

> http://magento.example.com/nofrills_booklayout/index/index
	
which corresponds to the controller file at

	app/code/local/Nofrills/Booklayout/controllers/IndexController.php
	
We'll be adding our code examples to the indexAction method

	public function indexAction()
	{
		var_dump(__METHOD__);
	}
	
If you load the above URL with an unmodified extension, you should see a mostly blank browser screen that looks something like *Figure 1.1*

<img src="images/chapter1/start.png" />
	
First, we'll create a simple text block.

	public function indexAction()
	{
		$block = new Mage_Core_Block_Text();
		$block->setText("Hello World");
		echo $block->toHtml();	
	}
	
Our first line instantiates a block object from the class <code>Mage\_Core\_Block\_Text</code>.  Our second line sets the text we want to output, and the third line calls the <code>toHtml</code> method, which returns our string and echos the output.  

If you reload your browser page, you should see the following output.

	Hello World
	
So far so good. We now have an object oriented echo statement. In our example above we instantiated a <code>Mage\_Core\_Block\_Text</code> object. When you call this type of block's <code>toHtml</code> method, it simply outputs whatever text has been set with the <code>setText</code> method.

Magento has literally hundreds of different types of block classes for every possible need. The Magento core team subscribes to a style of development that's similar to Java and C# programming that says

> When in doubt, make a new class

Each block type may have a slightly different implementation of how its <code>toHtml</code> method is implemented.  Fortunately, you don't need to know what every single block class does.  In fact, you can accomplish most of what you'll ever need with the <code>Mage\_Core\_Block\_Template</code> class.

Template Blocks
--------------------------------------------------
Most PHP developers quickly discover that producing HTML output by concatenating strings in PHP leads to code that's hard to debug and maintain.  That's why most HTML Output/View systems break out the HTML into template files.  Magento is no different.  As mentioned, the majority of the blocks in the system inherit from the <code>Mage\_Core\_Block\_Template</code> block class.  

Each <code>Mage\_Core\_Block\_Template</code> object has an associated phtml template file.  When a template block's <code>toHtml</code> method is called this phtml template will be output using PHP's built-in <code>include</code> statement.  Output is routed into a variable using output buffering.  By including the template from a class method, the template gains access to all the parent block's public, private, and protected methods.

If that didn't quite make sense, an example should clear things up.  Let's create a block object from the <code>Mage\_Core\_Block\_Template</code> class, set a template, and then output it.

	public function indexAction()
	{
		$block = new Mage_Core_Block_Template();
		$block->setTemplate('helloworld.phtml');
		echo $block->toHtml();	
	}
	
With the above in your controller, reload the page and ... nothing happened.  That's because we didn't create a <code>helloworld.phtml</code> file.  Let's take care of that!

Template Files
--------------------------------------------------
Of course this raises the question, "where do template files live in the system?".  Magento has a hierarchical design theming/packaging system that determines where your template files should be stored.  Magento will look in a folder with the following naming conventions

	[BASE DESIGN FOLDER]/[AREA FOLDER]/[DESIGN PACKAGE FOLDER]/[THEME FOLDER]/template

More recent versions of Magento have a fallback mechanism, where if a folder isn't found at one of the above locations, Magento will check a "base" design package for the same file

	[BASE DESIGN FOLDER]/[AREA FOLDER]/base/[THEME FOLDER]/template
	
This allows you to rely on the base Magento design package, and only add files that you wish to change to your own packages and themes.  See Appendix E for more information if you're interested in how this fallback system works.  Here's a little trick to find out where Magento is loading any block's template from.  

	public function indexAction()
	{
		$block = new Mage_Core_Block_Template();
		$block->setTemplate('helloworld.phtml');
		var_dump($block->getTemplateFile());
		//echo $block->toHtml();	
	}

By calling the block's <code>getTemplateFile</code> method, we're doing the same thing Magento will when rendering the block.  Running the above will result in 
	
	string 'frontend/base/default/template/helloworld.phtml' (length=47)

As mentioned, since we haven't created a <code>helloworld.phtml</code> file, Magento falls back to the base package/theme. 

	
Back to our Template
--------------------------------------------------
We're going to assume you're working on a freshly installed Magento system, which means you'll want to add your <code>helloworld.phtml</code> template to the default design package in the default theme.  Create a file at the following location 

	app/design/frontend/default/default/template/helloworld.phtml
	
Add something like the following to that file

	<?php #File: app/design/frontend/default/default/template/helloworld.phtml ?>
	<h1>Hello World</h1>
	<p>
	Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
	incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
	nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
	Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
	fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
	culpa qui officia deserunt mollit anim id est laborum	
	</p>

As a reminder, our controller looks this

	public function indexAction()
	{		
		$block = new Mage_Core_Block_Template();
		$block->setTemplate('helloworld.phtml');
		echo $block->toHtml();	
	}
	
If you reload the page, you should see a hello world lorem ipsum, loaded from the template, (see *Figure 1.2*)

<img src="images/chapter1/helloworld.png" />
	
Congratulations, you've created your first template block!

Nesting Blocks
--------------------------------------------------
Let's go back to our Layout definition

> A Layout is a collection of blocks in a tree structure

We've defined, very basically, what a block is, but what do we mean by "in a tree structure"?

Magento blocks are sort of like HTML nodes.  For example, here

	<p>
		<span>Lorem</span>
	</p>
	
The <code>&lt;p&gt;</code> tag is the parent node, and the <code>&lt;span&gt;</code> is the child node.  All blocks share a similar relationship.  Oversimplifying things a bit, this sort of parent/child relationship is known as a "Tree" in computer science circles.

Let's consider our previous template block.  Alter the phtml file so it contains the following

	<?php #File: app/design/frontend/default/default/template/helloworld.phtml  ?>
	<h1>Hello World</h1>
	<p>
		<?php echo $this->getChildHtml('the_first'); ?>
	</p>
	<p>
	The second paragraph is hard-coded.
	</p>
	
There's a few new concepts to cover here.  First, you'll notice we've dropped into PHP code

	<?php $this->getChildHtml('the_first'); ?>
	
You may be wondering what <code>$this</code> is a reference to.  If you'll remember back to our definition of a template block, we said that each template block object has a phtml template file.  So, when you refer to <code>$this</code> within a phtml template, you're referring to the template's  block object.  If that's a little fuzzy future examples below should clear things up.

Next, we have the <code>getChildHtml</code> method.  This method will fetch a child block, and call its <code>toHtml</code> method.  This allows you to structure blocks and templates in a logical way. So, with the above code in our template, let's reload the page, (see *Figure 1.3*)

<img src="images/chapter1/nofirst.png" />
	
Our second hard-coded paragraph rendered, but nothing happened with our call to <code>getChildHtml</code>.  That's because we failed to add a child.  Let's change our controller action so it matches the following.

	public function indexAction()
	{		
		$paragraph_block = new Mage_Core_Block_Text();
		$paragraph_block->setText('One paragraph to rule them all.');
		
		
		$main_block = new Mage_Core_Block_Template();
		$main_block->setTemplate('helloworld.phtml');
		
		$main_block->setChild('the_first',$paragraph_block);
		echo $main_block->toHtml();	
	}
	
We'll dissect this chunk by chunk.  First, we have the following

	$paragraph_block = new Mage_Core_Block_Text();
	$paragraph_block->setText('One paragraph to rule them all.');
	
Here we've created a simple text block.  We've set its text so that when the block is rendered, it will output the sentence *One paragraph to rule them all.*.  Then, as we did before, 

	$main_block = new Mage_Core_Block_Template();
	$main_block->setTemplate('helloworld.phtml');
	
we define a template block, and point it toward our hello world template.  Finally (and here's the key) 	

	$main_block->setChild('the_first',$paragraph_block);
	
Here we call a method we haven't see before, called <code>setChild</code>.  Here we're telling Magento that the <code>$paragraph\_block</code> is a child of the <code>$main\_block</code>.  We've also given that block a name (or alias) of <code>the_first</code>.  This name is how we'll refer to the block later, and what we'll pass into our call to <code>getChildHtml</code>

	<?php echo $this->getChildHtml('the_first'); ?>

Expressed as a generic XML tree, the relationship between blocks might look like

	<main_block>
		<paragraph_block name="the_first"></paragraph_block>
	</main_block>

Or maybe (getting a bit ahead of ourselves)
	
	<block type="core/template" name="root" template="helloworld.phtml">
		<block type="core/text" name="the_first">
			<action name="setText">
				<text>One paragraph to rule them all</text>
			</action>
		</block>
	</block>
	
A block may have an unlimited number of children, and because we're dealing with PHP 5 objects, changes made to the block after it has been appended will carry through to the final rendered object.  Try the following code

	public function indexAction()
	{		
		$block_1 = new Mage_Core_Block_Text();
		$block_1->setText('Original Text');

		$block_2 = new Mage_Core_Block_Text();
		$block_2->setText('The second sentence.');		
		
		$main_block = new Mage_Core_Block_Template();
		$main_block->setTemplate('helloworld.phtml');
		
		$main_block->setChild('the_first'	,$block_1);
		$main_block->setChild('the_second'	,$block_2);
		
		$block_1->setText('Wait, I want this text instead.');
		echo $main_block->toHtml();	
	}
	
With the following template changes	

	<?php #File: app/design/frontend/default/default/template/helloworld.phtml  ?>
	<h1>Hello World</h1>
	<p>
		<?php echo $this->getChildHtml('the_first'); ?>
		<?php echo $this->getChildHtml('the_second'); ?>
	</p>
	<p>
	The second paragraph is hard-coded.
	</p>
	
You should now see output something like

	Hello World
	
	Wait, I want this text instead.	 The second sentence.
	
	The second paragraph is hard-coded.
	
One final trick with rendering child blocks.  If you don't provide <code>getChildHtml</code> with the name of a block, **all child blocks** will be rendered.  That means the following template will give us the same result as the one above

	<?php #File: app/design/frontend/default/default/template/helloworld.phtml  ?>
	<h1>Hello World</h1>
	<p>
		<?php echo $this->getChildHtml(); ?>	
	</p>
	<p>
	The second paragraph is hard-coded.
	</p>

Advanced Block Functionality
--------------------------------------------------
There's a few more bits of block functionality we should cover before moving on. 

The first thing we'll cover is creating your own block classes.  There will be times where you want a block with some custom programmatic functionality.  While it may be tempting to use a standard template block and then include all your logic in the phtml template, the preferred way of doing this is to create a Magento module for adding your own code to the system, and then adding your own block classes that extend the existing classes.

We're not going to cover creating a new module here, although if you're interested in learning the basics then checkout Appendix C.  Instead, we'll have you create your custom block in the <code>NoFrills\_Booklayout</code> module.

So, we just spent a lot of effort to create a hello world block.  Let's take what we've done so far, and create a hello world block.  The first thing we'll want to do is create a new class file at the following location, with the following contents

	#File: app/code/local/Nofrills/Booklayout/Block/Helloworld.php
	<?php
	class Nofrills_Booklayout_Block_Helloworld extends Mage_Core_Block_Template
	{
	}	

And then add the following code to the specific controller action, and load its corresponding URL in your browser

	#http://magento.example.com/nofrills_booklayout/index/helloblock
	public function helloblockAction()
	{
		$block_1 = new Mage_Core_Block_Text();
		$block_1->setText('The first sentence. ');

		$block_2 = new Mage_Core_Block_Text();
		$block_2->setText('The second sentence. ');		
	
		$main_block = new Nofrills_Booklayout_Block_Helloworld();
		$main_block->setTemplate('helloworld.phtml');			
		
		$main_block->setChild('the_first',$block_1);
		$main_block->setChild('the_second',$block_2);
		
		echo $main_block->toHtml();
	}
	
When you load the page in your browser, you should see your <code>helloworld.phtml</code> template rendered the same as before.

What we've done is create a new block named <code>Nofrills\_Booklayout\_Block\_Helloworld</code>. This class extends <code>Mage\_Core\_Block\_Template</code>, which means it automatically gains the same functionality as a standard template block.  

Next, let's add the following method to our new class, 

	class Nofrills_Booklayout_Block_Helloworld extends Mage_Core_Block_Template
	{
		public function _construct()
		{
			$this->setTemplate('helloworld.phtml');
			return parent::_construct();
		}
	}	

and remove the <code>setTemplate</code> class in our controller.

	public function helloblockAction()
	{	
		$block_1 = new Mage_Core_Block_Text();
		$block_1->setText('The first sentence. ');

		$block_2 = new Mage_Core_Block_Text();
		$block_2->setText('The second sentence. ');		
	
		$main_block = new Nofrills_Booklayout_Block_Helloworld();
		// $main_block->setTemplate('helloworld.phtml');			
		
		$main_block->setChild('the_first',$block_1);
		$main_block->setChild('the_second',$block_2);
		
		echo $main_block->toHtml();
	}

A page refresh should result in the same exact page.

Every block class can define an optional "pseudo-constructor".  This is a method that's called whenever a new block of this type is created, but that is separate from PHP's standard constructor.  What we've done is ensure that our block **always** has a template set.

		public function _construct()
		{
			$this->setTemplate('helloworld.phtml');
			return parent::_construct();
		}

There's a few  other special methods you can define in a block class.  The first that we're interested in is <code>\_beforeToHtml</code>.  When we call <code>toHtml</code> on our block, this method is called immediately before the block content is rendered.  There's also a corresponding <code>\_afterToHtml($html)</code> method which is called after a block is rendered, and is passed the completed HTML string.  We're going to use the <code>\_beforeToHtml</code> method to automatically add our two child blocks, making everything self contained.

	class Nofrills_Booklayout_Block_Helloworld extends Mage_Core_Block_Template
	{
		public function _construct()
		{
			$this->setTemplate('helloworld.phtml');
			return parent::_construct();
		}
		
		public function _beforeToHtml()
		{
			$block_1 = new Mage_Core_Block_Text();
			$block_1->setText('The first sentence. ');
			$this->setChild('the_first', $block_1);		
	
			$block_2 = new Mage_Core_Block_Text();
			$block_2->setText('The second sentence. ');		
			$this->setChild('the_second', $block_2);		
		}
	}	
	
This will let us remove the extraneous code from our controller

	public function helloblockAction()
	{
		$main_block = new Nofrills_Booklayout_Block_Helloworld();			
		echo $main_block->toHtml();
	}
	
Again, a page refresh should result in the exact same page.  We've gone from having to manually create our hello world block with 10 or so lines of code to completely encapsulating its functionality and output in 2 lines. This is a pattern you'll see over and over again in Magento. 

Block Methods
--------------------------------------------------
The other thing we want to cover is calling, and adding, custom methods to your phtml templates.   Go to your helloworld.phtml file and change the title line so it matches the following.

	<!-- <h1>Hello World</h1> -->
	<h1><?php echo $this->fetchTitle(); ?></h1>

If you reload your page with this in place, you'll get the following error

	Invalid method Nofrills_Booklayout_Block_Helloworld::fetchTitle(Array
	(
	)
	)

As previously mentioned, if you use the <code>$this</code> keyword in your template, you're referring to a template's parent block object.  Let's add a method that returns the page title

	class Nofrills_Booklayout_Block_Helloworld extends Mage_Core_Block_Template
	{
		public function _construct()
		{
			$this->setTemplate('helloworld.phtml');
			return parent::_construct();
		}
		
		public function _beforeToHtml()
		{
			$block_1 = new Mage_Core_Block_Text();
			$block_1->setText('The first sentence. ');
			$this->setChild('the_first', $block_1);		
	
			$block_2 = new Mage_Core_Block_Text();
			$block_2->setText('The second sentence. ');		
			$this->setChild('the_second', $block_2);		
		}
		
		public function fetchTitle()
		{
			return 'Hello Fancy World';
		}
	}
	
Reload the page with the above <code>Nofrills\_Booklayout\_Block\_Helloworld</code> in place, and you'll see your page with its new title.
	
This is the preferred way to create templates with dynamic data in Magento.  Your <code>phtml</code> file should contain

1. HTML/CSS/Javascript code
2. Calls to echo
3. Looping and control structures
4. Calls to block methods

Any PHP more complicated than the above should be put in block methods. This includes calls to Magento models to read back data which was saved in the controller layer.  

Enter the Layout
--------------------------------------------------
Coming back again to our definition of a Layout

> A Layout is a collection of blocks in a tree structure

We now know what a block is and what a block can do.  We understand how blocks are organized in a tree like structure.  The only thing that leaves us to cover is the layout object itself.  

A layout object, (instantiated from a <code>Mage\_Core\_Model\_Layout</code> class)  

- Is a wrapper object for interacting with your blocks.  

- Provides helper methods for creating blocks

- Allows you to designate which block should start the rendering for a page

- Provides a mechanism for loading complex layouts described by XML files

Let's take a look at some layout examples.  Add the following action to our controller 

	#http://magento.example.com/nofrills_booklayout/index/layout
	public function layoutAction()
	{
		$layout = Mage::getSingleton('core/layout');				
		$block = $layout->createBlock('core/template','root');
		$block->setTemplate('helloworld-2.phtml');
		echo $block->toHtml();
	}

Next, create a file named <code>helloworld-2.phtml</code> that's in the same location as your <code>helloworld.phtml</code> template.

	<?php #File: app/design/frontend/default/default/template/helloworld-2.phtml  ?>
	<h1><?php //echo $this->fetchTitle(); ?></h1>
	<h1>Hello World 2</h1>
	<p>
		<?php echo $this->getChildHtml(); ?>	
	</p>
	<p>
	The second paragraph is hard-coded.
	</p>
	
Load your page and you'll see the second hello world template rendered in your browser, without any output for <code>getChildHtml</code> (as we didn't add any child nodes).
	
There's a lot new going on here, so let's cover things line by line.	

	$layout = Mage::getSingleton('core/layout');
	
This instantiates your layout object as a singleton model (see below). The string <code>core/layout</code> is known as a class alias.  It's beyond the scope of this book to go fully into what class aliases are used for (see Appendix B: Class Alias for a better description), but from a high level; when creating a Magento model, a class alias is used as a shortcut notation for a full class name.  It can be translated into a class name by the following set of transformations

	Core Layout 			//adding a space at the slash, and capitalizing
	Core Model Layout 		//Add the word Model in between 
	Mage Core Model Layout	//Add the word Mage before 
	Mage_Core_Model_Layout	//underscore the spaces		
	
This is a bit of an over simplification, but for now when you see something like

	$o = Mage::getModel('foo/bar');
	$o = Mage::getSingleton('foo/bar');
	
just substitue

	$o = new Mage_Foo_Model_Bar();
	
in your mind.

###What's a Singleton!?

A singleton is a fancy object oriented programming term for an object that may only be instantiated once.  The first time you instantiate it, a new object will be created.  However, if you attempt to instantiate the object again, rather than create a new object, the originally created object will be returned.  

A singleton is used when you only want to create a single instance of any type of object.  Magento assumes you'll only want to render **one** HTML page per request (probably a safe assumption), and by using a singleton it's ensured you're always getting the same layout object.

If all that went over your head don't worry.  All you need to know is whenever you want to get a reference to your layout object, use

	$layout = Mage::getSingleton('core/layout');	

Back to the Code
--------------------------------------------------
Next up we have the line

	$block = $layout->createBlock('core/template','root');
	
This line creates a <code>Mage\_Core\_Template\_Block</code> object	named <code>root</code> (we'll get to the why of "<code>root</code>" in a bit) by calling the <code>createBlock</code> method on our newly instantiated layout object.  

Again, in place of a class name, we have the <code>core/template</code> class alias.  Because we're using the class alias to instantiate a **block**, this translates to 

	Mage_Core_Block_Template
	
Again, check Appendix B if you're interested in how class aliases are resolved.  Whenever we use a class alias for the remainder of this book, we'll let you know the real PHP class. 

Everything else from here on out should look familiar.  The following

	$block->setTemplate('helloworld-2.phtml');
	echo $block->toHtml();
	
sets our block template, and renders the block using its <code>toHtml</code> method.  Let's use a class alias to instantiate our custom block from the previous examples

	//class alias 'nofrills_booklayout/helloworld' is translated into 
	//the class name Nofrills_Booklayout_Block_Helloworld
	public function layoutAction()
	{
		$layout = Mage::getSingleton('core/layout');				
		$block = $layout->createBlock('nofrills_booklayout/helloworld','root');			
		echo $block->toHtml();
	}	

Reload the page, you should see our original block.

Who's the Leader
--------------------------------------------------
Give our next example a try

	public function layoutAction()
	{
		$layout = Mage::getSingleton('core/layout');				
		$block = $layout->createBlock('nofrills_booklayout/helloworld','root');			

		$layout->addOutputBlock('root');
		$layout->setDirectOutput(true);
		$layout->getOutput();
	}

Refresh the page, and you should see the same output as your did before.  

What we've done here is replace our call to the block's <code>toHtml</code> with the following

	$layout->addOutputBlock('root');
	$layout->setDirectOutput(true);
	$layout->getOutput();

The call to <code>addOutputBlock</code> tells our layout block that **this** is the block that should start the page rendering process.  Following that is a call to <code>getOutput</code>,  which is the call that actually **starts** the page rendering process.  Every time you use <code>createBlock</code> to create an object, the Layout object will know about that block.  That's why we gave it a name earlier.  

The call to <code>setDirectOutput</code> is us telling the Layout object that it should just automatically <code>echo</code> out the results of the page.   If we wanted to capture the results as a string instead, we'd just use

	$layout->addOutputBlock('root');
	$layout->setDirectOutput(false);
	$output = $layout->getOutput();	
	
Method Chaining
--------------------------------------------------
Now's probably a good time to mention a PHP feature that Magento makes extensive use of called method chaining.  Let's replace our code from above with the following.

	public function layoutAction()
	{
		$layout = Mage::getSingleton('core/layout');				
		$block = $layout->createBlock('nofrills_booklayout/helloworld','root');			
		echo $layout->addOutputBlock('root')->setDirectOutput(false)->getOutput();
	}	

You'll notice that we've trimmed a few lines from the code, but that we're using a funky syntax in that last line.		
		
	echo $layout->addOutputBlock('root')->setDirectOutput(false)->getOutput();		
	
This is method chaining. It's not a Magento feature per se, but it's a feature of PHP that's become much more popular as applications start leveraging PHP 5 OOP capabilities.  

If a call to a method returns an object, PHP lets you chain a another method call on the end for brevity. This can be repeated as long as each method call returns an object.  The above is equivalent to the following

	$block = $layout->addOutputBlock('root');
	$block->setDirectOutput(false);
	echo $block->getOutput();

You'll also see chaining syntax that spans multiple lines

	$layout->addOutputBlock('root')
	->setDirectOutput(false)
	->getOutput();		
	
Again, this isn't anything that's specific to Magento.  It's just a pattern that's becoming more popular with PHP developers as PHP 5 style objects are used more and more.  Magento **enables** this syntax by having most of its set, create, and add methods return an appropriate object. You're not required to use it, but get used to seeing it if you spend any time with core or community modules

A Full Page Layout
--------------------------------------------------
From here on out we're going to start using a special block we've created in the Nofrills module you installed.  It's called  

	nofrills_booklayout/template
	Nofrills_Booklayout_Block_Template
	
The block is identical to the Core template block, with one exception.  

	public function fetchView($fileName)			    
	{	    	
		//ignores file name, just uses a simple include with template name
		$this->setScriptPath(
			Mage::getModuleDir('', 'Nofrills_Booklayout') . 
			DS . 
			'design'
		);			
		return parent::fetchView($this->getTemplate());
	}
	
We've overridden the <code>fetchView</code> function in our template with the code above.  What this does is move the base folder for templates from 

	app/design
	
to a folder in our local module hierarchy.

	app/code/local/Nofrills/Booklayout/design
	
This has allowed us to package our template files in the same folder as our PHP files, and save you from a lot of copy/paste

Let's open up the URL that corresponds to the Layoutdemo controller 

	#URL:	http://magento.example.com/nofrills_booklayout/layoutdemo
	#File: 	app/code/local/Nofrills/Booklayout/controllers/LayoutdemoController.php

You should see a browser screen that looks like *Figure 1.4*

<img src="images/chapter1/twocities.png" />	

If you view the source of this page, you'll see we have a full (if very basic) HTML page structure.  Let's take a look at the code we used to create the layout and blocks necessary to pull this off.  

	#File: app/cod/local/Nofrills/Booklayout/controllers/LayoutdemoController.php
	class Nofrills_Booklayout_LayoutdemoController  
	extends Mage_Core_Controller_Front_Action
	{	
		public function _initLayout()
		{
			$layout 		= Mage::getSingleton('core/layout');			
			$layout->addOutputBlock('root');
			
			$additional_head = $layout->createBlock(
			'nofrills_booklayout/template','additional_head')
			->setTemplate('simple-page/head.phtml');


			$sidebar = $layout->createBlock('nofrills_booklayout/template','sidebar')
			->setTemplate('simple-page/sidebar.phtml');

			$content = $layout->createBlock('core/text_list', 'content');			

			$root	 = $layout->createBlock('nofrills_booklayout/template','root')
			->setTemplate('simple-page/2col.phtml')
			->insert($additional_head)
			->insert($sidebar)
			->insert($content);
			
			return $layout;
		}

		public function indexAction()
		{
			$layout = $this->_initLayout();
			
			$text = $layout->createBlock('core/text','words');
			$text->setText('It was the best of times, it was the BLURST?! of times?');
			
			$content = $layout->getBlock('content');
			$content->insert($text);
			
			$layout->setDirectOutput(true);
			$layout->getOutput();
			
			exit;
		}
	}
	
Again, we have some new concepts we'll need to cover here.

Initializing the Layout and Setting Content
--------------------------------------------------
The first thing you'll notice is the <code>\_initLayout</code> layout method.  We're using this controller method to setup a base layout object that has common components (like navigation, some <code>&lt;head&gt;</code> HTML, etc.) defined.   This allows many different controller methods to share the same basic layout, without us having to rewrite the setup code every time.  Instead, all each action would need to do is call

	$layout = $this->_initLayout();
	
and a base/shared layout would already be created.  

Insert vs. Set
--------------------------------------------------

You also probably noticed we're not using the <code>setChild</code> method to add blocks to our layout.  Instead, we're using the <code>insert</code> to add child blocks to other blocks.

	->insert($additional_head)
	
The <code>insert</code> method is the preferred method for adding child blocks to an existing block.	There's a bit of redundancy in the <code>setChild</code> method, as it requires you to pass in a name for your block.

	$block->setChild('block_name', $block);
	
However, the <code>insert</code> method will automatically use the name you set when you created it (the following code created a block named "sidebar")

	$layout->createBlock('nofrills_booklayout/template','sidebar');
	
There are a few other problems with using <code>setChild</code> in a public context; as of CE 1.4.2 it still doesn't add blocks to the internal <code>\_sortedBlocks</code> array, which will cause problems down the road, (see Chapter 5 for more information).

Stick with <code>insert</code> method and you'll be a happy camper.

Getting a Reference and Text List
--------------------------------------------------
So, the <code>\_initLayout</code> method serves as a central location for instantiating a base layout object.  Templates are set, and a complete layout object with an empty "content" node is returned.  We'll want to turn our attention to the following code.

	$text = $layout->createBlock('core/text','words');
	$text->setText('It was the best of times, it was the BLURST?! of times?');
	
	$content = $layout->getBlock('content');
	$content->insert($text);

The first two lines should look familiar.  We're creating a simple <code>core/text (Mage\_Core\_Block\_Text)</code> block that looks like it was written by an infinite number of monkeys - 1.  The next set of lines is far more interesting.  The <code>getBlock</code> method allows you to re-obtain a reference to any block that's been added to the layout (including those added using <code>createBlock</code>).  

What this code does is get a reference to the content block that was added in <code>\_initLayout</code>, and then **add** our new content block to it.  The <code>getBlock</code> method is what allows us to centralize the creation of a general layout, and then customize it further for any specific action's needs.  

Let's look back up at the creation of our block named content.

	$content = $layout->createBlock('core/text_list', 'content');			
	
You'll notice we used the class alias <code>core/text\_list</code> here, which corresponds to the class Mage\_Core\_Block\_Text\_List.  Text list blocks have a slightly deceptive name, as you don't set their text.  Instead, what a <code>core/text\_list</code> block does is **automatically** render **all** child blocks that have been added to it.  

This feature of the <code>core/text\_list</code> block is what allows us to add a block named content and just start <code>insert</code>ing blocks into it.  Any block we add will be automatically rendered.

A Recap and a Dilema
--------------------------------------------------
Look back one last time at our definition of a Layout

> A Layout is a collection of blocks in a tree structure

We appear to have covered everything a layout is.  We know what a block is, we know how to create a nested structure of blocks, and we now understand how the Layout object provides command and control for the entire show.  We've also seen how a generic layout can be built, and then added to depending on our needs.

However, by answering these questions, we've created a new one.  **How** should Magento create the layouts needed for each page?  Consider our example code above where we abstracted the creation of the Layout object to a <code>\_initLayout</code> method. This made sense for the tutorials, but Magento core code contains over 180 controllers.  If we put layout instantiation in each controller, that means anytime the core team wanted to make a change to the base layout we'd need to update over 180 files.  These different <code>\_initLayout</code> functions would inevitably start to differ in slight ways, eventually causing incompatibility.

The next choice would be to create a separate, centralized, master Layout object for the base layout. Core system programmers could then get a reference to the object, and add to it as need be.  This solves some problems, but creates a situation where we're either relying on system programmers whenever designers need to change something, or letting designers into core system code to change highly abstracted PHP code they may not understand.  While services based agencies have long used designer/coders and coder/designers, this metaphor hasn't penetrated as deeply in the computer science world, which prefers a layer of separation between the two worlds.

Magento's solution to this situation was to create a system where designers could **configure** what layout they wanted for any particular URL request in Magento.  This is the Layout XML system that many of you are already familiar with, and the system that we'll be diving into in our next chapter.

*Visit http://www.pulsestorm.net/nofrills-layout-chapter-one to join the discussion online.*