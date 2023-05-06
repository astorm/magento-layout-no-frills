Widgets
==================================================	
Consder the following situation. You're a developer.  You have a deep knowledge of the Magento system.  The corporate VP in charge of giving you things to do runs into your work area and says

> I want to add a YouTube video to the sidebar?!

You start explaining layouts, and blocks, and pages, and how they render, and which XML file he'll need to edit, or maybe you could add it as a page update o...

Your boss then gives you that steely, bossy look and says again

> <strong>I want</strong> to add a YouTube video to the sidebar

Most people don't work on their own cars.  Most people don't harvest or hunt their own food.  And most people don't want to code their own websites.  That's the problem widgets set out to solve.  In this chapter we'll give you a full overfull of the Magento widget system.  From using the widgets that ship with Magento, to creating your own widgets, to understanding how widgets are inserted into the flow of the Layout.

Widgets Overview
--------------------------------------------------
So, what are widgets?

1. Widgets are Magento Template Blocks

2. Widgets Contain Structured Data

3. Widgets Contain Rules for Building User Interfaces

4. Widgets are formally associated with a number of phtml template files

5. Widgets contain rules that say which blocks in the layout system are allowed to contain them

Let's start by building ourselves a minimum viable widget, and inserting it into a CMS page.  We'll be building our widget in the <code>Nofrills_Booklayout</code> module.  You, of course, are free to add widgets to **any** module you create.

To start with, we need to create a configuration file that will let Magento know about our widget.  Being a newer subsystem of Magento, widgets have their own custom XML config file which will be merged with the Magento config as needed.  Widget config file are named <code>widget.xml</code>, and should be placed in your module's <code>etc</code> folder

	<!-- #File: app/code/local/Nofrills/Booklayout/etc/widget.xml -->
	<widgets>
	</widgets>	
	
There are times where Magento will load the widget config from cache, and there's other times where the config will always be loaded from disk. Because of that, it's best to always clear the cache when making changes to this file.

We now have an empty widget config.  Next, let's add a node to hold our widget definition

	<!-- #File: app/code/local/Nofrills/Booklayout/etc/widget.xml -->
	<widgets>
		<nofrills_layoutbook_youtube type="nofrills_booklayout/youtube">
			<name>YouTube Example Widget</name>
			<description type="desc">
				This wiget displays a YouTube video.
			</description>
		</nofrills_layoutbook_youtube>
	</widgets>	

Each second level node in this file tells Magento about a single widget that's available to the system.  You should take steps to ensure this node's name is  unique to avoid possible collisions with other widgets that are loaded in the system from other modules. In this case, the name <code>nofrills\_layoutbook\_youtube</code> should suffice.

It's the <code>type="nofrills_booklayout/youtube"</code> attribute we're interested in.  This defines a block class alias for our widget.  We're telling Magento that the block class

	Nofrills_Booklayout_Block_Youtube
	
should be used for rendering this widget.  The <code>&lt;name/&gt;</code> and <code>&lt;description/&gt;</code> tags are used for text display in the Magento Admin Console.

Let's create that class.  Add the following file

	#File: app/code/local/Nofrills/Booklayout/Block/Youtube.php
	<?php
	class Nofrills_Booklayout_Block_Youtube extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
	{
		protected function _toHtml()
		{
			return '<object width="640" height="505">
			<param name="movie" 
			value="http://www.youtube.com/v/dQw4w9WgXcQ?fs=1&amp;hl=en_US">
			</param>
			<param name="allowFullScreen" value="true"></param>
			<param name="allowscriptaccess" value="always"></param>
			<embed src="http://www.youtube.com/v/dQw4w9WgXcQ?fs=1&amp;hl=en_US"
			type="application/x-shockwave-flash" 
			allowscriptaccess="always" allowfullscreen="true" 
			width="640" height="505"></embed></object>';
		}
	}
	
This class is *mostly* a standard block class.  It extends from the <code>Mage\_Core\_Block\_Abstract</code> class, and we've overridden the base <code>\_toHtml</code> method to have this block return the embed code for a specific YouTube video.  The one difference you'll notice is the class definition also has this

	implements Mage_Widget_Block_Interface
	
This line is important.  It tells PHP that our class is implementing the widget interface.  If you don't understand the concept of PHP OOP interfaces, don't worry.  Just include this line with your widget class.  Without it, Magento won't be able to fully identify your block as a widget class.    	

