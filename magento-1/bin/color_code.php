#!/usr/bin/env php
<?php

function code_type($string)
{
	$semicolon 	= array();
	preg_match_all('{;}',$string, $semicolon);
	$semicolon = $semicolon[0];
	
	$brackets 	= array();
	preg_match_all('/\{|\}/',$string, $brackets);
	$brackets = $brackets[0];
	
	$gt 		= array();
	preg_match_all('/&lt;/',$string, $gt);
	$gt	  		= $gt[0];

	$lt 		= array();
	preg_match_all('/&gt;/',$string, $lt);
	$lt	  		= $lt[0];
	
	if(strpos($string,'System -&gt; Configuration') !== false)
	{
		return 'Navigation';
	}
	
	if(strpos($string, 'block type="page/html"'))
	{
		return 'XML';
	}

	if(
	strpos($string,'var_dump') ||
	strpos($string,'getStoreConfig') ||
	strpos($string,'$config') ||
	strpos($string,'Mage::getSingleton') ||
	strpos($string,'getModelClassName') ||
	strpos($string,'$updateFiles') ||
	strpos($string,'/**') ||
	strpos($string,'Mage::getModel') ||
	strpos($string,'getPageTemplateProcessor') ||
	strpos($string,'$customer = new Product()') ||
	strpos($string,'simplexml_load_string') ||
	strpos($string,'youtube.phtml') ||
	strpos($string,'app/design/frontend/default/default/template/helloworld.phtml')	
	)
	{
		return 'PHP';
	}
	
	if(count($semicolon) > 1 && count($gt) != count($lt))
	{
		return 'PHP';
	}
	
	if(count($brackets) > 1)
	{
		return 'PHP';
	}
	
	if(count($gt) == count($lt) && count($lt) > 1)
	{
		return 'XML';
	}
	
	return 'Unknown';
}

function nf_format_php($code)
{
	$code = trim(htmlspecialchars_decode($code));
	$added = false;
	if(strpos($code,'<?php') === false)
	{
		$added = true;
		$code = '<?php' . "\n" . $code;
	}
	$code = highlight_string($code,true);
	if($added)
	{
		$code = str_replace('<span style="color: #000000">'
		. "\n"
		. '<span style="color: #0000BB">&lt;?php<br /></span>',
		'',
		$code);
	}
	
	$code = str_replace('-&gt;','&#8209;&gt;',$code);

	return $code;
}

function nf_format_navigation($code)
{
	return "<pre><code>$code</code></pre>";
}

function nf_format_xml($code)
{
// 	$code = trim(htmlspecialchars_decode($code));
// 	$xp = new XsltProcessor();
// 	$xsl = new DomDocument;
// 	$xsl->load('bin/xmlverbatim.xsl');
// 	$xp->importStyleSheet($xsl);
// 	
// 	$xml= new DomDocument;
// 	$xml->loadXml('<root>' . $code . '</root>');
// 	return $xp->transformToXML($xml);
// 	exit;
	return "<pre><code>$code</code></pre>";
}

function nf_format_unknown($code)
{
	return "<pre><code>$code</code></pre>";
}


function format_code($code)
{	
	$type = code_type($code[1]);	
	$callback = strtolower('nf_format_'.$type);
	return $callback($code[1]);
}

function main($argv)
{
	$book = file_get_contents($argv[1]);
	$book = preg_replace_callback('{<pre><code>(.+?)</code></pre>}six',
	'format_code',
	$book);
	
	file_put_contents($argv[1], $book);
}
main($argv);