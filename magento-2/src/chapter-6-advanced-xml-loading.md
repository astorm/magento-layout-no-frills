# Advanced XML Loading

Compared to Magento 1, the code behind Magento 2's layout rendering is woefully complicated. There's no clear story to be told about *how* a Magento 2 page renders.  There is, perhaps, a story to be told about what happens when a team of engineers sets out to refactor and enhancing a domain specific language they neither use nor fully understand. That is, however, a story for another day.  

While Magento 2's layout system uses many of the same objects as Magento 1's layout system and still uses XML as its medium for a domain specific language, *how* Magento 2 uses these objects has changed dramatically. There are also new, similarly named objects that perform new tasks, heretofore unseen in the world of Magento layouts.  

This preamble is to warn you that this section will be both difficult and rarely necessary for day-to-day work. Skip ahead to the front end chapters if you like. 

## Layout Merge/Update Object

In Magento 1, the most important layout object was the `Mage::getSingleton('core/layout')` object.  This object lives on in Magento 2 as the `Magento\Framework\View\Layout` object, but it's no longer the most important layout object. 

Instead, if you want to understand **how** a layout gets built in Magento, you need to understand a slew of different classes.  

Layout files are loaded by the `Magento\Framework\View\Model\Layout\Merge` objects.  Code from this class has **two** responsibilities.  First, it loads **all** possible layout handle XML files into a single XML tree that looks something like this

    <layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <handle id="...">
            <!-- ... -->
        </handle>
        <handle id="...">
            <!-- ... -->
        </handle>
        
        <!-- etc... -->
    </layouts>

This is all the layout handle XML files in modules, and all the layout handle XML files in the current theme.  This also includes the `page_layout` files we learned about in Chapter 4.  

Each individual file is represented by a single `<handle id="...">` node.  The bulk of this work is done here.

    #File: vendor/magento/framework/View/Model/Layout/Merge.php
    protected function _loadFileLayoutUpdatesXml()
    {
        //...
    }

We'll call this gigantic tree of nodes the *Global XML Handle Tree*.

There's one other job for `Magento\Framework\View\Model\Layout\Merge` objects.  These objects can return portions of the the Global XML Handle Tree based on handle ID.  Programmers tell this object

> Please give me the XML nodes for the following handles: `default`, `customer_account_login`, etc.

and the object returns *just* those XML nodes that are inside `<handle/>` nodes with matching IDs.   Each returned node is known individually as an "update".  This work is kicked off via the `load` method.

    #File: vendor/magento/framework/View/Model/Layout/Merge.php
    public function load($handles = [])
    {
        if (is_string($handles)) {
            $handles = [$handles];
        } elseif (!is_array($handles)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Invalid layout update handle')
            );
        }

        $this->addHandle($handles);

        /* ... */

        foreach ($this->getHandles() as $handle) {
            $this->_merge($handle);
        }

        $layout = $this->asString();
        /* ... */
    }

and then continues in the `_merge` method.

    #File: vendor/magento/framework/View/Model/Layout/Merge.php
    protected function _merge($handle)
    {
        if (!isset($this->allHandles[$handle])) {
            $this->allHandles[$handle] = $this->handleProcessing;
            $this->_fetchPackageLayoutUpdates($handle);
            $this->_fetchDbLayoutUpdates($handle);
            $this->allHandles[$handle] = $this->handleProcessed;
        } elseif ($this->allHandles[$handle] == $this->handleProcessing
            && $this->appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER
        ) {
            $this->logger->info('Cyclic dependency in merged layout for handle: ' . $handle);
        }
        return $this;
    }

After calling `load`, the `Magento\Framework\View\Model\Layout\Merge` object will have a populated `->updates` property, which is an array of XML strings.  Programmers can fetch these results using the `Magento\Framework\View\Model\Layout\Merge` object's `asArray`, `asString`, or `asSimplexml` methods.  The resulting set of nodes is very similar to the Page Layout XML tree in Magento 1, so we'll continue to call it the Page Layout XML tree. 

## Reading XML, Generating Blocks

So far, all this is very similar to what happens in Magento 1.  There's small differences -- instead of multiple handles in a single layout file, Magento 2 uses a file's name as a handle identifier for the nodes inside.  However, the Global XML Handle Tree is still very similar to Magento 1's Package Layout XML tree.

At this point, if this were Magento 1, the `Mage::getSingleton('core/layout');` object would start parsing through the Page Layout XML tree and recursively generate blocks, and call action method nodes.  

    #File: app/code/core/Mage/Core/Model/Layout.php
    public function generateBlocks($parent=null)
    {
        if (empty($parent)) {
            $parent = $this->getNode();
        }
        foreach ($parent as $node) {
            $attributes = $node->attributes();
            if ((bool)$attributes->ignore) {
                continue;
            }
            switch ($node->getName()) {
                case 'block':
                    $this->_generateBlock($node, $parent);
                    $this->generateBlocks($node);
                    break;

                case 'reference':
                    $this->generateBlocks($node);
                    break;

                case 'action':
                    $this->_generateAction($node, $parent);
                    break;
            }
        }
    }
    
Magento 2 has the same goal -- a set of blocks, organized in a tree structure, with a single root block at the top.  However, it takes the long way to get there.  Magento 2 takes the Page Layout XML tree from the `Magento\Framework\View\Model\Layout\Merge` object and

1. Hands it off to a series of reader objects, organized under a ReaderPool.
2. Each reader object will, based on what they find inside the Page Layout XML tree, call methods on the `Magento\Framework\View\Layout\ScheduledStructure` object. This schedules certain actions for later.
3. Once Magento has read the XML files and scheduled actions, control is passed to a series of Generator objects, organized under a GeneratorPool.
4. Generators use the information in the `Magento\Framework\View\Layout\ScheduledStructure` block to do three things.
5. First, generators create block objects.
6. Second, generators keep track of the parent block relationships between blocks and containers via the `Magento\Framework\View\Layout\Data\Structure` object.
7. Third, for non-block/container features (front end assets, page title, etc), the generators make note of this information in the `Magento\Framework\View\Page\Config\Structure` object.

Phew! Our apologies.  That's a lot of information to dump on you at once.  Don't worry if you didn't follow it all at first.  We'll try to get to everything.  Before we do that though -- once Magento has done all of the above, Magento will use the structure and layout objects we've mentioned to populate some view variables

    #File: vendor/magento/framework/View/Result/Page.php
    protected function render(ResponseInterface $response)
    {
        $this->pageConfig->publicBuild();
        if ($this->getPageLayout()) {
            $config = $this->getConfig();
            $this->addDefaultBodyClasses();
            $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
            $requireJs = $this->getLayout()->getBlock('require.js');
            $this->assign([
                'requireJs' => $requireJs ? $requireJs->toHtml() : null,
                'headContent' => $this->pageConfigRenderer->renderHeadContent(),
                'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
                'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
                'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
                'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),
                'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif'),
            ]);

            $output = $this->getLayout()->getOutput();
            $this->assign('layoutContent', $output);
            $output = $this->renderPage();
            $this->translateInline->processResponseBody($output);
            $response->appendBody($output);
        } else {
            parent::render($response);
        }
        return $this;
    }