That's it!  We now have a super simple widget.  Let's take it for a spin!
	
Adding a Widget to a CMS Page
--------------------------------------------------

We'll need to setup a new CMS Page for our widget.  Complete the following steps

1. Go to <code>CMS -&gt; Pages</code> in the Admin Console

2. Click on **Add New Page**

3. Enter **YouTube Video** in the Page Title field

4. Enter **example-youtube** in the URL Key field

5. Select **All Store Views**

6. Ensure that **Enabled** is selected for status

7. Click on the **Content** tab, and enter a Content Heading, as well as some text in the editor

8. Click on **Save and Continue Edit** button

9. Load your new page in a browser, at **http://magento.example.com/example-youtube**

Now that we've got our new page setup, let's add the widget. Choose the **Content Tab** in the CMS Page editing interface, and click on the Show/Hide Editor (see *Figure 7.1*) 

<img src="images/chapter7/show-hide.png" />
	
The WYSIWYG editing will disappear and be replaced by an HTML source editor.  More importantly, you'll have a new list of buttons, one of which is **Insert Widget**.  Click on this button, and a modal window will come up (see *Figure 7.2*) 

<img src="images/chapter7/widget-window.png" />
	
If you click on the **Widget Type** drop-down, you'll see a list of standard Magento widgets, with your **YouTube Example Widget** widget listed last.

Select your widget from the menu and click in **Insert Widget**.  You should notice the following text has been added to your HTML source

	{{widget type="nofrills_booklayout/youtube"}}
	
Save your CMS page, and then load the page 

	http://magento.example.com/example-youtube
	
in a your web browser.   You should see your embedded YouTube video. 

CMS Template Directives
--------------------------------------------------
The <code>{{curly braces}}</code> text is a template directive.  When Magento encounters these, a template engine will kick in.  If your widget isn't displaying correctly and you want to debug this template engine, hop to the following file
	
	#File: app/code/core/Mage/Widget/Model/Template/Filter.php
	...
	class Mage_Widget_Model_Template_Filter extends Mage_Cms_Model_Template_Filter
	{
		...
    	public function widgetDirective($construction)
    	{
    		...widget directives are rendered here...
    	}
		...
	}

Every directive in a CMS page works this way.  Just look for the method name that matches the directive name, followed by the word directive.

	widgetDirective
	templateDirective
	foobazbarDirective
	
The <code>{{widget}}</code> directive has a useful feature. You can use it to set properties on your widget block object (see Appendix G: Magento Magic setters and getters).  We can use this to make our widget a bit more useful.  

Change your block code so it matches the following, and refresh the CMS page.

	<?php
	class Nofrills_Booklayout_Block_Youtube extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
	{
		protected function _toHtml()
		{				
			$this->setVideoId('dQw4w9WgXcQ');	
			return '
				<object width="640" height="505">
					<param name="movie" value="http://www.youtube.com/v/' .
					$this->getVideoId() . 					
					'?fs=1&amp;hl=en_US"></param>
					<param name="allowFullScreen" value="true"></param>
					<param name="allowscriptaccess" value="always"></param>
					<embed src="http://www.youtube.com/v/' .
					$this->getVideoId() . 
					'?fs=1&amp;hl=en_US" 
					type="application/x-shockwave-flash" allowscriptaccess="always" ' . 
					'allowfullscreen="true" width="640" height="505"></embed>
				</object>			
			';
		}
	}
	
Your CMS page will remain unchanged.  We've altered the code above to set a <code>video_id</code> data property on the block object, and then used that property in rendering the YouTube embed code. (Remember, data properties are stored with underscore\_notation, but the magic methods to fetch them are CamelCased)
	
Next, remove the following line from your block	and reload the CMS page.

	$this->setVideoId('dQw4w9WgXcQ');	
	
Without setting this property, the video will fail to render.  So far that's all pretty obvious.  Next, edit the widget directive so it looks like the following

	{{widget type="nofrills_booklayout/youtube" video_id="dQw4w9WgXcQ"}}
	
Save the CMS page, and reload the frontend page in your browser.  Your video is back!

The <code>widgetDirective</code> method will parse the directive text for attributes, and if it finds any they'll be assigned as data attributes to the widget object.  With this feature, your widgets go from static content renderers to dynamic content renderers.

