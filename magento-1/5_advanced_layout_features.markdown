Advanced Layout Features
==================================================	
If you've absorbed the previous chapters of information, you have everything you need to be a skillful practitioner of Magento layouts.  Next we're going to cover some advanced features of the Layout system.  We'll be moving pretty fast so if you're getting frustrated stop, take a deep breath, and remember that this isn't hard, it's just different.  Once we're through, you'll be a true layout master.

**Note:** If you came here from Chapter 3, be sure to turn off the <code>nofrills\_layoutbook</code> theme.

Action Parameters
--------------------------------------------------
We've already covered calling Action Methods, but let's quickly review. During the loading of the layout and instantiation of the block object, the XML below

	<block type="some/foo" name="some_block">
		<action method="someMethod">
			<param1>a value</param1>
			<param2>27</param2>			
		</action>
	</block>

would run code something like the following 

	$block = new Mage_Some_Block_Foo();
	$block->someMethod('a value','27');
	
There are, however, a few extra features you can tap into while calling methods via blocks.  Let's take a look.

Translation System
--------------------------------------------------
Magento ships, out of the box, with a <code>gettext</code> like translation system.  This system allows you to define a number of symbols (usually the English language version of a phrase), and then conditionally swap in a translated phrase.  By default the action method parameters aren't run through this system, but it's possible (on an action by action basis) to tell Magento to translate certain parameters.  

To indicate a parameter should be translated, you'd do something like

	<action method="someMethod" translate="param1" module="core">
		<param1>a value</param1>
		<param2>27</param2>			
	</action>
		
We've added two parameters to the <code>&lt;action/&gt;</code> node here. The first is 

	translate="param1"
	
This tells Magento we want to run the <code>&lt;param1/&gt;</code> parameter through the translation engine.  In this case, that's the string "a value".  This is the reason each parameter is an extra node in the tree, it allows us to identify strings that need translation.  If you want to translate more than one parameter, the attribute will accept multiple names

	translate="param1 param2"
	
Next, we have 

	module="core"
	
This tells Magento which module's data helper should be used to translate our strings.  Each module has (or should have) a helper class named Data.

	Mage_Core_Helper_Data
	Mage_Catalog_Helper_Data
	
This helper can be instantiated via a call to the static helper method on the Mage object	

	Mage::helper('core');		//shortcut for the one below
	Mage::helper('core/data');
	
It's this helper object that has the translation function

	$h = Mage::helper('core');
	$hello = $h->__('Hello');
	
	//in the above scenario "$hello" might contain
	//the string "Hola" if the spanish locale was loaded
	
The reason you need to specify a module for the translation helper is, each module can contain **its own** translations.  This allows different modules to translate their own symbols slightly differently based on context. 	

Conditional Method Calling
--------------------------------------------------
Another attribute you may see in the <code>&lt;action/&gt;</code> node is <code>ifconfig</code>.

	<block type="page/html_head" name="head" as="head">
		<action method="addJs" ifconfig="dev/js/deprecation">
			<script>prototype/deprecation.js</script>
		</action>
	</block>
	
This attribute can be used to tell Magento to **conditionally** call the specified method. The above XML is equivalent to PHP code something like

	$block = new Mage_Page_Block_Html_Head();
	if(Mage::getStoreConfigFlag('dev/js/deprecation'))
	{
		$block->addJs('prototype/deprecation.js');
	}

That is, when you use the <code>ifconfig</code> attribute, you're telling Magento 

> Only make the following method call if the following System Configuration Variable returns true

System Configuration variables can be set in the Admin Console under 

	System -> Configuration
	
See Appendix I for more information of using the System Config system.

The <code>ifconfig</code> attribute is a powerful feature you can use to allow end users to selectively turn certain layout features on or off.  You can also use it to display different layout states based on the existing System Configuration values.  

Dynamic Parameters
--------------------------------------------------

