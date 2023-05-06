XML Page Layout Files
==================================================	
The core problem the Magento Layout XML system sets out to solves is

> How do we allow designers and theme developers to configure a layout, but still offer them full control over the HTML output if they need/want it

So why XML? XML gets used for a lot of things.  If you've been doing web development for a while you probably think of XML as a generic document format. While that's **one** of the things XML can be used for, software engineers and computer scientists have other uses for it.  

The Magento XML Layout format is less a document format, and more a mini-programming language.  The Magento core team created a special format of XML files that fully describes the process of creating blocks programmatically that was described in the previous chapter.  There is no public schema or DTD for this dialect of XML, but don't worry, by the time we're through with this chapter you'll have the format down cold.

The Magento Layout XML System is made up of multiple independent pieces.  It may seem like some of what we're doing is more of a hassle than just using PHP to create our blocks.  However, once you've seen all the pieces, and how those pieces fit together, the advantages of the system should be apparent.

All of which is a fancy way of saying, "Hang in There".  This is new, this is different than what you're used to, but it's no harder than any other web development you've learned before.  

Finally, while not 100% necessary, the content of this chapter assumes you've been through Chapter 1.  Even if you're a master at creating blocks programmatically, you may want to skim through the previous chapter before venturing on.

Hello World in XML
--------------------------------------------------

We'll be working in <code>UpdateController.php</code> in this chapter, which may be accessed at the following URL/file

	http://magento.example.com/nofrills_booklayout/update
	app/code/local/Nofrills/Booklayout/controllers/UpdateController.php
	
The first type of XML tree Magento uses is called the Page Layout.  We say tree instead of file, as the Page Layout	XML is normally generated on the fly.  We're going to create a few Page Layouts manually to get an idea of how they work.
In the previous chapter, we created a Hello World block with a class alias of <code>nofrills_booklayout/helloworld</code> (corresponding to the class <code>Nofrills\_Booklayout\_Block\_Helloworld</code>).  Let's start by creating a Page Layout that uses this block. 

First, here's the XML we'll use

	<layout>
		<block type="nofrills_booklayout/helloworld" name="root" output="toHtml" />
	</layout>
	
We have an XML node with a root node named layout.  Within the root node is a single block node with three attributes; type, name, and output. 

The **type** attribute is where we specify the class alias of the block we'd like to instantiate.  The <code>name</code> attribute allows us to set a name for the block which can be used later to get a reference.  The <code>output="toHtml"</code> attribute/value pair tells the layout system that **this** is the block which should start output.

The Page Layout XML above is roughly equivalent to the following PHP code

	$layout = new Mage::getSingleton('core/layout');
	$layout->createBlock('nofrills_booklayout/helloworld','root');
	$layout->addOutputBlock('root','toHtml')

You'll notice we've passed in a second parameter ('<code>toHtml</code>') to the <code>addOutputBlock</code> method.  This is an optional parameter that tells the layout object **which method** on the output block should be be used to kick off output.  If you look at its definition, it normally defaults to <code>toHtml</code>

    public function addOutputBlock($blockName, $method='toHtml')
    {
        //$this->_output[] = array($blockName, $method);
        $this->_output[$blockName] = array($blockName, $method);
        return $this;
    }

In practice you'll never set this optional parameter, but we're including it here to make it more clear what the output attribute in the XML node above is doing