Adding Data Property UI
--------------------------------------------------
Of course, the whole point of widgets is that they're meant as a method of code-less block adding.  While it's good to **know** you can edit the widget directives directly, something more is needed if this feature is going to fulfill its promise.

In your widget config, add a <code>&lt;parameters/&gt;</code> node as defined below.

	<!-- #File: app/code/local/Nofrills/Booklayout/etc/widget.xml -->
	<widgets>
		<nofrills_layoutbook_youtube type="nofrills_booklayout/youtube">
			<name>YouTube Example Widget</name>
			<description type="desc">
				This wiget displays a YouTube video.
			</description>

			<!-- START new section -->
			<parameters>
				<video_id>
					<required>1</required>
					<visible>1</visible>
					<value>Enter ID Here</value>
					<label>YouTube Video ID</label>
					<type>text</type>
				</video_id>
			</parameters>	
			<!-- END new section -->
		
		</nofrills_layoutbook_youtube>
	</widgets>	
	
Clear your cache, and then click on the **Insert Widget** button again.  Select your widget from the drop-down, and you will now see a UI for entering a video ID, (see *Figure 7.3*) 

<img src="images/chapter7/widget-window-with-data.png" />

Enter an ID (we recommend <code>qYkbTyHXwbs</code> to keep with the theme) and click on **Insert Widget**.  The following directive code should be inserted into the content area. 

	{{widget type="nofrills_booklayout/youtube" video_id="qYkbTyHXwbs"}}
	
Easy as that, you now have a widget for inserting any YouTube video into any page. 	Let's take a look at the XML we added to our widget config

	<parameters>
		<video_id>
			<required>1</required>
			<visible>1</visible>
			<value>Enter ID Here</value>
			<label>YouTube Video ID</label>
			<type>text</type>
		</video_id>
	</parameters>	
	
This node will *formally* add data parameters to our widget, and allow us to specify a field type for data entry.  The <code>&lt;video_id&gt;</code> tag here **does have** semantic value, it's the name of the attribute that will be added to the directive tag

	{{widget type="nofrills_booklayout/youtube" video_id="[VALUE]"}}
	
The <code>&lt;required&gt;</code> tag allows a level of data validation, setting this to "1" will force the Admin Console user to enter a value before inserting the widget.  

The <code>&lt;visible/&gt;</code> node allows you to hide the input field for this data parameter, and have the inserted widget directive tag automatically include an attribute every time its used, with a value provided by the <code>&lt;value/&gt;</code> tag.  When <code>&lt;visible/&gt;</code> is set to 1 the <code>&lt;value/&gt;</code> tag will be used as a default ID.

The value in <code>&lt;label&gt;</code> will be used to provide your rendered HTML form with a label, and <code>&lt;type/&gt;</code> controls what sort of form element is rendered.  See Appendix G for a full list and explanation of form rendering configurations.

<strong>Important</strong>: Be careful changing data parameters of a deployed widget.  Once a <code>{{widget...}}</code> directive tag has been added to a CMS page, it become "detached" from its definition.  That is, if we changed the <code>&lt;video\_id/&gt;</code> above to be <code>&lt;youtube\_id/&gt;</code>, our CMS page would still have the

	{{widget type="nofrills_booklayout/youtube" video_id="[VALUE]"}}
	
widget tag.  While this isn't necessarily a problem, it may cause confusion while further developing the widget or debugging rendering issues.

Widget Templates
--------------------------------------------------
Looking back at our five defining widget properties

1. Widgets are Magento Template Blocks

2. Widgets Contain Structured Data

3. Widgets Contain Rules for Building User Interfaces

4. Widgets are formally associated with a number of phtml template files

5. Widgets contain rules that say which blocks in the layout system are allowed to contain them

we can see that we've covered 1 - 3.  Next up is widget templates.

Just like an ordinary block, a widget can be rendered using a phtml template.  Additionally, using the UI rendering features, we can make templates a **customizable** feature of our widget.

Let's make our YouTube widget a template block.  First, we'll alter our class so it inherits from the core template block and we'll remove the hard coded <code>\_toHtml</code> method.  

	#File: app/code/local/Nofrills/Booklayout/Block/Youtube.php
	<?php
	class Nofrills_Booklayout_Block_Youtube extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
	{

	}

