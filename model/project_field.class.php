<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class project_field extends base_db {

    function __construct() {
        parent::__construct('project_field', 'project_id');
    }


    /**
     * get project_field
     *
     * @param $id
     */
    public function get($id) {
        $data = parent::get($id);
        $data['urls'] = json_decode($data['urls'], 1);
        $data['3ndinfo'] = json_decode($data['3ndinfo'], 1);
        return $data;
    }

    /**
     * replace data
     *
     * @param $data
     */
    public function replace($data) {
        $data['urls'] = json_encode($data['urls']);
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
        $data['urls'] = json_encode($data['urls']);
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
        $data['urls'] = json_encode($data['urls']);
        return parent::update($data, $id);
    }
}