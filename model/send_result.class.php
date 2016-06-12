<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/09/18
 * Time: 16:01
 */
!defined('ROOT_PATH') && exit('Access Denied');

class send_result extends base_db {

    function __construct() {
        parent::__construct('send_result', 'id');
    }
    
}