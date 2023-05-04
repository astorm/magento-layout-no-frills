## Unix Find
	
This book will occasionally use the venerable unix `find` command to look for files that match a particular pattern.  This appendix will briefly cover `find`'s syntax in case you've never run across it before. 

Consider the following command, run from a Magento root project folder

    $ find vendor/magento -name '*.phtml'
    
The first argument to the `find` command -- `vendor/magento` above -- is the folder you want to search.  You can actually run `find` with just this single argument to list out **all** the files in a folder. 

    $ find vendor/magento
    vendor/magento/
    vendor/magento//composer
    vendor/magento//composer/.gitignore
    vendor/magento//composer/composer.json
    vendor/magento//composer/LICENSE.txt
    //... huge list snipped ...
    vendor/magento//zendframework1/resources/languages/sk/Zend_Captcha.php
    vendor/magento//zendframework1/resources/languages/sk/Zend_Validate.php
    vendor/magento//zendframework1/resources/languages/sr
    vendor/magento//zendframework1/resources/languages/sr/Zend_Validate.php
    vendor/magento//zendframework1/resources/languages/uk
    vendor/magento//zendframework1/resources/languages/uk/Zend_Validate.php
    vendor/magento//zendframework1/Vagrantfile               

However, when used with the `-name` option (as we have above and below)

    $ find vendor/magento -name '*.phtml'
    
The `find` command will pare down this list using the provided wildcard expression (`*.phtml` above).  This means our command will find all the `phtml` template files in Magento's `composer` source directory. 

Another common `find` invocation will look something like this

    $ find . -name '*.phtml'
    
When the first argument is a `.`, `find` will search starting at the current directory.  If you're not familiar with unix/linux, `.` usually refers to the *current* directory, whereas `..` refers to the directory **above** the current directory. 