<?php

class ConfigForm extends CFormModel {

    protected $data = array();
    private $_attribute = array();
    public $file_path = false;

    /**
     *
     * @param array $config file array
     * @param string $scenario scenario
     *
     */
    public function __construct($config_path, $scenario = '') {
        parent::__construct($scenario);
        $this->setConfig($config_path);
    }

    public function setConfig($config_path) {

        $this->file_path = $config_path;

        if (!is_file($this->file_path))
            file_put_contents($this->file_path,"<?php \n return array();  \n ?>");

        $this->data = require $config_path;
        if (is_scalar($this->data)) {
            file_put_contents($this->file_path,"<?php \n return array();  \n ?>");
            $this->data = require $config_path;
        }

        $conf_str = file_get_contents($config_path);
        if (is_array($this->data))
        foreach ($this->data as $n => $cf) {
            if (preg_match("#" . $n . "[^\r\n]*//([^\n\r]+)[\n\r]#", $conf_str, $m)) {
                $this->_attribute[$n] = trim($m[1]);
            }
        }
    }

    public function updateConfig($conf) {
        foreach ($conf as $n => $v) {
            if(is_array($v)) {
                foreach ($v as $n1 => $v1) {
                    if (trim($v1) === "") {
                        unset($conf[$n][$n1]);
                    }
                }
            } 

            if (sizeof($this->data[$n]) >= sizeof($conf[$n]))
                $this->data[$n] = $conf[$n];
        }
    }
    
    public function updateParam($param,$value){
        $this->data[$param]=$value;
    }

    public function saveToFile() {
        //$InitConf = $model->getConfig();
        $conf_str = var_export($this->data, true);
        //$atr = $model->getAttribute();
        foreach ($this->_attribute as $n => $v) {
            $conf_str = preg_replace("#([^\n\r]*" . $n . "[^\n\r]*)[\n\r]#is", "\\1//" . $v . "\n", $conf_str);
        }
        file_put_contents($this->file_path, "<?php \n return " . $conf_str . " \n ?>");
    }

    public function getConfig() {
        return $this->data;
    }

    public function getAttribute() {
        return $this->_attribute;
    }

    public function __get($name) {
        if (isset($this->data[$name]))
            return $this->data[$name];
        else
            return parent::__get($name);
    }

    public function __set($name, $value) {
        if (isset($this->data[$name]))
            $this->data[$name] = $value;
        else
            parent::__set($name, $value);
    }

    public function save($path) {
        $config = $this->generateConfigFile();
        if (!is_writable($path))
            throw new CException("Cannot write to config file!");
        file_put_contents($path, $config, FILE_TEXT);
        return true;
    }

    public function generateConfigFile() {
        $this->generateConfigFileRecursive($this->data, $output);
        $output = preg_replace('#,$\n#s', '', $output);
        return "<?php\n return " . $output . ";\n";
    }

    public function generateConfigFileRecursive($attributes, &$output = "", $depth = 1) {
        $output .= "array(\n";
        foreach ($attributes as $attribute => $value) {
            if (!is_array($value))
                $output .= str_repeat("\t", $depth) . "'" . $this->escape($attribute) . "' => '" . $this->escape($value) . "',\n";
            else {
                $output .= str_repeat("\t", $depth) . "'" . $this->escape($attribute) . "' => ";
                $this->generateConfigFileRecursive($value, $output, $depth + 1);
            }
        }
        $output .= str_repeat("\t", $depth - 1) . "),\n";
    }

    private function escape($value) {
        return str_replace("'", "\'", $value);
    }

    /**
     * Возвращает все атрибуты с их значениями
     *
     * @return array
     */
    public function getAttributes($names = NULL) {
        $this->attributesRecursive($this->data, $output);
        return $output;
    }

    /**
     * Возвращает имена всех атрибутов
     *
     * @return array
     */
    public function attributeNames() {
        $this->attributesRecursive($this->data, $output);
        return array_keys($output);
    }

    /**
     * Рекурсивно собирает атрибуты из конфига
     *
     * @param array $config
     * @param array $output
     * @param string $name
     */
    public function attributesRecursive($config, &$output = array(), $name = '') {
        foreach ($config as $key => $attribute) {
            if ($name == '')
                $paramName = $key;
            else
                $paramName = $name . "[{$key}]";
            if (is_array($attribute))
                $this->attributesRecursive($attribute, $output, $paramName);
            else
                $output[$paramName] = $attribute;
        }
    }

    public function attributeLabels() {
        return $this->_attribute;
    }

    public function rules() {
        $rules = array();
        $attributes = array_keys($this->data);
        $rules[] = array(implode(', ', $attributes), 'safe');
        return $rules;
    }


    public function reloadFileProperties($properties)
    {
        if (is_array($properties)){
            $this->data = $properties;
            return true;
        }
        return false;
    }


}

?>