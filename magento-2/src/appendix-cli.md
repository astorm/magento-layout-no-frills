## The Command Line
	
The "command line" is one of the oldest ways to run programs and communicate with your computer.  Despite being a relic of another time, the command line remains popular for developers and other technical professionals who need programs that take clear inputs, and produce clear outputs.  

If you're unfamiliar with the command line, you can access it via your Linux or MacOS/OS X based computer by running a terminal program (included with the OS).  Once you launch this program, the command line will just sit there, waiting for you to run a command

    $

Running a command line environment on a Windows computer is a slightly more complicated affair -- the windows command line is different in many ways from a unix/linux environment. Windows also offers the ability to connect to a \*nix based computer or virtual machine and run command line programs.  We're going to assume you windows folks know what you're doing, or are smart enough to figure it out. i.e. this appendix will assume a \*nix environment.

### Running a Command Line Program

To run a command line program, all you need to do is type that program's name, and hit enter.  For example, the `ls` program will list a directory's current contents

    $ ls
    ...
    
Command line programs can also, like functions in programming languages, accept arguments.  For example, the `cd` command (which changes directories) accepts a single argument (the folder/directory you want to change to)

    $ cd /path/to/magento

Command line programs also often accept options -- for example you can use the `-1` option with `ls` to get a single column listing of files

    $ ls -1
    
Typically, you'll use a single dash is for one letter options, and two dashes for a full-word options.  i.e.

    $ curl --progress-bar http://example.com

The single letter options are a remnant of computer past, where every byte of memory was important.

### Magento CLI

Many application frameworks include their own command line framework.  These frameworks allow a programmer to create command line scripts in the language of the application framework and with easy access to the functions and methods (or, in today's vague parlance, the "API") of the application framework itself.  

Magento 2 includes a command line framework, and ships with many useful commands.  To see a list of commands in the Magento command line framework, just run the following from your project's root folder

    $ php bin/magento list 
    ...
    
This command may require some explaining.  First,  the `php` portion is just us running the command line version of PHP. The command line version of PHP accepts an argument that's a path to the file to execute via php -- in this case, `bin/magento`.  If you look in the `bin/magento` file

    #File: bin/magento 
    
    #!/usr/bin/env php
    <?php
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    if (PHP_SAPI !== 'cli') {
        echo 'bin/magento must be run as a CLI application';
        exit(1);
    }

    try {
        require __DIR__ . '/../app/bootstrap.php';
    } catch (\Exception $e) {
        echo 'Autoload error: ' . $e->getMessage();
        exit(1);
    }
    try {
        $handler = new \Magento\Framework\App\ErrorHandler();
        set_error_handler([$handler, 'handler']);
        $application = new Magento\Framework\Console\Cli('Magento CLI');
        $application->run();
    } catch (\Exception $e) {
        while ($e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
            echo "\n\n";
            $e = $e->getPrevious();
        }
        exit(Magento\Framework\Console\Cli::RETURN_FAILURE);
    }

Here we see the PHP code that bootstraps Magento's command line framework. The second argument to `php` (above, `list`) is *also* the first argument to the `bin/magento` command line script.  Many command line programs use this "sub-command" pattern.  For example, the git version control system has many sub-commands

    $ git add ...
    $ git status
    //etc...
    
When you run the `list` command, you're telling Magento's command line framework to list out all the commands available
    
Describing each Magento command in full is well beyond the scope of this appendix (or this book, or possibly any single book).  However, here's an example of a command that will clear Magento's cache

    $ php bin/magento cache:clean
    
And here's another command that will enable a module

    $ php bin/magento module:enable Foo_Bar

You'll notice the `module:enable` command also accepts an argument -- `Foo_Bar`.  It's arguments all the way down!  You can see an example of the arguments, and options, supported by each command by using the `help` command

    $ php bin/magento help module:enable 

BE CAREFUL: Command line scripts are incredibly powerful, but they often come without guard rails.  In addition, Magento often ships command line scripts that aren't as well tested as more common application code paths.  A bug in a command line script or a misuse of a bug-free script could damage your system in a way that might take hours, or even days, to fix.  Don't use a command line script that you don't understand (or that you don't find in a tutorial you trust), and it's always a good idea to test out a command that's new to you in a backed up development environment.

Finally, as mentioned, these commands are just PHP code.  For example, you can find the source to the `module:enable` command here

    vendor/magento/magento2-base/setup/src/Magento/Setup/Console/Command/ModuleEnableCommand.php
