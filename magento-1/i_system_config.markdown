System Configuration Variables
==================================================	
A simple system configuration system might store a set of key/value pairs something like this.

	$config['db_name'] 		= 'localhost';
	$config['db_password'] 	= '12345';	
	$config['logo'] 		= 'awesomelogo.gif';		
	etc.
	
However, in keeping with its core philosophy of "When in doubt, use XML", Magento stores its system configuration values in *tree* format.  The above might be represented something like this

	<system_config>
		<store>
			<database>
				<name>localhost</name>
				<password>12345</password>
			</databases>
			<design>
				<logo>awesomelogo.gif</logo>
			</design>
		</store>
	</system_config>

This is a common approach to modern configuration systems, as it allows you to develop a hierarchy of organized values as more and more sections of your system or application become configurable.  What's really interesting is, using xpath-like expressions, you can still treat a node-based configuration system as a set of key/value pairs.  That's exactly how you fetch a Magento System Configuration value

	Mage::getStoreConfig('store/database/name');
	Mage::getStoreConfig('store/database/password');	
	Mage::getStoreConfig('store/design/logo');		
	
Magento allows each module to define new nodes for this configuration tree, as well as user interfaces for store owners to enter configuration values in the Admin Console under

	System -> Configuration
	
This system is beyond the scope of this book, but there's plenty of information online.  If you're interested, you can start reading here

	http://alanstorm.com/custom_magento_system_configuration

*Visit http://www.pulsestorm.net/nofrills-layout-appendix-i to join the discussion online.*