Next, we'll add the following parameter to our widget config

	<parameters>
		<!-- ... -->
		<template>
			<required>1</required>
			<visible>0</visible>
			<value>youtube.phtml</value>
			<label>Frontend Template</label>
			<type>text</type>
		</template>
		<!-- ... -->
	</parameters>	
	
Finally, we'll add the <code>youtube.phtml</code> to our theme's template folder.  We're adding it to the default/default theme here, but if your site's using a different theme, make sure you put it in the appropriate location

	<!-- #File: app/design/frontend/default/default/template/youtube.phtml -->
	<h2>Rick</h2>
	<object width="640" height="505">
		<param name="movie" value="http://www.youtube.com/v/'<?php 
		echo $this->getVideoId();?>?fs=1&amp;hl=en_US"></param>
		<param name="allowFullScreen" value="true"></param>
		<param name="allowscriptaccess" value="always"></param>
		<embed src="http://www.youtube.com/v/<?php 
		echo $this->getVideoId();?>?fs=1&amp;hl=en_US"
		type="application/x-shockwave-flash" allowscriptaccess="always" 
		allowfullscreen="true" width="640" height="505"></embed>
	</object>	
	
With all of the above in place (and a cache clear), re-insert your widget.  You should get a widget tag with a template attribute

	{{widget type="nofrills_booklayout/youtube" video_id="qYkbTyHXwbs"
	template="youtube.phtml"}}
	
Reload your frontend page and your configured YouTube video should render the same as before.

Because template blocks store their template as a regular block data parameter, all we're really doing here is adding a new widget data parameter named <code>&lt;template/&gt;</code>.  We hard coded a value (by using an invisible data field), but there's no reason we couldn't make it a truly configurable value.  Give the following a try in your widget config

	<template>
		<required>1</required>
		<visible>1</visible>
		<value>youtube.phtml</value>
		<label>Frontend Template</label>
		<type>select</type>
		
		<values>
			<as_video>
				<value>youtube.phtml</value>
				<label>Embed Video</label>
			</as_video>	
			<as_link>
				<value>youtube-as-link.phtml</value>
				<label>Link Video</label>											
			</as_link>
		</values>
	</template>	
	
Don't forget to add the new template to your theme	

	<?php 
	#File: app/design/frontend/default/default/template/youtube-as-link.phtml 
	?>
	<a href="http://www.youtube.com/watch?v=<?php 
		echo $this->getVideoId();?>">Watch this!?</a>
	
Clear your cache and reinsert your widget.  You should now see a new drop-down menu allowing you to pick which template your widget should use, (see *Figure 7.4*) 

<img src="images/chapter7/with-template.png" />

While it may appear that the template tag is being treated as just another widget property, when we move outside of CMS based widgets and into Instance Widgets, we'll see that the Instance Widget engine treats this parameter specially.
	
Instance Widgets
--------------------------------------------------
If we look back on our list of five things that make a widget

1. Widgets are Magento Template Blocks

2. Widgets Contain Structured Data

3. Widgets Contain Rules for Building User Interfaces

4. Widgets are formally associated with a number of phtml template files

5. Widgets contain rules that say which blocks in the layout system are allowed to contain them

we can see our explorations have completely ignored number five.  So far all we've done is insert a widget into a CMS content area.  We also haven't met our core widget requirement, which is to allow a non-programming user to add a widget to <strong>any</strong> page on the site. This is where Instance Widgets enter the picture.

So far we've been creating one off widgets that can't be reused.  For example, if we wanted to add the same video to multiple CMS pages, we'd need to manually insert it into each page.  Then, if we wanted to **change** something about each widget (say, the ID of that video), we'd need to go to each individual page and edit the template directive tag

	{{widget type="nofrills_booklayout/youtube" video_id="dQw4w9WgXcQ"}}

With Instance Widgets, we can create **and save** a widget with a specific set of data, and then insert that widget into multiple locations on the site.  Then, if we later change the definition of that specific widget, it will be automatically updated throughout the site.  

Creating an Instance Widget
--------------------------------------------------
Navigate to
	
	CMS -> Widgets
	
in the Admin Console to see a list of all the widgets in your system.  We're going to add a new one, so click on the **Add New Widget Instance** button

Instance Widget creation is a two step process.  First, we need to select the widget type we're going to create, as well as which theme the widget will be added to.  Select our **YouTube** example widget from the drop down menu, and pick the currently configured theme.  We'll be assuming <code>default/default</code> for the following examples, (see *Figure 7.5*) 

