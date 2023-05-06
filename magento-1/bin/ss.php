#!/usr/bin/env php
<?php
	function main($argv)
	{
		$template = 'No Frills Magento Layout
--------------------------------------------------

[TITLE HERE]
--------------------------------------------------

*Synapsis here*

Use the form below if you run into any bugs with this chapter, or need help with the example code!

<div id="disqus_thread"></div>
<script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = \'pulsestormllc\'; // required: replace example with your forum shortname

    // The following are highly recommended additional parameters. Remove the slashes in front to use.
    var disqus_identifier = \'nofrills-layout-####\';
    var disqus_url = \'http://pulsestorm.net/####\';

    /* * * DON\'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement(\'script\'); dsq.type = \'text/javascript\'; dsq.async = true;
        dsq.src = \'http://\' + disqus_shortname + \'.disqus.com/embed.js\';
        (document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>';

		echo str_replace('####',$argv[1],$template), "\n\n";
	}
	main($argv);