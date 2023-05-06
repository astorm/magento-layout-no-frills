#!/bin/bash  

bin/chapters_to_html.php 
bin/code_from_chapters.php
bin/to_shell.php book.html
bin/color_code.php book.html
bin/hyphenate.php book.html
bin/finalize.php
bin/cleanup.php
#bin/remove_code.php /tmp/book_in_shell.html > /tmp/no_code.html