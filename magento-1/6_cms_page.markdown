CMS Pages
==================================================	
This chapter covers the CMS Page feature.  For day-to-day Magento work most of the knowledge here is unnecessary.  However, if you ever need to debug a Magento CMS page render, or are curious how CMS pages interact with the layout, this is the chapter for you.  We'll also being laying the groundwork for our final chapter on Widgets.

Back in 1996, if you wanted to put a piece of content online, you just uploaded an HTML file.  If you were really savvy, you'd upload an HTML **include** file that contained your content, and the HTML page itself would use server side includes. 

It's weird, that in 2011, if you asked a developer how to add some content to a site or a web application, their process would be almost exactly the same.  Instead of adding an HTML file, they'd add a controller and a template, and then put the HTML content in the template.

However, for **non-developers**, managing content on a website has gone completely GUI.  Systems like Drupal, Concrete5, and Joomla rule the roost.  Users expect to manage their sites via a user interface, and *not* via code or adding files.  Magento's often overlooked CMS features allows users the control they want. Don't worry though, there's plenty in the CMS for a developer to sink their teeth into, particularly a developer who knows the layout system inside out. 

Creating a Page
--------------------------------------------------
The CMS starts with a CMS Page entity. If you browse to 

	CMS -> Pages 
	
in the Admin Console, you'll see a list of CMS pages that have already been added to the system, (see *Figure 5.1*) 

<img src="images/chapter6/cms-page.png" />

If you click on "Add New Page" you'll be presented with a standard Magento editing UI, allowing you to enter information and create your page, (see *Figure 5.2*) 

<img src="images/chapter6/page-info.png" />

Let's create a simple page by entering the following values.  Don't worry about the specifics right now, we'll get to them down below

	[Page Information : Page Title] 	Our Hello World Page
	[Page Information : URL Key] 		hello-world
	[Page Information : Store View]		All Store Views
	[Page Information : Status]			Enabled
	
	[Content: Content Heading]			Welcome World!
	[Content: Editor]					The quick brown fox jumps over the lazy dog.
	
Once you're done, click on save and Next, load up the following URL in your browser

	http://magento.example.com/hello-world
	
You should see your new CMS page, (see *Figure 5.3*) 

<img src="images/chapter6/page1.png" />

When you saved your page in the admin, Magento stored all that data as a <code>cms/page</code> model.  

	$page = Mage::getModel('cms/page');

When Magento identifies a URL as a CMS page, it loads this model up, reads its information, and then displays it to the page.

Let's take a look at all of the CMS Page fields and briefly describe what they do.

###Page Information : Page Title

This is the title of your CMS page.  It will display in the Admin Console grid, your page's <code>&lt;title/&gt;</code> tag, and the default breadcrumb display. 

###Page Information : URL Key 

This is the non-server portion of your page URL.  This can be any string that's valid in a URL.  In our example above, we used 

	hello-world
	
If we had used

	hello-world.html
	
our page URL would have been

	http://magento.example.com/hello-world.html

###Page Information : Store View

This setting determines which stores your page may appear in.  This allows you to hide content from stores where it may not be appropriate, or provide different version of a page for different stores.

Stores here is referring to Magento's internal abstract "Store" concept.

###Page Information : Status
The status field allows you to disable or enable a page.  A disabled page will return a response with a 404 HTTP Status code.  This is great for embargoed content, or for saving seasonal content to use over again.

###Content: Content Heading
The content heading determines your page's top level <code>&lt;h1/&gt;</code> tag. (See rendering section below for more information).

###Content: Editor
This is the rich text editor where you enter your page's content.  In addition to the various formatting buttons common to most rich text editors, clicking the Show/Hide Editor button will toggle the raw source view.  When viewing a page in raw source view, you can view and edit the actual HTML that will render your page.  

Also in raw source view, you'll see a few additional buttons. Insert Widget, Insert Image, and Insert Variable.  Clicking one these buttons eventually results in text something like

	{{config path="trans_email/ident_general/email"}}
	
