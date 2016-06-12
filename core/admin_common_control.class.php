<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-08-11
 * Time: 20:02
 */
class admin_common_control extends base_common_control {

    public $is_admin = 0;

    /**
     * login
     *
     * @param $conf
     */
    function __construct(&$conf) {
        parent::__construct($conf);
        //check user auth
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->on_login();
        }
        // check admin group
        $this->is_admin = $this->U->group_id == 999 ? 1 : 0;
        if (!$this->is_admin) {
            $this->on_login();
            exit;
        }
        // unshift view path
        array_unshift($conf['view_path'], ROOT_PATH . 'view/');
        VI::assign('user', $this->U);

        //过滤字段
        $table_field = array(
            'editorValue',
        );

        //添加日志
        if (!is_array($_GET)) {
            $_GET = array();
        }
        if (!is_array($_POST)) {
            $_POST = array();
        }
        if ($_POST) {
            $post = array_merge($_GET, $_POST);
            $post = array_filter($post);//去掉数组空元素
            if (is_array($post['data'])) {
                $post['data'] = array_filter($post['data']);
            }
            foreach ($table_field as $key) {
                unset($post[$key]);
            }
            $request_json = json_encode($post, 1);
            $data = array(
                'user_id' => $this->U->user_id,
                'dateline' => time(),
                'control' => $_GET['c'],
                'action' => $_GET['a'],
                'request' => $request_json,
            );
            $this->logs->insert($data, 1);
        }

    }

    /**
     * admin login
     */
    function on_login() {
        $this->show('user/login');
    }

    function json($data, $error) {
        $data = array(
            'message' => $data,
            'error' => $error,
        );
        echo json_encode($data);
        exit;
    }

    /**
     * bind list variables from query table
     *
     * @param $list
     * @param $param
     */
    function query_tables(&$list, $param) {
        $field_array = array();
        $id_array = array();
        $cache_array = array();
        // build get field
        foreach ($param as $table => $val) {
            if (!$field_array[$table]) {
                $field_array[$table] = array();
            }
            if (is_array($val)) {
                foreach ($val as $id_field => $target_field) {
                    $field_array[$table][$id_field] = $target_field;
                }
            } else {
                $field_array[$table]['id'] = $val;
            }
        }

        // find ids
        foreach ($list as $key => $val) {
            foreach ($field_array as $table => $fields) {
                foreach ($fields as $field) {
                    $value = $val[$field];
                    $id_array[$field][$value] = $value;
                    $cache_array[$field . $value][] = &$list[$key];
                }
            }
        }
        // find in table
        foreach ($field_array as $table => $fields) {
            $where_sql = ' 1 ';
            foreach ($fields as $field => $target_field) {
                if ($value) {
                    $where_sql .= ' AND `' . $field . '` IN(\'' . implode("','", $id_array[$target_field]) . '\')';
                }
            }
            $sql = 'SELECT * FROM ' . DB::table($table) . ' WHERE ' . $where_sql;
            $query_list = DB::fetch_all($sql);
            $result_list = array();
            foreach ($query_list as $key => $val) {
                foreach ($cache_array[$target_field . $val[$field]] as $k => &$v) {
                    $v[$table] = $val;
                }
            }
        }
    }

    /*
     * 模糊查询  查询user_id
     */
    function user_id($obj){
        $user = $this->user_field->select(" truename LIKE '%$obj%'");
        $user_ids = array();
        foreach ($user as $key => $value) {
            $user_ids[] = $value['user_id'];
        }
        return $user_ids;
    }

    /*
     * 模糊查询  查询project_id
     */
    function project_id($obj){
        $project = $this->project->select(" title LIKE '%$obj%'");
        $project_ids = array();
        foreach ($project as $key => $value) {
            $project_ids[] = $value['id'];
        }
        return $project_ids;
    }

    /*
     * 搜索
     */
    function search($search, $where, $order, $data) {
        //默认条件
        if ($where) {
            $where_sql = $where;
        }

        //排序
        if ($search['rank'] && $search['sort_order']) {
            if ($data['rank_field']) {
                $order_sql = ' ' . $data['rank_field'] . ' ' . $search['sort_order'] . ' ';
            } else {
                $order_sql = ' ' . $search['rank'] . ' ' . $search['sort_order'] . ' ';
            }
        } else {
            $order_sql = $order;
        }

        //指定字段模糊搜索
        if($data['search_value']){
            if ($search['search'] && $search['search_value']) {
                $where_sql .= ' AND `' . $search['search'] . '` like  \'%' . $search['search_value'] . '%\'  ';
            }
        }

        //全部字段模糊搜索
        if ($data['all_field']) {
            if (!$search['search'] && $search['search_value']) {
                $str = '';
                foreach ($data['all_field'] as $val) {
                    if ($val == 'user_id') {
                        $str .= ' user_id IN(\'' . implode("','", $data['user_ids']) . '\') OR ';
                    }else if($val == 't1.user_id'){
                        $str .= ' t1.user_id IN(\'' . implode("','", $data['user_ids']) . '\') OR ';
                    }
                    $str .= $val . ' LIKE \'%' . $search['search_value'] . '%\' OR ';
                }
                $where_sql .= 'AND ( ' . substr($str, 0, -3) . ' ) ';
            }
        }

        //状态筛选
        if ($data['state_field']) {
            if ($search['screen'] != '') {
                $where_sql .= ' AND '.$data['state_field'] .'= \''.$search['screen'] .'\' ';
            }
        }

        //日期筛选
        if ($search['start']) {
            $start = strtotime($search['start'] . ' 00:00:00');
            $where_sql .= ' AND ' . $data['data_field'] . ' >= \'' . $start . '\'  ';
        }
        if ($search['end']) {
            $end = strtotime($search['end'] . ' 23:59:59');
            $where_sql .= ' AND ' . $data['data_field'] . ' <= \'' . $end . '\'  ';
        }

        return array('where' => $where_sql, 'order' => $order_sql);
    }

}

?>