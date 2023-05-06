#!/usr/bin/env php
<?php
	function main($argv)
	{
		shell_exec('rm -rf code/all/*');
		shell_exec('rm -rf build/*');
		#shell_exec('rm No_Frills_Magento_Layout.*');
	}
	main($argv);