<?php
/**
 * CStubActiveRecord for unittests
 *
 * This class is intended for unittests. 
 * 
 *
 * @author 
 * @link 
 * @copyright 
 * @license 
 * @version 
 */
class CStubActiveRecord extends CActiveRecord{
    /*
     * for used 2 db
     */
    protected $_useLong = false;

    public static function getDbConnect($useLong=false){
        if($useLong===true)
            return Yii::app()->db_long;
        return Yii::app()->db;
    }
    public function long(){
        $this->_useLong = true;
        return $this;
    }
    public function short(){
        $this->_useLong = false;
        return $this;
    }
    public function selectDb($useLong=false){
        return !$useLong ? $this->short() : $this->long();
    }
    public function getUseLong(){
        return $this->_useLong;
    }
    public function getDbConnection(){
        return self::getDbConnect($this->_useLong);
    }
    public function __construct($useLong=false, $scenario='insert'){
        if($useLong===null){
            $this->_useLong = false;
            parent::__construct(null);
        } else {
            $this->_useLong = $useLong;
            parent::__construct($scenario);
        }
    }
    /**
     * Model of current active record
     * @param string $className optional class name
     * @return CStubActiveRecord
     */
    public static function model($className=__CLASS__){
        return parent::model($className)->short();//for use 2db
    }

    /**
	 * Returns true if now is unittest context.
	 * @return boolean
	 */
	public static function isUnittests()
	{
		return (Yii::app()->params['isUnitTests'] === true);		
	}
	

	/**
	 *
	 * @param boolean $runValidation
	 * @param array $attributes
	 * @return boolean 
	 */
	public function save($runValidation = true, $attributes = NULL)
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'save');
		}
		
		return parent::save($runValidation, $attributes);		
	}	
	
	/**
	 *
	 * @param string $condition
	 * @param array $params
	 * @return CStubActiveRecord 
	 */
	public function find($condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'find');
		}
		
		return parent::find($condition, $params);		
	}	
	
	/**
	 *
	 * @param string $condition
	 * @param array $params
	 * @return array 
	 */
	public function findAll($condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findAll');
		}
		
		return parent::findAll($condition, $params);		
	}
	
	/**
	 *
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 * @return array 
	 */
	public function findAllByAttributes($attributes, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findAllByAttributes');
		}
		
		return parent::findAllByAttributes($attributes, $condition, $params);		
	}
	
	/**
	 *
	 * @param mixed $pk
	 * @param string $condition
	 * @param array $params
	 * @return array 
	 */
	public function findAllByPk($pk, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findAllByPk');
		}
		
		return parent::findAllByPk($pk, $condition, $params);		
	}
	
	/**
	 *
	 * @param string $sql
	 * @param array $params
	 * @return array 
	 */
	public function findAllBySql($sql, $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findAllBySql');
		}
		
		return parent::findAllBySql($sql, $params);		
	}
	
	/**
	 *
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 * @return CStubActiveRecord 
	 */
	public function findByAttributes($attributes, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findByAttributes');
		}
		
		return parent::findByAttributes($attributes, $condition, $params);		
	}
	
	/**
	 *
	 * @param mixed $pk
	 * @param string $condition
	 * @param array $params
	 * @return CStubActiveRecord 
	 */
	public function findByPk($pk, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findByPk');
		}
		
		return parent::findByPk($pk, $condition, $params);		
	}
	
	
	/**
	 *
	 * @param array $sql
	 * @param array $params
	 * @return CStubActiveRecord 
	 */
	public function findBySql($sql, $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'findBySql');
		}
		
		return parent::findBySql($sql, $params);		
	}
	
	/**
	 *
	 * @param array $attributes
	 * @return boolean 
	 */
	public function update($attributes = NULL)
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'update');
		}
		
		return parent::update($attributes);		
	}
	
	/**
	 *
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 * @return type 
	 */
	public function updateAll($attributes, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'updateAll');
		}
		
		return parent::updateAll($attributes, $condition, $params);		
	}
	
	/**
	 *
	 * @param mixed $pk
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 * @return boolean 
	 */
	public function updateByPk($pk, $attributes, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'updateByPk');
		}
		
		return parent::updateByPk($pk, $attributes, $condition, $params);		
	}
	
	/**
	 *
	 * @return boolean 
	 */
	public function delete()
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'delete');
		}
		
		return parent::delete();		
	}
	
	/**
	 *
	 * @param string $condition
	 * @param array $params
	 * @return boolean 
	 */
	public function deleteAll($condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'deleteAll');
		}
		
		return parent::deleteAll($condition, $params);		
	}
	
	/**
	 *
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 * @return boolean 
	 */
	public function deleteAllByAttributes($attributes, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'deleteAllByAttributes');
		}
		
		return parent::deleteAllByAttributes($attributes, $condition, $params);		
	}
	
	/**
	 *
	 * @param mixed $pk
	 * @param string $condition
	 * @param array $params
	 * @return boolean 
	 */
	public function deleteByPk($pk, $condition = '', $params = array())
	{
		if (CStubActiveRecord::isUnittests())
		{
			return CallFactory::call($this, 'deleteByPk');
		}
		
		return parent::deleteByPk($pk, $condition, $params);		
	}
}

?>