And then, in `renderPage`, use those variables to render a `phtml` template via a PHP `include`.

    #File: vendor/magento/framework/View/Result/Page.php
    protected function renderPage()
    {
        $fileName = $this->viewFileSystem->getTemplateFileName($this->template);
        if (!$fileName) {
            throw new \InvalidArgumentException('Template "' . $this->template . '" is not found');
        }

        ob_start();
        try {
            extract($this->viewVars, EXTR_SKIP);
            include $fileName;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        $output = ob_get_clean();
        return $output;
    }

These are the same variables and templates we covered briefly in Chapter 4.  
    
## Reader Pools
    
After `Magento\Framework\View\Model\Layout\Merge` does its work, most of the action for rendering a Magento layout kicks off here

    #File: vendor/magento/framework/View/Layout.php
    public function generateElements()
    {
        /* ... */
        if ($result) {
            /* ... */
        } else {
            /* ... */
            $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
            /* ... */
        }

        /* ... */
        
        $this->generatorPool->process($this->getReaderContext(), $generatorContext);

        /* ... */
        
        $this->addToOutputRootContainers();
        \Magento\Framework\Profiler::stop(__CLASS__ . '::' . __METHOD__);
    }

We've commented out a lot of the extraneous code to draw attention to the reader pool

    #File: vendor/magento/framework/View/Layout.php

    $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
    
the generator pool

    #File: vendor/magento/framework/View/Layout.php

    $this->generatorPool->process($this->getReaderContext(), $generatorContext);

and a final command that tells the layout object which structure blocks it should start rendering (which kicks off the rendering of children)

    #File: vendor/magento/framework/View/Layout.php

    $this->addToOutputRootContainers();

Examining the call to the readerPool's interpret method

    #File: vendor/magento/framework/View/Layout.php

    $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
    
The `$this->getNode()` method returns the tree we've been calling the Page Layout XML tree.  As a reminder, the Page Layout XML tree is the XML that comes from loading **all** the layout related XML files in the system, and then plucking out nodes associated with a specific handle name.  This tree will look something like the following

    <?xml version="1.0"?>
    <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <body>
           <referenceContainer name="header.panel">
              <block class="Magento\Directory\Block\Currency" name="currency" before="store_language" template="currency.phtml"/>
           </referenceContainer>
        </body>

        <head>
           <meta name="viewport" content="width=device-width, initial-scale=1"/>
           <css src="mage/calendar.css"/>
           <script src="requirejs/require.js"/>
        </head>

        <body>
           <referenceContainer name="after.body.start">
              <block class="Magento\Framework\View\Element\Js\Components" name="head.components" as="components" template="Magento_Theme::js/components.phtml" before="-"/>
           </referenceContainer>
        </body>

        <!-- ... more nodes -->
    </layout>

The `readerPool` property is a `Magento\Framework\View\Layout\ReaderPool` object.  Its `interpret` method looks like the following

    #File: vendor/magento/framework/View/Layout/ReaderPool.php

    public function interpret(Reader\Context $readerContext, Layout\Element $element)
    {            
        $this->prepareReader($this->readers);
        /** @var $node Layout\Element */
        foreach ($element as $node) {
            $nodeName = $node->getName();
            if (!isset($this->nodeReaders[$nodeName])) {
                continue;
            }
            /** @var $reader Layout\ReaderInterface */
            $reader = $this->nodeReaders[$nodeName];
            $reader->interpret($readerContext, $node, $element);
        }
        return $this;
    }

Here's where things get a little weird.  On the surface, this code is simple enough. The loop `foreach`es over the top level nodes of the layout update XML (`<body/>`, `<head/>`, and `<body/>` in the above example).  

If there's no reader object in the `nodeReaders` array for the named node

    #File: vendor/magento/framework/View/Layout/ReaderPool.php

    // $nodeName = 'body';
    if (!isset($this->nodeReaders[$nodeName])) {
        continue;
    }

then Magento skips on to the next one.  Otherwise, Magento calls the reader's `interpret` method, passing along the context object, the specific child node (`$node`), and its parent (`$element`).  

The `nodeReaders` object are configured via automatic constructor dependency injection, via a virtualType.  The readerPool is injected into the `Magento\Framework\View\Layout` object here

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Layout">
        <arguments>
            <argument name="readerPool" xsi:type="object" shared="false">commonRenderPool</argument>
            <argument name="cache" xsi:type="object">Magento\Framework\App\Cache\Type\Layout</argument>
        </arguments>
    </type>

and the `commonRenderPool` virtual type is configured here.  
    
    #File: vendor/magento/magento2-base/app/etc/di.xml
    <virtualType name="commonRenderPool" type="Magento\Framework\View\Layout\ReaderPool">
        <arguments>
            <argument name="readers" xsi:type="array">
                <item name="html" xsi:type="string">Magento\Framework\View\Page\Config\Reader\Html</item>
                <item name="head" xsi:type="string">Magento\Framework\View\Page\Config\Reader\Head</item>
                <item name="body" xsi:type="string">Magento\Framework\View\Page\Config\Reader\Body</item>
                <item name="container" xsi:type="string">Magento\Framework\View\Layout\Reader\Container</item>
                <item name="block" xsi:type="string">Magento\Framework\View\Layout\Reader\Block</item>
                <item name="move" xsi:type="string">Magento\Framework\View\Layout\Reader\Move</item>
                <item name="uiComponent" xsi:type="string">Magento\Framework\View\Layout\Reader\UiComponent</item>
            </argument>
        </arguments>
    </virtualType>         

The readers are strings that the `prepareReader` method will use to to instantiate actual reader objects.

    #File: vendor/magento/framework/View/Layout/ReaderPool.php

    protected function prepareReader($readers)
    {
        if (empty($this->nodeReaders)) {
            /** @var $reader Layout\ReaderInterface */
            foreach ($readers as $readerClass) {
                $reader = $this->readerFactory->create($readerClass);
                $this->addReader($reader);
            }
        }
    }      
    
Also, each reader object can support multiple tag/node types (`container` and  `containerReference` are just two examples).  The `addReader` method makes sure that each reader object is indexed in the `nodeReaders` array via the node names it supports.

    #File: vendor/magento/framework/View/Layout/ReaderPool.php
    public function addReader(Layout\ReaderInterface $reader)
    {
        foreach ($reader->getSupportedNodes() as $nodeName) {
            $this->nodeReaders[$nodeName] = $reader;
        }
        return $this;
    }

This is pretty heavy on Object Manager concepts (which you can read about in the appendix), but not **too** bad, right?  Let's take a look at one of those reader objects

    public function interpret(
        Layout\Reader\Context $readerContext,
        Layout\Element $bodyElement
    ) {
        /** @var \Magento\Framework\View\Layout\Element $element */
        foreach ($bodyElement as $element) {
            if ($element->getName() === self::BODY_ATTRIBUTE) {
                $this->setBodyAttributeToStructure($readerContext, $element);
            }
        }
        $this->readerPool->interpret($readerContext, $bodyElement);
        return $this;
    }

This is the code Magento uses to parse the `<body/>` nodes of the layout updates.  When it's done parsing, you'll notice a call here

    $this->readerPool->interpret($readerContext, $bodyElement);
    
The individual `Magento\Framework\View\Page\Config\Reader\Body` object has **it's own** `ReaderPool` object.  Your instincts might be that this is the same `ReaderPool` object as before. Those instincts are normal, but in this case, incorrect. While the injected object uses the same `Magento\Framework\View\Layout\ReaderPool` class

    #File: vendor/magento/framework/View/Page/Config/Reader/Body.php
    public function __construct(
        \Magento\Framework\View\Layout\ReaderPool $readerPool)
    {
        $this->readerPool = $readerPool;
    }
    
Thanks to some more virtual type shenanigans, this reader pool uses a different virtual type (`bodyRenderPool`).  

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Page\Config\Reader\Body">
        <arguments>
            <argument name="readerPool" xsi:type="object">bodyRenderPool</argument>
        </arguments>
    </type>

That virtual type's definition configures a **different** set of reader pools

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <virtualType name="bodyRenderPool" type="Magento\Framework\View\Layout\ReaderPool">
        <arguments>
            <argument name="readers" xsi:type="array">
                <item name="container" xsi:type="string">Magento\Framework\View\Layout\Reader\Container</item>
                <item name="block" xsi:type="string">Magento\Framework\View\Layout\Reader\Block</item>
                <item name="move" xsi:type="string">Magento\Framework\View\Layout\Reader\Move</item>
                <item name="uiComponent" xsi:type="string">Magento\Framework\View\Layout\Reader\UiComponent</item>
            </argument>
        </arguments>
    </virtualType>

This means nodes inside the `<body/>` node will only be scanned by the container, block, move, and uiComponent readers. 

Many of the other readers also have their own reader pools. A reader's reader pool can contain a reference to itself, which is how Magento reads nested `container` and nested `block` configurations.  

Whether using a reader pool and individual readers to parse through the XML files is *the right* thing to do is a judgment call.  One thing that's clear though -- this system makes full use of Magento's object manager and automatic constructor dependency injection systems, and can be particularly difficult to debug with so much object instantiation happening magically for us. 

## What does a Reader Do?

Putting aside the complicated pool mechanics, what do work do reader objects do?  Two things.  First, depending on the data they find in the XML file, they call methods on a "Scheduled Structure" object.

    #File: vendor/magento/framework/View/Layout/Reader/Block.php
    protected function scheduleReference(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $elementName = $currentElement->getAttribute('name');
        $elementRemove = filter_var($currentElement->getAttribute('remove'), FILTER_VALIDATE_BOOLEAN);
        if ($elementRemove) {
            $scheduledStructure->setElementToRemoveList($elementName);
        } else {
            $data = $scheduledStructure->getStructureElementData($elementName, []);
            $data['attributes'] = $this->mergeBlockAttributes($data, $currentElement);
            $this->updateScheduledData($currentElement, $data);
            $this->evaluateArguments($currentElement, $data);
            $scheduledStructure->setStructureElementData($elementName, $data);
        }
    }
    
This `Magento\Framework\View\Layout\ScheduledStructure` object is a way for programmers to tell Magento what sort of block objects Magento should create when it creates the layout, or what sort of additional actions it should take w/r/t to those object.  Regarding the later, the move reader has a good example of this

    #File: vendor/magento/framework/View/Layout/Reader/Move.php
    $scheduledStructure->setElementToMove(
        $elementName,
        [$destination, $siblingName, $isAfter, $alias]
    );     
    
When Magento parses through `<move/>` elements, it calls the `setElementToMove` method on the `Magento\Framework\View\Layout\ScheduledStructure`.  This is like Magento writing a reminder down on a list

> And then, I'll move the element named foo under the block named bar

However, this is **only** a list of instructions to be completed later.  The scheduled structure object is Magento's attempt to separate out out the **instructions** configured in the layout update XML files from the **actions** (creating a block, manipulating a block's children, etc.) to take. 

