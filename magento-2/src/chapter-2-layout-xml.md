# Building Layouts via XML

In Chapter 1, we spent a lot of time using PHP code to create block objects.  Towards the end, you may have noticed the PHP code involved was growing in both size and complexity.  Depending on your comfort level and expertise with PHP, this was somewhere between a "mild annoyance" and an "occasion to go into the stairwell and cry".

The need to expose non-expert programmers to this much systems level code is a common problem in programmatic systems.  One industry solution to this problem is to use something called a *Domain Specific Language*, or DSL.  Domain Specific Languages are still programming languages, but they

1. Offer reduced syntax and functionality when compared to a regular programming language
2. Are designed to solve a specific problem, sometimes called the problem domain
3. Are often simple enough to implement in a configuration based language (YAML, JSON, XML)

While you *can* create Magento page layouts directly with PHP code, Magento offers an XML based DSL they'd prefer you use.  This XML based language allows a front end developer to create and control their HTML layouts without writing a single line of PHP code.  In this chapter, we'll start to explore this XML based language and how it compares to the PHP based code we've used so far. 

## Starting with Containers

Like chapter 1, chapter 2 has a pre-built controller file that's ready to go.  It should have an execute method that looks like the following

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();        
        $layout        = $objectManager->get('Magento\Framework\View\Layout');        
        $layout->addContainer('top', 'The top level container');  
        
        $blockOne   = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_chapter2_block1'
        );
        $blockOne->setTemplate('Pulsestorm_Nofrillslayout::chapter2/block1.phtml');
        
        $blockTwo    = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_chapter2_block2'
        );
        $blockTwo->setTemplate('Pulsestorm_Nofrillslayout::chapter2/block2.phtml');
                      
        $structure = $layout->getStructure(); //note: not standard magento
        $structure->setAsChild('pulsestorm_nofrills_chapter2_block1', 'top');
        $structure->setAsChild('pulsestorm_nofrills_chapter2_block2', 'top');
        
        $layout->generateElements();
        echo $layout->getOutput();       
    }
    
Everything in the above `execute` was covered in chapter 1.  If you load the following URL in your Magento system

    http://magento.example.com/pulsestorm_nofrillslayout/chapter2
    
you should see output that looks similar to the following

    ## First!

    This is the first block. It's the loneliest block.

    ## Second!

    This is the second block. It's the loneliest block since the 
    block pulsestorm_nofrills_chapter2_block1.    

Let's replace our `execute` method with the following code.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();        
        $layout        = $objectManager->get('Magento\Framework\View\Layout');        
        
        //START new code
        $updateManager = $layout->getUpdate();  
        $updateManager->addUpdate(
            '<container name="top"></container>'
        );         
        //END   new code
        
        $blockOne   = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_chapter2_block1'
        );
        $blockOne->setTemplate('Pulsestorm_Nofrillslayout::chapter2/block1.phtml');
        
        $blockTwo    = $layout->createBlock(
            'Magento\Framework\View\Element\Template',
            'pulsestorm_nofrills_chapter2_block2'
        );
        $blockTwo->setTemplate('Pulsestorm_Nofrillslayout::chapter2/block2.phtml');
                      
        $structure = $layout->getStructure(); //note: not standard magento
        $structure->setAsChild('pulsestorm_nofrills_chapter2_block1', 'top');
        $structure->setAsChild('pulsestorm_nofrills_chapter2_block2', 'top');
        
        //START new code (commenting out structure)
        //$layout->generateElements();
        //END   new code 
        echo $layout->getOutput();       
    }
    
The biggest change you'll notice here is we've removed the line which creates our container, 

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    $layout->addContainer('top', 'The top level container');  

and replaced it with the following. 

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    //START new code
    $updateManager = $layout->getUpdate();  
    $updateManager->addUpdate(
        '<container name="top"></container>'
    );         
    //END   new code    

This is our first example of Magento's domain specific language for creating layouts.  Clear your cache, reload the page -- and everything should still look the same, despite our change!

## Updating the Layout
    
Before we can explain the new XML, we'll need to talk a little more PHP.  

