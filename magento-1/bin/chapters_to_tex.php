#!/usr/bin/env php
<?php
    function markdown_to_html($item)
    {   
        return str_replace('.markdown','.html',$item);
    }
    
    function remove_start_and_end($tag, $node)
    {
    	$string 	= $node;
    	if(is_object($string))
    	{
    		$string = $node->asXml();
    	}
    	$string = substr($string, strlen($tag)+2);
    	$string = substr($string, 0,strlen($string)-(strlen($tag)+3));    
    	return $string;
    }
    
    function format_blockquote($node)
    {
    	$string = trim(remove_start_and_end('blockquote',$node));
    	if(strpos($string, '<p>') !== 0)
    	{
    		throw new Exception("Unexpected format at " . __LINE__ . " in " . __FILE__);
    	}
    	else
    	{
	    	$string = trim(remove_start_and_end('p',$string));
    	}    
		return '\begin{quote}' . "\n" . 
		escape_tex_characters($string) . "\n" .
		'\end{quote}';
    	//return '\subparagraph{'.escape_tex_characters($string).'} ' . "\n";;
    }
    
    function format_p($node)
    {
    	$string = remove_start_and_end('p',$node);
    	return escape_tex_characters($string);
    }

	function format_h3($node)
	{
    	$section = remove_start_and_end('h2',$node);
    	return sprintf('\subsection{%s}',escape_tex_characters($section));		
	}

    function format_h2($node)
    {
    	$section = remove_start_and_end('h2',$node);
    	return sprintf('\section{%s}',escape_tex_characters($section));	
    }
    
    function format_pre($node)
    {
		$code = remove_start_and_end('pre',$node);
		$code = remove_start_and_end('code',$code);
		$code = str_replace("\n\n","\n",$code); //not sure why
		
		//unspecialchars
		$code = htmlspecialchars_decode($code);
		//no tex escpanig for listings
		// \begin{lstlisting}
		// <?php
		// 	echo "This is a test" . $again;
		// 	echo "This is a test" . \$again;
		// 	abcdefghiJklMnoPQrStuVwxyznikmnbc
		// \end{lstlisting}		
		
		return sprintf( '\begin{lstlisting}' . "\n" .
		'%s' . "\n" .
		'\end{lstlisting}' . "\n", $code);
    }
    
    function get_list_items_from_node($node)
    {
    	$string = remove_start_and_end('Xl',$node);
    	preg_match_all('{<li>(.+?)</li>}six',$string,$matches);
    	$items = array();
    	foreach($matches[1] as $line)
    	{
    		$final_line	= $line;
    		if(strpos($final_line,'<p>') === 0)
    		{
    			$final_line = remove_start_and_end('p',$line);
    		}
    		$items[] = $final_line;
    	}
    	$s_items = '';
    	foreach($items as $item)
    	{
    		$s_items .= '\item ' . escape_tex_characters($item) . "\n";
    	}
    	return $s_items;
    }
    
    function format_ul($node)
    {
		$s_items = get_list_items_from_node($node);
    	$list = '\begin{itemize}' . "\n" . 
    	$s_items .
    	'\end{itemize}' . "\n";    	
    	return $list;
    }
    
    function format_ol($node)
    {    	
    	file_put_contents('/tmp/test.log',"--------------------------------------------------\n",FILE_APPEND);
    	file_put_contents('/tmp/test.log',$node->asXml()."\n",FILE_APPEND);
		$s_items = get_list_items_from_node($node);		
		file_put_contents('/tmp/test.log',"$s_items\n",FILE_APPEND);
		file_put_contents('/tmp/test.log',"--------------------------------------------------\n",FILE_APPEND);
		
    	$list = '\begin{enumerate}' . "\n" . 
    	$s_items .
    	'\end{enumerate}' . "\n";    	
    	return $list;
    }
    
    function html_to_tex($contents)
    {
    	$tex 	= array();
    	$xml 	= simplexml_load_string('<root>' . $contents . '</root>');    	    	
		foreach($xml->children() as $child)
		{			
			$tex[] = call_user_func('format_'.$child->getName(),$child);
		}
		
		return implode("\n\n",$tex);
    }
    
    function escape_tex_characters($string)
    {
    	$string = str_replace('\\','\\backslash',$string);											//stand alone backspaces
    	$string = preg_replace('/([#$%^&_{}~])/','\\'.'\$1',$string);								//LaTeX things that need escaping
    	$string = preg_replace('{<strong>(.+?)</strong>}six','\textbf{$1}',$string);				//strong text
    	$string = preg_replace('{<em>(.+?)</em>}six','\emph{$1}',$string);							//emphasize
    	$string = preg_replace_callback('{<code>(.+?)</code>}six','format_inline_code',$string);	//code samples
    	$string = str_replace('\\&gt;','\\textgreater ',$string);									//escaped htmlspecialchars
    	$string = str_replace('\\&lt;','\\textless ',$string);    									//escaped htmlspecialchars
    	$string = str_replace('\\&amp;','\\&',$string);    									//escaped htmlspecialchars    	
    	$string = preg_replace_callback('{<img src="(.+?)"\s*/>}','format_images',$string);			//images
    	$string = str_replace('appendix\_f','appendix_f',$string);									//lazy fixing
    	$string = str_replace('appendix\_g','appendix_g',$string);									//lazy fixing
    	
    	#could probably combine some of these, but seems cleaner to do one by one
    	//inline code end of sentence
    	$string = preg_replace('%(texttt\{.+?\}) ([\\\]normalsize) \.%','$1$2.',$string);

    	//inline code wuth a following comma
    	$string = preg_replace('%(texttt\{.+?\}) ([\\\]normalsize) ,%','$1$2,',$string);    	

    	//inline code wuth a following a paren
    	$string = preg_replace('%(texttt\{.+?\}) ([\\\]normalsize) \)%','$1$2)',$string);    	

    	//inline code wuth a following a quote
    	$string = preg_replace('%(texttt\{.+?\}) ([\\\]normalsize) \'%','$1$2\'',$string);    	
    	
    	//one offs
    	$string = preg_replace('%(texttt\{insert}) ([\\\]normalsize) (ing)%','$1$2 $3',$string);    	
    	
    	//one offs
    	$string = preg_replace('%(texttt\{foreach}) ([\\\]normalsize) (ing)%','$1$2 $3',$string);    	
    	
    	return $string;
    }
    
    function format_images($matches)
    {
		$string = '\begin{figure}[htb]
\begin{center}
\leavevmode
\includegraphics[width=1\textwidth]{'.$matches[1].'}
\end{center}
\caption{}
%\label{fig:awesome_image}
\end{figure}' . "\n";
		return $string;
    }
    function format_inline_code($matches)
    {
    	// $inside = str_replace('\_','_',$matches[1]);
    	$inside = $matches[1];
    	return '\footnotesize\texttt{'.$inside.'} \normalsize ';
    }
    
    function get_chapter_contents()
    {
		include('files.php');		
		$files = array_map('markdown_to_html',$files);
		
		$chapter_contents = array();
		foreach($files as $file)
		{
			$lines 		= file('build/' . $file);
			$title 		= array_shift($lines);			
			$contents	= trim(implode("\n",$lines));
			$contents   = html_to_tex($contents);
			
			$chapter_contents[trim(strip_tags($title))] = $contents;
		}
		return $chapter_contents;    
    }
	function main($argv)
	{
		$contents = get_chapter_contents();		
		
		$main_file = array();
		$appendix = false;
		foreach($contents as $title=>$contents)
		{			
			$file_name = preg_replace('{[^a-z0-9]}i','_',$title);			
			$full_path = 'build/'.$file_name.'.tex';
			file_put_contents($full_path,$contents);			
			if(!$appendix && strpos($title, 'Magento Block Hierarchy') === 0)
			{
				$main_file[] = '\appendix';
			}
			$main_file[] = '\chapter{'.$title.'}';
			
			$main_file[] = $contents;
			// $main_file[] = '\input{'.$full_path.'}';
		}		
		$for_main_file = implode("\n",$main_file);
		$book = file_get_contents('var/shell.tex');
		$book = str_replace('includeshere',$for_main_file,$book);
		file_put_contents('No_Frills_Magento_Layout.tex',$book);
	}
	main($argv);