The other thing a reader will do is manipulate the `Magento\Framework\View\Page\Config\Structure` object.  This object keeps track of things like the page's title, and its front end asset files

    #File: vendor/magento/framework/View/Page/Config/Reader/Head.php
    public function interpret(
        Layout\Reader\Context $readerContext,
        Layout\Element $headElement
    ) {
        $pageConfigStructure = $readerContext->getPageConfigStructure();
        /* ... */
        $pageConfigStructure->addAssets($node->getAttribute('src'), 
            $this->getAttributes($node));
        /* ... */
    }

When it comes time to render these elements later, Magento will reference the `Magento\Framework\View\Page\Config\Structure` object. 
    
## Generator Objects

If we return to our `GenerateElements` method

    #File: vendor/magento/framework/View/Layout.php
    public function generateElements()
    {
        /* ... */
        if ($result) {
            /* ... */
        } else {
            /* ... */
            $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
            /* ... */
        }

        /* ... */
        
        $this->generatorPool->process($this->getReaderContext(), $generatorContext);

        /* ... */
        
        $this->addToOutputRootContainers();
        \Magento\Framework\Profiler::stop(__CLASS__ . '::' . __METHOD__);
    }    
    
Once Magento's finished running through the reader pools, it turns over control to the generator pool    

    #File: vendor/magento/framework/View/Layout.php

    $this->generatorPool->process($this->getReaderContext(), $generatorContext);
    
The generator pool is a `Magento\Framework\View\Layout\GeneratorPool` object.  If we take a look at its `process` method,

    #File: vendor/magento/framework/View/Layout/GeneratorPool.php
    public function process(Reader\Context $readerContext, Generator\Context $generatorContext)
    {
        $this->buildStructure($readerContext->getScheduledStructure(), $generatorContext->getStructure());
        foreach ($this->generators as $generator) {
            $generator->process($readerContext, $generatorContext);
        }
        return $this;
    }     
    
we'll see a `foreach` loop that runs through an array of generator objects and calls `process` on each one of them. However, before we investigate the generators in the pool, we should look at the call to `buildStructure`

    #File: vendor/magento/framework/View/Layout/GeneratorPool.php
    protected function buildStructure(ScheduledStructure $scheduledStructure, Data\Structure $structure)
    {
        //Schedule all element into nested structure
        while (false === $scheduledStructure->isStructureEmpty()) {
            $this->helper->scheduleElement($scheduledStructure, $structure, key($scheduledStructure->getStructure()));
        }
        $scheduledStructure->flushPaths();
        while (false === $scheduledStructure->isListToSortEmpty()) {
            $this->reorderElements($scheduledStructure, $structure, key($scheduledStructure->getListToSort()));
        }
        foreach ($scheduledStructure->getListToMove() as $elementToMove) {
            $this->moveElementInStructure($scheduledStructure, $structure, $elementToMove);
        }
        foreach ($scheduledStructure->getListToRemove() as $elementToRemove) {
            $this->removeElement($scheduledStructure, $structure, $elementToRemove);
        }
        foreach ($scheduledStructure->getIfconfigList() as $elementToCheckConfig) {
            list($configPath, $scopeType) = $scheduledStructure->getIfconfigElement($elementToCheckConfig);
            if (!empty($configPath)
                && !$this->scopeConfig->isSetFlag($configPath, $scopeType, $this->scopeResolver->getScope())
            ) {
                $this->removeIfConfigElement($scheduledStructure, $structure, $elementToCheckConfig);
            }
        }
        return $this;
    }
    
