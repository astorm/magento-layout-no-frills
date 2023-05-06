The Package Layout
==================================================	
We're finally getting closer to fully understanding the Magento layout rendering process.  At the end of the last chapter, we stated that the final puzzle pieces were

1. How should we store **all** the layout update for our system
2. How should we **load** these layout files into the system

The approach Magento has taken is to introduce **another** XML tree, this one called the *Package Layout*.  The Package Layout is an XML tree which contains any number of Layout XML Update Fragments. The Package Layout contains **all the updates fragments** that the **entire application** might want to use.

The top level node in the package layout is named <code>&lt;layouts/&gt;</code>.  

	<layouts>
		<!-- ... --->
	</layouts>

Make note of the plural, layout<strong>s</strong>.  This is different from the top level singular  <code>&lt;layout/&gt;</code> node of the Page Layout XML tree you were building in previous chapters.  That's because, as mentioned, this is a **different** XML tree.

The second level nodes of the Package Layout are not <code>blocks</code>, or <code>references</code>, or **any** tag we've seen so far.  The second level nodes are something new, called *Handles*.  Each handle node contains a single XML Update Fragment.

	<layouts>
		<handle_name>
			<!-- ... XML Update Fragment --->
		</handle_name>
		
		<handle_name2>
			<!-- ... XML Update Fragment --->
		</handle_name2>
		
		<handle_name3>
			<!-- ... XML Update Fragment --->
		</handle_name3>
		
		<handle_name>
			<!-- ... XML Update Fragment --->
		</handle_name>		
		
		<default> <!-- default is an actual handle name from the system-->
			<!-- ... -->
		</default>
		
		<catalog_product_send> <!-- another real name -->
			<!-- ... -->
		</catalog_product_send>
		
		
		<!-- etc. -->
	</layouts>
	
Handle names may be repeated, but before we discuss what they mean, lets discuss how they're loaded into the system.

The Why and Where of the Package Layout
--------------------------------------------------
This collection of Layout Update XML nodes is called the Package Layout because it contains every possible Layout Update XML fragment that might be used in a particular design package. 

Jumping back a few chapters, you'll remember that Magento stores its theme templates in the following location

	[BASE DESIGN FOLDER]/[AREA FOLDER]/[DESIGN PACKAGE FOLDER]/[THEME FOLDER]/template

Magento also stores its layout files in a similar location.

	[BASE DESIGN FOLDER]/[AREA FOLDER]/[DESIGN PACKAGE FOLDER]/[THEME FOLDER]/layout
	
Magento will look for layout files in this folder first.  If it doesn't find a specific layout file here, it will check the base fold at 
	
	[BASE DESIGN FOLDER]/[AREA FOLDER]/base/default/layout
	
See Appendix E for more information on the <code>base</code> folder.

So, that's the **folder** where layout files are stored.  Where does Magento get the name of individual layout files?  Every individual code module in Magento has a <code>config.xml</code>.  In this file, there's a node at

	<frontend>  <!-- frontend is the "area" name. -->
        <layout>
            <updates>
                <section>
                    <file>section.xml</file>
                </section>
                <anysection>
                    <file>anysection.xml</file>
                </anysection>                
            </updates>
        </layout>
	</frontend>

On each request, Magento will scan the config for any XML files located in the  <code>&lt;updates&gt;</code> node.  These filenames will be the files Magento will attempt to load up as the Package Layout.  The code that does this can be found in 

	app/code/core/Mage/Core/Model/Layout/Update.php
	Mage_Core_Model_Layout_Update::getFileLayoutUpdatesXml

The actual code in <code>getFileLayoutUpdatesXml</code> is pretty dense. However, you can approximate the code that grabs the list of files with something like this

	#URL: http://magento.example.com/nofrills_booklayout/reference/layoutfiles
	public function layoutfilesAction()
	{
		$updatesRoot = Mage::app()->getConfig()->getNode('frontend/layout/updates');
		$updateFiles = array();
		foreach ($updatesRoot->children() as $updateNode) {
			if ($updateNode->file) {
				$module = $updateNode->getAttribute('module');
				if ($module && 
				Mage::getStoreConfigFlag('advanced/modules_disable_output/' .
				$module)) {
					continue;
				}
				$updateFiles[] = (string)$updateNode->file;
			}
		}
		// custom local layout updates file - load always last
		$updateFiles[] = 'local.xml';		
		var_dump($updateFiles);
	}
	
