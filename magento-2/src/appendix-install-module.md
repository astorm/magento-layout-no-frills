## Installing the Pulsestorm_Nofrills Magento Module

This book includes a Magento code module (`Pulsestorm_Nofrills`) which you'll need to install.  

There are three broad ways to install a code module into a Magento system. You may 

1. Use Magento's Marketplace, whose technology is based on PHP Composer
2. Manually edit your system's composer.json file to install a module hosted elsewhere
3. Manually install code files into the `app/code` folder

Because we're distributing the module files to you directly, the easiest way to install them is via the `app/code` folder.   This is a 5 step process

1. Unarchive the files

2. Create (if necessary) an `app/code` folder in your Magento system

3. Move the unarchived files to this folder

4. Run the `module:enable` command

5. Run the `setup:upgrade` command

### Unarchive the Files

The `Pulsestorm_Nofrills` module is distributed as a compressed tar archive.  Many operating systems support unarchiving these files by double clicking on them.  You may also use the command line to extract these files.

    $ tar -zxvf Pulsestorm_Nofrills.tar.gz

### Moving the Files    

If necessary, create an `app/code` folder in your system.  The `app` folder should already exist, the `code` folder may not.  Once you have this folder in place, move the module files from the previous step to the `app/code` folder.  Once you've moved everything, you should have a file at 

    app/code/Pulsestorm/Nofrillslayout/etc/module.xml
    
This is not the only file in your module -- it's just a convenient one to use to make sure you've moved your module files to the correct folder.

### Telling Magento About the Module

Finally, we need to tell Magento about the new module.  We do this by running the following two command line commands

    $ php bin/magento module:enable Pulsestorm_Nofrillslayout
    //...
    $ php bin/magento setup:upgrade
    //...
    
If you don't know about the Magento command line, checkout *The Command Line* appendix.    
    
At this point, the module is installed and you should be ready to start!    
    