The `$scheduledStructure` object is the object populated during the runs through the readerPool.  The `$structure` object is something we haven't see yet, a  `Magento\Framework\View\Layout\Data\Structure` object.  This structure object is where Magento 2 keeps track of a layout's **structure**.  In Magento 1, the parent/child relationships between blocks were kept track of in the block objects themselves -- with each block having a reference to its child blocks.  Magento 2 has moved that data that tracks these relationships out of the blocks and into the `Magento\Framework\View\Layout\Data\Structure` object. 

In the `buildStructure` method mentioned above, Magento reads through the list of instructions built up during the reader pool run (stored in the `Magento\Framework\View\Layout\ScheduledStructure` object), and runs those instructions against the `Magento\Framework\View\Layout\Data\Structure` object.  

The first while loops runs through the scheduled structure and creates each element in the actual structure object (via `scheduleElement`).  

    #File: vendor/magento/framework/View/Layout/GeneratorPool.php

    while (false === $scheduledStructure->isStructureEmpty()) {
        $this->helper->scheduleElement($scheduledStructure, $structure, key($scheduledStructure->getStructure()));
    }

Then Magento runs through any requests to reorder elements

    #File: vendor/magento/framework/View/Layout/GeneratorPool.php

    while (false === $scheduledStructure->isListToSortEmpty()) {
        $this->reorderElements($scheduledStructure, $structure, key($scheduledStructure->getListToSort()));
    }
    
Then Magento runs through any requests to move an element from one place in the layout to another
    
    #File: vendor/magento/framework/View/Layout/GeneratorPool.php
    
    foreach ($scheduledStructure->getListToMove() as $elementToMove) {
        $this->moveElementInStructure($scheduledStructure, $structure, $elementToMove);
    }

Then Magento runs through any requests to remove any elements
    
    #File: vendor/magento/framework/View/Layout/GeneratorPool.php
    
    foreach ($scheduledStructure->getListToRemove() as $elementToRemove) {
        $this->removeElement($scheduledStructure, $structure, $elementToRemove);
    }
    
Finally, Magento runs through any requests to remove elements that fail an `ifconfig` check. 
    
    #File: vendor/magento/framework/View/Layout/GeneratorPool.php
    
    foreach ($scheduledStructure->getIfconfigList() as $elementToCheckConfig) {
        list($configPath, $scopeType) = $scheduledStructure->getIfconfigElement($elementToCheckConfig);
        if (!empty($configPath)
            && !$this->scopeConfig->isSetFlag($configPath, $scopeType, $this->scopeResolver->getScope())
        ) {
            $this->removeIfConfigElement($scheduledStructure, $structure, $elementToCheckConfig);
        }
    }

In each of those helper methods, Magento manipulates the `Magento\Framework\View\Layout\Data\Structure` object.  This `Magento\Framework\View\Layout\Data\Structure` object becomes the "source of truth" for what blocks are and aren't a part of Magento's layout.  

## The Generator Pools

One the `buildStructure` method finishes, the `process` method runs through each generator object

    #File: vendor/magento/framework/View/Layout/GeneratorPool.php
    public function process(Reader\Context $readerContext, Generator\Context $generatorContext)
    {
        $this->buildStructure($readerContext->getScheduledStructure(), $generatorContext->getStructure());
        foreach ($this->generators as $generator) {
            $generator->process($readerContext, $generatorContext);
        }
        return $this;
    } 
    
Generators are configured via the dependency injection system -- take note that these are `xsi:type="object"` items, meaning Magento will automatically instantiate these objects for us

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Layout\GeneratorPool">
        <arguments>
            <argument name="generators" xsi:type="array">
                <item name="head" xsi:type="object">Magento\Framework\View\Page\Config\Generator\Head</item>
                <item name="body" xsi:type="object">Magento\Framework\View\Page\Config\Generator\Body</item>
                <item name="block" xsi:type="object">Magento\Framework\View\Layout\Generator\Block</item>
                <item name="container" xsi:type="object">Magento\Framework\View\Layout\Generator\Container</item>
                <item name="uiComponent" xsi:type="object">Magento\Framework\View\Layout\Generator\UiComponent</item>
            </argument>
        </arguments>
    </type>
    
By and large, these generators make additional adjustments to the `Magento\Framework\View\Layout\Data\Structure` object.  The one big exception is the `Magento\Framework\View\Layout\Generator\Block` generator.  This generator is the one that actually instantiates the block objects.  This starts in the `generateBlock` method

    #File: vendor/magento/framework/View/Layout/Generator/Block.php
    protected function generateBlock(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Data\Structure $structure,
        $elementName
    ) {
        list(, $data) = $scheduledStructure->getElement($elementName);
        $attributes = $data['attributes'];

        if (!empty($attributes['group'])) {
            $structure->addToParentGroup($elementName, $attributes['group']);
        }
        if (!empty($attributes['display'])) {
            $structure->setAttribute($elementName, 'display', $attributes['display']);
        }

        // create block
        $className = $attributes['class'];
        $block = $this->createBlock($className, $elementName, [
            'data' => $this->evaluateArguments($data['arguments'])
        ]);
        if (!empty($attributes['template'])) {
            $block->setTemplate($attributes['template']);
        }
        if (!empty($attributes['ttl'])) {
            $ttl = (int)$attributes['ttl'];
            $block->setTtl($ttl);
        }
        return $block;
    }
    
It's worth tracing this method down through `createBlock`, and then `getBlockInstance`, but we'll leave that as an exercise for the reader.  Also, notice how the generator uses information from the scheduled structure to manipulate the actual structure object -- this is typical of the work done by the generators.      

## The Structure Object

If you've only interacted with the layout via XML files and blocks, you may be surprised to discover it's the structure object that's keeping track of everything.  However, if you look at a method you may have used from your block code -- `getChildBlock`

    #File: vendor/magento/framework/View/Layout.php
    public function getChildBlock($parentName, $alias)
    {
        $this->build();
        $name = $this->structure->getChildId($parentName, $alias);
        if ($this->isBlock($name)) {
            return $this->getBlock($name);
        }
        return false;
    }

you'll see the `$this->structure` object is the same `Magento\Framework\View\Layout\Data\Structure` object we've been talking about above.  Magento uses the structure object to fetch the child block's real name, and then returns the block. 

Returning one last time to our `generateElements` method

    #File: vendor/magento/framework/View/Layout.php
    
    public function generateElements()
    {
        /* ... */
        if ($result) {
            /* ... */
        } else {
            /* ... */
            $this->readerPool->interpret($this->getReaderContext(), $this->getNode());
            /* ... */
        }

        /* ... */
        
        $this->generatorPool->process($this->getReaderContext(), $generatorContext);

        /* ... */
        
        $this->addToOutputRootContainers();
        \Magento\Framework\Profiler::stop(__CLASS__ . '::' . __METHOD__);
    }  