being added to your raw source. This is a directive tag, and we'll cover it in greater detail in just a bit. 

###Meta : Keywords

The text in this field controls the <code>&lt;meta name="keywords"/&gt;</code> tag on your page.  The contents of this field will be added directly to the <code>content</code> attribute of the tag. 

	<meta name="keywords" content="This is a test of the keywords." />
	
	
###Meta : Description

Much like **Keywords**, the **Description** field controls the contents of your page's <code>&lt;meta name="description"/&gt;</code> tag.

	<meta name="description" content="Describing the meta." /> 

###Design : Layout
This select box allows you to set which page structure template your CMS page will use. This select is populated by a call to

	//Mage_Page_Model_Source_Layout
	Mage::getSingleton('page/source_layout')->toOptionArray()
	
which, in a default installation, ultimately reads from the global config nodes at the following location

	<config>
		<global>
			<cms>
				<page>
					...
				</page>
			</cms>
		</global>
	</config>

You can take a look at the structure in 

	app/code/core/Mage/Page/etc/config.xml
	
for an idea on what Magento expects to find in there	

	<page>
		<layouts>
			<empty module="page" translate="label">
				<label>Empty</label>
				<template>page/empty.phtml</template>
				<layout_handle>page_empty</layout_handle>
			</empty>
			<one_column module="page" translate="label">
				<label>1 column</label>
				<template>page/1column.phtml</template>
				<layout_handle>page_one_column</layout_handle>
				<is_default>1</is_default>
			</one_column>
			<two_columns_left module="page" translate="label">
				<label>2 columns with left bar</label>
				<template>page/2columns-left.phtml</template>
				<layout_handle>page_two_columns_left</layout_handle>
			</two_columns_left>
			<two_columns_right module="page" translate="label">
				<label>2 columns with right bar</label>
				<template>page/2columns-right.phtml</template>
				<layout_handle>page_two_columns_right</layout_handle>
			</two_columns_right>
			<three_columns module="page" translate="label">
				<label>3sum columns</label>
				<template>page/3columns.phtml</template>
				<layout_handle>page_three_columns</layout_handle>
			</three_columns>
		</layouts>
	</page>

If you can hold on, you'll eventually understand to the meaning of all the tags above. For now, if you take a peek at the HTML source for that select

	<select id="page_root_template" name="root_template" 
	class="required-entry select">
		<option value="empty">Empty</option>
		<option value="one_column" selected="selected">1 column</option>
		<option value="two_columns_left">2 columns with left bar</option>
		<option value="two_columns_right">2 columns with right bar</option>
		<option value="three_columns">3sum columns</option>
	</select>

you can see that Magento's taking the **tag names** from the above nodes for option values.  This is what Magento will save with its page model, and will then use later to retrieve the label, template, and layout_handle values.

###Design : Layout Update XML

The Magento CMS system still uses the layout/block mechanism for page rendering.  This field will allow you to add additional Layout Update XML fragments to your Page Layout for a CMS Page request.  For example, you could add an additional text content block if you liked with 

	<reference name="content">
		<block type="core/text" name="redundant">
			<action method="setText"><text>Hello Again</text></action>
		</block>
	</reference>

###Design : Custom Design	

Fields in this section allow users to override the above values, and our default theme, for specific date ranges.  

CMS Page Rendering
--------------------------------------------------
Overview out of the way, let's get to those seemingly confusing abstractions!  You're probably wondering how Magento knows a particular request should be rendered with a CMS Page.  If you're a certain kind of developer, you're wondering how Magento routes a URL to the CMS rendering routines, (which is just a different way of saying the same thing)

When a URL request comes into Magento, the first thing Magento asks itself is

> Based on my current configuration, should this URL be handled by an admin controller?

If the answer is yes, Magento dispatches to the appropriate admin action controller. If not, the next thing Magento asks itself is

> Based on my current configuration, should this URL be handled by a frontend controller action?

