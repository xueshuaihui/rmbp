<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015/8/19
 * Time: 9:13
 */
!defined('ROOT_PATH') && exit('Access Denied');

class area extends base_db {

    function __construct() {
        parent::__construct('area', 'id');
    }

    function get_area_name() {
        $list = $this->select('', 0);
        return $list;
    }

}