We have the final `$this->addToOutputRootContainers();` to consider.  

    #File: vendor/magento/framework/View/Layout.php

    protected function addToOutputRootContainers()
    {
        foreach ($this->structure->exportElements() as $name => $element) {
            if ($element['type'] === Element::TYPE_CONTAINER && empty($element['parent'])) {
                $this->addOutputElement($name);
            }
        }
        return $this;
    }
    /* ... */
    public function addOutputElement($name)
    {
        $this->_output[$name] = $name;
        return $this;
    }    
    
Once the generators have finished creating the structure object, the `addToOutputRootContainers` will fetch every top-level *container* element and add them to the `_output` array.  This is the array of elements Magento will output when it's time to render the page

    #File: vendor/magento/framework/View/Layout.php
    public function getOutput()
    {
        $this->build();
        $out = '';
        foreach ($this->_output as $name) {
            $out .= $this->renderElement($name);
        }
        return $out;
    }
    
Remember, rendering a container means rendering every element inside of it.  Rendering a block also means rendering its child blocks, so these root containers kick-off rendering of *the entire* layout tree.     

## More Gotchas

That was a bit of a long and winding road, wasn't it?  Well, we're not quite done yet.  There's one other major change to how Magento makes use of the layout system.  In Magento 1, loading layout XML was a one time, singular event for each HTTP page request.   However, in Magento 2, most pages load **two** sets of layout handle XML files.  Some load even more.  

Take a break.  Drink some tea.  Pet your cat.  When you're ready and recharged, jump back in.

## Page Layout and Regular Layout

In Magento 1, every page was separated out into sections like `content`, `before-body-end`, etc.  Behind the scenes, these sections were a special sort of block -- a `text/list` block (`Mage_Core_Text_List`).  These Magento 1 blocks automatically render all their children.

As we've already learned, Magento replaced these `text/list` blocks with `<container/>` nodes.  We've also learned that Magento took the additional step of separating out the containers that make up individual pages into their own page layout XML files.

    $ ls -1 vendor/magento/module-theme/view/base/page_layout \
            vendor/magento/module-theme/view/frontend/page_layout

    vendor/magento/module-theme/view/base/page_layout:
    empty.xml

    vendor/magento/module-theme/view/frontend/page_layout:
    1column.xml
    2columns-left.xml
    2columns-right.xml
    3columns.xml

While these page layout files use the same syntax as a regular layout handle XML files, their updates **are not** loaded at the same time as the regular layout handle XML files.  

Additionally, consider the following code

    #File: vendor/magento/framework/Pricing/Render.php
    protected function _prepareLayout()
    {
        $this->priceLayout->addHandle($this->getPriceRenderHandle());
        $this->priceLayout->loadLayout();
        return parent::_prepareLayout();
    }
    
The price renderer adds a layout handle to a `priceLayout` property.  This also triggers a loading/parsing of the layout handle XML files.  Making things even more confusing, Magento calls the `_prepareLayout` method when blocks are instantiated and assigned to the layout object.  This means the above layout handle parsing happens well after Magento's passed through the Merge/ReaderPool/GeneratorPool process for the normal layout rules.

We're going to explain conceptually how this works, and then walk you through the execution chains that lead up to each of the above use cases.

## Multiple Merges

The key to understanding how these multiple merges work is the multiple instance `Magento\Framework\View\Model\Layout\Merge` objects, the multiple instance `Magento\Framework\View\Layout\ScheduledStructure` objects, and the multiple instance structure object (`Magento\Framework\View\Layout\Data\Structure`) we mentioned earlier.

First, unlike most automatic constructor dependency injection objects, the `Magento\Framework\View\Model\Layout\Merge` objects are **not** "single instance/singleton" objects.  This is important, as it means calls to the `load` method on individual `Magento\Framework\View\Model\Layout\Merge` objects **will not** share state with each other. This means loading and merging the `page_layout` XML files is completely separate from loading and merging the `layout` files, which is completely separate from the `priceLayout` invocations.

The way Magento goes about doing this is worth calling out.  Magento **does not** use the `shared=false` feature of the object manager system.  Instead, each individual Merge object is created via a factory object.  

Here's where it happens for the main layout object

    #File: vendor/magento/framework/View/Layout.php
    
    public function getUpdate()
    {            
        if (!$this->_update) {
            $theme = $this->themeResolver->get();
            $this->_update = $this->_processorFactory->create(['theme' => $theme]);
        }
        return $this->_update;
    }

Here's where it happens for the page layout object

    #File: vendor/magento/framework/View/Page/Layout/Reader.php
    protected function getPageLayoutMerge()
    {
        if ($this->pageLayoutMerge) {
            return $this->pageLayoutMerge;
        }
        $this->pageLayoutMerge = $this->processorFactory->create([
            'theme'       => $this->themeResolver->get(),
            'fileSource'  => $this->pageLayoutFileSource,
            'cacheSuffix' => self::MERGE_CACHE_SUFFIX,
        ]);
        return $this->pageLayoutMerge;
    }

Finally, here's where it happens for the price rendering layout object.

    #File: vendor/magento/framework/Pricing/Render/Layout.php
    public function __construct(
        LayoutFactory $layoutFactory,
        \Magento\Framework\View\LayoutInterface $generalLayout
    ) {
        $this->layout = $layoutFactory->create(['cacheable' => $generalLayout->isCacheable()]);
    }
    
We'll talk more about this when we trace out the method calls below.    

Another interesting thing about the `Magento\Framework\View\Model\Layout\Merge` objects -- each one still loads **every** layout XML file in the system.  Or, more accurately, the first object loads every layout XML file in the system. Any `Magento\Framework\View\Model\Layout\Merge` objects instantiated later read that same information from cache.  Even though the XML files are separated out into `page_layout` and `layout` folders, they're all loaded into one giant tree.  It's the layout handles that ensure each individual merge object returns only the updates we're after.

We also need to be aware that the `Magento\Framework\View\Layout\ScheduledStructure` object is also an unshared, multiple instance object.  This time, however, Magento uses their `di.xml` system to make sure of this.

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Layout\ScheduledStructure" shared="false" />

This is (again) important because since this is an instance object, it means there's a **different** set of rules and state at play for each run through the reader and generator pools.  

This is also true of the structure object itself

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Layout\Data\Structure" shared="false" />

This may give you pause.  The whole point of the `Magento\Framework\View\Layout\Data\Structure` is it keeps track of the entire page's structure.  From a certain point of view, it seems illogical that this wouldn't be a single instance/singleton object.

This is why it's important to understand how Magento 2 composes these objects and invokes the Merge/Reader/Generator process.

## Invoking the Merges

In the simplest case, a Magento controller's `execute` method returns a 

    Magento\Framework\View\Result\Page
    
object, and the system code calls this object's `render` method.  While Magento may instantiate multiple `Magento\Framework\View\Result\Page` objects, it only ever **renders** a single one.        

    #File: vendor/magento/framework/View/Result/Page.php
    protected function render(ResponseInterface $response)
    {        
        $this->pageConfig->publicBuild();
        /* ... */
        return $this;
    }

