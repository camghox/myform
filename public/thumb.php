<?php
error_reporting(0);
ini_set('display_errors', 'Off');

define('ROOT_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/'))."/");
define('DS', '/');

//exit(ROOT_PATH.DS);

$pic = $_GET['pic'];
$picpath = ROOT_PATH . DS . 'images' . DS . $pic;
$picsize = 400;

$size       = $_GET['s'];
$widthD     = 0;
$heightD    = 0;
if(isset($size)){
    $size   = explode("x", $size);
    $widthD  = intval($size[0]);
    $heightD = intval($size[1]);
}

list($width, $height) = getimagesize($picpath); //获取原图尺寸

if($widthD == 0){
    $widthD = $picsize;
}
if($heightD == 0){
    $heightD = $height * ($picsize/$width);
}
$src_im = NULL;
$dst_im = NULL;
if(strpos($picpath, ".jpg") || strpos($picpath, ".jpeg")){
    header('Content-Type:image/jpeg;');

    $src_im = imagecreatefromjpeg($picpath);
    $dst_im = imagecreatetruecolor($widthD, $heightD);
    imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $widthD, $heightD, $width, $height);
    //imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    imagejpeg($dst_im);
}else if(strpos($picpath, ".png")){
    header('Content-Type:image/png;');

    $src_im = imagecreatefrompng($picpath);
    $dst_im = imagecreatetruecolor($widthD, $heightD);
    $alpha = imagecolorallocatealpha($dst_im, 0, 0, 0, 127);
    imagefill($dst_im, 0, 0, $alpha);
    imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $widthD, $heightD, $width, $height);
    //imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    imagesavealpha($dst_im, true);
    imagepng($dst_im);
}
if(isset($src_im)){
    imagedestroy($src_im);
    unset($src_im);
}
if(isset($dst_im)){
    imagedestroy($dst_im);
    unset($dst_im);
}