Load the URL for the above action, and you'll see the list of files that Magento will load, and then combine, into the package layout. 

Two additional things to note about the loading of the package layout.  First, you'll see in the above code, (which was copied from <code>Mage\_Core\_Model\_Layout\_Update::getFileLayoutUpdatesXml</code>), that Magento checks for a config flag at <code>advanced/modules\_disable\_output</code> before loading any particular file.  This corresponds to the System Config section at 

	System -> Configuration -> Advanced -> Disable Module's Output

If you've disabled a module's output through this config section, Magento will ignore loading that module's updates into the Package Layout.  

The second thing you'll want to notice is this line

	// custom local layout updates file - load always last
	$updateFiles[] = 'local.xml';		

After loading XML files found in the configuration, Magento will add a <code>local.xml</code> file to the end of the list.  This file is where store owners can add their own Layout Update XML fragments to the Package Layout. We'll learn more about this later, but by loading <code>local.xml</code> last, Magento ensures any Layout Update XML Fragments here have the final say of what goes into the Layout.

Once Magento has determined which files should be loaded into the Package Layout, the contents of each file will be combined into a single, massive XML tree. 

Package Layout Examples
--------------------------------------------------
As part of the module that came with this book, we've included a theme that **clears out** all most of the handles in the default Package Layout.  We've done this to provide some clarity in the examples below.  Unlike our examples so far, there's no public API for programmatically manipulating the Package Layout once its loaded.

You'll want to switch to this theme now.  We've placed this theme in the default package.  **IMPORTANT**: Doing this will make every part of your frontend cart produce a blank page.  It goes without saying, but bears repeating, don't do this with a production store.

If you go to 

	System -> Configuration -> Design -> Package -> Current Package Name
	
