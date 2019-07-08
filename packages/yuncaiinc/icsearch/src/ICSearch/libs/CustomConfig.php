<?php

namespace ICSearch\libs;

/**
 * 用户配置类
 */

class CustomConfig
{

    private $config = null;
    private $customDirName = null;

    public function __construct($customDirName)
    {
        $this->customDirName = $customDirName;
        $config = Common::custom($this->customDirName . '/app');
        $defaultConfig = Common::custom('common/app');
        Common::arrayMerge($config, $defaultConfig);
        $this->config = $config;
    }



    public function s($s, $sub = null)
    {
        $str = null;
        if ($sub) {
            $str = Common::arrayGet($this->getField(), $s . '.fields.' . $sub . '.field', null);
        } else {
            $str = Common::arrayGet($this->getField(), $s . '.field', null);
        }
        return $str;
    }

    public function fieldName($arr, $default = null)
    {
        return Common::arrayGet($arr, 'field', $default);
    }

    public function config($key){
        return Common::arrayGet($this->config,$key, array());
    }



    public function ss($s)
    {
        return Common::arrayGet($this->config, 'fields.' . $s . '.fields', array());
    }

    public function getField()
    {
        return Common::arrayGet($this->config, 'fields', array());
    }


    public function expandFields($key)
    {
        $res = [];
        $lists = $this->_expandFields($key);
        if ($lists) {
            foreach ($lists as $item) {
                if (isset($item['fields'])) {
                    foreach ($item['fields'] as $field) {
                        $res[] = $field;
                    }
                } else {
                    $res[] = $item;
                }
            }
        }
        return $res;
    }

    private function _expandFields($key)
    {
        return Common::arrayGet($this->config, 'expandFields.' . $key, array());
    }


    public function getSetting($key)
    {
        return Common::arrayGet($this->config, 'setting.' . $key, null);
    }

    public function getBoost($key)
    {
        return self::getSetting('boost.' . $key);
    }


    public function mfrCapacitor()
    {
        return Common::arrayGet($this->mfrBoost(), 'capacitor', array());
    }

    public function mfrResistor()
    {
        return Common::arrayGet($this->mfrBoost(), 'resistor', array());
    }

    public function mfrInductance()
    {
        return Common::arrayGet($this->mfrBoost(), 'inductance', array());
    }

    public function mfrCrystalOscillator()
    {
        return Common::arrayGet($this->mfrBoost(), 'crystal_oscillator', array());
    }

    public function mfrConnector()
    {
        return Common::arrayGet($this->mfrBoost(), 'connector', array());
    }

    public function mfrBoost()
    {
        return Common::custom($this->customDirName . '/mfr_boost');
    }
}
