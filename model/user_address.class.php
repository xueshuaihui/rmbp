<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user_address extends base_db {

    function __construct() {
        parent::__construct('user_address', 'id');
    }


}