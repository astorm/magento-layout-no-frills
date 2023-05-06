Widget Field Rendering Options
==================================================	
The simplest configuration for a Magento widget data parameter is

	<parameters>
		<our_parameter>
			<visible>0</visible>
			<required>1</required>
			<value>foobazbar</value>
			<type>text</type>
		</our_parameter>
	</parameters>
	
This creates a hidden field (<code>&lt;visible&gt;0&lt;/visible&gt;</code>) that will always be populated with the value 'foobazbar' (<code>&lt;value&gt;foobazbar&lt;/value&gt;</code>).

While hidden fields are useful for widgets entered via a CMS content area, it's  far more common for parameters to have a visible user interface element that  allows end-system-users to enter data.  That is to say, a visible text field is more common

	<parameters>
		<our_parameter>
			<visible>1</visible>
			<required>1</required>
			<label>Label for our Parameter</label>
			<type>text</type>
			<value>bazbarfoo</value>
			<sort_order>10</sort_order>
		</our_parameter>
	</parameters>
	
We've changed the <code>&lt;visible/&gt;</code> tag so it contains the value "1" (boolean for true).  We've also added a <code>&lt;label&gt;</code> tag which will be used as the text label which describes the field, and a <code>&lt;sort_order&gt;</code> field which control where (above or below) a particular UI element will show up compared to others.  The <code>&lt;value&gt;bazbarfoo&lt;/value&gt;</code> tag will set the **default** value for the UI element.  

<img src="images/appendix_g/field.png" />

You can also augment your fields with some instructional text by using the <code>&lt;description/&gt;</code> node.  

	<parameters>
		<our_parameter>
			<visible>1</visible>
			<required>1</required>
			<label>Label for our Parameter</label>
			<type>text</type>
			<value>bazbarfoo</value>
			<sort_order>10</sort_order>
			<description>
				This is the field where we put the thing
			</description>
		</our_parameter>
	</parameters>
	
Sometimes a free form text field gives users too much control over what values they enter in a widget.  For cases where we want to restrict a user's choices, we can use a <code>select</code> or <code>multiselect</code>.

	<parameters>
		<our_parameter>
			<visible>1</visible>
			<required>1</required>
			<label>Should I Stay or Should I Go?</label>
			<type>select</type>
			<value>stay</value>
			<values>
				<staying>
					<value>stay</value>
					<label>There will be Trouble</label>
				</staying>
				<going>
					<value>go</value>
					<label>There will be Double</label>
				</going>
			</values>
			<sort_order>10</sort_order>
		</our_parameter>
	</parameters>
	
The important changes here are we've changed our to <code>&lt;type/&gt;</code> tag to <code>select</code>, and added a new <code>&lt;values/&gt;</code> node.  The sub-nodes of the <code>&lt;values/&gt;</code> node will be used to create the label/value pairs for the HTML <code>&lt;select&gt;</code> elements generated for the front end.  Alternatly, you can provide the name of a source model.

	<parameters>
		<our_parameter>
			<visible>1</visible>
			<required>1</required>
			<label>Should I Stay or Should I Go?</label>
			<type>select</type>
			<value>stay</value>
			<source_model>adminhtml/system_config_source_yesno</source_model>
			<sort_order>10</sort_order>
		</our_parameter>
	</parameters>
	
The string <code>adminhtml/system\_config\_source\_yesno</code> is a class alias for a Magento model (in this case <code>Mage\_Adminhtml\_Model\_System\_Config\_Source\_Yesno</code>). Source models are special model classes with a <code>toOptionArray</code> method.  

	class Mage_Adminhtml_Model_System_Config_Source_Yesno
	{
	
		/**
		 * Options getter
		 *
		 * @return array
		 */
		public function toOptionArray()
		{
			return array(
				array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Yes')),
				array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('No')),
			);
		}
	
	}
	
