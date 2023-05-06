Class Aliases
==================================================	
Magento uses a factory pattern for instantiating certain objects. Don't let the design patterny name scare you though, it's not that complicated.

In raw PHP, if you wanted to instantiate an object from a class, you'd say something like

	$customer = new Product();
	
There's nothing in Magento stopping you from doing this.  However, most of the Magento core code and its various sub-systems do things a little differently.

In Magento, when you want to instantiate an object from a class, you use code like this

	$customer = Mage::getModel('catalog/product');
	
This is calling a static method on the <code>Mage</code> class named <code>getModel</code>.  This method will examine Magento's configuration, and ask 

> What model class does the string <code>catalog/product</code> associate with. 

Magento will answer back <code>"Mage\_Catalog\_Model\_Product"</code>, and then a <code>"Mage\_Catalog\_Model\_Product"</code> will be instantiated. This <code>catalog/product</code> string is known as the class alias. 

Magento uses this instantiation method for 

1. Block classes:  <code>$layout->createBlock('foo/bar')</code>
2. Helper classes: <code>Mage::helper('foo/bar')</code>
3. Model classes: <code>Mage::getModel('foo/bar'),Mage::getModel('foo/bar')</code>

The <code>createBlock</code>, <code>helper</code>, and <code>getModel</code> methods are all factories.  They make objects or a particular type.

Why so Complicated?
--------------------------------------------------
This may seem like a lot of misdirection for something as simple as a class declaration, but that misdirection brings some benefits along for the ride.  It helps create a type system around classes, Magento itself knows what classes have or have not been declared at any one time, the shorthand saves some verbosity in typing, and it helps enable one of Magento's unique PHP feature, class rewrites (similar to duck-typing or monkey-patching in the Ruby and Python communities)

What Class?
--------------------------------------------------
This is all well and good, but can sometimes leave you wondering what class alias corresponds to what class definition.  The easiest thing to do is use the free, online demo of Commerce Bug

	http://commercebugdemo.pulsestorm.net/
	
The class URI lookup tab will let you lookup which class aliases correspond to which PHP classes for a core system.

The way Magento actually looks up class definitions is via its configuration system.  All the <code>config.xml</code> files in a Magento install are merged into one, large, global config.  This giant tree contains a top level <code>&lt;global/&gt;</code> node that looks something like this

	<config>
	    <global>
    	    <models>...</models>
    	    <helpers>...</helpers>
    	    <blocks>...</blocks>
    	</global>
    </config>
    
The first thing Magento does when you use a class alias to instantiate a class is determine the context (model, helper, block), and then look in an appropriate node (<code>&lt;models&gt;</code>, <code>&lt;helpers&gt;</code>, and <code>&lt;blocks&gt;</code>).

Next, each of the <code>&lt;models&gt;</code>, <code>&lt;helpers&gt;</code>, and <code>&lt;blocks&gt;</code> contains a number of "group" nodes

	<models>
		<catalog>...</catalog>
		<core>...</core>
		<page>...</page>
	</models>
	
If you look at a class alias

	catalog/product
	
The portion to the left of the <code>/</code> is the group name. Magento will use this to determine which of the group nodes it should look in next.

Finally, each group node contains, at minimum, a class node <code>&lt;class&gt;</code>

	<models>
		<catalog>
            <class>Mage_Catalog_Model</class>        
        </catalog>	
	</model>
	
This node contains the **base** PHP class name for the model (or helper, or block) group.  This base name in place, the non-group portion of the class alias is appended to the base class name, with the first letter of each underscored word uppercased

	catalog/product
	Mage_Catalog_Model_Product
	
	catalog/product_review
	Mage_Catalog_Model_Product_Review
	
That's how Magento resolves which PHP class to use for a class alias.  

Class Rewrites
--------------------------------------------------
There's one additional node in the config that Magento will check while looking up a class name.  End users of the system (that means you) may provide a <code>&lt;rewrite/&gt;</code> node that will tell Magento to replace one class with another.  This is Magento's famous class rewrite system.  Using the following

	<models>
	  <catalog>
	    <rewrite>
		  <product_review>Yourpackage_Yourmodule_Model_Someclass</product_review>
		</rewrite>
      </catalog>	
	</model>

would tell Magento that whenever a <code>catalog/product_review</code> is instantiated, is should use a <code>Yourpackage\_Yourmodule\_Model\_Someclass</code>.

*Visit http://www.pulsestorm.net/nofrills-layout-appendix-b to join the discussion online.*