If the answer is yes, Magento dispatches to the appropriate action controller.   If the answer is no, Magento asks itself one last question

> Looking at that URL, is there a CMS page that matches its key/identifier?  

If so, manually set <strong>the request's</strong> module, controller, and action.  Also, add a parameter with the page ID.  The Page ID is the database ID of the <code>cms/page</code> object.  The code that does this looks something like

        $request->setModuleName('cms')
            ->setControllerName('page')
            ->setActionName('view')
            ->setParam('page_id', $pageId);

By doing this, Magento will automatically dispatch to the following controller action.

	Mage_Cms_PageController::viewAction
	
If you're interested in checking out the code that looks for a CMS page, checkout the <code>match</code> method in 

	#File: app/code/core/Mage/Cms/Controller/Router.php
    public function match(Zend_Controller_Request_Http $request)
    {
    	...
	}	
	
Index Page
--------------------------------------------------
The one exception to the routing scenario described above is the special root page of a site, alternately called the "index" page or the "home" page.

	http://magento.example.com/

Magento URLs that lack ANY path portion (that is, they contain only a server name) will be dispatch to the following controller action.	

	Mage_Cms_IndexController::indexAction
	
This check happens **after** the check for a standard controller is instantiated, but **before** the CMS Page check is done. 

You can override which controller the index page dispatches to via the System Config variable

	System -> Configuration -> Web -> Default Pages -> Default Web Url
	
In a base install, this value is <code>cms</code>.  What that means is, when you go to the root level page, Magento will treat it as though you've gone to 	

	http://magento.example.com/cms
	
If you wanted to have a particular category page display as the home page, you could set this value to something like <code>catalog/category/view/id/8</code>.

What You **Need** to Know
--------------------------------------------------
That was some heavy abstract lifting back there.  If you're not interested in those kind of details, all you really need to know can be summed up by the following

> If Magento decides a URL needs a CMS page, it dispatches to <code>Mage\_Cms\_PageController::viewAction</code>

Let's take a look at that controller

	#File: app/code/core/Mage/Cms/controllers/PageController.php
	class Mage_Cms_PageController extends Mage_Core_Controller_Front_Action
	{
		/**
		 * View CMS page action
		 *
		 */
		public function viewAction()
		{
			$pageId = $this->getRequest()
				->getParam('page_id', $this->getRequest()->getParam('id', false));
			if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
				$this->_forward('noRoute');
			}
		}
	}	

That's only four lines in the method body, but if you're not familiar with Magento coding conventions, it's four dense looking lines.  We're going to tease apart what's going on in this method. If you're already familiar with Magento conventions you may want to skip ahead (although reviewing information never hurt anyone)

The call to <code>$this->getRequest()</code> returns the Magento request object.  Rather than have you interact directly with <code>$\_GET</code>, <code>$\_POST</code> and <code>$_COOKIES</code>, Magento provides a request object that allows you access to the same information.  This object is a <code>Mage\_Core\_Controller\_Request\_Http</code>, which extends from a Zend class (<code>Zend\_Controller\_Request\_Http</code>)

Next, we're chaining in a call to <code>getParam</code> in order to retrieve the value of <code>page\_id</code>.  This is the id of our <code>cms/page</code> model.  The second parameter to <code>getParam</code> is a default value to return if <code>page\_id</code> isn't found.  In this case, we're calling <code>getParam</code> **again**, this time looking for value of the id parameter.  If there's no id parameter, <code>$pageId</code> is set to false. 

So, we now have our page id.  Next, 

	if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
		$this->_forward('noRoute');
	}
			
we instantiate a <code>cms/page</code> helper class, and call its render method.  We pass in a reference to the controller, and the page id we just fetched from the request.

If this method returns false, we forward on to <code>noRoute</code>, which for our purposes we'll call the 404 Page.

Where's the Layout?
--------------------------------------------------
Earlier we mentioned the CMS system used the same layout rendering engine as the rest of Magento.  However, you're probably wondering where the calls to <code>$this->loadLayout()</code> and <code>$this->renderLayout()</code> are.  You may also be wondering why we're doing something weird like passing a reference to the Controller (<code>$this</code>) to our <code>cms/page</code> helper.

