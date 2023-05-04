## Magento 2 Areas
	
Areas were a tricky concept to understand in Magento 1, and remain so in Magento 2.  To understand areas, you need to consider **how** web applications are typically built.  Consider

1. A URL
2. A web application that lives at that URL
3. A web application that has several distinct features living at sub-URLs
4. And those features each require a different set of assets and resources

Magento's area system uses the current URL to identify which "area" of an application a user is in, and uses that area to load different assets and resources.  For example, when you're on a site's homepage, or on the cart checkout page, your URL might look like the following.  

    http://magento.example.com/
    http://magento.example.com/checkout
        
These are URLs in the `frontend` area.  However, when you're looking at the back office

    http://magento.example.com/admin/admin/dashboard/index    

you're in a different area named `adminhtml`.  

Areas are important to front end developers because each area loads a *different* set of layout handle XML files, and a different set of front end asset files.  For example, in the catalog module you can find the `phtml` files for the `frontend` cart application in the following folder

    //notice the "frontend" path portion
    
    vendor/magento/module-catalog/view/frontend/templates/
      
while the files for the back office `adminhtml` area are in a different folder.

    vendor/magento/module-catalog/view/adminhtml/templates/
       
When Magento need to load a template URN like the following

    Magento_Catalog::path/to/template.phtml

the base folder it uses will be determined by the application's current area.

### Base Area Folders

One problem with the area system in Magento 1 was the inability to easily share assets between two areas.  For example, as an extension or theme developer you might want to share a `phtml` template with both a back office `adminhtml` page **and** and `frontend` cart page.  

To solve this problem, Magento 2 introduced the idea of a `base` area.  Assets placed in the base folder are available to **both** areas.  

For example, if Magento tries to expand the `Magento_Catalog::path/to/template.phtml` URN out to a full file path in the `frontend` cart application, Magento will check for a file in the following location first
           
    vendor/magento/module-catalog/view/frontend/templates/path/to/template.phtml
    
If it doesn't find a file there, Magento will check the the `base` area folder next.               

    vendor/magento/module-catalog/view/base/templates/path/to/template.phtml
    
### Programmatically Determining the Area

Another way Magento 2 improves the area system is a more straightforward mechanism for programmatically checking the current area code.  The `Magento\Framework\App\State` single instance object keeps track of the area code.  If you use this class in an automatic constructor dependency injection constructor, you can easily check the current area

    public function __construct(
        \Magento\Framework\App\State $appState
    )
    {
        $this->appState = $appState;
    }
    /* ... */
    public function someMethod()
    {
        var_dump(
            $this->appState->getAreaCode()
        );
    }

Each sub-system in Magento handles areas a little bit differently.  We'll cover the specifics as we come across them -- this appendix was meant as a gentle introduction to the topic, and as a way to cover changes to the area system in Magento 2. 