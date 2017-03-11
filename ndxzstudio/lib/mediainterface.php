<?php

interface MediaInterface
{
    public function autoResize($img=array(), $page=array());
    public function upload_max_size();
    public function regenerate($id = 0, $file = '');
    public function user_image();
    public function cover_image();
    public function getFileType();
    public function allowThumbs();
    public function file_size();
    public function video_thumbnailer();
    public function make_video_image($source_name);
    public function uploader();
    public function make_image();
    public function make_systhumb();
    public function make_system();
    public function make_thumbnail_proportional();
    public function make_thumbnail_cinematic();
    public function make_thumbnail_4x3();
    public function make_thumbnail_3x2();
    public function make_thumbnail_3x4();
    public function make_thumbnail_square();
    public function make_user_thumbnail();
    public function makeCustomSize($width, $height, $sourceFilename, $destinationFilename);

}