<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class user_control extends admin_common_control {

    public function on_index() {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        if (!isset($_GET['isauth'])) {
            $_GET['isauth'] = 999;
        }
        $isauth = core::G('isauth:int');

        //搜索
        $search = core::R('search');
        if($search){
            $where = ' 1 ';
            $order = ' authtime DESC, uid DESC ';
            $data = array(
                'search_value' => '1',
                'data_field' => 'authtime',
                'all_field' => array('uid', 'truename', 'phone', 'email', 'company', 'job', 'region')
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
            if ($search['screen']) {
                if ($search['screen'] == 'investor1') {
                    $where_sql .= " AND investor in('','0','1','2','3')";
                } else {
                    $where_sql .= " AND investor not in ('','0','1','2','3')";
                }
            }
        }else{
            $where_sql = ' 1 ';
            $order_sql = ' authtime DESC, uid DESC ';
        }
        VI::assign('search', $search);

        switch ($isauth) {
            case -1:
            case 1:
            case 2:
                $where_sql .= ' AND isauth=\'' . $isauth . '\'';
            break;
            case 0:
                $where_sql .= ' AND group_id=\'' . 0 . '\'';
            break;
            case 100:
            case 999:
                $where_sql .= ' AND group_id=\'' . $isauth . '\'';
            break;
            case 99:
            break;
        }
        // find uid
        $uid = core::G('uid:int');
        if ($uid) {
            $where_sql .= ' AND uid=\'' . $uid . '\'';
        }
        $user_list = $this->user->get_list($where_sql, $order_sql, $perpage, $page);
        $user_count = $this->user->get_list($where_sql, $order_sql, -2, $page);
        $page_html = misc::pages($user_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/user/index/isauth/' . $isauth . '/?' . $this->http_query($search, 'search') . '&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('user_list', $user_list);
        VI::assign('isauth', $isauth);
        $this->show('admin/user_index');
    }

    public function http_query($search, $field) {
        $result = array();
        foreach ($search as $key => $val) {
//            $result[] = $field . '[' . $key . ']=' . urlencode($val);
            $result[] = $field . '[' . $key . ']=' . $val;
        }
        return implode("&", $result);
    }

    /**
     * logout
     */
    public function on_logout() {
        if (misc::form_submit()) {
            core::C('auth', '', -1);
            $this->show('admin/logout', $this->conf['app_dir']);
        } else {
            $this->show_message('ErrorRequest');
        }
    }

    private function _assign_project() {
        $user_id = core::R('user_id');
        $user_id = url_id($user_id, 'decode');
        if ($user_id) {
            $user_data = $this->U->field('', $user_id);
            VI::assign('user_data', $user_data);
        }
        return $user_data;//用户信息
    }

    /*
     * 投资人信息
     */
    function on_field_project() {
        $user_ids = core::R('user_id');//对应的user_id
        VI::assign('user_id', $user_ids);
        $user_info = $this->_assign_project();//用户信息
        $user_id = $user_info['user_id'];

        $area_list = $this->user_feed->get_feed($user_id, 'area');
        $user_area = array();
        foreach ($area_list as $area) {
            $user_area[$area['id']] = $area;
        }
        VI::assign('user_area', $user_area);
        unset($area_list);
        $user_region = $this->user_feed->get_feed($user_id, 'region');
        VI::assign('user_region', $user_region);
        $area_list = $this->area->select(array(), 0);
        VI::assign('area_list', $area_list);
        $this->show('admin/user_field_project');
    }

    /*
     * 项目列表
     */
    function on_list_project() {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $user_ids = core::R('user_id');//对应的user_id
        VI::assign('user_id', $user_ids);
        $user_info = $this->_assign_project();//用户信息
        $user_id = $user_info['user_id'];

        $where_sql = ' user_id = \'' . $user_id . '\'  ';
        $project_list = $this->project->select($where_sql, 0, $perpage, $page);
        $project_count = $this->project->select($where_sql, 0, -2, $page);
        $page_html = misc::pages($project_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/user/list_project/page/%d/');
        foreach ($project_list as $key => &$project) {
            $project_list[$key]['invest_num'] = $this->user_invest->select('project_id = \'' . $project['id'] . '\'', 0, -2);//投资人数
        }
        VI::assign('page_html', $page_html);
        VI::assign('project_list', $project_list);
        $this->show('admin/user_list_project');
    }

    /**
     * 关注列表
     */
    public function on_feed_project() {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $user_ids = core::R('user_id');//对应的user_id
        VI::assign('user_id', $user_ids);
        $user_info = $this->_assign_project();//用户信息
        $user_id = $user_info['user_id'];

        $feed_list = $this->user_feed->user_feed_project($user_id, 0, $perpage, $page);
        $feed_count = $this->user_feed->user_feed_project($user_id, 0, -2);
        $page_html = misc::pages($feed_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/user/invest_project/page/%d');
        foreach ($feed_list as $key => $val) {
            $feed_list[$key]['user_invest'] = $this->user_invest->select('project_id = \'' . $val['id'] . '\' and user_id=\'' . $val['t1_user_id'] . '\'', 0, 0);
        }
        VI::assign('page_html', $page_html);
        VI::assign('feed_list', $feed_list);
        $this->show('admin/user_feed_project');
    }

    /*
     * 锁定、解锁功能
     */
    public function on_islock() {
        $field = core::R('field');
        $isauth = core::R('isauth');
        $this->user_field->update($field, $field['user_id']);
        $this->json('操作成功', 0);
    }

    /*
     * 更改用户权限
     */
    public function on_permissions() {
        $field = core::R('field');
        $isauth = core::R('isauth');
        $this->user_field->update($field, $field['user_id']);
        $this->U->delete_session($field['user_id']);
        $this->json('操作成功', 0);
    }

    /*
     * 更改审核状态
     */
    public function on_change_state() {
        $isauth = core::R('isauth');
        $data = core::R('data');

        $source = $this->user->get($data['user_id']);
        if (!$source) {
            $this->json('该用户不存在');
        }
        $this->user_field->update($data, $data['user_id']);
        $event_param = array('message' => $data['authmessage']);
        $this->event->add('auth_message', $data['user_id'], $event_param, $this->U->user_id);
        $this->json('操作成功', 0);
//        $this->show_message('操作成功', $this->conf['app_dir'] . ADMIN_DIR . '/user/index/isauth/' . $isauth . '/');
    }

    /**
     * 投资列表
     */
    public function on_invest_project() {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $user_ids = core::R('user_id');//对应的user_id
        VI::assign('user_id', $user_ids);
        $user_info = $this->_assign_project();//用户信息
        $user_id = $user_info['user_id'];

        $invest_list = $this->user_invest->user_invest_project($user_id, 0, $perpage, $page);
        $invest_count = $this->user_invest->user_invest_project($user_id, 0, -2);
        $page_html = misc::pages($invest_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/user/invest_project/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('invest_list', $invest_list);
        $this->show('admin/user_invest_project');
    }

    /**
     * 约见列表
     */
    public function on_meet_project() {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $user_ids = core::R('user_id');//对应的user_id
        VI::assign('user_id', $user_ids);
        $user_info = $this->_assign_project();//用户信息
        $user_id = $user_info['user_id'];

        $invest_list = $this->project_meet->user_meet_project($user_id, 0, $perpage, $page);
        $invest_count = $this->project_meet->user_meet_project($user_id, 0, -2);
        $page_html = misc::pages($invest_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/user/invest_project/page/%d');
        foreach ($invest_list as $key => $val) {
            $invest_list[$key]['user_field'] = $this->user_field->select(' user_id = \'' . $user_ids . '\'', 0, 0);
        }
        VI::assign('page_html', $page_html);
        VI::assign('invest_list', $invest_list);
        $this->show('admin/user_meet_project');
    }

}

?>