<?php

/**
 * Created by PhpStorm.
 * User: liuyanqing
 * Date: 2015/11/18
 * Time: 17:59
 */
!defined('ROOT_PATH') && exit('Access Denied');

class logs extends base_db {

    function __construct() {
        parent::__construct('log', 'id');
    }

}