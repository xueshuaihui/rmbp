<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/22
 * Time: 21:30
 */
!defined('ROOT_PATH') && exit('Access Denied');
class project_timeline extends base_db {

    function __construct() {
        parent::__construct('project_timeline', 'id');
    }

    function get_timeline_list($project_id){
        $list = $this->select(array('project_id'=>$project_id), 0);
        return $list;
    }



}