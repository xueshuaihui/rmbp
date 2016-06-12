<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user_field extends base_db {

    function __construct() {
        parent::__construct('user_field', 'user_id');
    }

    public function get_user_field($user_id) {
        return $this->select(array('user_id' => $user_id), 0,0);
    }
}
