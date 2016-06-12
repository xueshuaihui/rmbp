<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user_feed extends base_db {

    function __construct() {
        parent::__construct('user_feed', 'id');
    }

    public function get_feed($user_id, $type) {
        $sql = 'SELECT * FROM ' . DB::table('user_feed') . ' a ' .
            'LEFT JOIN  ' . DB::table($type) . ' b ON a.target_id = b.id ' .
            'WHERE a.user_id=\'' . $user_id . '\' and a.feedtype= \'' . $type . '\' ';
        return DB::fetch_all($sql);
    }

    public function _get_feed($where, $order, $perpage, $page) {
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't1.*,t2.truename';
        $sql = 'SELECT '. $field  .' FROM '. DB::table('user_feed')  .' t1 LEFT JOIN '. DB::table('user_field')  .' t2 ON t1.user_id=t2.user_id WHERE t1.feedtype =\''.'project'.'\'and t1.target_id= \''. $where  .'\'';
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    function user_feed_project($user_id,$order,$perpage,$page){
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't2.*,t1.user_id t1_user_id,t1.dateline t1_dateline';
        $sql = 'SELECT '. $field  .' FROM '.DB::table('user_feed'). ' t1 '.
            ' LEFT JOIN '.DB::table('project').' t2 ON t1.target_id=t2.id WHERE t1.user_id=\''.$user_id.'\' AND feedtype=\''.project.'\' ORDER BY '.$order;
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    /*
     * 关注用户
     */
    public function get_user_attent($data, $order, $perpage, $page) {
        $where = '1';
        if($data['user_id']){
            $where .= ' AND t1.user_id = \''.$data['user_id'].'\'';
            $on = ' t1.target_id=t2.user_id ';
        }
        if($data['target_id']){
            $where .= ' AND t1.target_id = \''.$data['target_id'].'\'';
            $on = ' t1.user_id=t2.user_id ';
        }
        if($data['feedtype']){
            $where .= ' AND t1.feedtype = \''.$data['feedtype'].'\'';
        }
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't1.*,t2.truename,t2.company,t2.job';
        $sql = 'SELECT '. $field  .' FROM '. DB::table('user_feed')  .' t1 LEFT JOIN '. DB::table('user_field')  .' t2 ON '.$on.' WHERE '.$where;
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

}
