#!/usr/bin/env php
<?php
	function main($argv)
	{
		$dir_deliverable = 'deliverable/nofrills_layout';
		if(is_dir($dir_deliverable))
		{
			exit("Please remove [".$dir_deliverable."] \n");
		}
				
		mkdir($dir_deliverable);
		rename('No_Frills_Magento_Layout.tex', $dir_deliverable . '/' . 'No_Frills_Magento_Layout.tex');
		#shell_exec('cp cover.html ' . $dir_deliverable);
		shell_exec('cp d_block_action_reference.html ' . $dir_deliverable);		
		shell_exec('cp -r code ' . $dir_deliverable . '/code');
		shell_exec('cp -r images ' . $dir_deliverable . '/images');		
		#shell_exec('cp -r h.js ' . $dir_deliverable . '/h.js');		
	}
	main($argv);