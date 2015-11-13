<?php
abstract class PFileModel
{
    protected $filepath__;
    protected $filename__ =   '.json';
    protected $fileData__;

    public abstract function initFieldsValues();

    public function __construct()
    {
        $this->filepath__ = Yii::app()->getBasePath().DIRECTORY_SEPARATOR.'nosqlvars'.DIRECTORY_SEPARATOR.$this->filename__;

        if (!file_exists($this->filepath__)) {
            $array = $this->initFieldsValues();
            file_put_contents($this->filepath__,json_encode($array,true));
        }

        $this->fileData__ = json_decode(file_get_contents($this->filepath__),true);
        while($this->fileData__ == false){
            time_nanosleep(0, 200000000);
            $this->fileData__ = json_decode(file_get_contents($this->filepath__),true);
        }
        $this->setValuesAsProperties();
    }

    public function  getFileData()
    {
        return  $this->fileData__;
    }

    //magic and sorcery, be careful
    protected function setValuesAsProperties()
    {
        $propertiesList = $this->getFileData();
        $classVars = get_class_vars(get_class($this));
        foreach ($propertiesList as $propertyName => $propertyValue) {
            if(!array_key_exists($propertyName, $classVars)) {
                $this->$propertyName = $propertyValue;
            }
        }
    }
    
    public function save()
    {
        $propertiesList = $this->getFileData();
        $array = array();
        foreach ($propertiesList as $propertyName =>  $propertyValue) {
            $array[$propertyName] =  $this->$propertyName;
        }

        $serializeData = json_encode($array,true);
        file_put_contents($this->filepath__,$serializeData);

        return $this;
    }

}