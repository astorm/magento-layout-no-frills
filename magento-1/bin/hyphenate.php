#!/usr/bin/env php
<?php

function main($argv)
{
	$book = file_get_contents($argv[1]);
	$book = preg_replace('{^<p>}m','<p class="hyphenate">',$book);
	file_put_contents($argv[1], $book);
}
main($argv);