and enter <code>default</code> (if it's not already there).  Next, go to 

	System -> Configuration -> Design -> Themes -> Layout 
	
and enter <code>nofrills_layoutbook</code>. Click **Save**, and you'll be set for the example in the next section, (see <em>Figure 3.1</em>)
	
<img src="images/chapter3/config-package.png" />

You can find your new "zeroed out" layout files at 

	app/design/frontend/default/nofrills_layoutbook/layout/

If you load any page in your store, you'll encounter an empty, blank, and errorless browser screen.

What is a Handle?
--------------------------------------------------
Handles are used to organize the Layout Update XML fragments that your application needs.  Every time an HTTP request is sent to the Magento system, it generates handle names for the request.  There are some handle names which are produced on every request.  They include the handle <code>default</code>, as well as a handle based on the controller's "Full Action Name" that was discussed in the previous chapter.

The Full Action Name, as a reminder, is a combination of the current module name, controller name, and action name.  

- Module Name: <code>nofrills\_booklayout</code>
- Controller Name: <code>reference</code>
- Action Name: <code>fox</code>

In the Package Layout example above, the

	<catalog_product_send>...</catalog_product_send>

node is an example of a full action name handle for the **send** Action in the **Product** controller of the **Catalog** module.

In general, it's the responsibility of the controller object to set handles for any particular request.  Also, you generally don't need to worry about setting your own handles.  Magento's base controller methods do this for you.  If you want to **see** the handles set from a particular action controller, use the following code snippet

	#URL: http://magento.example.com/nofrills_booklayout/reference/handle
	public function handleAction()
	{
		$this->loadLayout();
		$handles = Mage::getSingleton('core/layout')->getUpdate()->getHandles();
		var_dump($handles);
		exit;
	}

You're probably wondering about the call to <code>$this->loadLayout();</code>. Don't worry about it too much for now, we'll get to it soon enough.  Just know that that you need to call this method before being able to get a list of handles for a particular request. 

Rendering a Magento Layout
--------------------------------------------------
So, we've finally arrived at the point where we have the vocabulary to fully explore how a Magento layout is created and then rendered for each request.  The rest of this chapter will explain that process in full.  From a high level, here's what happens
	
1. If it's not already cached, Magento loads the entire package layout into memory (from the individual XML file already discussed) 	

2. In the controller, the <code>loadLayout</code> method is called

3. In <code>loadLayout</code>, Magento generates a list of "handles" for the request

4. In <code>loadLayout</code>, Magento takes this list of handles, and uses them to search the Package Layout for a list of Layout XML Update Fragments

5. In <code>loadLayout</code>, after fetching a list of Layout XML Update fragments, Magento checks those fragments for <code>&lt;update name=&quot;&quot;/&gt;</code> tags.  If it finds any, it checks the package layout for any additional handles which match this tag

6. In <code>loadLayout</code>, the Layout Update XML Fragments found in steps four and five are combined.  This combined XML tree is now the Page Layout

7. Magento uses the Page Layout to instantiate the needed block objects

8. In the controller, the <code>renderLayout</code> method is called.  This kicks off rendering of the Layout via its <code>getOutput</code> method.  The resulting output is added to a Magento Response object.  

Let's take a look at some concrete code examples. We'll be working in the following controller file.

	#File: app/code/local/Nofrills/Booklayout/controllers/PackageController.php
	class Nofrills_Booklayout_PackageController extends
	Mage_Core_Controller_Front_Action
	{		
		public function loadLayout($handles=null, $generateBlocks=true,
		$generateXml=true)		
		{
			$original_results = parent::loadLayout($handles,$generateBlocks,
			$generateXml);

			$handles = Mage::getSingleton('core/layout')->getUpdate()->getHandles();
			echo "<strong>Handles Generated For This Request: ",
			implode(",",$handles),"</strong>";
			
			return $original_results;
		}
		
		#http://magento.example.com/nofrills_booklayout/package/index
		public function indexAction()
		{
			$this->loadLayout();
			$this->renderLayout();			
		}
	}

You'll notice we've extended the <code>loadLayout</code> method to print out the handles generated by Magento.  This is for our own debugging purposes.  Load up the index URL, and you should see a blank white page with only the handles listed, (see *Figure 3.2*)

<img src="images/chapter3/blank-with-handles.png" />
	

Getting a Handle on Handles
--------------------------------------------------
There's two handles you can always rely on being generated.  Those are the handle named <code>default</code>, and the handle that's named for the "Full Action Name".  

	default
	nofrills_booklayout_package_index
	
Because of this, in the layout files that ship with Magento the handles for a page's structure are kept under the <code>&lt;default/&gt;</code> handle.  This is also where the "root" tag with the <code>output="toHtml"</code> attribute is stored. If you look at a stock <code>page.xml</code>, you can see this.

	<!-- #File: app/design/frontend/base/default/layout/page.xml -->
    <default translate="label" module="page">
        <label>All Pages</label>
        <block type="page/html" name="root" output="toHtml"
        template="page/3columns.phtml">

            <block type="page/html_head" name="head" as="head">           
                <action method="addJs">
                	<script>prototype/prototype.js</script>
                </action>
                <action method="addJs" ifconfig="dev/js/deprecation">
                	<script>prototype/deprecation.js</script>
                </action>
                <action method="addJs">
                	<script>lib/ccard.js</script>
                </action>
				...
				
Layout Update XML Fragments located in the <code>default</code> handle will always be loaded.  

If you look at our custom <code>page.xml</code>, you'll see we've removed all the handle tags

	<!-- #File: app/design/frontend/default/nofrills_layoutbook/layout/page.xml -->
	<?xml version="1.0"?>
	<layout version="0.1.0">
	</layout>

That's why our page is rendering blank. Let's restore those tags and see what effect it has.  We'll copy the base <code>page.xml</code> over our blank one.  

	cp app/design/frontend/base/default/layout/page.xml \
	app/design/frontend/default/nofrills_layoutbook/layout/page.xml

Clear your Magento cache and reload your page.  You should now see a base Magento layout, (see *Figure 3.3*)

<img src="images/chapter3/blank-theme.png" />

You'll want to either turn caching off or clear it between each page reload from here on out, see Appendix F for more information if you're interested in why.

Take a look at the top of page.xml and find the node named root

	#File: app/design/frontend/default/nofrills_layoutbook/layout/page.xml 
	<block type="page/html" name="root" output="toHtml"
	template="page/3columns.phtml">
	
Let's edit this to remove the output tag	

	<block type="page/html" name="root" 
	template="page/3columns.phtml">
	
Refresh your page (again, after clearing your cache).  The page now renders as blank.  You'd probably never do this for a production site, we've done here to demonstrate that the Magento Layout itself is built on the same concepts our simple templates from previous chapters. 

Let's restore that output attribute before continuing

	<block type="page/html" name="root" output="toHtml"
	template="page/3columns.phtml">
	
So, that's the <code>default</code> handle.  Any Layout Update XML fragments inside a default handle will always be a part of the Page Layout, and that's where most of the structural blocks live.  That brings us to the other handle you can always rely on being present, the Full Action Name handle.  In our examples above this is

	nofrills_booklayout_package_index
	
As previously mentioned, this handle is generated from the module name (<code>nofrills_booklayout</code>), the controller name (<code>package</code>) and the action name (<code>index</code>).  The handle will uniquely identify any request into the system.  Therefore, Layout Update XML fragments located in this handle are most often used to add content to the page.  

Let's add a Layout Update XML fragment using our handle.  Open up the <code>local.xml</code> file, and paste in the following code.

	<!-- #File: app/design/frontend/default/nofrills_layoutbook/layout/local.xml -->
	<?xml version="1.0"?>
	<layout version="0.1.0">
		<nofrills_booklayout_package_index>
			<reference name="content">
				<block type="core/text" name="our_message">
					<action method="setText"><text>Hello Mars</text></action>
				</block>
			</reference>
		</nofrills_booklayout_package_index>
	</layout>

Inside our <code>nofrills\_booklayout\_package\_index</code> node we've added a Layout Update XML fragment to update the content block with a little bit of text.  Reload, clear cache, and you can see our simple *Hello Mars* text block has been added to the page.  However, if we move to this URL/Action 

	#http://magento.example.com/nofrills_booklayout/package/second
	public function secondAction()
	{
		$this->loadLayout();
		$this->renderLayout();			
	}

We can see that our text block is, as expected, NOT added.  We'd need to add another handle to the Package Layout with its own Layout Update XML fragment for that to happen. Let's do that now.

	<!-- #File: app/design/frontend/default/nofrills_layoutbook/layout/local.xml -->
	<layout version="0.1.0">
		<!-- ... -->
		<nofrills_booklayout_package_second>
			<reference name="content">
				<block type="core/text" name="our_message">
					<action method="setText"><text>Hello Jupiter</text></action>
				</block>
			</reference>
		</nofrills_booklayout_package_second>
		<!-- ... -->
	</layout>

Notice the new handle's name (<code>nofrills\_booklayout\_package\_<strong>second</strong></code>) matches the <code>secondAction</code> method .  Refresh the page (after clearing your cache) and you'll see the Hello Jupiter text.

We can also use the Full Action Handle to change which template an existing block uses.  For example, to make this a one column layout, we'll get a reference to the root block and call its <code>setTemplate</code> method.

	<nofrills_booklayout_package_second>
		<reference name="content">
			<block type="core/text" name="our_message">
				<action method="setText"><text>Hello Jupiter</text></action>
			</block>						
		</reference>
		
		<reference name="root">
			<action method="setTemplate">
				<template>page/1column.phtml</template>
			</action>
		</reference>
	</nofrills_booklayout_package_second>	
	
When editing a single Layout XML file, you can either put all your additional tags changes into a single handle, or spread them out. The following would be functionally the same as the above.

	<nofrills_booklayout_package_second>
		<reference name="content">
			<block type="core/text" name="our_message">
				<action method="setText"><text>Hello Jupiter</text></action>
			</block>						
		</reference>
	</nofrills_booklayout_package_second>	

	<nofrills_booklayout_package_second>	
		<reference name="root">
			<action method="setTemplate">
				<template>page/1column.phtml</template>
			</action>
		</reference>
	</nofrills_booklayout_package_second>

The order the update handles are placed in **is** significant. Consider multiple layout files that try to change a block's template.  The last file processed (<code>local.xml</code>) will be the one that wins, just like the last method called on a PHP block wins

	$block->setTemplate('3columns.phtml');
	$block->setTemplate('6columns.phtml');
	$block->setTemplate('1column.phtml');  

There's no firm rule in place here, but try not to have your layout action in one group of handles be too dependent on what's happened in another handle.  

More local.xml
--------------------------------------------------
Just because we're in <code>local.xml</code> doesn't mean we're limited to Full Action Handles.  **Any** handle can be added to **any** Layout XML file, as all these files are combined into the Package Layout.  For example, we could add a default handle that would ensure the same content always gets added to the page in <code>local.xml</code>

	<layout>
		<!-- ... -->
		<default>
			<reference name="content">
				<block type="core/text" name="for_everyone">
					<action method="setText">
						<text>I am on all pages!</text>
					</action>
				</block>						
			</reference>		
		</default>

		<!-- ... -->
	</layout>
	
Adding Other Handles to the Page Layout
--------------------------------------------------
There's one other tag you'll need to be aware of in the Package Layout.  You'll  often  want to use the same set of blocks over and over again for different Full Action Handles, similar to the way you'd use a simple subroutine or function in a full programming language.  
		
To handle this situation there's an additional tag that the Package Layout understands named <code>&lt;update/&gt;</code>.

When Magento is scanning the package layout for Layout Update XML fragments to use, it will do a secondary scan of those fragments for an <code>&lt;update/&gt;</code> tag.  If it finds one, it will **go back to the entire package layout** and grab any Layout Fragments that match the handle attribute.  Consider, based on our example above, the following <code>local.xml</code>

	<layout version="0.1.0">
		<nofrills_booklayout_package_index>
			<reference name="content">
				<block type="core/text" name="planet_4">
					<action method="setText"><text>Hello Mars. </text></action>
				</block>                        
			</reference>
		
			<update handle="nofrills_booklayout_package_second" />
		</nofrills_booklayout_package_index>
		
		<nofrills_booklayout_package_second>
			<reference name="content">
				<block type="core/text" name="planet_5">
					<action method="setText"><text>Hello Jupiter. </text></action>
				</block>                        
			</reference>
		</nofrills_booklayout_package_second>   
		
		<nofrills_booklayout_package_second>    
			<reference name="root">
				<action method="setTemplate">
					<template>page/1column.phtml</template>
				</action>
			</reference>
		</nofrills_booklayout_package_second>
	</layout>

If we loaded our index page here, the Page Layout would contain the following (sans comments)

	<!-- from our handle -->
	<reference name="content">
		<block type="core/text" name="planet_4">
			<action method="setText"><text>Hello Mars. </text></action>
		</block>						
	</reference>
	
	<!-- from our [update handle="nofrills_booklayout_package_second"/] -->
	<reference name="root">
		<action method="setTemplate"><template>page/1column.phtml</template></action>
	</reference>	
	<reference name="content">
		<block type="core/text" name="planet_5">
			<action method="setText"><text>Hello Jupiter. </text></action>
		</block>						
	</reference>
		
That's because while processing the <code>nofrills\_booklayout\_package\_index</code> handle, Magento encountered the <code>&lt;update/&gt;</code> tag.

	<update handle="nofrills_booklayout_package_second" />

By including this tag, we've told Magento that we **also** want to grab Layout Update XML fragments that are included in the <code>nofrills\_booklayout\_package\_second</code> handle.

You can think of this as a sort of "include" for Layout Update fragments. Magento itself uses this technique extensively.  For example, Magento defines the blocks for the <code>customer\_account\_login</code> handle, and then uses those again later on when it wants to include the same login on the multi-shipping checkout page. 

	<checkout_multishipping_login>
		<update handle="customer_account_login"/>
	</checkout_multishipping_login>


Package Layout Term Review
--------------------------------------------------
Phew!  That was a lot of new terminology to take in.  Let's close with a quick recap of the structure of our two XML trees, the Package Layout and the Page Layout

###Package Layout

The Package Layout is an XML tree that contains all possible Layout XML Update Fragments for a design package.  Frangments are organized by handle.

**Top Level Node in the Package Layout**

- <code>&lt;layouts&gt;</code>

**Allowed Second Level Nodes**

- Any arbitrary named node called a <code>handle</code>

**Allowed Third Level Nodes**

- <code>&lt;block&gt;</code> or <code>&lt;reference&gt;</code>, as the start of a Layout Update XML fragment

- <code>&lt;update&gt;</code>, used to include another handle's Layout XML Update Fragments
		
###Page Layout

The Page Layout is the final collection of Layout Update XML Fragments used to create block objects for a request.

**Top Level Node in the Page Layout**

- <code>&lt;layout&gt;</code>

**Allowed Second Level Nodes**

- <code>&lt;block&gt;</code> 		
- <code>&lt;reference&gt;</code>
- <code>&lt;remove/&gt;</code>
	
**Allowed Third Level nodes**

- <code>&lt;block&gt;</code>'s and <code>&lt;reference&gt;</code>'s may contain - other blocks, or actions
- <code>&lt;remove/&gt;</code>

**Note**

- <code>&lt;remove/&gt;</code> is technically allowed anywhere, as the xpath expression used to parse it (<code>//remove</code>) ends up searching the entire Page Layout.  Convention keeps it at the second nesting level
		  
###Layout Update XML Fragment

A partial XML document fragment that describes a series of PHP commands to run which may

- Create block objects
- Insert block objects into other block objects
- Reference block objects to call their methods
	  
*Visit http://www.pulsestorm.net/nofrills-layout-chapter-three to join the discussion online.*	  