<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class variable extends base_db {

    static $value_cache = array();

    function __construct() {
        parent::__construct('variable', 'name');
    }

    /**
     * set varialbes
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    function set($name, $value) {
        self::$value_cache = array();
        $data = array(
            'name' => $name,
            'value' => $value,
            'dateline' => core::R('time')
        );
        return $this->replace($data);
    }

    /**
     * get varialbe
     *
     * @param $name
     * @return mixed
     */
    function get($name) {
        $value_cache = &self::$value_cache;
        if (isset($value_cache[$name])) {
            return $value_cache[$name];
        }
        list($true_key, ) = explode('.', $name);
        $variable = parent::get($true_key);
        if (!$variable) {
            $value_cache[$name] = '';
        } else {
            if (substr($name, -5) == '.json') {
                $value_cache[$name] = json_decode($variable['value'], 1);
            } else {
                $value_cache[$name] = $variable['value'];
            }
        }
        return $value_cache[$name];
    }

}