<img src="images/chapter7/instance-step1.png"/>
	
Once you've done this, click on the **Continue** button.  

You should now see a two tab editing interface; **Frontend Properties**, and **Widget Options**.	Widget Options contains an editing form for all the data properties for a particular widget, (with the exception of templates).  Click on this tab and add a video id, and then return to the Frontend Properties tab, (see *Figure 7.6*) 

<img src="images/chapter7/instance-set-data.png" />

In Frontend Properties you have two option groups.  The first allows you to select a Widget Instance Title, Assign a Store View, and set a Sort Order for the widget.  The Widget Instance Title is used in the Admin Console when displaying information about the widget (i.e. the listing page), Store View allows you to specify which Magento Stores a widget appears in.

Let's save our widget with a title, and select *All Store Views*.  Click on the **Save** button, and you'll be returned to the widget listing page.  You should see your widget listed along with any others that have been added to your Magento system.  Click on the widget row to edit it.  You'll notice you've been brought directly to the second stage, and that the **Widget Type** and **Design Package/Theme** options are un-editable. Once you select these during widget creation they **cannot** be changed, (see *Figure 7.7*) 

<img src="images/chapter7/instance-step2-always.png"/>

Inserting a Widget
--------------------------------------------------
Here's where Instance Widgets get interesting. At the bottom of the Instance Widget editing page, there's an empty option group named **Layout Update**.  Click the **Add Layout Update** button, (see *Figure 7.8*) 

<img src="images/chapter7/instance-displayon.png" />
	
This drop down menu contains several options, each one describing a particular set of, or a specific, Magento page.  What we're configuring here is the page or pages we want to add our Widget Instance to.  Select All Pages from this menu, (see *Figure 7.9*)

<img src="images/chapter7/instance-pageselected.png" />

Two more menus have appeared.  The first is **Block Reference**, the second is **Template**.  

The first menu is defining **which block** you want to add your Widget Instance to.  Select **Main Content Area**.  The values in the second menu should look familiar to you.  They're the templates we defined earlier.  Select "Embed Video", and then Save you Widget Instance.

At this point you may receive a message at the top of your Magento admin that looks something like *Figure 7.10*.

<img src="images/chapter7/invalidated.png" />
	
This is Magento telling you that it has detected a change to the system that requires you to clear your cache.  Do this, and then load any page in your site.  You should now see your YouTube video added to the main content area.	

Behind the Scenes
--------------------------------------------------
Open up your favorite MySQL browser, and run the following query against your database

	select * from core_layout_update;  
	+------------------+----------------------+---------+------------+
	| layout_update_id | handle               | xml     | sort_order |
	+------------------+----------------------+----------------------+
	|                1 | default 			  | [...]   |          0 |
	+------------------+----------------------+----------------------+

This table contains a list of Layout Update XML fragments, organized by handle.  When building the Page Layout for any request, Magento will check this table <strong>after</strong> checking the loaded package layout.  If it finds any matching handles, they'll be added to the Page Layout.  When you select a value from the **Display On** menu, you're actually telling Magento **which** handles should be applied.  When you save your Widget Instance, this table is updated.  Because these updates add blocks to other block's that inherit from <code>core/text\_list</code>, the widget blocks are automatically rendered. 

If you take a look at the <code>Mage\_Core\_Model\_Layout\_Update::merge</code> method, you can see the additional call to <code>fetchDbLayoutUpdates</code>

    public function merge($handle)
    {
        $packageUpdatesStatus = $this->fetchPackageLayoutUpdates($handle);
        if (Mage::app()->isInstalled()) {
            $this->fetchDbLayoutUpdates($handle);
        }
        return $this;
    }
    
Without an abstract Layout system, adding a feature like widgets would have required (at minimum) editing every single controller action, and inserting blocks into an unknown layout structure. This is the kind of power that sort of abstraction enables.

Similarly, the list of blocks which you insert a widget into is **not** hardcoded into a configuration system.  It's generated automatically.  Magento takes the handle indicated by the **Display On** drop down, and applies it to the Package Layout to create a temporary Page Layout.  Then, rather than render a page, it looks at the top level body blocks for that layout to get a list of eligible blocks to display in the drop-down menu.  This means if you add additional structural blocks to a page via means of custom XML layout files or <code>local.xml</code>, those blocks will show up in this menu.  Again, this sort of thing becomes much easier to implement when using an abstract layout system.

