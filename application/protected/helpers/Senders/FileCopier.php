<?php


class FileCopier
{
    protected $file_name_local;
    protected $file_name_destination;
    protected $folder_permission;
    protected $errors;

    public function __construct()
    {
        $this->folder_permission = 0777;
    }

    public function setFolderPermission($folder_permission)
    {
        //todo validation
        $this->folder_permission =  $folder_permission;
        return $this;
    }

    public function copy($file_name_local, $file_name_destination)
    {
        $this->file_name_local          =   $this->rmPathSteps($file_name_local);
        $this->file_name_destination    =   $file_name_destination;

        if ($this->checkDirPath()) {
            if(!copy($this->file_name_local, $this->file_name_destination)) {
                $this->errors[] = "File copy problem";
            }
        } else {
            $this->errors[] = "Path is invalid";
        }
        return $this;
    }

    public  function checkDirPath($file_name_destination=false)
    {

        if ($file_name_destination != false) {
            $this->file_name_destination    =   $file_name_destination;
        }

        $this->file_name_destination = str_replace(array("\\", "//"), "/", $this->file_name_destination);
        //remove file name
        if(substr($this->file_name_destination, -1) != "/")
        {
            $p = strrpos($this->file_name_destination, "/");
            $path = substr($this->file_name_destination, 0, $p);
        }

        $path = rtrim($path, "/");
        $path = $this->rmPathSteps($path);
        if(!file_exists($path))
            return mkdir($path, $this->folder_permission, true);
        else
            return is_dir($path);
    }

    public function rmPathSteps($path)
    {
        $pathArray = explode('/',$path);
        for ($i=0;$i<count($pathArray) ; $i++ ) {
            if (isset($pathArray[$i+1]) && $pathArray[$i+1]=='..') {
                unset($pathArray[$i]);
                unset($pathArray[$i+1]);
                $newPath = implode('/',$pathArray);
                $this->rmPathSteps($newPath);
            }
        }
        $newPath = implode('/',$pathArray);
        return $newPath;
    }
    public function getErrors()
    {
        return $this->errors;
    }
}

?>