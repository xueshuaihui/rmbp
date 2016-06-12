<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');
class project_guy extends base_db {

    function __construct() {
        parent::__construct('project_guy', 'id');
    }

    function get_project_guy_list($project_id){
        $list = $this->select(array('project_id'=>$project_id), 'id ASC');
        return $list;
    }

}