The answers to both questions lies within the <code>renderPage</code> method, so lets take a look
	
	#File: app/code/core/Mage/Cms/Helper/Page.php
	class Mage_Cms_Helper_Page extends Mage_Core_Helper_Abstract
	{
		public function renderPage(Mage_Core_Controller_Front_Action $action,
		$pageId = null)
		{
			return $this->_renderPage($action, $pageId);
		}	

		...
		
		protected function _renderPage(
			Mage_Core_Controller_Varien_Action  $action, $pageId = null,
			$renderLayout = true)
		{		
			...

			$action->loadLayoutUpdates();
			$layoutUpdate = ($page->getCustomLayoutUpdateXml() && $inRange) 
			? $page->getCustomLayoutUpdateXml() : $page->getLayoutUpdateXml();
			$action->getLayout()->getUpdate()->addUpdate($layoutUpdate);
			$action->generateLayoutXml()->generateLayoutBlocks();			

			...
			
			if ($renderLayout) {
				$action->renderLayout();
			}
			
		}
	}

We've truncated much of the actual code (...) to focus on the specific lines above.  You'll see that the <code>renderPage</code> method wraps a call to the internal, protected <code>\_renderPage</code> method.  Notice that the controller we've passed in is (locally) known as <code>$action</code>.  Without going into too much detail, the code above replaces your calls to <code>$this->loadLayout()</code>.  