Magento also has the ability to pass **dynamic** parameters via Layout Update XML.  Normally, parameter values need to be fixed values

	<action method="someMethod" translate="param1" module="core">
		<param1>a value</param1>
		<param2>27</param2>			
	</action>

Above we're passing in the fixed values

	a value
	27
	
However, consider the following alternate syntax.

	<action method="addLink" translate="label title" module="customer">
		<label>My Account</label>
		<url helper="customer/getAccountUrl"/>
		<title>My Account</title>
		<prepare/>
		<urlParams/>
		<position>10</position>
	</action>

Here we're passing in three fixed values

	<label>My Account</label>
	...
	<title>My Account</title>	
	...
	...
	<position>10</position>
	
We're also passing in two null values
	
	...
	...
	...
	<prepare/>
	<urlParams/>
	...
	
But there's one final parameter we're using with a syntax we haven't seen before

	...
	<url helper="customer/getAccountUrl"/>
	...
	...
	...
	...
	
This <code>url</code> parameter tag is fetching data dynamically using Magento's helper classes.  When Magento encounters an action parameter with a helper attribute, it

1. Splits the helper by the last "/"
2. The first part of the split is used to instantiate the helper
3. The second part of the split is used as a method name
4. A helper is instantiated and the method from step #3 is called. 
5. The value returned by the method is used in the action method call

So, that means the above XML translates into PHP code something like;
	
	$block;		//the block object
	$h 			= Mage:helper('customer'); //instantiate the customer data helper
	$url		= $h->getAccountUrl();
	$block->addLink('My Account',$url,'My Account',null,null,10);

Magento examines the <code>helper</code> attribute and splits off <code>getAccountUrl</code> to use as a method, leaving <code>customer</code> to be used to instantiate the helper class.  The helper is instantiated and <code>getAccountUrl</code> is called. The value returned from this method is then used as the parameter to pass to <code>addLink</code>.   

The above example uses the shorthand "data" helper format, but fear not. You can use **any** helper class alias to return a value. Consider the following example

	<action method="addLink" translate="label title" module="catalog"
	ifconfig="catalog/seo/site_map">
		<label>Site Map</label>
		<url helper="catalog/map/getCategoryUrl" />
		<title>Site Map</title>
	</action>

Here we're instantiating a <code>catalog/map</code> helper and calling its <code>getCategoryUrl</code> method.  The value which <code>getCategoryUrl</code> returns will be used in the call to the <code>addLink</code> method.

This powerful feature is the missing link for layout programming.  The ability to call into blocks with dynamic data parameters unlocks a world of potential for developers and designers alike. 

Ordering of Blocks
--------------------------------------------------

Next up we have block ordering.  We'll be working in the following controller action

	#File: app/code/local/Nofrills/Booklayout/controllers/OrderController.php
	class Nofrills_Booklayout_OrderController 
	extends Mage_Core_Controller_Front_Action
	{
		public function indexAction()
		{
			$this->loadLayout();
			$this->renderLayout();			
		}
	}

Which corresponds to the URL

	http://magento.example.com/nofrills_booklayout/order	

Consider the following <code>&lt;nofrills\_booklayout\_order\_index&gt;</code> update handle for our controller action.  You should know how to add this to your system by now, but if you don't putting it in your <code>local.xml</code> will do.  Review the previous chapters if you're unsure where <code>local.xml</code> is.

	<nofrills_booklayout_order_index>
		<reference name="content">
			<block type="core/text" name="one">
				<action method="setText">
					<text><![CDATA[<p>One</p>]]></text>
				</action>
			</block>
			<block type="core/text" name="two">
				<action method="setText">
					<text><![CDATA[<p>Two</p>]]></text>
				</action>
			</block>
			<block type="core/text" name="three">
				<action method="setText">
					<text><![CDATA[<p>Three</p>]]></text>
				</action>
			</block>
			<block type="core/text" name="four">
				<action method="setText">
					<text><![CDATA[<p>Four</p>]]></text>
				</action>
			</block>
			<block type="core/text" name="five">
				<action method="setText">
					<text><![CDATA[<p>Five</p>]]></text>
				</action>
			</block>			
			<block type="core/text" name="six">
				<action method="setText">
					<text><![CDATA[<p>Six</p>]]></text>
				</action>
			</block>						
			<block type="core/text" name="seven">
				<action method="setText">
					<text><![CDATA[<p>Seven</p>]]></text>
				</action>
			</block>							
			<block type="core/text" name="line">
				<action method="setText">
					<text><![CDATA[<hr/>]]></text>
				</action>
			</block>													
		</reference>
	</nofrills_booklayout_order_index>

