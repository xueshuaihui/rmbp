<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/22
 * Time: 21:33
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user_invest extends base_db {

    function __construct() {
        parent::__construct('user_invest', 'id');
    }

    function get_user_invest_list($where, $order = 0, $perpage = -1, $page = 1) {
        $list = $this->select($where, $order, $perpage, $page);
        return $list;
    }

    function get_invest_num($project_id) {
        $list = $this->select(array('project_id' => $project_id), 0, -2);
        return $list;
    }

    /**
     * @param $user_id
     * @param $project_id
     * @return mixed
     */
    function get_unpay_by_project($user_id, $project_id) {
        $invest = $this->select(array('project_id' => $project_id, 'user_id' => $user_id, 'ispaied' => 0, 'iscancel' => 0), 0, 0);
        return $invest;
    }

    /**
     * @param $user_id
     * @param $project_id
     * @return mixed
     */
    function get_paied_count_by_project($user_id, $project_id) {
        $count = DB::select($this->table.':SUM(num) AS C', array('project_id' => $project_id, 'user_id' => $user_id, 'ispaied' => 1, 'iscancel' => 0, 'isrefund' => 0), 0, 0);
        return $count['C'];
    }


    function get_invest_by_user($project_id, $order, $perpage, $page) {
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : '*';
        $sql = 'SELECT ' . $field . ' FROM ' . DB::table('user_invest') . ' i ' .
            ' LEFT JOIN (SELECT u.*,f.isauth,f.investor,f.truename FROM ' . DB::table('user') . ' u LEFT JOIN ' . DB::table('user_field') . ' f ON u.uid=f.user_id) u ON i.user_id=u.uid ' .
            ' WHERE i.project_id=\'' . $project_id . '\' ORDER BY uid DESC';
        if ($perpage == -2) {
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        } else {
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    function user_invest_project($user_id, $order, $perpage, $page) {
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't2.*,t1.user_id t1_user_id,t1.dateline t1_dateline,t1.status t1_status,t1.price t1_price,t1.invest_id t1_invest_id';
        $sql = 'SELECT ' . $field . ' FROM ' . DB::table('user_invest') . ' t1 ' .
            ' LEFT JOIN ' . DB::table('project') . ' t2 ON t1.project_id=t2.id WHERE t1.user_id=\'' . $user_id . '\' ORDER BY ' . $order;
        if ($perpage == -2) {
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        } else {
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    function get_order_manage($where_sql, $order, $perpage, $page) {
        $start = ($page - 1) * $perpage;
        $order = $order ? $order : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 't1.*,t1.id as t1_id,t1.user_id AS invest_uid, t1.dateline AS invest_dateline,t2.*';
        $sql = 'SELECT ' . $field . ' FROM ' . DB::table('user_invest') . ' t1 ' .
            ' LEFT JOIN ' . DB::table('project') . ' t2 ON t1.project_id=t2.id WHERE 1 ' . $where_sql . ' ORDER BY ' . $order;
//        echo $sql;exit;
        if ($perpage == -2) {
            $count = DB::fetch_all($sql);
            return count($count);
        } else {
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    function invest_money($where_sql, $order, $perpage, $page) {
        $where_sql = ' WHERE ' . $where_sql;
        $sql = 'SELECT SUM(price) sum_price FROM plus_user_invest' . $where_sql;
        $invest_money = DB::fetch_all($sql);
        return $invest_money['0']['sum_price']?$invest_money['0']['sum_price']:0;
    }


    /**
     * get project_field
     *
     * @param $id
     */
    public function get($id) {
        $data = parent::get($id);
        if ($data['data']) {
            $data['data'] = json_decode($data['data'], 1);
        }
        return $data;
    }

    /**
     * replace data
     *
     * @param $data
     */
    public function replace($data) {
        if ($data['data']) {
            $data['data'] = json_encode($data['data']);
        }
        return parent::replace($data);
    }

    /**
     * insert
     *
     * @param     $data
     * @param int $return_id
     * @param int $replace
     * @return mixed
     */
    public function insert($data, $return_id = 0, $replace = 0) {
        if ($data['data']) {
            $data['data'] = json_encode($data['data']);
        }
        $id = parent::insert($data, $return_id, $replace);
        return $id;
    }

    /**
     * update
     *
     * @param $data
     * @param $id
     */
    public function update($data, $id) {
        if ($data['data']) {
            $data['data'] = json_encode($data['data']);
        }
        return parent::update($data, $id);
    }

}