Magento Connect
==================================================	
Magento Connect is a lot of things.  First and foremost, it's Magento Inc's online repository for free, downloadable extensions.  It's also a package management system that was, originally, based on the PHP PEAR packaging format.  Magento Connect 2.0 was released along with Magento CE 1.5.  This means there's two separate package file formats, which is why we've included two different sets of modules. 

What is an Extension
--------------------------------------------------
As you may already know, Magento separates its "backend" code into formal code modules. A Magento Connect extension may **contain** modules, but a Magento Connect extension is not **just** a code module.  

A Magento Connect extension is a packaged collection of files that Magento will install into your system.  Each file in the package has a Magento Connect type, which will control where Magento installs the file.  For example, a <strong>local module file</strong> knows to install itself in <code>./app/code/local</code>, whereas a <strong>PHP Library file</strong> installs itself in <code>./lib</code>.  See	

> System -&gt; Connect -&gt; Package Extensions
	
for a full list of types. 

There's one type in particular you'll want to be aware of.  That's the <strong>other</strong> type.  This type's base folder is Magento's base installation folder, which gives a Magento extension the ability to install a file **anywhere** in your system, and in turn you can create a package that includes files from anywhere.

Installing Extensions: The GUI Way
--------------------------------------------------
There's a GUI admin for Magento Connect.  You can reach it from the Admin Console by navigating to 

	System -> Magento Connect -> Magento Connect Manager
	
You'll need to reauthorize your session as the admin user, (or any user with Magento Connect ACL rights).  The code that bootstraps the Magento Connect Manager is separate from the source code of your Magento system proper. 

Installing extensions that have been uploaded to Magento Inc's central server is as easy as entering the extension key into the installation field.
	
If you've downloaded a <code>.tgz</code> package file from the internet, Magento 1.5 also offers a handy upload form, allows you to directly upload an extension


Installing Extensions: The Command Line Way
--------------------------------------------------
Both the 1.4x and 1.5x branches of Magento offer the ability to install extension from the command line.  However, the tools used for each version differ slightly.
	
###Magento Connect CLI install for Magento 1.42

In the root folder of Magento 1.4.2 there's a shell script named <code>pear</code>.  This shell script in **not** the standard PEAR installer.  It's a customized installer you may use to install Magento Connect extensions.  To use it, you'll need to tell your operating system its allowed to execute it as a program

	chmod +x pear
	
After that, you'll need to run 

	./pear mage-setup
	
After setting a number of configuration variables and initializing two channels

	connect.magentocommerce.com/core
	connect.magentocommerce.com/community
	
the script will exit.  You're now ready to install and uninstall packages using the command line installer

	./pear install No_Frills_Magento_Layout_1_start-1.0.0.tgz
	./pear uninstall \
	channel://connect.magentocommerce.com/community/No_Frills_Magento_Layout_1_sta...
	
###Magento Connect CLI install for Magento 1.5+

Magento 1.5 removed the <code>pear</code> installer, and introduced a new command line script (<code>mage</code>) that offers a similar function.  Again, you'll need to give it executable permissions 

	chmod +x mage
	
and then initialize it with 

	./mage mage-setup
	
After the setup script finishes running, you'll be able to install extensions from a file. **NOTE**: The command has changed to <code>install-file</code>, and the arguments to uninstall have changed as well

	./mage install-file No_Frills_Magento_Layout_3_start-1.0.0.tgz 
	./mage uninstall community No_Frills_Magento_Layout_3_start

*Visit http://www.pulsestorm.net/nofrills-layout-appendix-j to join the discussion online.*