Loading up our page with this bit of Layout Update XML in place will give us a simple ordered list of paragraphs, followed by a line, (see *Figure 5.1*) 

<img src="images/chapter5/order-base.png" />

(We're using <code>&lt;![CDATA[&lt;hr/&gt;]]&gt;</code> nodes for our <code>setText</code> parameter. This allows us to insert HTML.)

Once you've got the above working, change your Layout Update XML such that an extra attribute named <code>before</code> is added to the block named line

	<block type="core/text" name="line" before="two">
		<action method="setText"><text><![CDATA[<hr/>]]></text></action>
	</block>								

Refresh your page.  The <code>&lt;hr/&gt;</code> element should now be rendered in between the "One" and "Two" paragraph, (see *Figure 5.2*) 

<img src="images/chapter5/order-before-two.png" />


In plain english, the block <code>line</code> was inserted <code>before</code> the block <code>two</code>.  There's also a corresponding after parameter.

	<block type="core/text" name="line" after="six">
		<action method="setText"><text><![CDATA[<hr/>]]></text></action>
	</block>								

Reload your page with the above in place, and your line block should render between six and seven.  If you want a block to be inserted last, just use

	<block type="core/text" name="line" after="-">

If, however, you want your block to be inserted first, use

	<block type="core/text" name="line" before="-">
	
The <code>before</code> and <code>after</code> attributes are most useful when you're inserting blocks into an existing set.  For example, with the above in place, we might have another Layout Update XML node somewhere that looked like

	<reference name="content">
		<block type="core/text" name="fakeline" after="four">
			<action method="setText">
				<text><![CDATA[<div style="border-color:black;
				border-style:solid;border-top:1px;width:300px;"></div>]]>
				</text>
			</action>
		</block>
	</reference>

Assuming blocks one - seven had already been inserted, this bit of Layout Update XML would ensure your new block was inserted <code>after</code> the block named <code>four</code>.  This feature makes working with a package or theme's default layout blocks far easier.

Reordering Existing Blocks
--------------------------------------------------
One thing that trips people up when dealing with block ordering is, you can only control where an individual block is inserted **at the time of insertion**.  Once you've inserted a block into the layout, it's "impossible" to change where it's rendered via the Layout XML files alone.

If you wanted to re-order a block that was already inserted, sometimes you can get away with removing it via the <code>unsetChild</code> method, and then reinserting it at the desired location

	<reference name="content">
		<action method="unsetChild"><name>one</name></action>		
		<block type="core/text" name="one" after="-">
			<action method="setText"><t>one</t></action>
		</block>
	</reference>

While this will sometimes work, if the block you're removing had children, or has data parameters set by other parts of the layout, you'll need to reset them after reinserting the block.  This makes the unset/re-insert method perilous at best, and should only be considered when all other options have been exhausted.

Template Blocks Need Not Apply
--------------------------------------------------
The <code>before</code> and <code>after</code> attributes work due to the way <code>core/text_list</code> blocks automatically render their children

	class Mage_Core_Block_Text_List extends Mage_Core_Block_Text
	{
		protected function _toHtml()
		{
			$this->setText('');
			foreach ($this->getSortedChildren() as $name) {
				$block = $this->getLayout()->getBlock($name);
				if (!$block) {
					Mage::throwException(Mage::helper('core')
					->__('Invalid block: %s', $name));
				}
				$this->addText($block->toHtml());
			}
			return parent::_toHtml();
		}
	}

The important line here is 

	foreach ($this->getSortedChildren() as $name) {
	
This code is <code>foreach</code>ing over a list of **sorted** children.  If you climb the chain back up to the <code>Mage\_Core\_Block\_Abstract</code>	class, you can see that Magento keeps track of both children blocks, as well as a a sorted array of children

    /**
     * Contains references to child block objects
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Sorted children list
     *
     * @var array
     */
    protected $_sortedChildren = array();

So, while a <code>core/template</code> has this list of sorted children, the <code>before</code> and <code>after</code> attributes have no influence on a template block, as the order there is determined by where <code>$this->getChildHtml(...)</code> is called in the <code>phtml</code> template.

While it's beyond the scope of this book, an enterprising extension developer could probably create a class rewrite that would add a method to <code>core/text\_list</code> blocks allowing for an explicit reorder of the <code>$_sortedChildren</code> array. I wouldn't be surprised to see the feature crop up in a future version of Magento.

Block Name vs. Block Alias
--------------------------------------------------
There's one last block attribute we need to talk about, and that's the <code>as</code> attribute.

	<block type="sales/order_info" as="info" name="sales.order.info"/>

A block's name attribute defines its unique name in the layout object.  If present, the <code>as</code> attribute will define the block's alias in the layout.  If an alias is defined, you still interact with a block programmatically via its name.  The only time you use an alias is when rendering the block in a template.  For example, the above block's parent renders it with the following

	<?php $this->getChildHtml('info'); ?>
	
This allows someone programming Layout XML Updates to insert a different block to be rendered **without** changing the template. If you take a look at the <code>insert</code> method

	#File: app/code/core/Mage/Core/Block/Abstract.php
    public function insert($block, $siblingName='', $after=false, $alias='')
    {
		//...
		if ($block->getIsAnonymous()) {
			$this->setChild('', $block);
			$name = $block->getNameInLayout();
		} elseif ('' != $alias) {
			$this->setChild($alias, $block);
			$name = $block->getNameInLayout();
		} else {
			$name = $block->getNameInLayout();
			$this->setChild($name, $block);
		}
		//...
	}
you can see if an alias is used that's the value that will be used to set the child block (and therefore the "template name").  Otherwise, Magento defaults to using the the block's name in the layout.

Block aliases are a feature you may never personally use, but recent versions of Magento have made **heavy** use of them to overcome some earlier design decisions. You'll want to make sure you're aware of the difference between an alias and name, even if you never use an alias in your own updates.

Skipping a Child
--------------------------------------------------
We've already covered the <code>getChildHtml</code> method in a previous chapter.  However, it has a cousin method named <code>getChildChildHtml</code>.  This method is also defined on the <code>Mage\_Core\_Block\_Abstract</code> class

    public function getChildChildHtml($name, $childName = '', $useCache = true,
    $sorted = false)
    {
        if (empty($name)) {
            return '';
        }
        $child = $this->getChild($name);
        if (!$child) {
            return '';
        }
        return $child->getChildHtml($childName, $useCache, $sorted);
    }

You use this method from a <code>phtml</code> template, and it might look something like

	<?php echo $this->getChildChildHtml('my_child', 'foo'); ?>
	
The <code>getChildHtml</code> method will render out the specified child.  The <code>getChildChildHtml</code> method obtains a reference to the first child block (<code>my_child</code> above), and then calls <code>getChildHtml</code> on it.  

This method is most useful when you're editing a <code>phtml</code> template and don't want to restructure your blocks.  I personally haven't found much use for it, but you will see it used in the wild and in the core, so it's worth knowing about.  The most typical use is to render a <code>core/template</code> block as though it was a <code>core/text_list</code> block.

*Visit http://www.pulsestorm.net/nofrills-layout-chapter-five to join the discussion online.*