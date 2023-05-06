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
		$codes = array();
		foreach($files as $file)
		{
			$chapter = file_get_contents('build/'.$file);
			preg_match_all('{<pre><code>(.+?)</code></pre>}six',$chapter,$matches);
			$fragments = $matches[1];
			
			$dir_name = 'code/all/' . str_replace('.html','',$file);
			if(!is_dir($dir_name))
			{
				mkdir($dir_name);
			}

			$c=1;
			foreach($fragments as $fragment)
			{
				file_put_contents($dir_name . '/'
				. $c . '.txt',htmlspecialchars_decode($fragment));
				$c++;
			}			
		}
	}	
	main($argv);