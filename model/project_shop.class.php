<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');
class project_shop extends base_db {

    function __construct() {
        parent::__construct('project_shop', 'id');
    }
}