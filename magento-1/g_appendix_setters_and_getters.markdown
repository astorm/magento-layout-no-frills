Magento Setters and Getters
==================================================	
In most MVC model systems a common pattern develops.  Developers find they need to store two general types of data

1. "Business Logic" data (ex. a product's SKU)

2. Data that allows the model to function (ex. the database table name)

It's very common to see developers use a single <code>array</code> (or similar, hash table like structure) property to store the business logic data, allowing the other class/object properties to be used for system functionality.  Magento is no different.  Any object that comes from a class that inherits from <code>Varien\_Object</code> (which includes both models and blocks) has a protected <code>$\_data</code> property

    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = array();

This array holds all the object's business logic data.  You can get an array of key/value pairs for this data with the <code>getData</code> method.

	var_dump($object->getData());
	
If you want to **set** a specific data field, use

	$object->setData('the_key', 'value');
	
similarly, if you want a specific field back from an object, you can use

	$value = $object->getData('the_key');
	
and you can set multiple keys at once by using <code>setData</code> with an array.

	$value = $object->setData(array(
	'the_key'=>'value'
	'the_thing'=>$thing,
	));
	
You've probably noticed we're naming our keys using an all lower-case, underscore-for-spaces convention.  While nothing enforces this, it *is* the standard Magento convention.  Beyond consistency, this also helps when it comes to Magento's magic getter and setter methods.

Getter and Setter
--------------------------------------------------
In addition to the data getting and setting methods mentioned above, there's also a more "magic" syntax.

	$key = $object->getTheKey();	
	$object->setTheKey('value');
	
Using PHP's <code>\_\_call</code> method, Magento has implemented their own <code>get</code> and <code>set</code> methods.  If you call a method on an object (with <code>Varien_Object</code>in the inheritance chain) whose name begins with <code>get</code> or <code>set</code>, **and** there isn't an existing method already with the same name, Magento will use the remainder of the method name to create a data property key, and either get or set the value.  That means this

	$object->setTheKey('value');
	
is equivalent to this	

	$object->setData('the_key','value');
	
That's why it's important to keep with the lowercase/underscore key convention.  Magento will convert the leading-camel-case

	TheKey
	
into a key named

	the_key

Another neat feature here is that the <code>set</code> method will always return an instance of the object being set, which enables method chaining

	$object->setFoo('bar')->setBaz('hola')->save();
	
After using this style interface for a few weeks you'll be loath to return to typing out array brackets.
	
Other Magic Methods
--------------------------------------------------
Magento also has magic methods for unsetting, and checking for the existence of a property

	$this->unsTheKey();
	$this->hasTheKey();
	
Checkout the source of <code>Varien_Object</code> for more information

    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                //Varien_Profiler::start('GETTER: '.get_class($this).'::'.$method);
                $key = $this->_underscore(substr($method,3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                //Varien_Profiler::stop('GETTER: '.get_class($this).'::'.$method);
                return $data;

            case 'set' :
                //Varien_Profiler::start('SETTER: '.get_class($this).'::'.$method);
                $key = $this->_underscore(substr($method,3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                //Varien_Profiler::stop('SETTER: '.get_class($this).'::'.$method);
                return $result;

            case 'uns' :
                //Varien_Profiler::start('UNS: '.get_class($this).'::'.$method);
                $key = $this->_underscore(substr($method,3));
                $result = $this->unsetData($key);
                //Varien_Profiler::stop('UNS: '.get_class($this).'::'.$method);
                return $result;

            case 'has' :
                //Varien_Profiler::start('HAS: '.get_class($this).'::'.$method);
                $key = $this->_underscore(substr($method,3));
                //Varien_Profiler::stop('HAS: '.get_class($this).'::'.$method);
                return isset($this->_data[$key]);
        }
        throw new Varien_Exception("Invalid method " . get_class($this) . "::" . 
        $method."(".print_r($args,1).")");
    }
    
    
*Visit http://www.pulsestorm.net/nofrills-layout-appendix-g to join the discussion online.*