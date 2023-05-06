Theme and Layout Resolution
==================================================	
The default Magento design package ships with a default Magento theme.  

	#Location of default theme
	app/design/frontend/default/default/*
	
You could (and can) change the location of this default theme in the Admin Console

	System -> Configuration -> Design -> Themes -> Default
	
but even with a new folder name this theme is still your **default** theme.  It's meant to contain the main design of your store.  

If you navigate to 

	System -> Design
	
Magento will allow you select a **custom** theme for a particular date range.  	The most obvious use case of this is the holiday special.  Put Santa on your store, watch those sales soar. 

Template Resolution
--------------------------------------------------
Prior to Magento 1.4.1, when Magento went looking for a block's template file, it would check if a custom design was set, and 

1. If no custom design was set, the default theme folder was used

2. If a custom design **was** set, Magento was first look for a template there.  If it didn't find one, it would fallback to the default theme

This system worked, but had a small problem.  Many stores would never change the default theme.  They'd leave it

	default/default
	
and just modify those files to skin their store.  When they upgraded their system, the <code>default/default</code> theme would also be updated, wiping out their changes.  This was viewed as a untenable state of affairs, and the concept of the <code>base</code> design package and theme was created.  	

The Base Package
--------------------------------------------------
The Magento theming system still operates as described above. However, if a template isn't found in the default theme rather than render nothing Magento will look in one final place for the template file in **the base package's default theme**

	app/design/frontend/base/default/*
	
This is the **final** fallback.  Currently, the default theme that ships with Magento is mostly implemented in the base design package. This allows designers and developers to **selectively** update <code>phtml</code> in the **default** theme if they want to change the behavior of something.  The intent is that you **never** edit the base design package.  If you want to change a particular template you add it to your default theme.

Layout Files
--------------------------------------------------
Layout files follow the same cascading loading rules.  First the current custom design is checked, then the default theme, and the default theme in the base design package.  Layout files were included in this grand design of the base theme system, as many developers ignore, or are unaware of,  <code>local.xml</code> for layout updates. This led many system owners to edit the default Package Layout files to achieve their final design goals, resulting in the same upgrade problems mentioned above.

*Visit http://www.pulsestorm.net/nofrills-layout-appendix-e to join the discussion online.*