This method returns a set of key/value pairs for your select. You may create your own source models, or use one the models that ships with Magento.  Checkout the PHP files in 

	app/code/core/Mage/Adminhtml/Model/System/Config/Source
	
for a list of the source models that ship with Magento. 

Creating Your Own Form Elements
--------------------------------------------------
Sometimes you're going to want a form element that's more interactive than a single text or a select.  The widget system has a mechanism that allows you to build your own form elements for the widget UI.  

First, your parameter configuration should look like the following

	<our_parameter>
		<visible>1</visible>
		<required>1</required>
		<label>Should I Stay or Should I Go?</label>
		<type>label</type>
		<helper_block>
			<type>yourpackage_yourmodule/widgettest</type>				
		</helper_block>
		<sort_order>10</sort_order>
		<description>This is the field where you'll put the synergy.</description>			
	</our_parameter>

The key nodes here are <code>&lt;type&gt;</code> and <code>&lt;helper_block&gt;</code>.  Our field type here is <code>label</code>.  Normally, a field type of label will render the label for a field without any form element.  In other words, a form element with no functional value, only instructional/branding/experience.  However, we've also included a <code>&lt;helper\_block&gt;</code> node.  This node (via the <code>&lt;type/&gt;</code> sub-node) configures a block class that will render our form.  

After configuring this parameter, you'll need to define your block class.  Despite living in the standard block heirarchy, we **do not** want to implement our rendering in its <code>\_toHtml</code> method.  Instead, this class needs a special method named <code>prepareElementHtml</code>

	#File: app/code/local/Yourpackage/Yourmodule/Block/Widgettest.php
	class Yourpackage_Yourmodule_Block_Widgettest extends Mage_Core_Block_Abstract
	{
		/**
		* Overly simple example
		*/	
		public function prepareElementHtml(
		Varien_Data_Form_Element_Abstract $element)
		{
			$simple_input = '<input type="text" name="' . 
			strip_tags($element->getName()) . 
			'" value="' . 
			strip_tags($element->getValue()).
			'"/>';
			
	        $element->setData('after_element_html', $simple_input);			
	        
	        $element->setValue(''); //blank out value
			return $element;
		}
	}
	
During the rendering of your parameter's UI, Magento will call the  <code>prepareElementHtml</code> method of your <code>helper\_block</code>, passing in a <code>Varien\_Data\_Form\_Element\_Abstract</code> object.  This <code>$element</code> is the object that Magento will use to render out your form element.  To implement a custom form element, your job is

1. Grab the form element's name and value from <code>$element</code>. The value will contain previously saved values, and the name will be the correct name for the HTML form element to ensure the form data is saved on post

2. Add HTML to the form element's rendering process to implement your custom element

3. Optionally, if you don't want the default value rendering to take place, clear the value from <code>$element</code> before returning it.

In our example above, we've created a ludicrously simple example to demonstrate how you might acheive this. We've 

1. Created a HTML element <code>($simple_input</code>) using string concatenation.

2. Added this HTML to the element with <code>$element->setData('after\_element\_html', $simple\_input);</code>

3. Zeroed out the value of <code>$element</code>

Advanced Examples
--------------------------------------------------
As mentioned, our example above is ludicrously simple.  However, with the power to create any arbitrary HTML, CSS or Javascript for your form, the possibilities are endless.  Look to a few of Magento's <code>&lt;block\_helper&gt;</code>s for inspiration
		
	app/code/core/Mage/Adminhtml/Block/Catalog/Category/Widget/Chooser.php
	app/code/core/Mage/Adminhtml/Block/Catalog/Product/Widget/Chooser.php	
	app/code/core/Mage/Adminhtml/Block/Cms/Block/Widget/Chooser.php
	app/code/core/Mage/Adminhtml/Block/Cms/Page/Widget/Chooser.php


*Visit http://www.pulsestorm.net/nofrills-layout-appendix-h to join the discussion online.*