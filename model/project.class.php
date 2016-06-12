<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/6
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class project extends base_db {

    private $tables = array(
        'project' => array(
            'id',
            'user_id',
            'title',
            'intro',
            'company',
            'status',
            'balance',
            'stage',
            'region_id',
            'region',
            'dateline',
            'existfinancing',
            'existpercent',
            'valuation',
            'minfinancing',
            'maxfinancing',
            'financingday',
            'expiretime',
            'isverify',
            'creator',
            'phone',
            'email',
            'sortid',
            'totalfinancing',
            'feednum',
            'projecttype',
            'openday',
            'province_id',
            'city_id',
            'county_id',
            'istrade',
        ),
        'project_field' => array(
            'project_id',
            'urls',
            'description',
            'financingplan',
            'verifymessage',
            '3ndinfo',
        ),
    );

    private $json_fields = 'urls,3ndinfo';

    function __construct() {
        parent::__construct('project', 'id');
    }

    /**
     * get project extends fields
     *
     * @param $id
     * @return mixed
     */
    function get_fields($id) {
        $sql = 'SELECT * FROM ' . DB::table('project') . ' p' .
            ' LEFT JOIN ' . DB::table('project_field') . ' f ON p.id=f.project_id' .
            ' WHERE p.id=\'' . $id . '\'';
        $project = DB::query($sql, 1);
        $project['urls'] = json_decode($project['urls'], 1);
        $project['3ndinfo'] = json_decode($project['3ndinfo'], 1);
        $this->format($project);
        return $project;
    }


    /**
     * get project
     *
     * @param $id
     */
    public function get($id) {
        $data = parent::get($id);
        $this->format($data);
        return $data;
    }


    public function format(&$project) {
        $project['degree'] = get_percent($project['totalfinancing'], $project['minfinancing']);
        $project['sellpercent'] = round($project['minfinancing'] * 100 / $project['valuation'], 2);
        $project['remaintime'] = get_left_time($project['expiretime']);
        $project['superraise'] = $project['totalfinancing'] < ($project['maxfinancing'] * 10000) ? 0 : 1;
    }

    /**
     * get project field
     *
     * @param $user_id
     * @return mixed
     */
    function get_by_uid($user_id, $order = 0, $perpage = -1, $page = 1) {
        $project = $this->select("user_id = '".$user_id."' AND title !=''", $order, $perpage, $page);
        return $project;
    }


    /**
     * insert new project
     *
     * @param $fields
     */
    public function insert_all($fields) {
        // get select table & update field
        $select_tables = $this->select_fields($fields);

        // insert table get project_id first;
        $project = &$select_tables['project'];
        if (!$project) {
            return false;
        }
        // insert table
        $project_id = $this->insert($project, 1);

        // must insert all relation from tables
        $tables = $this->tables;
        unset($tables['project']);

        foreach ($tables as $table => $all_fields) {
            $field_array = $select_tables[$table];
            if (!$field_array) {
                $field_array = array();
            }
            // update
            $field_array['project_id'] = $project_id;
            DB::insert($table, $field_array);
        }

        return $project_id;
    }

    /**
     * update project field
     *
     * @param     $fields
     * @param int $project_id
     * @return bool
     */
    public function update_all($fields, $project_id = 0) {
        // get select table & update field
        $select_tables = $this->select_fields($fields);
        // update
        foreach ($select_tables as $table => $field_array) {
            $id_field = $table == 'project' ? 'id' : 'project_id';
            // update
            DB::update($table, $field_array, array($id_field => $project_id));
        }
        return true;
    }

    /**
     * get select table
     *
     * @param $fields
     * @return array
     */
    public function select_fields($fields) {
        $select_tables = array();
        $project_field = &$this->tables;
        // get update field
        foreach ($fields as $field => $value) {
            foreach ($project_field as $table => $_fields) {
                if (in_array($field, $_fields)) {
                    if (strpos(',' . $this->json_fields . ',', ',' . $field . ',') !== false) {
                        $select_tables[$table][$field] = core::json_encode($value);
                    } else {
                        $select_tables[$table][$field] = $value;
                    }
                }
            }
        }
        return $select_tables;
    }

}