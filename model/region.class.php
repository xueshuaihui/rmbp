<?php

/**
 * Created by PhpStorm.
 * User: liuyanqing
 * Date: 2015/8/20
 * Time: 11:06
 */
!defined('ROOT_PATH') && exit('Access Denied');

class region extends base_db {

    function __construct() {
        parent::__construct('region', 'id');
    }

    /**
     * get user address by three id
     *
     * @param $province_id
     * @param $city_id
     * @param $county_id
     */
    function get_user_address($ids) {
        $sql = 'SELECT * FROM ' . DB::table('region') . ' WHERE id in(\'' . implode("','", $ids) . '\') ORDER BY sortid DESC';
        return DB::fetch_all($sql);
    }
    /**
     * get user address by three id
     *
     * @param $province_id
     * @param $city_id
     * @param $county_id
     */
    function get_by_ids($ids) {
        $sql = 'SELECT * FROM ' . DB::table('region') . ' WHERE id in(\'' . implode("','", $ids) . '\')';
        return DB::fetch_all($sql);
    }

    /**
     * get region children
     *
     * @param $id
     */
    function get_childrens($ids) {
        $sql = 'SELECT * FROM ' . DB::table('region') . ' WHERE pid in(\'' . implode("','", $ids) . '\') ORDER BY sortid DESC';
        return DB::fetch_all($sql);
    }

}