The following code fetches a special object called the "Update Manager" from the layout object.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    /**
     * @var Magento\Framework\View\Model\Layout\Merge
     */
    $updateManager = $layout->getUpdate();    

This object is responsible for keeping track of the changes a client programmer  wants to make to the layout.  i.e. It manages the XML updates.

Next, we used the update manager's `addUpdate` method to update the layout. 

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    $updateManager->addUpdate(
        '<container name="top" label="The top level container"></container>'
    ); 

A *Layout Update* is a chunk of XML.  Unless you have a deep software engineering background, you probably think of XML as a configuration language.  A layout update isn't, strictly speaking, configuration.  Instead, it is code written in a Domain Specific programming Language (DSL).  When you say

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    <container name="top" label="The top level container"></container>
    
Magento will, behind the scenes, transform this XML into PHP code that looks like (or, for the sticklers, behaves like) the following

    $objectManager = $this->getObjectManager();                
    $layout        = $objectManager->get('Magento\Framework\View\Layout');        
    $layout->addContainer('top', 'The top level container');   

Domain specific programming languages exist to limit the number of things you can do with a system, reduce the complexity of a system, and to allow non-or-new programmers the ability to to write code that does simple-but-important things.  

Ideally, you shouldn't need to think about the PHP code generated by the DSL -- however, understanding the basics of how a DSL works is useful when debugging DSL code.  

Before we move on, you may be wondering why we commented out `generateElements`.

    //START new code (commenting out structure)
    //$layout->generateElements();
    //END   new code 
    
We will need to beg off discussing `generateElements` for the time being.  We'll discuss this method more in chapter six.  

## Creating Blocks with Updates

Layout updates can do more than create containers.  Change your controller `execute` method so it matches the following.  

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php
    public function execute()
    {
        $objectManager = $this->getObjectManager();        
        $layout        = $objectManager->get('Magento\Framework\View\Layout');        

        $updateManager = $layout->getUpdate(); 

        $updateManager->addUpdate(
            '<container name="top"></container>'
        );        
        
        $updateManager->addUpdate(
            '<referenceContainer name="top">
                <block 
                    class="Magento\Framework\View\Element\Template" 
                    name="pulsestorm_nofrills_chapter2_block1"
                    template="Pulsestorm_Nofrillslayout::chapter2/block1.phtml">                  
                </block>
            </referenceContainer>'
        ); 
        
        echo $layout->getOutput();    
    }
    
You'll notice we've removed the PHP code that instantiated the two blocks and added those blocks to the `top` container.  Instead, we've added a second chunk of XML to the update manager via its `addUpdate` method.

If you **clear Magento's cache** and reload the page, you should see a single rendered block

    #First!

    This is the first block. It's the loneliest block.

If we take a closer look at our layout update XML

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    <referenceContainer name="top">
        <block 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrills_chapter2_block1"
            template="Pulsestorm_Nofrillslayout::chapter2/block1.phtml">                  
        </block>
    </referenceContainer>   
    
we'll see two new tags -- `referenceContainer` and `block`.  In plain english, this XML tells Magento

> For each block tag inside the container tag, instantiate a new block object, and then add those blocks to the container named `top`

Or, in PHP pseudo code

    $container = $layout->getContainer('top');
    $block     = new Magento\Framework\View\Element\Template;
    $block  ->setNameInLayout('pulsestorm_nofrills_chapter2_block1')
            ->setTemplate('Pulsestorm_Nofrillslayout::chapter2/block1.phtml');
    $container->addBlock($block);
    
The `pulsestorm_nofrills_chapter2_block1` block gets added to the container top because it's **inside** the `referenceContainer` tag.    
    
Taking a closer look at the block XML itself

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    <block 
        class="Magento\Framework\View\Element\Template" 
        name="pulsestorm_nofrills_chapter2_block1"
        template="Pulsestorm_Nofrillslayout::chapter2/block1.phtml">                  
    </block>    

The `class` attribute tells magento which PHP class to use when instantiating the block (same as the class we used in the object manager earlier).  The `name` attribute gives the block a unique identifier in the layout (same as the second argument to `createBlock`), and the `template` attribute sets a template URN (same as the `setTemplate` method we used earlier).

