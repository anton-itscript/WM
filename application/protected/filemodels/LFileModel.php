<?php

class LFileModel
{
    protected $filepath;
    protected $filename='.json';

    protected $fileData;

    protected $joinedObjects = array();

    public function __construct()
    {
        $this->filepath = Yii::app()->getBasePath().DIRECTORY_SEPARATOR.''.'appConfig'.DIRECTORY_SEPARATOR.$this->filename;
        $this->fileData = json_decode(file_get_contents($this->filepath),true);
    }

    public function  getFileData()
    {
        return  $this->fileData;
    }

    public function findAll()
    {
        $fileData = $this->fileData;
        $resultArray = array();
        foreach ( $fileData as $key =>$thisItems) {
            $this->LetsJoin($thisItems);
            $resultArray[$key] = $thisItems;
        }

        return $resultArray;
    }

    /**
     * @param $pk string
     */
    public function findByPk($pk)
    {
        $thisItem =  $this->fileData[$pk];
        $this->LetsJoin($thisItem);
        return $thisItem;
    }

    public function findByAttribute($key,$value)
    {
        $data =   $this->fileData;
        $resultArray = array();

        foreach ($data as $k => $item) {
            if($item[$key] == $value) {
                $this->LetsJoin($item);
                $resultArray[$k] = $item;
            }
        }

        return $resultArray;
    }

    /**
     * @param $LFileModelObject LFileModel
     * @param $onKey string
     * @param $thisModelKey string
     * @param $relationName string
     * @return $this
     */
    public function leftJoin($LFileModelObject,$onKey,$thisModelKey, $relationName=false)
    {
        if ($relationName===false) {
            $relationName = get_class($LFileModelObject);
        }

        $this->joinedObjects[] = array(
            'modelObj'=>$LFileModelObject,
            'modelObjKey'=>$onKey,
            'thisModelKey'=>$thisModelKey,
            'relationName'=>$relationName
        );
        return $this;
    }


    /**
     * @param $findeditem
     **/
    protected function LetsJoin(&$findedItem)
    {
        if (count($this->joinedObjects)) {
            foreach ($this->joinedObjects as $joinedModel) {

                    if ($list = $joinedModel['modelObj']->findByAttribute($joinedModel['modelObjKey'],$findedItem[$joinedModel['thisModelKey']])) {

                        $findedItem[$joinedModel['relationName']] = $list;

                    }

            }
        }
    }

}