In fact, if you looked at the implementation of the <code>loadLayout</code> method in the base action controller, you'd see code similar to what's above.  The only difference here is, after loading the layout update handles from the package layout files, we then add any additional layout handles from our CMS Page. (You'll recall that Admin Console allowed us to add layout update handles for specific CMS pages)

We won't go into every little detail of the page rendering process, but we will highlight a few other chunks of code that should shed some light on what we were doing in the Admin Console GUI.  

Adding the CMS Blocks
--------------------------------------------------
Take a look at the following line

        $action->getLayout()->getUpdate()
            ->addHandle('default')
            ->addHandle('cms_page');

Here, the handle <code>cms_page</code> is being issued.  This means when we're pulling Layout Update XML from the package layout, the following will be included.

	<!-- File: app/design/frontend/base/default/layout/cms.xml -->
	<layout>
		<!-- ... -->
		<cms_page translate="label">
			<label>CMS Pages (All)</label>
			<reference name="content">
				<block type="core/template" name="page_content_heading"
				template="cms/content_heading.phtml"/>
				<block type="page/html_wrapper" name="cms.wrapper" translate="label">
					<label>CMS Content Wrapper</label>
					<action method="setElementClass"><value>std</value></action>
					<block type="cms/page" name="cms_page"/>
				</block>
			</reference>
		</cms_page>
		<!-- ... --->
	</layout>
	
This is the key Layout Update XML for CMS pages.  It adds the blocks for the content heading, and the page content itself, to the page layout.  Later on in the render method we set the page content header by grabbing the saved content header values from our page model

	$contentHeadingBlock = $action->getLayout()->getBlock('page_content_heading');
	if ($contentHeadingBlock) {
		$contentHeadingBlock->setContentHeading($page->getContentHeading());
	}
	
This value is then referenced in the content heading block's template <code>cms/content_heading.phtml</code>.

Setting the Page Template
--------------------------------------------------
You may remember configuring a page template in the GUI. This template is stored in the root_template property, and Magento uses it here

	if ($page->getRootTemplate()) {
		$action->getLayout()->helper('page/layout')
			->applyTemplate($page->getRootTemplate());
	}

The Layout object's <code>applyTemplate</code> method takes the saved value (say, <code>two\_columns\_left</code>), jumps back into the config to find the name of the template it should set, and then sets it.

    public function applyTemplate($pageLayout = null)
    {
        if ($pageLayout === null) {
            $pageLayout = $this->getCurrentPageLayout();
        } else {
            $pageLayout = $this->_getConfig()->getPageLayout($pageLayout);
        }

        if (!$pageLayout) {
            return $this;
        }

        if ($this->getLayout()->getBlock('root') &&
            !$this->getLayout()->getBlock('root')->getIsHandle()) {
                // If not applied handle
                $this->getLayout()
                    ->getBlock('root')
                    ->setTemplate($pageLayout->getTemplate());
        }

        return $this;
    }
    
You'll remember that the <code>two\_columns\_left</code> config node looked something like this

	<two_columns_left module="page" translate="label">
		<label>2 columns with left bar</label>
		<template>page/2columns-left.phtml</template>
		<layout_handle>page_two_columns_left</layout_handle>
	</two_columns_left>

You can see we're using the <code>&lt;template/&gt;</code> node above, but we don't seem to be using the <code>&lt;layout_handle/&gt;</code> anywhere.  Plus, there's the <code>getIsHandle</code> method call above.  What's that all about?   

Other parts of the system will add a layout handle named after the values in the <code>&lt;layout\_handle&gt;</code> tag. If you look at one of these handles

    <page_two_columns_left translate="label">
        <label>All Two-Column Layout Pages (Left Column)</label>
        <reference name="root">
            <action method="setTemplate">
            	<template>page/2columns-left.phtml</template>
            </action>
            <!-- Mark root page block that template is applied -->
            <action method="setIsHandle"><applied>1</applied></action>
        </reference>
    </page_two_columns_left>

you can see it's applying a template via the <code>setTemplate</code> method, and also setting a <code>IsHandle</code> flag on the object.  This flag is used internally by the block to prevent multiple handles from setting the root template.  This isn't done by the CMS Page render, but it's good to know about. 

Rendering the Content Area
--------------------------------------------------
So that covers some of the ancillary items around rendering a CMS page, but what about the page content itself?  We've added a <code>cms/page</code> block named <code>cms\_page</code> using the <code>cms\_page</code> handle, but we don't seem to do anything with it.

That's because the CMS block itself does the rendering.  If you take a look at its <code>\_toHtml</code> method

	class Mage_Cms_Block_Page extends Mage_Core_Block_Abstract
	{
		//...
		protected function _toHtml()
		{
			/* @var $helper Mage_Cms_Helper_Data */
			$helper = Mage::helper('cms');
			$processor = $helper->getPageTemplateProcessor();
			$html = $processor->filter($this->getPage()->getContent());
			$html = $this->getMessagesBlock()->getGroupedHtml() . $html;
			return $html;
		}
		//...
	}
	
we can see we're calling <code>$this->getPage()->getContent()</code>, which looks like a likely candidate.  But how is <code>getPage()</code> obtaining a reference to our CMS Page object?  

    public function getPage()
    {
        if (!$this->hasData('page')) {
            if ($this->getPageId()) {
                $page = Mage::getModel('cms/page')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getPageId(), 'identifier');
            } else {
                $page = Mage::getSingleton('cms/page');
            }
            $this->setData('page', $page);
        }
        return $this->getData('page');
    }

It looks like this method will

1. Look for a data member named 'page'.  If it finds it, return it

2. If not, look for a data member named page_id (<code>getPageId</code>). If we find it, use it to instantiate a page object

3. If there's no <code>page_id</code> data member, get a reference to the <code>cms/page</code> singleton.

It's #3 that's the key here.  We didn't set any data properties named <code>page</code> or <code>page_id</code>.  However, when we originally instantiated our page object

	#File: app/code/core/Mage/Cms/Helper/Page.php
	$page = Mage::getSingleton('cms/page');        
	if (!is_null($pageId) && $pageId!==$page->getId()) {
		$delimeterPosition = strrpos($pageId, '|');
		if ($delimeterPosition) {
			$pageId = substr($pageId, 0, $delimeterPosition);
		}

		$page->setStoreId(Mage::app()->getStore()->getId());
		if (!$page->load($pageId)) {
			return false;
		}
	}
	
