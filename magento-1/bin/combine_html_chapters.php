<?php
function main($argv)
{    
    $files = glob('build/*.html');
    sort($files);
    
    $html = '';
    foreach($files as $file)
    {
        $html .= file_get_contents($file) . "\n\n";
        $html .= '<div>Prepared for Lucas Radaelli. Contents Copyright 2011 Pulse Storm LLC</div>';
    }
    
    file_put_contents('build/all.html',$html);
    symlink('../images','build/images');
}
main($argv);