The first line of `render` is important -- the `pageConfig` object is a `Magento\Framework\View\Page\Config` object.  This is **different** from the `Magento\Framework\View\Page\Config\Structure` object we saw earlier.  This config object's `build` method looks like this

    #File: vendor/magento/framework/View/Page/Config.php
    /**
     * TODO Will be eliminated in MAGETWO-28359
     * @return void
     */
    public function publicBuild()
    {
        $this->build();
    }
    
    protected function build()
    {        
        if (!empty($this->builder)) {
            $this->builder->build();
        }
    }

The `builder` property, (a `Magento\Framework\View\Page\Builder` object), is our next stop.  The build method is defined on this object's parent, `Magento\Framework\View\Layout\Builder` class. 
    
    #File: vendor/magento/framework/View/Layout/Builder.php
    public function build()
    {
        if (!$this->isBuilt) {
            $this->isBuilt = true;
            $this->loadLayoutUpdates();
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
        }
        return $this->layout;
    }
    
Each of these methods -- `loadLayoutUpdates`, `generateLayoutXml`, and `generateLayoutBlocks` -- eventually call methods on a `layout` property.       

    #File: vendor/magento/framework/View/Layout/Builder.php

    protected function loadLayoutUpdates()
    {
        /* ... */
        $this->layout->getUpdate()->load();
        /* ... */
    }
        
    protected function generateLayoutXml()
    {
        /* ... */    
        $this->layout->generateXml();
        /* ... */
    }        

    protected function generateLayoutBlocks()
    {
        /* ... */    
        $this->layout->generateElements();
        /* ... */    
    }
    
This layout property comes from the automatic constructor dependency injection system injecting a `Magento\Framework\View\LayoutInterface` interface, which resolves to a `Magento\Framework\View\Layout` object.  We'll want to remember this for later.  

The next two methods we're interested in are `generateLayoutBlocks` (in the parent class) and `readPageLayout`.  

    #File: vendor/magento/framework/View/Page/Builder.php
    protected function generateLayoutBlocks()
    {
        $this->readPageLayout();
        return parent::generateLayoutBlocks();
    }

    protected function readPageLayout()
    {
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $readerContext = $this->layout->getReaderContext();
            $this->pageLayoutReader->read($readerContext, $pageLayout);
        }
    }

The `pageLayoutReader` is a `Magento\Framework\View\Page\Layout\Reader` object.  Its `read` method looks like this.

    #File: vendor/magento/framework/View/Page/Layout/Reader.php
    public function read(Layout\Reader\Context $readerContext, $pageLayout)
    {
        $this->getPageLayoutMerge()->load($pageLayout);
        $xml = $this->getPageLayoutMerge()->asSimplexml();
        $this->reader->interpret($readerContext, $xml);
    }

There's two really important, but subtle, things going on here.  The first is the `getPageLayoutMerge` method.

    #File: vendor/magento/framework/View/Page/Layout/Reader.php
    protected function getPageLayoutMerge()
    {
        if ($this->pageLayoutMerge) {
            return $this->pageLayoutMerge;
        }
        $this->pageLayoutMerge = $this->processorFactory->create([
            'theme'       => $this->themeResolver->get(),
            'fileSource'  => $this->pageLayoutFileSource,
            'cacheSuffix' => self::MERGE_CACHE_SUFFIX,
        ]);
        return $this->pageLayoutMerge;
    }       
    
This method uses a factory to instantiate a **fresh** `Magento\Framework\View\Model\Layout\Merge` object, and calls its load method here

    #File: vendor/magento/framework/View/Page/Layout/Reader.php

    $this->getPageLayoutMerge()->load($pageLayout);
    
This triggers a loading of the layout handle XML files into a Global XML Handle Tree, and then reduces it using the single handle in `$pageLayout` (which will be something like `1column`, `empty`, `2columns-left`, etc.  

Then, the `Magento\Framework\View\Page\Layout\Reader` object triggers a run through the reader pools with 

    #File: vendor/magento/framework/View/Page/Layout/Reader.php

    $xml = $this->getPageLayoutMerge()->asSimplexml();
    $this->reader->interpret($readerContext, $xml);     

This fetches the XML from the `Magento\Framework\View\Model\Layout\Merge` object with the `asSimpleXml` method (which will be XML from the `page_layout` folders **or** any other layout handle XML folder if a user has dropped a `1column.xml`, `empty.xml`, etc. in those locations)  Then, Magento starts the reader pool interpret process with 

    #File: vendor/magento/framework/View/Page/Layout/Reader.php

    $this->reader->interpret($readerContext, $xml);     

The `reader` property is a `Magento\Framework\View\Layout\ReaderPool` created with the `pageLayoutRenderPool` virtual type configuration.

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Page\Layout\Reader">
        <arguments>
            <argument name="pageLayoutFileSource" xsi:type="object">pageLayoutFileCollectorAggregated</argument>
            <argument name="reader" xsi:type="object">pageLayoutRenderPool</argument>
        </arguments>
    </type>

This gives the reader pool the following reader objects

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <virtualType name="pageLayoutRenderPool" type="Magento\Framework\View\Layout\ReaderPool">
        <arguments>
            <argument name="readers" xsi:type="array">
                <item name="container" xsi:type="string">Magento\Framework\View\Layout\Reader\Container</item>
                <item name="move" xsi:type="string">Magento\Framework\View\Layout\Reader\Move</item>
            </argument>
        </arguments>
    </virtualType>

All this is important because it means that, before Magento runs through its main layout reading process, some reader pools have already run on a separate set of XML files.  Also, notice the `$readerContext` object.  Magento grabbed this with `$this->layout->getReaderContext();` in the builder object. Remember, the builder's `layout` property is an automatic constructor dependency injection object, injected with the `Magento\Framework\View\LayoutInterface` interface, and resolves to a `Magento\Framework\View\Layout` object.

The reader context object is a `Magento\Framework\View\Layout\Reader\Context` object, and contains the `Magento\Framework\View\Layout\ScheduledStructure` and `Magento\Framework\View\Page\Config\Structure` objects that individual readers will use.  Keep this in mind, it will be important later.

Before we move on -- some of you may have noticed the reader object appears to configure a different file collector via the `$pageLayoutFileSource` constructor parameter

    #File: vendor/magento/magento2-base/app/etc/di.xml
    <type name="Magento\Framework\View\Page\Layout\Reader">
        <arguments>
            <argument name="pageLayoutFileSource" xsi:type="object">pageLayoutFileCollectorAggregated</argument>
            <argument name="reader" xsi:type="object">pageLayoutRenderPool</argument>
        </arguments>
    </type>

which is, in turn, used in the merge/update object

    #File: vendor/magento/framework/View/Page/Layout/Reader.php
    protected function getPageLayoutMerge()
    {
        if ($this->pageLayoutMerge) {
            return $this->pageLayoutMerge;
        }
        $this->pageLayoutMerge = $this->processorFactory->create([
            'theme'       => $this->themeResolver->get(),
            'fileSource'  => $this->pageLayoutFileSource,
            'cacheSuffix' => self::MERGE_CACHE_SUFFIX,
        ]);
        return $this->pageLayoutMerge;
    }

This would *seem* to run counter to what we're saying about the merge objects loading **every** XML file. However, if we look at the code that loads the XML files from the file system

    #File: vendor/magento/framework/View/Model/Layout/Merge.php
    protected function _loadFileLayoutUpdatesXml()
    {
        /* ... */
        $updateFiles = $this->fileSource->getFiles($theme, '*.xml');
        $updateFiles = array_merge($updateFiles, $this->pageLayoutFileSource->getFiles($theme, '*.xml'));
        //...
    }
    
we see that this `pageLayoutFileSource` is **an additional** source of files, and not a replacement.  So, the reader instantiating a merge object with a different set of files for the page layout is a red herring. 

This is potentially extra important since the results of `_loadFileLayoutUpdatesXml` are cached

    #File: vendor/magento/framework/View/Model/Layout/Merge.php
    public function getFileLayoutUpdatesXml()
    {
        if ($this->layoutUpdatesCache) {
            return $this->layoutUpdatesCache;
        }
        $cacheId = $this->generateCacheId($this->cacheSuffix);
        $result = $this->_loadCache($cacheId);
        if ($result) {
            $result = $this->_loadXmlString($result);
        } else {
            $result = $this->_loadFileLayoutUpdatesXml();
            $this->_saveCache($result->asXml(), $cacheId);
        }
        $this->layoutUpdatesCache = $result;
        return $result;
    }
    
This means if, the first time a merge object gets instantiated, the page layout files aren't properly included, the newer `Magento\Framework\View/Model/Layout/Merge` object may be missing them as well. 

## Loading the Rest of the Updates

Alight, coming back up to `generateLayoutBlocks`

    #File: vendor/magento/framework/View/Page/Builder.php
    protected function generateLayoutBlocks()
    {
        $this->readPageLayout();
        return parent::generateLayoutBlocks();
    }

We now know that, after `readPageLayout` finished, Magento will have run through the page layout handles and scheduled them into the `Magento\Framework\View\Layout\ScheduledStructure` object.  Let's jump to the **parent** `generateLayoutBlocks` method.

    #File: vendor/magento/framework/View/Layout/Builder.php
    protected function generateLayoutBlocks()
    {
        /* ... */

        /* generate blocks from xml layout */
        $this->layout->generateElements();

        /* ... */

        return $this;
    }

As previously mentioned, the builder's `layout` property is an injected  `Magento\Framework\View\LayoutInterface` interface, and resolves to a `Magento\Framework\View\Layout` object.  If we take a look at the `generateElements` method there

    #File: vendor/magento/framework/View/Layout.php
    public function generateElements()
    {
        /* ... */
        
        if ($result) {
            $this->readerContext = unserialize($result);
        } else {
            \Magento\Framework\Profiler::start('build_structure');
            $this->readerPool->interpret(
                $this->getReaderContext(), 
                $this->getNode()
            );
            /* ... */
        }

        $generatorContext = $this->generatorContextFactory->create(
            [
                'structure' => $this->structure,
                'layout' => $this,
            ]
        );

        /* ... */
        $this->generatorPool->process($this->getReaderContext(), $generatorContext);
        /* ... */    
    }

You'll recall from earlier that this is the main method that kicks off the main reader pools and generator pools. This time through, we're most interested in the call to `getReaderContext`

    #File: vendor/magento/framework/View/Layout.php

    public function getReaderContext()
    {
        if (!$this->readerContext) {
            $this->readerContext = $this->readerContextFactory->create();
        }
        return $this->readerContext;
    }

You'll recall the reader context object holds references to the `Magento\Framework\View\Layout\ScheduledStructure` and `Magento\Framework\View\Page\Config\Structure` objects.  By itself, this method makes it look like we're creating a **new** reader context object, which would mean new instance objects for each of the above.  **However** -- remember our earlier call to `readPageLayout`?

    #File: vendor/magento/framework/View/Page/Builder.php
    protected function readPageLayout()
    {
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $readerContext = $this->layout->getReaderContext();
            $this->pageLayoutReader->read($readerContext, $pageLayout);
        }
    }
    
