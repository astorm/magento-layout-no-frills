Creating Code Modules
==================================================	
The word **module** has come to be one of the most abused in software development.  If a designer's adding a table to a side bar, they call it a module.  If a developer is adding a class to a system, they call it a module.  If the project manager wants to sound tech savvy, they call everything a module.

The word **module** has a very specific meaning in Magento.  If referes to a particular organization of code, such that it may be loaded into an existing Magento system in a defined way, with the loading requiring no knowledge of what other Magento modules are doing.  In layman's terms, everyone keeps their code separate, and Magento is smart enough to know where to look for it.

If you look in

	app/code/core/Mage
	
you'll see around 50 - 60 different folders.  Each of these is a single module.  A module may contain controllers, models, helpers, blocks, SQL Install files, extra configuration files for changing Magento system behavior, new classes for Extending the SOAP and RPC APIs, the list goes on and on.  Rather than have a single folder with, say, 200 controller files, Magento uses code modules to organize them by functionality. 

When you want to add code to Magento, either to change existing functionality or add new functionality, you'll also add a new module.  However, your module will go in

	app/code/local/*
	
instead of <code>app/code/core</code>. This is part of Magento's Code Pool feature, which is separate from the module feature.  The <code>local</code> code pool is where you're can put your own code, such that it won't be overridden by Magento's system updates.  Magento also has a 

	app/code/community/*
	
code pool, which is meant for installation of modules from third-parties.

Adding a Module
--------------------------------------------------
The first step to creating a module is picking a *Package Name* or *Namespace*.  If Bill Gates was making a Magento module, he might pick the name *Microsoft*.  Once you've selected your name, create a folder in local

	mkdir app/code/local/Packagename
	
This package name can contain multiple code modules.  Consider Magento Inc.  They use the package name <code>Mage</code> (short for Magento).  While not necessary, the general consensus is that the package name should contain only alphanumeric characters, and be single word cased.  This helps avoid autoload problems when developing on case insensitive file systems (Windows, OS X sort of) that deploy to case sensitive systems (Linux). The <code>Packagename</code> **will** be used as part of PHP class names, so it also must meet those naming conventions as well.

Next, pick a name for your module. Strive for something simple that describes what the module is for.  **Important**: There's many tutorials that recommend you use names that are the same as Magento's module names if you're rewriting or changing the functionality of a core Magento class.  While there's nothing stopping you from doing this, it's not required and can actually cause mass confusion to developers when they're new to the system.  

When you've picked a name, create a folder inside your package name folder

	mkdir app/code/local/Packagename/Modulename
	
Finally, every module in Magento needs one more file, a configuration file. This file will contain information about the module's features, and will be merged into Magento's main config, along with all the other modules.  Create the following folder 

	mkdir app/code/local/Packagename/Modulename/etc

and then create the following file

	<!-- #File: app/code/local/Packagename/Modulename/etc/config.xml -->
	<config>
    	<modules>
        	<Packagename_Modulename>
            	<version>1.0.0</version>
        	</Packagename_Modulename>
    	</modules>
    </config>
	
The <code>&lt;Packagename_Modulename/&gt;</code> node should be named using the package name and module name you chose.  This unique string will be used to identify your modules.  It will also, (and should also), be used as the base name for any classes in your module

	class Packagename_Modulename_IndexController {}
	class Packagename_Modulename_Block_Myblock {}
	
Enabling your Module
--------------------------------------------------
There's one last step you'll need to take if you want to let Magento know about your module.  If you browse to

	app/etc/modules/
	
you'll see a number of XML files.  Think of the <code>etc</code> folder in Magento the same way you would on a *nix system.  It contains configuration files for your store's core systems.  These XML files tell Magento that you'd like to "turn on" a particular module.  Create an XML file using the unique Packagename_Modulename string with the following contents.

	<!-- #File: app/etc/modules/Packagename_Modulename.xml -->
	<?xml version="1.0" encoding="UTF-8"?>
	<config>
		<modules>
			<Packagename_Modulename>
				<active>true</active>
				<codePool>local</codePool>
			</Packagename_Modulename>
		</modules>
	</config>
	
Again, the <code>&lt;Packagename_Modulename/&gt;</code>	node should use the unique string that identifies your own module.  The <code>&lt;active&gt;true&lt;/active&gt;</code> node determines if Magento loads this particular module's <code>config.xml</code> into the system.  You can use this to completely shut off a module (although, if other module's attempt to use that module's functionality, object instantiations will fail).  The <code>&lt;codePool&gt;local&lt;/codePool&gt;</code> node lets the system know where it can find your module files.  The three valid values are <code>core, community, and local</code>

	app/code/core
	app/code/community
	app/code/local
		
With all of the above in place, clear your cache and load up the Admin Console.  Head over to 

	System -> Configuration -> Advanced -> Disable Module's Output
	
This configuration panel is one of the few areas of Magento where you can see a list of all the installed modules.  If you followed the above steps correctly, you should see your module listed.  Congratulations, you've added a module to the system!

Next Steps
--------------------------------------------------
Of course, a module is useless without additional code. Going into everything you can do with a module would be a book in and of itself.  However, the general pattern is, before you can add a type of class to your module (model, helper, etc.) you need to add some code to your <code>config.xml</code>.  This lets the system know that "hey, this module has feature X and use these classes".  This is what makes Magento a **configuration** based MVC system, rather than a convention based one.

*Visit http://www.pulsestorm.net/nofrills-layout-appendix-c to join the discussion online.*