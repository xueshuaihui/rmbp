<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class project_area extends base_db {

    function __construct() {
        parent::__construct('project_area', 'project_id');
    }


    function get_by_project_id($project_id) {
        $sql = 'SELECT * FROM ' . DB::table('project_area') . ' p ' .
            'LEFT JOIN  ' . DB::table('area') . ' a ON p.area_id = a.id ' .
            'WHERE p.project_id=\'' . $project_id . '\'';
        return DB::fetch_all($sql);
    }


    function group_by_area_id($where) {
        if (!$where) {
            $where = '1';
        }
        $sql = 'SELECT COUNT(*) AS C, area_id FROM ' . DB::table('project_area') . ' a ' .
            'WHERE project_id IN(SELECT id FROM ' . DB::table('project') . ' p WHERE ' . $where . ') ' .
            ' GROUP BY a.area_id ORDER BY C DESC';
        return DB::fetch_all($sql);
    }

}