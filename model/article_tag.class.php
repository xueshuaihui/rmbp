<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class article_tag extends base_db {

    function __construct() {
        parent::__construct('article_tag', 'id');
    }

}
