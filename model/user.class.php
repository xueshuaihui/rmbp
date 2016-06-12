<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user extends base_db {

    function __construct() {
        parent::__construct('user', 'uid');
    }


    function get_list($wheresql, $order, $perpage, $page){
        $start = (max($page, 1) - 1)*$perpage;
        $field = $perpage == -2 ? 'COUNT(0) AS C' : '*';
        $sql = 'SELECT '. $field  .' FROM '.DB::table('user').' u'.
               ' LEFT JOIN '.DB::table('user_field').' f ON u.uid=f.user_id '.
               ' WHERE '.$wheresql.' ORDER BY '.$order;
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }


}