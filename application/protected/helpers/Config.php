<?php

/**
 * Class Config
 * Load / save param in db
 */
class Config extends CStubActiveRecord{
    const space_array = ', ';

    public $formValue;

    /** @var  int */
    public $config_id;
    /** @var  string */
    public $key;
    /** @var  int|float|string|array */
    public $value;
    /** @var  int|float|string|array */
    public $default;
    /** @var  string */
    public $type;
    /** @var  string */
    public $label;

	public static function model($className=__CLASS__){
		return parent::model($className);
	}
	public function tableName(){
		return 'config';
	}
    public function rules() {
        return array(
            array('key','unique'),
            array('key, label, type', 'required'),
            array('value, default','safe')
        );
    }

    /**
     * Return array or string
     * @param $value
     * @param $format
     * @param bool $forSave
     * @return array|bool|int|string
     */
    private static function formatValue($value,$format,$forSave=false){
        switch($format){
            case 'array':
                return $forSave?
                    implode(self::space_array, array_filter($value,function($el){ return !empty($el);})):
                    explode(self::space_array, $value);
                break;
            case 'date':
                return $forSave?
                    strtotime($value):
                    date('',$value);
                break;
            default:
                return $value;
        }
    }

    /**
     * Edit|save config
     * @param $key
     * @param $default
     * @param $type
     * @param $label
     * @return bool
     */
    public static function edit($key,$default,$type,$label){
        $conf = self::model()->findByAttributes(array('key'=>$key));

        if(!$conf){
            $conf = new Config();
            $conf->key = $key;
        }

        $conf->default = self::formatValue($default,$type,true);
        $conf->type = $type;
        $conf->label = $label;
        if($conf->validate())
            return $conf->save();

        return false;
    }

    /**
     * Set config value
     * @param $key
     * @param $value
     * @return bool
     */
    public static function set($key,$value){
        $conf = self::model()->findByAttributes(array('key'=>$key));
        if($conf){
            $conf->value = self::formatValue($value,$conf->type,true);
            if($conf->validate()){
                return $conf->save();
            }
        }
        return false;
    }

    /**
     * Get config
     * @param $key
     * @param int $limit
     * @return array
     */
    public static function get($key = null,$limit=0){
        $criteria = new CDbCriteria();
        $criteria->index = 'key';
        $criteria->order = 't.key';

        if(!is_null($key)) {
            $criteria->condition ='t.key LIKE :key';
            $criteria->params = array(':key' => $key.'%');
        }

        if($limit) {
            $criteria->limit = $limit;
        }

        $res = self::model()->findAll($criteria);
        foreach($res as &$val){
            $val->value = self::formatValue($val->value,$val->type);
        }
        return $res;
    }
}