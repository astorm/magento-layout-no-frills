#!/usr/bin/env php
<?php
	function markdown_to_html($item)
	{
		return str_replace('.markdown','.html',$item);
	}
	
	function main($argv)
	{
		include('files.php');
		$files = array_map('markdown_to_html',$files);
		$book = '';
		foreach($files as $file)
		{
			$book .= file_get_contents('build/'.$file);
		}
		$shell = file_get_contents('var/shell.html');
		
		$book = preg_replace('{<body>(.*)</body>}six',
		'<body>' 	. 
		$book 		.
		'</body>',$shell);		
		file_put_contents($argv[1],$book);
	}	
	main($argv);