<?php
class ConvertDataloggerCsvToMessages
{
    /** @var  string */
    private $station;

    /** @var  string */
    private $source;

    /** @var  string */
    private $convert;

    protected $prefix = "D";

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string|null
     */
    public function getConvert()
    {
        if (is_null($this->convert) && $this->getSource() && $this->getStation()) {
            $convert = '';

            $tmp = mb_split('\s+', $this->getSource());
            if (count($tmp)) {
                $keys = array_filter(mb_split(',', $tmp[0]));
                $cnt  = count($keys);
                $tmp  = array_slice($tmp, 2);
                $prev = null;

                foreach($tmp as $value) {
                    $v = mb_split(',', $value, $cnt);
                    if (count($v) == $cnt) {
                        $data     = array_combine($keys, $v);
                        $convert .= $this->getMsg($data, $prev) . "\n";
                        $prev     = $data;
                    }
                }
            }

            $this->setConvert($convert);
        }

        return $this->convert;
    }

    /**
     * @param string $convert
     */
    private function setConvert($convert)
    {
        $this->convert = $convert;
    }

    /**
     * @return string
     */
    public function getStation()
    {
        return $this->station;
    }

    /**
     * @param string $station
     */
    public function setStation($station)
    {
        $this->station = $station;
    }

    /**
     * @param $data
     * @param $prev
     * @return string
     */
    private function getMsg($data, $prev)
    {
        if (is_null($prev)) {
            $prev = $data;
        }

        $params = [];
        $a = date_create_from_format('m/d/Y H:i', $prev['DATE'].' '.$prev['TIME']);
        $b = date_create_from_format('m/d/Y H:i', $data['DATE'].' '.$data['TIME']);

        $i = (int) $b->diff($a, true)->format('i');
        $h = (int) $b->diff($a, true)->format('h');

        if ($i == 0 && $h != 0) {
            $i = $h * 60;
        } elseif ($h != 0 && $i != 0) {
            $i = $i + $h * 60;
        }

        $params['interval'] = $i;
        $msg = $this->prefix;
        $msg .= $this->getStation();
        $msg .= $b->format('ymd');
        $msg .= $b->format('Hi');
        $msg .= '00';
        $msg .= $this->prepareData($data, $params);

        return '@' . $msg . It::prepareCRC($msg) .'$';

    }

    /**
     * @param $data
     * @param $params
     * @return string
     */
    private function prepareData($data, $params = [])
    {
        $res = '';

        $structure = $this->getStructure($data);
        foreach($structure as $sensor_type => $sensors) {
            foreach($sensors as $sensor_id => $features) {
                $res .= $this->prepareSensor($sensor_type, $sensor_id, $features, $params);
            }
        }

        return $res;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getStructure(array $data)
    {
        $structure = [];

        foreach($data as $k => $v) {
            $m = [];

            if (preg_match('/([A-Z]{2})(\d)\.([A-Z]+)/', $k, $m)) {
                $structure[$m[1]][$m[2]][$m[3]] = $v;
            }
        }

        return $structure;
    }

    /**
     * @param string $type
     * @param int $id
     * @param array $data
     * @param array $params
     * @return string
     */
    private function prepareSensor($type, $id, array $data, array $params = [])
    {
        $res = $type . $id;


        if ($type == 'BV') {
            $data['V'] = round($data['V'],1);
            $res .= str_replace('.', '',$data['V']);
        } elseif ($type == 'WS') {
             $res .= "3". str_pad(str_replace('.', '',$data['WS']), 4, "0", STR_PAD_LEFT) . str_pad("MMMM", 4, "0", STR_PAD_LEFT) . str_pad("MMMM", 4, "0", STR_PAD_LEFT);
        } elseif ($type == 'WD') {
            $res .= '3' . str_pad($data['WD'], 3, "0", STR_PAD_LEFT). str_pad("MMM", 3, "0", STR_PAD_LEFT). str_pad("MMM", 3, "0", STR_PAD_LEFT);
        } elseif ($type == 'TP') {
            $res .= ($data['TP'] > 0 ? 1 : 0) . str_pad(abs(str_replace('.', '',$data['TP'])), 3, "0", STR_PAD_LEFT);
        } elseif ($type == 'HU') {
            $res .=  str_pad(round($data['RH']), 3, "0", STR_PAD_LEFT);
        } elseif ($type == 'PR') {
            $res .= str_pad(round($data['P']), 5, "0", STR_PAD_RIGHT);
        } elseif ($type == 'RN') {
            $res .= str_pad($params['interval'], 3, "0", STR_PAD_LEFT) . str_pad(str_replace('.', '',$data['RN']), 6, "0", STR_PAD_LEFT) . str_pad("MMMMMM", 6, "0", STR_PAD_LEFT);
        } elseif ($type == 'SD') {
            $res .= str_pad($params['interval'], 3, "0", STR_PAD_LEFT) . str_pad($data['SD'], 3, "0", STR_PAD_LEFT) . 'MMMM';
        }

        return $res;
    }
}