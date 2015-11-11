<?php

/**
 * Created by PhpStorm.
 * User: dmitriy
 * Date: 03.11.15
 * Time: 18:18
 */




class TextFileWorker
{

    protected $f_name = "";
    protected $file_pointer = 0;
    protected $file_handle;
    protected $file_mode = 'r+';
    protected $file_array = array();


    public function __construct($filename)
    {
        $this->f_name       = $filename;
        $this->file_handle  = fopen($this->f_name, $this->file_mode);
        $this->file_array = file($this->f_name);
    }

    public function open()
    {


    }

    public function close()
    {
        fclose($this->file_handle);
    }


    public function __destruct()
    {
        //$this->close();
    }

    public function getLine($number)
    {
        for ($i = 0; $i < sizeof($this->file_array ); $i++) {
            if ($i == $number)
                return $this->file_array[$i];
        }

        return false;
    }

    public function removeLine($number)
    {
        for ($i = 0; $i < sizeof($this->file_array ); $i++) {
            if ($i == $number)
                unset($this->file_array[$i]);
        }
        fputs($this->file_handle, implode("", $this->file_array));
    }

    // Resets file pointer to the first position of the file
    private function resetfile_pointer()
    {
        $this->file_pointer = 0;
    }

    // Returns number of file lines
    public function countLines()
    {

        return count($this->file_array);
    }

    public function getFileArray()
    {
        return $this->file_array;
    }

}