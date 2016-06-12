<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user_trade extends base_db {

    function __construct() {
        parent::__construct('user_trade', 'id');
    }

    function get_buy_list($where_sql,$order=0,$perpage,$page){
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't1.*,t2.*,t1.id id2,t1.user_id user_id2,t1.dateline dateline2,t1.status status2,t1.notifymessage notifymessage2';
        $sql = 'SELECT ' . $field . ' FROM ' . DB::table('user_trade') . ' t1 ' .
            ' LEFT JOIN ' . DB::table('trade') . ' t2 ON t1.trade_id=t2.id WHERE ' . $where_sql . ' ORDER BY ' . $order;
        if ($perpage == -2) {
            $count = DB::fetch_all($sql);
            return count($count);
        } else {
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }
}