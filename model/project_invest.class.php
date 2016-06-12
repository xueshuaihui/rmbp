<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');
class project_invest extends base_db {

    function __construct() {
        parent::__construct('project_invest', 'id');
    }

    function get_project_list($where){
        $list = $this->select($where, 0);
        return $list;
    }


}