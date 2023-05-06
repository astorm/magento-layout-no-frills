Bringing it All Together
==================================================	
We've just spent the last three chapters reviewing some complicated concepts and **the interaction** of complicated concepts.  We're going to pause for a moment to review what we've learned, as well as provide an overall picture of how Magento builds a Page Layout.  Original drafts had this brief, mini-chapter at the start of the book, but it made people's head explode.  Hopefully it's safe enough to cover now


How a Magento Layout is Built
--------------------------------------------------
Somewhere in a controller action, the programmer creating the controller action method tells Magento that it's time to load the layout.  The end result of loading a layout is a Page Layout XML tree, (see *Figure 4.1*)

<img src="images/chapter4/page-layout.png" />
	
To load a Page Layout, Magento will pick and choose Layout Update XML fragments from a repository of Layout Update XML fragments.  This repository of Layout Update XML fragments is known as the Package Layout. The Package Layout is loaded from disk by combining several XML files into a single tree, (see *Figure 4.2*)

<img src="images/chapter4/package-layout-files.png" />
	
Users of the Magento system can add to the Package Layout by 

1. Creating and editing a <code>local.xml</code> file
2. Adding a custom XML file to the Layout via a module's <code>config.xml</code> file
3. Least desirably, but most commonly, editing or replacing existing Package Layout files in their their theme's layout

The Package Layout organizes its many Layout Update XML fragments by handle.    During a normal page request life cycle, various parts of the Magento system will tell the Layout Update Manager that, when the time comes, Layout Update XML fragments from "handle x" should be loaded.  When the Controller Action developer tells Magento to load the layout, the Layout Update Manager checks this list, and asks the Package Layout for a copy of the Layout Update XML fragments contained within those particular handles.
	
Also, each fetched Layout Update XML fragment is processed at this time for an <code>&lt;update handle="..."/&gt;</code> node.  This node can be used to tell the manager to fetch **additional nodes** based on the specified handle. 

Finally, a copy of all Layout Update XML fragments in hand, the Layout Update Manager combines them into a single XML tree.   This is the Page Layout.


What is the Page Layout
--------------------------------------------------
The Page Layout is a list of instruction for Magento.  Programmer types may call it a meta-programing language, or a domain-specific language.  Regardless of what you call, the last step of **loading** a Layout is for Magento to use the Page Layout to create and instantiate a nested tree of block objects.  These are PHP Objects, each one ultimately responsible for rendering a chunk of HTML.

That's the layout loaded.  The controller action programer may, at this point, choose to manipulate the layout object further.  This may include adding, removing, or setting properties on blocks.  The Magento Admin Console application does this regularly.  The Magento frontend (cart) application tends not to do this.  Irrespective of how, after loading a Layout and fiddling with it if they wish, the controller action developer then tells Magento it's time to render the layout object

Rendering a Layout
--------------------------------------------------
During the creation of Page Layout, certain Layout Update XML fragments marked certain blocks as "output blocks".  When we say certain blocks, this is almost always a single block, and equally almost always this is the block named <code>root</code>.  This root block renders a page template.  This template, in turn, includes calls to render child blocks.  Some of these child blocks render via a template file, others are <code>core/text\_list</code> blocks which automatically render all their children.  Others render via pure PHP in the <code>\_toHtml</code> method.  This blocks rendering sub-blocks, rending sub-sub-blocks can continue many layers deep.

The end result of this  rendering is a single string variable containing all the HTML from the cascading render.  The string is then passed into a Magento response object, which is responsible for outputting the HTML page. 

That, in a nutshell, is the Layout system.

*Visit http://www.pulsestorm.net/nofrills-layout-chapter-four to join the discussion online.*