#!/usr/bin/env php
<?php
	ob_start();
	require_once('bin/chapters_to_tex.php');
    ob_end_clean();
    
    function is_full_chapter($name)
    {
    	$full = array('No_Frills_Magento_Layout__Introduction','Widgets','Class_Aliases');
    	return in_array($name, $full);
    }
    
    function parse_sub_sections($contents)
    {
    	preg_match_all('{^\\\(?:sub)?section.+?$}mix',$contents,$matches);    	
    	//var_dump($matches);
    	return implode("\n\n", $matches[0]);
    }
    
	function main_sample($argv)
	{
		$contents = get_chapter_contents();		
		
		$main_file = array();
		$appendix = false;
		foreach($contents as $title=>$contents)
		{			
			$file_name = preg_replace('{[^a-z0-9]}i','_',$title);			
			$full_path = 'build/'.$file_name.'.tex';
			file_put_contents($full_path,$contents);						
			if(is_full_chapter($file_name))
			{								
				if(!$appendix && strpos($title, 'Magento Block Hierarchy') === 0)
				{
					$main_file[] = '\appendix';
				}
				$main_file[] = '\chapter{'.$title.'}';				
				$main_file[] = $contents;
			}
			else
			{
				if(!$appendix && strpos($title, 'Magento Block Hierarchy') === 0)
				{
					$main_file[] = '\appendix';
				}
				$main_file[] = '\chapter{'.$title.'}';				
				$main_file[] = 'This PDF is a sample, and contains Chapter 0, Chapter 7, and ' . 
				'Appendix B. Get the entire book online!' . "\n\n" .
				"http://store.pulsestorm.net/products/no-frills-magento-layout" . "\n";			
				$main_file[] = parse_sub_sections($contents);				
			}
		}		
		$for_main_file = implode("\n",$main_file);
		$book = file_get_contents('var/shell.tex');
		$book = str_replace('includeshere',$for_main_file,$book);
		$book = str_replace('Prepared for Robert Hoffner; ','',$book);
		file_put_contents('No_Frills_Magento_Layout.tex',$book);
	}
	main_sample($argv);