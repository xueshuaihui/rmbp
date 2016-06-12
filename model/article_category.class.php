<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class article_category extends base_db {

    function __construct() {
        parent::__construct('article_category', 'id');
    }

    /*
     * select article_category
     */
    public function get_article_category($where, $order = 0, $perpage = -1, $page = 1) {
        $list = $this->select($where, $order, $perpage, $page);
        return $list;
    }

    /*
     * insert article_category
     */
    public function add_article_category($data) {
        $this->insert($data, 1);
    }

    /*
     * update article_category
     */
    public function update_article_category($data, $where) {
        $this->update($data, $where);
    }

    /*
     * 取五个热门标签
     */
    public function hot_list($max = 6) {
        $sql = 'SELECT * FROM ' . DB::table('article_category') . ' WHERE type = \'label\' ORDER BY articlenum DESC LIMIT 0,'.$max;
        return DB::fetch_all($sql);
    }

}
