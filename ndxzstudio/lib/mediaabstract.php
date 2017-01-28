<?php

load_class('mediainterface', false, 'lib');

abstract class MediaAbstract implements MediaInterface
{
    /**
     * Returns server settings for max upload size
     *
     * @param void
     * @return integer
     */
    public function upload_max_size()
    {
        $upload_max_filesize = ini_get('upload_max_filesize');
        $upload_max_filesize = preg_replace('/M/', '000000', $upload_max_filesize);

        $post_max_size = ini_get('post_max_size');
        $post_max_size = preg_replace('/M/', '000000', $post_max_size);

        $this->upload_max_size = ($post_max_size >= $upload_max_filesize) ? $upload_max_filesize : $post_max_size;
    }

    /**
     * Returns filetype by file extension
     *
     * @param void
     * @return string
     */
    public function getFileType()
    {
        $type = explode('.', $this->filename);
        $this->filemime = array_pop($type);
    }

    /**
     * Returns array of image filetypes
     *
     * @param void
     * @return array
     */
    public function allowThumbs()
    {
        return $this->uploads['images'];
    }


    /**
     * Returns file size
     *
     * @param void
     * @return integer
     */
    public function file_size()
    {
        $size = str_replace('.', '', @filesize($this->image));
        $this->file_size = ($size == 0) ? 0 : $size;
    }
}