Restricting Blocks.
--------------------------------------------------
Widget Instances have one more interesting feature.  You can actually restrict **which** blocks a Widget Instance may be inserted into.  Head back to your <code>widget.xml</code> file, and add the following to  your widget's node
		
	<widgets>
		<nofrills_layoutbook_youtube>
			<!-- ... -->
			<supported_blocks>
				<uniquely_named_node>
					<block_name>content</block_name>
					<template>
						<unique_name_one>as_video</unique_name_one>
						<unique_name_two>as_link</unique_name_two>
					</template>
				</uniquely_named_node>
				
				<another_uniquely_named_node>
					<block_name>left</block_name>
					<template>
						<unique_name_one>as_video</unique_name_one>
						<unique_name_two>as_link</unique_name_two>
					</template>
				</another_uniquely_named_node>
				
			</supported_blocks>		
			<!-- ... -->
		</nofrills_layoutbook_youtube>
	</widgets>

Clear your cache and reload the Widget Instance editing page.  Your (formerly) long block menu now only allows you the choice of 

	Left Column
	Main Content Area
	
In the absence of a <code>&lt;supported\_blocks/&gt;</code> tag, Magento will display all eligible blocks for any particular page.  However, with this node in place, it will scan each top level node for a sub-node named <code>&lt;block_name&gt;</code> and restrict your choices to those it finds.  In our case above, the blocks are <code>content</code> and <code>left</code>.  These names are the block's name as defined in the Layout Update XML fragment

	<block type="core/text_list" name="content" as="content" translate="label">
	
You're also required to specify which, if any, templates are valid for a particular block.  This context sensitive template is a powerful feature.  Consider and add the following change to your <code>widget.xml</code> file

	<uniquely_named_node>
		<block_name>content</block_name>
		<template>
			<unique_name_one>as_video</unique_name_one>
			<unique_name_two>as_link</unique_name_two>
		</template>
	</uniquely_named_node>
	
	<another_uniquely_named_node>
		<block_name>left</block_name>
		<template>
			<unique_name_two>as_link</unique_name_two>
		</template>
	</another_uniquely_named_node>
	
Clear your cache and reload the widget editor.  You'll notice that switching between the <code>content</code> and <code>left</code> block will result in your template choice being restricted.  By using this technique, we've prevented a user from accidentally inserting a full video into the left hand column by restricting the templates they can use.  In essence, each widget definition is an abstract content type, and you can control how it displays in each section of the site.  This is only a few steps away from some of the advanced content management features of systems like Drupal.

The values being supplied for the templates (<code>as\_link</code> and <code>as\_video</code>) are the names of the nodes in the <code><templates /></code> block up in the <code><parameters/></code> section.  This is what we've meant when we said Magento treats this node differently.


Per Theme Widget Config
--------------------------------------------------
There's another feature of the widget engine, in relation to Instances, that you should be aware of.  It's possible to create fall back configurations for your widgets on a **per theme** basis.  You've probably noticed the default themes each ship with a widget file.

	app/design/frontend/default/default/etc/widget.xml
	
This file has the same format as the <code>widget.xml</code> in your module.  Values in these files can be used to **override** the values for Instance Widgets.  They **do not** apply to widgets inserted into CMS Pages or Static Blocks.  In practice, this is done primarily for the supported blocks feature.   Keeping with the generate principle of separating concerns, a general code module doesn't, technically, know which blocks or templates are going to be available for it.  By keeping this information in each theme (Magento's default widgets ship with all the <code>&lt;supported_blocks/&gt;</code> information in the theme configs), Magento ensures that any themes which add custom <code>core/text\_list</code> blocks also have the ability to allow or deny widgets access to these blocks.  

Wrap Up
--------------------------------------------------
And that, in a nutshell, is widgets.  We chose to end this book with widgets, because they appear to be the path forward for Magento content and layout management.  The abstract layout system described in this book is stepping stone towards larger, more robust content and layout management for Magento.  Less than four years old, Magento is dominating the ecommerce landscape like no other system. We hope the knowedge and techniques provided here will help you tame your Magento systems, and allow you to spend less time being confused by code, and more time serving your customers and building your businesses. 

*Visit http://www.pulsestorm.net/nofrills-layout-chapter-seven to join the discussion online.*