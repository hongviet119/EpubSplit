<?php
// Define ebook-convert to run with full path here. It depends on your OS
//$command = '/Applications/calibre.app/Contents/MacOS/ebook-convert';
 $command = 'xvfb-run /usr/bin/ebook-convert';

$origin = dirname(__FILE__) . "/origin/shute-what-happened-to-the-corbetts.epub";
$originPdf = str_replace('.epub', '.pdf', $origin);

$output = shell_exec("$command $origin $originPdf");

if ($output && file_exists($pdf)) {
    $previewPdf = str_replace(array('/origin/', '.pdf'), array('/preview/', '-preview.pdf'), $originPdf);
    $output = shell_exec("pdftk $pdf cat 1-20 output $preview");
    
    if ($output && file_exists($preview)) {
        $preview = str_replace('.pdf', '.epub', $previewPdf);
        $output = shell_exec("$command $previewPdf $preview");
    }
}
