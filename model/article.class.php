<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class article extends base_db {

    function __construct() {
        parent::__construct('article', 'id');
    }

    public function get_sortid($article) {
        // 三个月左右固顶
        // 1000次点击顶
        $stime = (($article['dateline'] - 1104508800) / 86400) * 300;
        $slog = $article['sortid'];
        $slog = $slog == 0 ? 0 : $slog * 3000;
        $order_id = round($stime + $slog + $article['clicknum'] * 0.3);
        return $order_id;
    }

    /*
     * select article
     */
    public function get_article($where) {
        //return $this->select($where, 0);
        $sql = 'SELECT a.*,b.type,b.id as b_id,category,b.disabled as b_disabled FROM ' . DB::table('article') . ' a ' .
            'LEFT JOIN  ' . DB::table('article_category') . ' b ON a.category_id = b.id ' . $where;
        return DB::fetch_all($sql);
    }

    /**
     * insert article
     *
     * @param $data
     */
    public function add_article($data) {
        return $this->insert($data, 1);
    }

    /*
     * update article
     */
    public function update_article($data, $where) {
        $this->update($data, $where);
    }

    /**
     * get list by article category type
     *
     * @param        $type
     * @param string $order_sql
     * @param        $perpage
     * @param        $page
     */
    public function get_list_by_type($type, $order_sql = '', $perpage, $page) {
        $start = ($page - 1) * $perpage;
        $order_sql = $order_sql ? $order_sql : ' dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 'a.*,b.id as b_id,b.sortid as b_sortid,b.disabled as b_disabled,category';
        $sql = 'SELECT ' . $field . ' FROM ' . DB::table('article') . ' a ' .
            'LEFT JOIN  ' . DB::table('article_category') . ' b ON a.category_id = b.id where b.type=\'' . $type . '\'  ORDER BY ' . $order_sql;
        if ($perpage == -2) {
//            $count = DB::query($sql);
//            return $count['C'];
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        } else {
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    /*
     * 根据标签id取文章列表
     */
    public function get_list_by_label($type, $label, $order_sql = '', $perpage, $page) {
        $start = ($page - 1) * $perpage;
        $order_sql = $order_sql ? $order_sql : ' dateline DESC';
        $field = $perpage == -2 ? 'COUNT(0) AS C' : 'a.*,b.id as b_id,b.sortid as b_sortid,b.disabled as b_disabled,category';
        $sql = 'SELECT ' . $field . ' FROM ' . DB::table('article') . ' a ' .
            'LEFT JOIN  ' . DB::table('article_category') . ' b ON a.category_id = b.id where b.type=\'' . $type . '\' AND a.label LIKE \'%' . $label . '%\' ORDER BY ' . $order_sql;
        if ($perpage == -2) {
            $count = DB::fetch_all($sql);
            return $count[0]['C'];
        } else {
            $sql .= ' LIMIT ' . $start . ',' . $perpage;
            return DB::fetch_all($sql);
        }
    }

    /*
     * 根据id 取文章内容
     */
    public function get_article_content($id) {
        $sql = 'SELECT a.*,b.id as b_id,b.sortid as b_sortid,b.disabled as b_disabled,category FROM ' . DB::table('article') . ' a ' .
            'LEFT JOIN  ' . DB::table('article_category') . ' b ON a.category_id = b.id where a.id=\'' . $id . '\' ';
        $res = DB::fetch_all($sql);
        return $res['0'];
    }

    /*
     * 取点击率最高的五个文章
     */
    public function hot_list($max = 5) {
        $sql = 'SELECT * FROM ' . DB::table('article') . ' a WHERE type = \'news\'  ORDER BY clicknum DESC LIMIT 0,'.$max;
        return DB::fetch_all($sql);
    }

    /**
     * get article content by id
     *
     * @param $id
     * @return mixed
     */
    public function get_content($id) {
        $sql = 'SELECT * FROM ' . DB::table('article') . ' a where a.id=\'' . $id . '\' ';
        $res = DB::fetch_all($sql);
        return $res['0'];
    }
}
