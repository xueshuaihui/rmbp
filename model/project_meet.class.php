<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class project_meet extends base_db {

    function __construct() {
        parent::__construct('project_meet', 'id');
    }

    public function get_project_meet($where,$order,$perpage,$page){
        $list = $this->select($where, $order,$perpage,$page);
        return $list;
    }

    public function get_project_by_meet($where_sql,$order=0,$perpage,$page){
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't1.*,t2.title,t3.truename';
        $sql = 'SELECT ' . $field . ' FROM ' .DB::table('project_meet'). ' t1 ,'.DB::table('project') . ' t2 ,' .DB::table('user_field') .' t3  WHERE t1.project_id=t2.id and t1.user_id=t3.user_id '.$where_sql .' ORDER BY ' .$order;
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    function user_meet_project($user_id,$order,$perpage,$page){
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't2.*,t1.user_id t1_user_id,t1.dateline t1_dateline,t1.message t1_message';
        $sql = 'SELECT '. $field  .' FROM '.DB::table('project_meet'). ' t1 '.
            ' LEFT JOIN '.DB::table('project').' t2 ON t1.project_id=t2.id WHERE t1.user_id=\''.$user_id.'\' ORDER BY '.$order;
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }
}
