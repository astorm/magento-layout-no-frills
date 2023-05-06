#!/usr/bin/env php
<?php
	require_once('markdown.php');
	function main($argv)
	{
		include('files.php');		
		foreach($files as $file)
		{
			$filename_html = 'build/' . str_replace('.markdown','.html',$file);
			$chapter = markdown(file_get_contents($file));
			file_put_contents($filename_html, $chapter);
		}
	}
	main($argv);