The builder's `layout` property is the same `Magento\Framework\View\Layout` object.  In other words, when we called `getReaderContext` in `readPageLayout`, we ensured the `Magento\Framework\View\Layout` object would already have a reader context object instantiated.  This means when we call `getReaderContext` from `generateLayoutBlocks`, the context object will contain the same `Magento\Framework\View\Layout\ScheduledStructure` and `Magento\Framework\View\Page\Config\Structure` objects that Magento manipulated during the page layout reader pool run.  

Finally, we have the generator context object.  

    #File: vendor/magento/framework/View/Layout.php

    $generatorContext = $this->generatorContextFactory->create(
        [
            'structure' => $this->structure,
            'layout' => $this,
        ]
    );

    /* ... */
    $this->generatorPool->process($this->getReaderContext(), $generatorContext);     

This object contains the `Magento\Framework\View\Layout\Data\Structure` object and a reference to the current layout object.  These objects are not re-instantiated in the context object returned by the `generatorContextFactory`. This factory's create method

    #File: vendor/magento/framework/View/Layout/Generator/ContextFactory.php
    public function create(array $data = [])
    {
        return $this->objectManager->create('Magento\Framework\View\Layout\Generator\Context', $data);
    }

simply passes these arguments as constructor arguments via the (little used) second argument to the object manager's `create` method.  This ensures the context object receives the already instantiated objects

    #File: vendor/magento/framework/View/Layout/Generator/Context.php
    public function __construct(
        Layout\Data\Structure $structure,
        LayoutInterface $layout
    ) {
        $this->structure = $structure;
        $this->layout = $layout;
    }
    
So, what have we learned?  We've learned that the page layout handles and the regular layout handles, which Magento merges via different instances of the `Magento\Framework\View\Model\Layout\Merge` object will both operate on the same instances of the `Magento\Framework\View\Page\Config\Structure`,
`Magento\Framework\View\Layout\ScheduledStructure` and `Magento\Framework\View\Layout\Data\Structure` objects.  

We've also learned that their ability to do so is fragile, and relies on a confusing hierarchy of objects. 

## Preparing the Layout

This leaves us with this curious bit of code

    #File: vendor/magento/framework/Pricing/Render.php
    protected function _prepareLayout()
    {
        $this->priceLayout->addHandle($this->getPriceRenderHandle());
        $this->priceLayout->loadLayout();
        return parent::_prepareLayout();
    }

In Magento 1, the `_prepareLayout` method was available for block developers to make additional adjustments to the `Mage::getSingleton('core/layout')` object and/or its own children.  This is still the purpose of `_prepareLayout` in Magento 2.  Magento calls a block's `_prepareLayout` method when some other code assigns a layout to a block

    #File: vendor/magento/framework/View/Element/AbstractBlock.php
    public function setLayout(\Magento\Framework\View\LayoutInterface $layout)
    {
        $this->_layout = $layout;
        $this->_prepareLayout();
        return $this;
    }
    
For blocks, "some other code" is either the `createBlock` method in the layout object

    #File: vendor/magento/framework/View/Layout.php
    public function createBlock($type, $name = '', array $arguments = [])
    {
        $this->build();
        $name = $this->structure->createStructuralElement($name, Element::TYPE_BLOCK, $type);
        $block = $this->_createBlock($type, $name, $arguments);
        $block->setLayout($this);
        return $block;
    }

