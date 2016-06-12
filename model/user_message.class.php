<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class user_message extends base_db {

    function __construct() {
        parent::__construct('user_message', 'id');
    }

    /**
     * send message for user
     *
     * @param        $user_id
     * @param        $fuser_id
     * @param        $message
     * @param string $title
     * @param int    $sort_id
     */
    public function send($user_id, $fuser_id, $event_id, $message, $title = '', $sortid = 0) {
        $data = array(
            'user_id' => $user_id,
            'fuser_id' => $fuser_id,
            'message' => $message,
            'dateline' => core::S('time'),
            'title' => $title,
            'sortid' => $sortid,
            'event_id' => $event_id,
        );
        // query exists
        if ($event_id) {
            $where = array('user_id' => $user_id, 'event_id' => $event_id);
            $message = $this->select($where);
            if ($message) {
                return 0;
            }
        }
        $id = $this->insert($data, 1);
        return $id;
    }


    /**
     * get message by ids
     *
     * @param     $ids
     * @param int $user_id
     * @param int $isread
     * @param int $perpage
     * @param int $page
     * @return array|mixed
     */
    public function get_by_ids($ids, $user_id = 0, $isread = -1) {
        if (!$ids) {
            return array();
        }
        $where_sql = " id IN('" . implode("','", $ids) . "')";
        if ($user_id) {
            $where_sql .= ' AND user_id=\'' . $user_id . '\'';
        }
        if ($isread > -1) {
            $where_sql .= ' AND isread=\'' . ((int)$isread) . '\'';
        }
        $message_list = $this->select($where_sql, 0);
        return $message_list;
    }

    /**
     * get message multi page
     *
     * @param int $user_id
     * @param     $perpage
     * @param     $page
     */
    public function get_list($user_id = 0, $isread = -1, $perpage = 20, $page = 1) {
        $where_sql = '1';
        if ($user_id) {
            $where_sql .= ' AND user_id=\'' . $user_id . '\'';
        }
        if ($isread > -1) {
            $where_sql .= ' AND isread=\'' . $isread . '\'';
        }
        $order_sql = ' isread ASC, dateline DESC';
        $message_list = $this->select($where_sql, $order_sql, $perpage, $page);
        return $message_list;
    }

    /**
     * set message is readed
     *
     * @param $ids
     * @return bool
     */
    public function readed($ids) {
        if (!$ids) {
            return false;
        }
        $where_sql = " id IN('" . implode("','", $ids) . "')";
        DB::update('user_message', array('isread' => 1, 'readtime' => core::S('time')), $where_sql);
        return true;
    }

    /**
     * select message concat user
     *
     * @param     $where_sql
     * @param     $order_sql
     * @param int $perpage
     * @param int $page
     */
    public function get_list_and_user($where_sql, $order_sql, $perpage = 20, $page = 1) {
        $start = ($page - 1) * $perpage;
        $order = $order_sql ? $order_sql : ' t1.dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : '*';
        $sql = 'SELECT '. $field .' FROM ' . DB::table('user_message') . ' m ' .
                'LEFT JOIN ' . DB::table('user_field') . ' u ON m.user_id=u.user_id ' .
                ' WHERE ' . $where_sql . ' ORDER BY ' . $order_sql ;
        if($perpage == -2){
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        }else{
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

}