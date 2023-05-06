#!/bin/bash  

bin/chapters_to_html.php 
bin/code_from_chapters.php
bin/chapters_to_tex.php

#bin/to_shell.php book.html
#bin/color_code.php book.html
#bin/hyphenate.php book.html
bin/finalize.php
bin/cleanup.php