#!/usr/bin/env php
<?php
	function main($argv)
	{
		$contents = file_get_contents($argv[1]);
		echo preg_replace('{<pre>.+?</pre>}six','',$contents); 
	}
	main($argv);
	exit(0);