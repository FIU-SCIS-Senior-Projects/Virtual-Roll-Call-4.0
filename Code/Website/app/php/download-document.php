<?php
$fileName = $_GET['upload_name'];
$fileExt =strtolower(substr($fileName, -4));

if( $fileExt == ".pdf" )
{
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header("Cache-Control: no-cache");
    readfile('uploads/' . $fileName);
}
else if ( $fileExt == ".jpg" || $fileExt == "jpeg" || $fileExt == ".gif" || $fileExt == ".png" || $fileExt == ".bmp")
{
    header('Content-Type: image');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo file_get_contents('uploads/' . $fileName);
}
else
{
    header('Content-Type: force-download');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('HTTP/1.0 200 OK', true, 200);
    header("Cache-Control: no-cache");
    readfile('uploads/' . $fileName);
}