Before we continue, there's a bit of pedagogical infrastructure we need to address:  Namely -- let's get our XML strings into stand-alone files.

## Moving to Files

For readers with programming experience, you're probably a little weirded out by our use of XML in PHP string literals.  This is, generally speaking, a bad default practice. Let's remove those strings and move our XML updates to files.

First, we'll create two XML files with the following contents for our updates

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/top-container.xml
    <container name="top"></container>

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml
    <referenceContainer name="top">
        <block 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrills_chapter2_block1"
            template="Pulsestorm_Nofrillslayout::chapter2/block1.phtml">                  
        </block>
    </referenceContainer>  

Next, we'll replace our controller's execute method with the following.  

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

    public function execute()
    {
        $objectManager = $this->getObjectManager();        
        $layout        = $objectManager->get('Magento\Framework\View\Layout');        
        //$layout->addContainer('top', 'The top level container');  
        $updateManager = $layout->getUpdate(); 

        $container_xml = $this->loadXmlFromSampleXmlFolder('chapter2/user/top-container.xml');
        $updateManager->addUpdate($container_xml);        
        
        $block_xml     = $this->loadXmlFromSampleXmlFolder('chapter2/user/blocks.xml');
        $updateManager->addUpdate($block_xml); 
        
        // $layout->generateElements();
        echo $layout->getOutput();
    }
    
This code should be functionally identical to our previous code.  What we've done is replace the XML string literals with calls to the `loadXmlFromSampleXmlFolder` method.

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Chapter2/Index.php

        $container_xml = $this->loadXmlFromSampleXmlFolder('chapter2/user/top-container.xml');
        $updateManager->addUpdate($container_xml);  
        
The `loadXmlFromSampleXmlFolder` method is not part of stock Magento. It's something we've written to make working through these tutorials a bit easier.  Being able to store XML in files means we get all the syntax highlighting, formatting, and searching abilities most text editors and IDEs bring to XML files.  Using "raw" PHP string literals makes it too easy to introduce errors that are hard to spot.   If you're curious in the implementation of `loadXmlFromSampleXmlFolder`, you can find it here

    #File: app/code/Pulsestorm/Nofrillslayout/Controller/Base.php
    protected function loadXmlFromSampleXmlFolder($path)
    {
        $path = realpath(__DIR__) . '/../sample-xml/'  . $path;
        //using the hated error surpression operator 
        //to avoid xsi:type warnings from simple XML 
        @$xml = simplexml_load_file($path);        
        if(!$xml)
        {
            throw new \Exception("Could not load valid XML from $path");
        }
        return str_replace('<?xml version="1.0"?>', '', $xml->asXml());
    }  
    
Alright! With our XML in files, lets return to our programming!

## Multiple Blocks

XML updates aren't limited to a single block.  Try changing your `blocks.xml` so it looks like this.

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml

    <referenceContainer name="top">
        <block 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrills_chapter2_block1"
            template="Pulsestorm_Nofrillslayout::chapter2/block1.phtml">                  
        </block>
    
        <!-- START: added this XML -->
        <block 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrills_chapter2_block2"
            template="Pulsestorm_Nofrillslayout::chapter2/block2.phtml">                  
        </block>    
        <!-- END:   added this XML -->        
        
    </referenceContainer>  
     
Clear your cache, and reload the page.  You should see the second block rendered!
    
## Parent/Child Blocks

You'll remember back in chapter 1 we showed you you parent/child blocks worked.

    $blockParent   = $layout->createBlock('Magento\Framework\View\Element\Template');
    $blockParent->setTemplate('Pulsestorm_Nofrillslayout::chapter1/parent.phtml');
    
    $blockChild    = $layout->createBlock('Magento\Framework\View\Element\Template');
    $blockChild->setTemplate('Pulsestorm_Nofrillslayout::chapter1/child.phtml');
 
    $blockParent->append($blockChild);   
      