An Interesting use of the Word Simple
--------------------------------------------------
Let's load our XML into the layout object and use it to generate our output.  Edit the <code>indexAction</code> method in <code>UpdateController.php</code> so it matches the following

	public function indexAction()
	{
		$layout = Mage::getSingleton('core/layout');						
		$xml = simplexml_load_string('<layout>
				<block type="nofrills_booklayout/helloworld" 
				name="root" output="toHtml" />
			</layout>','Mage_Core_Model_Layout_Element');

		$layout->setXml($xml);
		$layout->generateBlocks();			
		echo $layout->setDirectOutput(true)->getOutput();			
	}		
	
Load the code above in a browser at

	http://magento.example.com/nofrills_booklayout/update
	
and you should see your Hello World block.  

The first thing that may look a little unfamiliar about the code above is the fragment that creates our simple XML object.

	$xml = simplexml_load_string('<layout>
	<block type="nofrills_booklayout/helloworld" name="root" output="toHtml" />
	</layout>','Mage_Core_Model_Layout_Element');
	
You may	have never seen a SimpleXML node created with that second parameter
	
	Mage_Core_Model_Layout_Element
	
One of SimpleXML's lesser known features is the ability to tell PHP to use a user defined class to represent the nodes.  By default, a SimpleXML node is a object of type <code>SimpleXMLElement</code>, which is a PHP built-in.  By using the syntax above, the Magento core code is telling PHP

> Make our simple XML nodes objects of type <code>Mage\_Core\_Model\_Layout\_Element</code> instead of type SimpleXMLElement

If you look at the inheritance chain, you can see that the <code>Mage\_Core\_Model\_Layout\_Element</code> class has <code>SimpleXMLElement</code> as an ancestor.

	class Mage_Core_Model_Layout_Element extends Varien_Simplexml_Element {...}
	class Varien_Simplexml_Element extends SimpleXMLElement {...}
	
So, the Magento provided class name extends <code>SimpleXMLElement</code>. That means all normal SimpleXML functionality is preserved.

If you tried to use <code>setXml</code> with a normal <code>SimpleXMLElement</code>, you'd end up with an error that looks something like this

	Recoverable Error: Argument 1 passed to Varien_Simplexml_Config::setXml() 
	must be an instance of Varien_Simplexml_Element, instance of
	SimpleXMLElement given

That's because Magento uses PHP's type hinting features to ensure that a normal SimpleXMLElement based object can't be used.  

	//notice the Varien_Simplexml_Element type hinting
    public function setXml(Varien_Simplexml_Element $node)
    {
		...

This is another example of Magento's object oriented system design. Some of you are probably thinking "**That's nuts! Why would you want to do this?**"  By providing a custom class here, we gain the ability to add custom methods to any XML node.  For example, if we were using the default SimpleXMLElement node, every time we wanted to grab a block's name attribute we'd need to do something like this

        $tagName = (string)$node->getName();
        if ('block'!==$tagName && 'reference'!==$tagName || empty($node['name'])) {
            $name = false;
        }
        $name = (string)$node['name'];
        
Using the SimpleXML custom class feature, we can define a method on our class to do this for us

    public function getBlockName()
    {
        $tagName = (string)$this->getName();
        if ('block'!==$tagName && 'reference'!==$tagName || empty($this['name'])) {
            return false;
        }
        return (string)$this['name'];
    }
    
and then use it wherever we want, resulting in cleaner end-user code which is easier to read and understand

	$name = $node->getBlockName();

If you're not convinced, a little paraphrased Tennyson might help you along the way

> Ours is not to question why/Ours is but to do or die

	
Adding the XML, Generating the Blocks
--------------------------------------------------
So, that little foray in lesser known PHP features complete, the next bit it pretty straight forward.  Our Layout object is responsible for managing our  Page Layout XML

		$layout = Mage::getSingleton('core/layout');	
		
Page Layout XML is one of its jobs.  After getting a reference to the Layout object, we set our newly created simple XML object

		$layout->setXml($xml);

Next, we tell the Layout object to use the Page Layout XML to generate the needed block objects 

		$layout->generateBlocks();			
		
This doesn't create any output.  When you call the <code>generateBlocks</code> method, it goes through your Page Layout XML and creates all the PHP block objects that are needed to generate your layout.  The Page Layout XML **configures** which blocks are used as well as the parent/child relationships between those blocks. 

It's not until we call

		echo $layout->setDirectOutput(true)->getOutput();			

that the <code>toHtml</code> method is called and rendering begins. 

Getting a Little More Complex
--------------------------------------------------
Let's take a look at a layout that's a bit more complex.  Create a new action in the <code>UpdateController.php</code> file, and load its corresponding URL

	#URL: http://magento.example.com/nofrills_booklayout/update/complex
	public function complexAction()
	{		
		$layout = Mage::getSingleton('core/layout');
		$path	= Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
		'page-layouts' . DS . 'complex.xml';
		$xml = simplexml_load_file($path,
		Mage::getConfig()->getModelClassName('core/layout_element'));
		$layout->setXml($xml);
		$layout->generateBlocks();			
		echo $layout->setDirectOutput(true)->getOutput();								
	}

Before we get into the Layout XML itself, there's two new things going on here, both related to how we're loading our XML. First,

	$path	= Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
	'page-layouts' . DS . 'complex.xml';
	$xml = simplexml_load_file($path,
	Mage::getConfig()->getModelClassName('core/layout_element'));

you'll notice we're loading our Page Layout XML from a file rather than passing in a string.  This isn't necessary, but will make it easier for us to examine/add-to the XML.  

The second thing you'll notice is we've replaced the hard coded XML element class

	Mage_Core_Model_Layout_Element

with a call to

	Mage::getConfig()->getModelClassName('core/layout_element')
	
While current versions of Magento use a <code>Mage\_Core\_Model\_Layout\_Element</code>, it's possible that a future version may change this.  Because of that, Magento engineers store and read this class name from a config file.  When possible, it's best to follow the same conventions you see in Magento core code to ensure maximum compatibility with future versions.  Again, this is something you won't need to concern yourself with while **using** the Layout system, rather it's something you'd want to understanding if you're working on extending it. 

Alright!  Let's take a look at the layout we just rendered and the XML that created it, (see *Figure 2.1*)

<img src="images/chapter2/complex.png" />

If you look at the <code>complex.xml</code> file (bundled with the Chapter 2 module code), 

	app/code/local/Nofrills/Booklayout/page-layouts/complex.xml

you'll see the following 

	<layout>			
		<block type="nofrills_booklayout/template" name="root"
		template="simple-page/2col.phtml" output="toHtml">
			<block type="nofrills_booklayout/template" name="additional_head"
			template="simple-page/head.phtml" />
	
			<block type="nofrills_booklayout/template" name="sidebar">
				<action method="setTemplate">
					<template>simple-page/sidebar.phtml</template>
				</action>
			</block>
	
			<block type="core/text_list" name="content" />
	
		</block>
	</layout>
	
Lots of new and interesting things to discuss here.  The first thing you'll notice is that we've added some sub-nodes to our parent block, as well as introduced a new attribute named template.	

	<block type="nofrills_booklayout/template" name="root"
	template="simple-page/2col.phtml" output="toHtml">
		...
	</block>
	
You'll remember that a <code>nofrills\_booklayout/template</code> block is our version of Magento's <code>core/template</code> block.  When your block is a template block, you can specify which template it should use in the template attribute

	template="simple-page/2col.phtml"
	
When you nest blocks in a Page Layout XML tree, it's the equivalent of using the <code>insert</code> method when you're creating them programmatically.  The node structure of the XML mirrors the parent/child relationships you were previously setting up programmatically.  

Depending on how well you're following along (and if you've taken a few days off to digest everything and/or drink heavily), you may be wondering why it's only the top level node that has a <code>output</code> attribute.  How does Magento know how to render the sub-blocks?  The answer, of course, is in your <code>simple-page/2col.phtml</code> template.  

	File: app/code/local/Nofrills/Booklayout/design/simple-page/2col.phtml
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8" />
		<title></title>
		<?php echo $this->getChildhtml('additional_head'); ?>
	</head>
	<body>
		<?php echo $this->getChildhtml('sidebar'); ?>
		<section>
		<?php echo $this->getChildhtml('content'); ?>
		</section>
	</body>
	</html>
	
The phtml template files don't care how their parent blocks have been instantiated, they'll function the same regardless of whether they've been created with PHP code or XML code.  The <code>simple-page/2col.phtml</code> template is still looking for a child block named (in this example) <code>additional_head</code>.  That's why it's important that all your sub block  <code>&lt;block/&gt;</code> elements have names

	name="additional_head"
	name="sidebar"
	name="content"

Action Methods
-------------------------------
Another new node is the <code>&lt;action/&gt;</code> node.  Let's take a look at the sidebar block

	<block type="nofrills_booklayout/template" name="sidebar">
		<action method="setTemplate">
			<template>simple-page/sidebar.phtml</template>
		</action>
	</block>

Here you'll see we're still using a template block, but we've left off the <code>template</code> attribute.  Instead, we've added a sub-node named <code>&lt;action/&gt;</code>.

An <code>&lt;action/&gt;</code> node will allow you to call methods on the block which contains it.  The above node is equivalent to the following PHP code

	$layout = Mage::getSingleton('core/layout');
	$block = $layout->createBlock('nofrills_booklayout/template','sidebar');
	$block->setTemplate('simple-page/sidebar.phtml');
	
You can call **any** public method on a block this way, although some methods won't have any meaning when called from XML.  Here we've used it as an alternate method of setting a template, but the Magento core themes are filled with other practical examples.  Consider the <code>page/html\_head</code> blocks (<code>Mage\_Core\_Block\_Html\_Head</code>).  They contain a number of methods for adding CSS and Javascript files to your page

	<action method="addCss"><stylesheet>css/styles.css</stylesheet></action>
	<action method="addJs"><script>lib/ccard.js</script></action>
	
We'll cover the <code>&lt;action/&gt;</code> node in greater depth later on in Chapter 5.  You also may be interested in Appendix D, which contains a full list, in XML format, of what actions may be called from what blocks.	

References and the Importance of text_lists
--------------------------------------------------
To review: We've rendered out our blank page template again, but this time with XML.  Let's add some content to it.  Edit your <code>complexAction</code> method so it matches the following

	public function complexAction()
	{		
		$layout = Mage::getSingleton('core/layout');
		$path	= Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
		'page-layouts' . DS . 'complex.xml';
		$xml = simplexml_load_file($path,
		Mage::getConfig()->getModelClassName('core/layout_element'));
		$layout->setXml($xml);

		$text = $layout->createBlock('core/text','foxxy')
		->setText("The quick brown fox jumped over the lazy dog.");
		
		$layout->generateBlocks();		
		$layout->getBlock('content')->insert($text);
		
		echo $layout->setDirectOutput(true)->getOutput();								
	}

Just as we were able to in the previous chapter, we obtained a reference to the <code>content</code> block, and inserted a new text block for the page.  It's important to note that we couldn't do this **before** we'd called  <code>generateBlocks</code>. If we tried to, we'd get an error along the lines of 

	Call to a member function insert() on a non-object 
	
because we can't get a reference to a block before it's been created.  Reload the page and you'll see our new content, (see *Figure 2.2*)

<img src="images/chapter2/with-content.png" />


Of course, now we're back to adding things to the layout via PHP.  Wouldn't it be nice if there was a way to get references to blocks via the Page Layout XML? As you might have guessed by our overtly rhetorical tone, The Page Layout XML offers just such capabilities.  At the top level of <code>complex.xml</code> add the node named <code>&lt;reference/&gt;</code> below and give your page a refresh.

	<layout>
		<block type="nofrills_booklayout/template" name="root"
		template="simple-page/2col.phtml" output="toHtml">
	
			<block type="nofrills_booklayout/template" name="additional_head"
			template="simple-page/head.phtml" />
	
			<block type="nofrills_booklayout/template" name="sidebar">
				<action method="setTemplate">
					<template>simple-page/sidebar.phtml</template>
				</action>
			</block>
	
			<block type="core/text_list" name="content" />
	
		</block>
		
	
		<reference name="content">
			<block type="core/text" name="goodbye">
				<action method="setText">
					<text> The lazy dog was only faking it. </text>
				</action>
			</block>
			
		</reference>	
	</layout>

Voila!  Another node added to content.

The <code>&lt;reference/&gt;</code> tag is the other tag that's valid at the top level of a Page Layout XML <code>&lt;layout/&gt;</code> node.  It allows you to get a reference to an existing, named node in the layout.  Placing blocks **inside** the <code>&lt;reference/&gt;</code> node is the equivalent of inserting them. So, the above Page Layout XML reference is equivalent to the following PHP

	$layout 	= Mage::getSingleton('core/layout');
	$content 	= $layout->getBlock('content');
	$text 		= $layout->createBlock('core/text', 'goodbye')
	->setText(' The lazy dog was only faking it. ');
	$content->insert($text);

Now's a good time to remind you that it's the <code>core/text\_list</code> node that makes this insert/auto-render process work.  If we were to get a reference to the top level <code>root</code> node and insert a block, that block wouldn't be rendered unless the <code>root</code> block's template explicitly rendered it.  A <code>core/text\_list</code> block, on the other hand, will **automatically** render any block inserted into it.  This difference in rendering between <code>core/text\_list</code> and <code>core/template</code> blocks is the biggest reason for head scratching layout problems I've seen in the field. 

Layout Updates
--------------------------------------------------
So, we've reached a waypoint in our journey to the depths of Magento's Layout XML system.  We now know how to create individual XML trees which can be used to generate a page layout.  However, it seems like we've swapped one problem for the another.  Instead of having to worry about multiple PHP scripts for each page in our site, now we need to worry about multiple XML files. We've moved laterally, but haven't made much progress on the core problem.

And what good is that reference tag?  It seems like it'd be easier just to add content directly to the block structure.

This brings us to the next piece of the Magento Layout puzzle:  Layout Updates.

What's an Update
--------------------------------------------------
Updates are fragments of XML that are added to a layout object one at a time.  These fragments are then processed for special instructions and combined into a Page Layout XML tree.  The Page Layout XML tree (which we covered in the first half of this chapter) then renders the page.  

By allowing us to build Page Layouts using these chunks of XML, Magento encourages splitting layouts up into logical components which can then be used to build a variety of pages. If that was a bit abstract and hard the follow, our code samples should clear things up.

We'll rely on our trusty hello world block to lead the way.  Add the following action to our <code>UpdateController.php</code> file.

	#http://magento.example.com/nofrills_booklayout/update/helloUpdates
	public function helloUpdatesAction()
	{
		$layout 		= Mage::getSingleton('core/layout');
		$update_manager = $layout->getUpdate();			
		$update_manager->addUpdate( '<block
		type="nofrills_booklayout/helloworld" 
		name="root" 
		output="toHtml" />');			
		$layout->generateXml();
		$layout->generateBlocks();			
		echo $layout->setDirectOutput(true)->getOutput();								
	}

Load the page, and you'll once again see your hello world block.

The three new lines we're interested in above are 

	$update_manager = $layout->getUpdate();			
	$update_manager->addUpdate('<block 
	type="nofrills_booklayout/helloworld" 
	name="root" 
	output="toHtml" />');			
	$layout->generateXml();

These replace the manual loading of our page layout that we did above.  First, a Layout object contains a reference to a <code>Mage\_Core\_Model\_Layout\_Update</code> object.  This object is responsible for managing and holding the individual XML chunks that we're calling updates.

###What's a "Model"

You may be wondering why both the Layout and this new Update Manager objects are models, even though they don't read/write to/from a database.  If you've used PHP based MVC systems in the past, you've probably become accustomed to the idea that a <code>Model</code> is an object that represents a table of data in a SQL database, or perhaps even **multiple tables**.  While that's become one common understanding of the term, the original meaning of Model in MVC was the computer science term *Domain Model*.

The Domain Model is an abstract concept.  It's where you describe the concepts and vocabulary of the problems you're trying to solve in code.  It's sometimes referred to as business logic, or the objects that you use when writing business logic code.

The "Un-Domain Model" portions of a project are things like the code that runs your controller dispatching, or the code that renders a template.  This is code you might use on any projects for any number of companies, each with their own Domain Model.  

Another way of thinking about this might be a school.  Teachers, students, classes, which classes are in each room; these things are all the Domain Model of a School.  The non Domain Model would then be the school building itself, its plumbing and boiler, etc.

We mention this here because much of the Magento model layer can be thought of in the more recent, "Models are data in a database way".  The layout and update hierarchy, however, cannot.  A layout and an update object are both models in the Domain Model sense of the word.  They are modeling the "business rules" of creating HTML pages.  This can be particularly confusing with the update object, as a single update object will be used to manage multiple Layout Update XML fragments.  That's why we're calling this object an Update Manager

	$update_manager = $layout->getUpdate();			
	
Adding our Updates
--------------------------------------------------
So, another little detour into Computer Science 101 out of the way, and we're left with the following two lines

	$update_manager->addUpdate('<block 
	type="nofrills_booklayout/helloworld" 
	name="root" 
	output="toHtml" />');			
	$layout->generateXml();
	
Here we're adding a single XML update that is our hello world block.  Once we've done that, we then tell our Layout object to generate its own Page Layout XML tree.  You may be a little confused, as it appears we've never told our **layout object** about the the updates. Remember, this is object oriented programming.  Our update object is already a part of the Layout object.  When we said 

	$update_manager = $layout->getUpdate();	
			
we got a reference to the update object, but it's **still a part of the layout object**.  So when we add a chunk of XML via the Update object, the Layout automatically knows about it.  

Our call to the <code>generateXml</code> method is roughly equivalent to our previous call that looked like

	$layout->setXml($xml);
	
When you tell a layout object to generate its XML, it will

1. Combine all the chunks of Update XML into a single tree by concatenating them under a top level <code>&lt;layout&gt;</code> node

2. Do some additional processing of the nodes (see "Removing Blocks" below)

3. Set this new tree as the Layout's XML.  In other words, set it as the Page Layout XML.

Fully Armed and Operational References
--------------------------------------------------
In this context, references start to make more sense.  Let's take a look at ReferenceController.php to see some more examples.

	#File: app/code/local/Nofrills/Booklayout/controllers/ReferenceController.php
	#URL:  http://magento.example.com/nofrills_booklayout/reference
	class Nofrills_Booklayout_ReferenceController 
	extends Mage_Core_Controller_Front_Action
	{
		
		/**
		* Use to set the base page structure
		*/	
		protected function _initLayout()
		{
			$path_page = Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
			'page-layouts' . DS . 'page.xml';					
			$xml = file_get_contents($path_page); 	
			$layout = Mage::getSingleton('core/layout')
			->getUpdate()
			->addUpdate($xml);			
		}
	
		/**
		* Use to send output 
		*/		
		protected function _sendOutput()
		{
			$layout = Mage::getSingleton('core/layout');
			
			$layout->generateXml()
			->generateBlocks();
			
			echo $layout->setDirectOutput(false)->getOutput();
		}
		
		
		public function indexAction()
		{
			$this->_initLayout();
			$this->_sendOutput();
		}
	}
	
	
If you load the above URL, you'll get our basic, but complete, page layout from previous examples. 

First off, let's cover a slight change it our approach. There's two protected methods on this controller

1. <code>\_initLayout</code>
2. <code>\_sendOutput</code>

The <code>\_initLayout</code> method we've used before.  This is where we'll setup a base Layout object, to which our primary controller action can add blocks.  We're also loading up a new file, <code>page.xml</code> (included with the Chapter 2 module). 

The <code>\_sendOutput</code> method centralizes the code we've been using to render a layout object once we're done manipulating it.  By centralizing these functions, all we need to do in our controller action is something like 

	public function indexAction()
	{
		$this->_initLayout();		
		//...add additional updates here...		
		$this->_sendOutput();
	}
	
Before we get deep into that, let's take a look at the code that's loading our layout in <code>\_initLayout</code>

	protected function _initLayout()
	{
		$path_page = Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
		'page-layouts' . DS . 'page.xml';							
		$xml = file_get_contents($path_page); 	
		
		$layout = Mage::getSingleton('core/layout')
		->getUpdate()
		->addUpdate($xml);			
	}

Here you can already see some of the efficiencies that updates have brought us.  We no longer need to worry about creating/adding the right type of simple XML object.  We can store our base XML fragment in a file, 

	<!-- #File: app/code/local/Nofrills/Booklayout/page-layouts/page.xml -->
	<block type="nofrills_booklayout/template" name="root" 
	template="simple-page/2col.phtml" output="toHtml">
		<block type="nofrills_booklayout/template" name="additional_head"
		template="simple-page/head.phtml" />

		<block type="nofrills_booklayout/template" name="sidebar">
			<action method="setTemplate">
				<template>simple-page/sidebar.phtml</template>
			</action>
		</block>

		<block type="core/text_list" name="content" />
	</block>
	
and then just pass it to the update object as a string.  You'll notice there's no surrounding <code>&lt;layout&gt;</code> node for Layout Update XML **fragments**.  Instead, we pass in the block nodes we want at the top level of our eventual Page Layout file.

So, with the basic page structure for our layout set, we're ready to add in our custom blocks.  It's only now that reference blocks show their true power.  Consider the <code>indexAction</code>, and then load up the controller URL

	#URL:  http://magento.example.com/nofrills_booklayout/reference
	public function indexAction()
	{
		$this->_initLayout();
		Mage::getSingleton('core/layout')
		->getUpdate()
		->addUpdate('<reference name="content">
			<block type="core/text" name="our_message">
				<action method="setText"><text>Here we go!</text></action>
			</block>
		</reference>');
		$this->_sendOutput();
	}

You should see the content area with the text "Here we go!".

What <code>&lt;reference/&gt;</code> nodes allow us to do is **alter** elements that have already been added to a layout elsewhere. This allows us to write our structural Page Layout XML once, and then have different controller actions insert the different blocks they need. 

Next, try adding the following methods to the controller

		protected function _loadUpdateFile($file)
		{
			$path_update = Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
			'content-updates' . DS . $file;			
			
			$layout = Mage::getSingleton('core/layout')
			->getUpdate()
			->addUpdate(file_get_contents($path_update));					
		}
		
		#URL: http://magento.example.com/nofrills_booklayout/reference/fox
		public function foxAction()
		{
			$this->_initLayout();
			$this->_loadUpdateFile('fox.xml');
			$this->_sendOutput();
		}

The <code>\_loadUpdateFile</code> method will load an XML Update from our module's "content-updates" folder.  This allows us a simple three line controller action to load up content for any particular controller action/URL.  Consider these other actions, <code>ceaser</code> and <code>dog</code>

		#URL: http://magento.example.com/nofrills_booklayout/reference/dog
		public function dogAction()
		{
			$this->_initLayout();		
			$this->_loadUpdateFile('dog.xml');
			$this->_sendOutput();
		}
		
		#URL: http://magento.example.com/nofrills_booklayout/reference/ceaser
		public function ceaserAction()
		{	
			$this->_initLayout();
			$this->_loadUpdateFile('ceaser.xml');
			$this->_sendOutput();
		}

We could even take this a step further.  Consider the following method in place of <code>\_loadUpdateFile</code>.

		protected function _loadUpdateFileFromRequest()
		{		
			$path_update = Mage::getModuleDir('', 'Nofrills_Booklayout') . DS . 
			'content-updates' . DS . $this->getFullActionName() . '.xml';		
			
			$layout = Mage::getSingleton('core/layout')
			->getUpdate()
			->addUpdate(file_get_contents($path_update));				
		}

and an adjustment made in the <code>foxAction</code> method.

		#URL: http://magento.example.com/nofrills_booklayout/reference/fox
		public function foxAction()
		{
			$this->_initLayout();
			$this->_loadUpdateFileFromRequest();
			$this->_sendOutput();
		}

Load the <code>foxAction</code> URL, and you'll see a warning something like this.

	Warning: file_get_contents(/mage/path/app/code/local/Nofrills/Booklayout/content-
	updates/nofrills_booklayout_reference_fox.xml) 
	[function.file-get-contents]: failed to open stream: No such file 
	or directory

The <code>\_loadUpdateFileFromRequest</code> method attempts to load up an XML update from the file <code>nofrills\_booklayout\_reference_fox.xml</code>.  This filename is created using controller method <code>$this->getFullActionName()</code>.  The "Full Action Name" is a string that combines, via underscores, the lowercase versions of 

- Module Name: <code>nofrills\_booklayout</code>
- Controller Name: <code>reference</code>
- Action Name: <code>fox</code>

It's essentially a name that allows us to uniquely identify any request that comes into Magento based on these three criteria.  Let's create a file for our new method to load

	<!-- #File: app/code/local/Nofrills/Booklayout/content-updates/
	nofrills_booklayout_reference_fox.xml -->	
	<reference name="content">
		<block type="core/text" name="our_message">
			<action method="setText"><text>
				Magento is a foxy system.
			</text></action>
		</block>
	</reference>	
	
Reload the page, and you'll see our new content block. 

Removing Blocks
--------------------------------------------------
As previously mentioned, when we call the <code>generateXml</code> method on the layout object, it does the following

1. Combines all the chunks of Update XML into a single tree by concatenating them under a top level <code>&lt;layout&gt;</code> node

2. Does some additional processing of the nodes

3. Sets this new tree as the Layout's XML.  In other words, set it as the Page Layout

So, in our examples above, that means we end up with a Page Layout that looks something like this

	<layout>
		<!-- update loaded from page.xml -->
		<block type="nofrills_booklayout/template" name="root"
		template="simple-page/2col.phtml" output="toHtml">
			<block type="nofrills_booklayout/template" name="additional_head"
			template="simple-page/head.phtml" />
	
			<block type="nofrills_booklayout/template" name="sidebar">
				<action method="setTemplate">
					<template>simple-page/sidebar.phtml</template>
				</action>
			</block>
	
			<block type="core/text_list" name="content" />
		</block>	
		
		<!-- update loaded from nofrills_booklayout_reference_fox.xml -->
		<reference name="content">
			<block type="core/text" name="our_message">
				<action method="setText"><text>
					Magento is a foxy system.
				</text></action>
			</block>
		</reference>				
	</layout>
	
The step we haven't covered yet is #2

> Do some additional processing of the nodes

After concatenating all the updates into a single XML tree, but before assigning that tree as the Page Layout XML, Magento will process the concatenated tree for additional directives.  As of Community Edition 1.4.2, the only other directive supported is <code>&lt;remove/&gt;</code>.  

Let's give the remove directive a try.  Alter your <code>nofrills\_booklayout\_reference\_fox.xml</code> to include a <code>&lt;remove/&gt;</code> tag, as below.

	<reference name="content">
		<block type="core/text" name="our_message">
			<action method="setText"><text>
				Sidebar?  We don't need a sidebar!
			</text></action>
		</block>
	</reference>
		
	<remove name="sidebar" />
	
Reload your URL

	http://magento.example.com/nofrills_booklayout/reference/fox
	
and you should see a page **without** the block named sidebar, which was rendering our navigation.  

###Before (*Figure 2.3*)

<img src="images/chapter2/before.png" />

###After (*Figure 2.4*)

<img src="images/chapter2/after.png" />

Remove instructions are processed in the <code>Mage\_Core\_Model\_Layout::generateXml</code> method.  This method 

1. Combines all updates with a call to <code>$xml = $this->getUpdate()->asSimplexml();</code>

2. Looks through the combined updates for any nodes named <code>remove</code>.

3. If it finds a <code>remove</code> node, it then takes that node's name and looks for any <code>block</code> or <code>references</code> nodes with the same name.  

4. If it finds any <code>blocks</code> or <code>references</code>, these nodes are marked with an <code>ignore</code> attribute.

5. The <code>remove</code> blocks are ignored during the Layout rendering process.  Their job is to mark which nodes should be ignored.  After that, they're irrelevant

Once the remove instructions have been processed, the resulting tree is set as the Page Layout.

This means in our most recent example we ended up with a Page Layout XML tree that looked exactly the same as before, with one exception

	<block type="nofrills_booklayout/template" name="sidebar" ignore="1">
	
When a <code>&lt;block/&gt;</code> or <code>&lt;reference/&gt;</code> has an <code>ignore="1"</code> attribute node, the Layout rendering process will **skip** that block.  In this way, the block, and all its sub-blocks, are removed from the final rendered page.   	

What's Next
--------------------------------------------------
So, we've now covered how to create and manage Page Layouts via XML files.  We've also explored Magento's "Update" mechanism, which allows us to build up Page Layout XML files via individuals Layout Update XML fragments, allowing for modular page layouts.

The final problems we need to solves are 

1. How should we store **all** the layout update for our system
2. How should we automatically **load** these layout files into the system

So far we've been using individual <code>\_init</code> methods in our controllers.  While this has offered us more modularity that previous methods, this will still get unwieldy as the number of controllers and actions grows.  Plus, there's still the sub-problem of how to create a method of doing this that allows back-end PHP developers and front-end PHP developers the ability to go about their jobs without crossing paths.  

The answer to this question, and the final large topic we need to cover, is the Package Layout.  

*Visit http://www.pulsestorm.net/nofrills-layout-chapter-two to join the discussion online.*