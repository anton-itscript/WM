<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 05.05.2015
 * Time: 15:05
 */

class loadFile
{
    protected $last_modified_file = 0;
    protected $file_path = NULL;
    protected $data = array();

    public function __construct($filePath)
    {
        if (file_exists($filePath)) {
            $this->file_path = $filePath;
            $this->setActualInfo();
        }

    }

    protected function is_file_modified()
    {
        if ($this->last_modified_file < filemtime($this->file_path)) {
            $this->last_modified_file = filemtime($this->file_path);
            return true;
        }
        return false;
    }

    protected function setActualInfo()
    {
       // if ($this->is_file_modified()) {
            $this->data =  require($this->file_path);
       // }

    }

    public function getFileData()
    {
        $this->setActualInfo();
        return $this->data;
    }

}