Parent/child blocks are **also** possible with XML Updates.  Give the following a try in `blocks.xml`.

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml

    <referenceContainer name="top">
        <block 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrills_chapter2_parent"
            template="Pulsestorm_Nofrillslayout::chapter2/parent.phtml">                  
                <block 
                    class="Magento\Framework\View\Element\Template" 
                    name="pulsestorm_nofrills_chapter2_child"
                    template="Pulsestorm_Nofrillslayout::chapter2/child.phtml">                  
                </block>        
        </block>    
    </referenceContainer>
    
Notice we've configured the `pulsestorm_nofrills_chapter2_block1` block **as a child node** of the `pulsestorm_nofrills_chapter2_parent` block node.  When we nest blocks like this, Magento knows to add the inner blocks as children of the outer blocks.

## Introducing Arguments

Another neat feature of the layout DSL is the ability to pass in data arguments to your templates.  For example, replace your `blocks.xml` file with the following

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml
    <referenceContainer name="top">
        <block 
            class="Magento\Framework\View\Element\Template" 
            name="pulsestorm_nofrills_chapter2_hello_argument"
            template="Pulsestorm_Nofrillslayout::chapter2/user/hello-argument.phtml">                  
                <arguments>
                    <argument name="the_message" xsi:type="string">Hello World</argument>
                </arguments>
        </block> 
    </referenceContainer>
    
Notice that we've created new `<argument/>` nodes under a new `<arguments/>` node.  Next, lets create a template for our new block.

    #File: app/code/Pulsestorm/Nofrillslayout/view/frontend/templates/chapter2/user/hello-argument.phtml
    <p>
        Printed with getData: <?php echo $block->getData('the_message'); ?>
    </p>

    <p>
        Printed with magic methods: <?php echo $block->getTheMessage(); ?>
    </p>
    
Reload the page (after clearing your cache) and you should see output similar to the following

    Printed with getData: Hello World

    Printed with magic methods: Hello World        

The `<arguments/>` node is deceptively named -- its real purpose is to set *data properties* on a block object.  Any named `<argument/>` will be available to the block Magento instantiates.  Eagle eyed readers may have noticed the `xsi:type` attribute that was part of the argument node.  

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml
    <argument name="the_message" xsi:type="string">

The `xsi:type` node allows you to pass in arguments that are more complex than simple scaler values.  For example, you could pass in an array of values using `xsi:type="array"`

    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml

    <arguments>
        <argument name="the_message" xsi:type="string">Hello World</argument>
        <argument name="the_array" xsi:type="array">
            <item name="key" xsi:type="string">value</item>
            <item name="foo" xsi:type="string">var</item>
        </argument>
    </arguments>

You can even tell Magento to instantiate a new object with `xsi:type="object"`

    @highlightsyntax@php
    #File: app/code/Pulsestorm/Nofrillslayout/sample-xml/chapter2/user/blocks.xml

    <arguments>
        <!-- ... -->
        <argument name="the_object" xsi:type="object">Pulsestorm\Nofrillslayout\Chapter2\Example</argument>                
    </arguments>

Try accessing both the array and the object in the `phtml` template files via the `getData` method, or via the block's magic methods (`getTheArray`, `getTheObject`) on your own.

## Pulling it All Together

Before we move on, we've prepared one last example for you.  In our controller file, let's try loading the prepared `page.xml` file instead of the `user/blocks.xml` file.

    //$block_xml     = $this->loadXmlFromSampleXmlFolder('chapter2/user/blocks.xml');
    $block_xml     = $this->loadXmlFromSampleXmlFolder('chapter2/page.xml');
        
Clear your cache, reload the page, and you'll find a crude example of the skeleton for an HTML page layout. 

    | Home | About | Etc |
    
    This is where the content goes.
    
    © 2017 Acme Widgets

If you open up `sample-xml/chapter2/page.xml`, you'll see

1. A root block with a root.phtml template
2. Three child blocks, `navigation`, `content`, and `footer`, each with their own templates
3. A custom template block class (that extends the base Magento template block)
4. An argument to set the content
5. An argument to add links
6. Templates that use custom PHP logic to output their arguments

While overly simple, this same sort of system is how Magento 2 builds the HTML for its own pages.  In the coming chapters we'll explore how Magento scales this simple concept to its own, very complex, HTML page layouts.