or (of more interest to us) this loop in the block generator.

    #File: vendor/magento/framework/View/Layout/Generator/Block.php
    // Set layout instance to all generated block (trigger _prepareLayout method)
    foreach ($blocks as $elementName => $block) {
        try {
            $block->setLayout($layout);
            $this->eventManager->dispatch('core_layout_block_create_after', ['block' => $block]);
        } catch (\Exception $e) {
            $this->handleRenderException($e);
            $layout->setBlock(
                $elementName,
                $this->exceptionHandlerBlockFactory->create(['blockName' => $elementName])
            );
            unset($blockActions[$elementName]);
        }
        $scheduledStructure->unsetElement($elementName);
    }

This makes the price render block all the more curious

    #File: vendor/magento/framework/Pricing/Render.php
    protected function _prepareLayout()
    {
        $this->priceLayout->addHandle($this->getPriceRenderHandle());
        $this->priceLayout->loadLayout();
        return parent::_prepareLayout();
    }
    
This method appears to add a layout handle to a layout object, and then loads that layout.  Semantically this seems to be saying "hey, load this extra layout handle and add the blocks. But how can that happen when Magento's already at the generator stage?  The handles have already done their thing by now. 

To understand this, we need to look at the `priceLayout` object

    #File: vendor/magento/framework/Pricing/Render.php
    use Magento\Framework\Pricing\Render\Layout;
    /* ... */    
    public function __construct(
        Template\Context $context,
        Layout $priceLayout,
        array $data = []
    ) {
        $this->priceLayout = $priceLayout;
        parent::__construct($context, $data);
    }

This is a new object we haven't seen before -- injected with the class `Magento\Framework\Pricing\Render\Layout`.  If we look at that class's source

    #File: vendor/magento/framework/Pricing/Render/Layout.php
    namespace Magento\Framework\Pricing\Render;

    use Magento\Framework\View\LayoutFactory;
    use Magento\Framework\View\LayoutInterface;

    /* ... */
    class Layout
    {
        /* ... */
        public function __construct(
            LayoutFactory $layoutFactory,
            \Magento\Framework\View\LayoutInterface $generalLayout
        ) {
            $this->layout = $layoutFactory->create(['cacheable' => $generalLayout->isCacheable()]);
        }        
        /* ... */        
    }
    
We see this is a stand alone class, with **no** parent.  We also can see this `Magento\Framework\Pricing\Render\Layout` object has a `layout` property thats built with a `Magento\Framework\View\LayoutFactory`.  This will be a **new** instance of the `Magento\Framework\View\Layout` object.  If we look at the `addHandle` and `loadLayout` methods defined on the `Magento\Framework\Pricing\Render\Layout` object

    #File: vendor/magento/framework/Pricing/Render/Layout.php
    
    public function addHandle($handle)
    {
        $this->layout->getUpdate()->addHandle($handle);
    }

    /**
     * Load layout
     *
     * @return void
     */
    public function loadLayout()
    {
        $this->layout->getUpdate()->load();
        $this->layout->generateXml();
        $this->layout->generateElements();
    }

we see they're calling through to the new layout object. 

This presents a problem.  If this is a new instance of the `Magento\Framework\View\Layout` object, that means a new instance of the `Magento\Framework\View\Model\Layout\Merge` object, as well as new instances of the `Magento\Framework\View\Layout\ScheduledStructure`, `Magento\Framework\View\Page\Config\Structure`, and `Magento\Framework\View\Layout\Data\Structure` objects.  In other words, this whole things *seems to* trigger the rendering of a second layout tree, but one that Magento will never have a chance to actually render.

This one had me stumped until I looked at how the system uses the `Magento\Framework\Pricing\Render` block. 
    
    #File: vendor/magento/module-catalog/Block/Product/ListProduct.php
    
    $priceRender = $this->getPriceRender();

    $price = '';
    if ($priceRender) {
        $price = $priceRender->render(
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            $product,
            [
                'include_container' => true,
                'display_minimal_price' => true,
                'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                'list_category_page' => true
            ]
        );
    }

The `$priceRender` variable above fetches the `Magento\Framework\Pricing\Render` block from the layout, and then calls its render method.  In that `render` method

    #File: vendor/magento/framework/Pricing/Render.php
    public function render($priceCode, SaleableInterface $saleableItem, array $arguments = [])
    {
        $useArguments = array_replace($this->_data, $arguments);

        /** @var \Magento\Framework\Pricing\Render\RendererPool $rendererPool */
        $rendererPool = $this->priceLayout->getBlock('render.product.prices');
        if (!$rendererPool) {
            throw new \RuntimeException('Wrong Price Rendering layout configuration. Factory block is missed');
        }

        // obtain concrete Price Render
        $priceRender = $rendererPool->createPriceRender($priceCode, $saleableItem, $useArguments);
        return $priceRender->toHtml();
    }
    
Magento *reaches into* this separate, stand-alone `priceLayout` object and pulls out blocks to render.  

    $rendererPool = $this->priceLayout->getBlock('render.product.prices');

This price layout **is** a layout object loaded completely separately from the rest of the layout system.  This means its XML files

    $ find vendor/magento/ -name catalog_product_prices.xml
    vendor/magento//module-bundle/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-catalog/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-configurable-product/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-grouped-product/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-msrp/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-tax/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-weee/view/base/layout/catalog_product_prices.xml
    vendor/magento//module-wishlist/view/base/layout/catalog_product_prices.xml      
         
**do not** have access to the other Magento layout blocks.  It also means the other layout blocks won't have access to this price renderer information. 

While an interesting example of building an isolated layout, this has caused a lot of confusion for early adopters, and will continue to cause confusion as developers transition to Magento 2. 
            
## Wrap Up

As you can see, what was once a simple two class (plus all the blocks) affair in Magento 1 has become something much more complex in Magento 2.  The most confusing bits here are

- Two different "layout" objects, each with different responsibilities
- The update/merge object's reliance on internal state leading to the need for instance objects
- The heavy use of factories to *sometimes* create multiple-instance objects 

This all leads to a situation where the system *may* behave differently, depending on how its invoked.  Adding to the confusion?   The above examples assume you're using the new page layout objects.  If you try (as some Magento core objects do) to load an interact with the layout with these page layout objects

    #File: vendor/magento/module-cms/Helper/Page.php
    if ($this->_page->getCustomLayoutUpdateXml() && $inRange) {
        $layoutUpdate = $this->_page->getCustomLayoutUpdateXml();
    } else {
        $layoutUpdate = $this->_page->getLayoutUpdateXml();
    }
    if (!empty($layoutUpdate)) {
        $resultPage->getLayout()->getUpdate()->addUpdate($layoutUpdate);
    }

    $contentHeadingBlock = $resultPage->getLayout()->getBlock('page_content_heading');
        
you run the risk of invoking these objects in an unintended way and creating behavior that's closer to a side effect than the intended system behavior.  

Unlike Magento 1, where working directly with the `Mage::getSingleton('core/layout');` object was a practical shortcut that could simplify your project, you're better off steering clear of Magento's layout related PHP objects and sticking the XML files.  Even if you are capable of following all the way along in this chapter, all this layout code leaves me with the distinct impression of a house that's has its sheetrock put up, but no fixtures installed.           