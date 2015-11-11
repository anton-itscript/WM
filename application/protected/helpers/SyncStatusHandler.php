<?php

/**
 * Class SyncStatusHandler
 */
class SyncStatusHandler extends BaseComponent
{
    const MESSAGE_PREFIX   = 'S';
    const WM_STATUS_SLAVE  = 'S';
    const WM_STATUS_MASTER = 'M';

    /** Fields in messages */
    const MESSAGE_TYPE     = 'message_type';
    const MESSAGE_FROM     = 'message_from';
    const WM_STATUS        = 'wm_status';
    const FOUNDED_IN       = 'founded_in';

    /** @var  string */
    protected $message;

    /** @var  array */
    protected $features;

    /** @var  array init by initDefaultFeatures()*/
    protected $default_features;

    /** @var  string */
    protected $from;

    /**
     * @param Logger $logger
     * @param array  $features
     *
     * @return SyncStatusHandler
     */
    public static function initByFeatures($logger, $features)
    {
        $new = new SyncStatusHandler($logger);
        $new->features = $features;

        return $new;
    }

    /**
     * @param Logger $logger
     * @param string $message
     *
     * @return SyncStatusHandler
     */
    public static function initByMessage($logger, $message)
    {
        $new = new SyncStatusHandler($logger);
        $new->message = $message;

        return $new;
    }

    /**
     * @return string - message
     */
    public function getMessage()
    {
        if (!$this->message && $this->features) {
            $features[] = '@';
            $features[] = self::MESSAGE_PREFIX;

            foreach ($this->getDefaultFeatures() as $key => $default) {

                if (!empty($this->features[$key])) {
                    $val = $default['code'] . $this->features[$key];
                    if (preg_match($default['rule'], $val)) {
                        $features[] = $val;
                    }
                }
            }
            $features[] = '$';

            $this->message = implode($features);
        }
        return $this->message;
    }

    /**
     * @return array - features
     */
    public function getFeatures()
    {
        if (!$this->features && $this->message) {
            $matches = [];
            foreach ($this->getDefaultFeatures() as $key => $default) {
                if (preg_match($default['rule'], $this->message, $matches)) {
                    $this->features[$key] = $matches[2];
                }
            }
        }
        return $this->features;
    }

    /**
     * @return array - default_features
     */
    public function getDefaultFeatures()
    {
        if (!$this->default_features) {
            $this->default_features = [
                self::MESSAGE_TYPE => [
                    'code'        => 'MT',
                    'rule'        => '((MT)([A-Z]))',
                    'description' => 'Message type',
                ],
                self::MESSAGE_FROM => [
                    'code'        => 'MF',
                    'rule'        => '((MF)([a-zA-Z0-9]{5}))',
                    'description' => 'Message from',
                ],
                self::WM_STATUS    => [
                    'code'        => 'ST',
                    'rule'        => '((ST)([' . self::WM_STATUS_SLAVE . self::WM_STATUS_MASTER . ']))',
                    'description' => 'WM status: slave or master',
                ],
                self::FOUNDED_IN => [
                    'code'      => 'FI',
                    'rule'      => '((FI)(\d{14}))',
                    'description' => 'Process Sync Status started in',
                ],
            ];
        }

        return $this->default_features;
    }

    /**
     * @param $url
     *
     * @return $this
     */
    public function setFrom($url)
    {
        $this->from = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }
}