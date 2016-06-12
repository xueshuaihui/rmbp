<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/09/18
 * Time: 15:57
 */
!defined('ROOT_PATH') && exit('Access Denied');

class send_queue extends base_db {

    function __construct() {
        parent::__construct('send_queue', 'id');
    }

}