we created a singleton instance.  This means that we'll only ever have one reference to this object during the PHP request lifecycle, which is why our call to 

	$page = Mage::getSingleton('cms/page');
	
returns the same page we were dealing with in the helper class	

Page Content Filtering
--------------------------------------------------
Our mystery of the CMS Page object solved, let's examine the <code>\_toHtml</code> method of our block again

    protected function _toHtml()
    {
        /* @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        var_dump
        $html = $processor->filter($this->getPage()->getContent());
        $html = $this->getMessagesBlock()->getGroupedHtml() . $html;
        return $html;
    }
    
Rather than just return the contents of <code>$this->getPage()->getContent()</code>, this code instantiates a filtering object and passes our content through it to get the final HTML.

This is the code that's responsible for swapping out the template <strong>directive</strong> tags we mentioned earlier.

	{{config path="trans_email/ident_general/email"}}
	{{media url="/workforfree.jpg"}}

The object returned by the call to 
	
	$helper->getPageTemplateProcessor()
	
contains all the code that will process these template directives. 	Like a lot of Magento, this is a configuration based class instantiation.  If you look at the implementation of <code>getPageTemplateProcessor</code>

	#File: app/code/core/Mage/Cms/Helper/Data.php
	class Mage_Cms_Helper_Data extends Mage_Core_Helper_Abstract
	{
	
		const XML_NODE_PAGE_TEMPLATE_FILTER = 'global/cms/page/tempate_filter';
		const XML_NODE_BLOCK_TEMPLATE_FILTER = 'global/cms/block/tempate_filter';	
		public function getPageTemplateProcessor()
		{
			$model = (string)Mage::getConfig()
			->getNode(self::XML_NODE_PAGE_TEMPLATE_FILTER);
			return Mage::getModel($model);
		}	
		<!-- ... -->
		
You can see we look for our directive filtering class at the global configuration node <code>global/cms/page/template_filter</code>.  As of Magento 1.4.2, this node contains the class alias <code>widget/template\_filter</code>, which translates into the class <code>Mage\_Widget\_Model\_Template\_Filter</code>.  However, this may have changed by the time you're reading this, as whenever Magento adds new template directives a new filtering class is created that extends the old one, add adds the new filtering methods.

Filtering Meta Programming
--------------------------------------------------
If you follow the inheritance chain of the Template Filter far enough back, you eventually reach <code>Varien\_Filter\_Template</code>

	Mage_Widget_Model_Template_Filter extends
	Mage_Cms_Model_Template_Filter  extends
	Mage_Core_Model_Email_Template_Filter extends
	Mage_Core_Model_Email_Template_Filter extends
	Varien_Filter_Template

This class defines an object which contains parsing code which will 

1. Look for any <code>{{foo path="trans\_email/ident\_general/email"}}</code> style strings

2. Parse the token for the directive name (<code>foo</code> above) 

3. Create a method name based on the directive name.  In the above example, the directive name is <code>foo</code>, which means the method name would be <code>fooDirective</code>

4. Use <code>call\_user\_func</code> to call this method on itself, passing in the an array containing a tokenized version of the directive string.

It's beyond the scope of this book to cover the implementation details of each specific directive.  We mention mainly to let you know that, if you're trying to debug a particular template directive, say 

	{{media url="/workforfree.jpg"}}

you can find its implementation method by taking the directive name (<code>media</code>), and adding the word <code>Directive</code>.  A quick search through the code base should turn up the implementation 

	#File: app/code/core/Mage/Core/Model/Email/Template/Filter.php
    public function mediaDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        return Mage::getBaseUrl('media') . $params['url'];
    }

In this specific case we can see that the <code>{{media ...}</code> directive simply grabs the base media URL using <code>Mage::getBaseUrl('media')</code>, and appends the <code>url</code> parameter to it.

*Visit http://www.pulsestorm.net/nofrills-layout-chapter-six to join the discussion online.*