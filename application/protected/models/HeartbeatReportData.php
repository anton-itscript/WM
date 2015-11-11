<?php


class HeartbeatReportData extends CStubActiveRecord
{
    const SPACE_ARRAY = '^~';
    const OTHER_KEYS = '^#&@!';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'heartbeat_report_data';
    }

    public function rules()
    {
        return array(
            array('report_id, handler, keys, value', 'required')
        );
    }

    private static function formatValue($array, $forSave = false)
    {
        return $forSave ?
            implode(self::SPACE_ARRAY, $array) :
            explode(self::SPACE_ARRAY, $array);
    }

    public static function set($report_id, $handler, $array, $use_handler_key = null)
    {
        $data = new HeartbeatReportData();

        $data->report_id = $report_id;
        $data->handler = $handler;

        if(!$use_handler_key)
            $data->keys =  self::formatValue(array_keys($array), true);
        else
            $data->keys = self::OTHER_KEYS . $use_handler_key;

        $data->value = self::formatValue($array, true);
        if ($data->validate()) {
            return $data->save();
        }

        return false;
    }

    public static function get($report_id, $handler = '')
    {
        $criteria = new CDbCriteria();
        $criteria->index = 'handler';
        if ($handler != '')
            $criteria->compare('t.handler', $handler);
        $criteria->compare('t.report_id', $report_id);

        $res = self::model()->findAll($criteria);
        $return = array();
        foreach ($res as $handler_id => $val) {
            $arr_keys  = self::formatValue($val['keys']);
            $arr_value = self::formatValue($val['value']);

            if( count($arr_keys) == 1 && stripos($arr_keys[0],self::OTHER_KEYS) !== false ){
                $arr_keys = self::formatValue($res[str_replace(self::OTHER_KEYS,'',$arr_keys[0])]['keys']);
            }
            $count = count($arr_keys);
            for ($i = 0; $i < $count; $i++)
                $return[$handler_id][$arr_keys[$i]] = $arr_value[$i];
        }
        return $return;
    }
}