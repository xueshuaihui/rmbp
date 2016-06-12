<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/30
 * Time: 23:20
 */
!defined('ROOT_PATH') && exit('Access Denied');

class project_lead extends base_db {

    function __construct() {
        parent::__construct('project_lead', 'id');
    }

}