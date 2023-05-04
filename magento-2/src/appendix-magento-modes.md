## Magento Modes
	
Once you've installed Magento 2, there's three different *modes* you can run it in. 

- Developer
- Default
- Production

These modes impact how certain Magento systems behave, and how "automatically" certain things happen in Magento.

One concrete example of that is front end asset files (JS files, CSS files, images, etc).  Magento stores these files inside code modules.  In order to make them available to the public for serving via URLs, you'll need to run the 

    php bin/magento setup:static-content:deploy 
    
command.  This command will look at all the installed modules and configured themes, and create front end assets for you in the `pub/static` folder.

When you're running in production mode, if Magento can't find a CSS file, it reports a file 404 and does nothing more.

However, when you're running in developer or default mode, Magento will attempt to automatically copy or symlink files from the module folders to `pub/static` for you.  This means if a file isn't there, Magento does some extra work to find it and put it in place for you. 

These three modes exists because of the performance and security tradeoffs involved in each of these features.

Speaking generally, production mode does nothing automatic for you.  You need to run commands like `setup:static-content:deploy` and `setup:di:compile` to generate static versions of things that are created automatically for you when running in developer mode.  This helps with Magento's performance, and creates less of a chance that one of these dynamic systems is hijacked for nefarious purposes.  

When you first install a Magento system, it will be in `default` mode.   The `default` mode sits somewhere between production and developer, and tries to create a "hands off" first run experience that's still relatively performant.  

For most of this book, we'll expect you to be running in `developer` mode.  

### Changing Modes

There are two ways to change modes of your Magento 2 system.

The first is the command line `bin/magento` program's `deploy:set:mode` command

    php bin/magento deploy:mode:set developer 
    php bin/magento deploy:mode:set production --skip-compilation

This allows you to swap between modes, and automatically run the generation (i.e. compilation) steps needed for production mode.  This command sets the `MAGE_MODE` value in `app/etc/env.php`

    #File: app/etc/env.php
      
      'MAGE_MODE' => 'production',
      
In addition to `app/etc/env.php`, it's also possible to set the Magento mode in your `.htaccess` file(s) or nginx equivalent configuration. 

    #File: .htaccess
    SetEnv MAGE_MODE "developer"
    
    #File: pub/.htaccess
    SetEnv MAGE_MODE "developer"    
    
Curiously, values set in the web server configuration will override the value set via `deploy:mode:set` -- so if you're seeing strange behavior in your systems be sure to check that you don't have your Magento mode configured differently in each of these files.