<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class project_control extends admin_common_control
{
    public function on_index()
    {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);

        //搜索
        $search = core::R('search');
        if($search){
            $where = ' title != \' \' ';
            $order = ' dateline DESC ';
            $user_ids = $this->user_id($search['search_value']);
            $data = array(
                'state_field' => 'stage',
                'data_field' => 'dateline',
                'all_field' => array('id', 'title', 'phone', 'email', 'region', 'user_id'),
                'user_ids' => $user_ids
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
            //指定字段模糊搜索
            if ($search['search'] && $search['search_value']) {
                if ($search['search'] == 'truename') {
                    $where_sql .= ' AND `user_id` IN(\'' . implode("','", $user_ids) . '\') ';
                } else {
                    $where_sql .= ' AND `' . $search['search'] . '` like  \'%' . $search['search_value'] . '%\'  ';
                }
            }
        }else{
            $where_sql = ' title != \' \' ';
            $order_sql = ' dateline DESC ';
        }
        VI::assign('search', $search);

//        if ($search['user_id']) {
//            $where_sql .= ' AND  user_id=\'' . $search['user_id'] . '\'';
//        }
//        if (core::R('user_id')) {
//            $where_sql .= ' AND user_id=\'' . core::R('user_id:int') . '\' ';
//        }

        //审核状态(-1未通过0提交中1待审核2预热中3众筹中4众筹成功5众筹失败)
        $verify = core::R('verify:int');
        switch ($verify) {
            case 0:
                $where_sql .= '';
                break;
            case 2:
                $where_sql .= ' AND (isverify=2 OR isverify=3 OR isverify=6)';
                break;
            case 3:
                $where_sql .= ' AND isverify>3';
                break;
            case 4:
                $where_sql .= ' AND isverify=-1';
                break;
            case 5:
                $where_sql .= ' AND isverify=0';
                break;
            default:
                $where_sql .= ' AND isverify=\'' . $verify . '\'';
                break;
        }

        $project_list = $this->project->select($where_sql, $order_sql, $perpage, $page);
        $project_count = $this->project->select($where_sql, $order_sql, -2, $page);
        $page_html = misc::pages($project_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/index/verify/' . $verify . '/?' . $this->http_query($search, 'search') . '&page=%d');
        foreach ($project_list as $key => &$project) {
            $project_list[$key]['invest_num'] = $this->user_invest->select('project_id = \'' . $project['id'] . '\'', 0, -2);//投资人数
            $project_list[$key]['field'] = $this->project_field->select('project_id = \'' . $project['id'] . '\'', 0, 0);//投资人数
        }
        $this->query_tables($project_list, array(
            'user_field' => array('user_id' => 'user_id'),
            'user' => array('uid' => 'user_id'),
        ));
        VI::assign('page_html', $page_html);
        VI::assign('project_list', $project_list);
        VI::assign('verify', $verify);
        $this->show('admin/project_index');
    }

    public function http_query($search, $field)
    {
        $result = array();
        foreach ($search as $key => $val) {
//            $result[] = $field . '[' . $key . ']=' . urlencode($val);
            $result[] = $field . '[' . $key . ']=' . $val;
        }
        return implode("&", $result);
    }

    private function _message($lang, $error = 1)
    {
        $format = core::R('format');
        if ($lang != 1) {
            $languages = array(
                'unknown_request' => '未知的请求，请重试',
                'phone_error' => '手机号码输入有误，请重试',
                'email_error' => '邮箱地址有误，请重试',
                'phone_exists' => '手机号码已经注册，请更换',
                'email_exists' => '邮箱地址已被注册，请更换',
                'request_too_short' => '两次请求时间太短，请稍后再试',
                'invalid_regcode' => '验证码错误或者失效，请重新获取',
                'password_not_same' => '两次输入的密码不一致，请检查',
                'username_exists' => '用户名已经被注册，请重新选择',
                'system_error' => '系统错误，请重试',
                'expire_regcode' => '验证码已经过期，请重新获取',
                'invalid_submit' => '错误的表单提交，请刷新页面重试',
                'login_not_exists' => '该用户不存在，请检查',
                'user_not_exists' => '用户不存在，请检查',
                'login_fail' => '登录失败，请检查',
                'login_first' => '请先登录后再操作',
                'no_authority' => '您没有权限编辑该项目',
                'no_project_select' => '请选择正确的项目进行发布',
                'meet_exists' => '您已经发送过约见请求。',
                'password_must_different_than_old' => '新密码不能和旧密码一致',
                'old_password_invalid' => '旧密码输入错误。',
            );
            $lang = isset($languages[$lang]) ? $languages[$lang] : $lang;
        } else {
            $error = 0;
        }

        $data = array(
            'message' => $lang,
            'error' => $error,
        );
        switch ($format) {
            case 'json':
            default:
                echo json_encode($data);
                break;
        }
        exit;
    }


    private function _assign_project()
    {
        $project_id = core::R('project_id');;
        $project_id = url_id($project_id, 'decode');
        if ($project_id) {
            $project = $this->project->get_fields($project_id);//项目信息;
            VI::assign('project_info', $project);
        }
        return $project;//项目信息;
    }

    /*
     * 项目信息
     */
    public function on_project_info()
    {
        $project_ids = core::R('project_id');//对应的project_id
        VI::assign('project_id', $project_ids);

        $project = $this->_assign_project();//项目基本信息
        $project_id = $project['id'];
        $project['areas'] = $this->project_area->get_by_project_id($project_id);//所属领域
        $attachment = $this->attachment->get_by_type($project_id, 'project', 'banner');//项目展示图
        $project['banner'] = $attachment;

        $project['guys'] = $this->project_guy->select(array('project_id' => $project_id), 0);//合作伙伴
        //$project['invest'] = $this->project_invest->select(array('project_id' => $project_id), 0);//投资档位回报
        $project['field'] = $this->project_field->select(array('project_id' => $project_id), 0, 0);
        $project['shops'] = $this->project_shop->select(array('project_id' => $project_id));//经营店铺
        VI::assign('project', $project);
        $this->show('admin/project_info');
    }

    /*
     * 项目约见列表页面
     */
    public function on_meet()
    {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $project_ids = core::R('project_id');//对应的project_id
        VI::assign('project_id', $project_ids);
        $project_info = $this->_assign_project();
        $project_id = $project_info['id'];

        //搜索
        $search = core::R('search');
        if($search){
            $where = '';
            $order = 0;
            $data = array(
                'search_value' => '1',
                'rank_field' =>  't1.' . $search['rank'],
                'data_field' => 't1.dateline',
                'all_field' => array('title', 'truename', 'message')
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
        }else{
            $where_sql = '';
            $order_sql = 0;
        }
        VI::assign('search', $search);

        if ($project_id) {
            $where_sql .= ' AND project_id = \'' . $project_id . '\'';
        }
        $meet_list = $this->project_meet->get_project_by_meet($where_sql, $order_sql, $perpage, $page);
        $meet_count = $this->project_meet->get_project_by_meet($where_sql, $order_sql, -2);
        if ($project_ids) {
            $page_html = misc::pages($meet_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/meet/project_id/' . $project_ids . '/page/%d');
        } else {
            $page_html = misc::pages($meet_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/meet/?' . $this->http_query($search, 'search') . '&page=%d');
        }
        VI::assign('page_html', $page_html);
        VI::assign('meet_list', $meet_list);
        VI::assign('search', $search);
        $this->show('admin/project_meet');
    }

    /*
     * 项目投资列表页面
     */
    public function on_invest()
    {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $project_ids = core::R('project_id');//对应的project_id
        VI::assign('project_id', $project_ids);
        $project_info = $this->_assign_project();
        $project_id = $project_info['id'];

        $invest_list = $this->user_invest->get_invest_by_user($project_id, 0, $perpage, $page);
        $invest_count = $this->user_invest->get_invest_by_user($project_id, 0, -2);
        $page_html = misc::pages($invest_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/invest/project_id/' . $project_ids . '/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('invest_list', $invest_list);
        $this->show('admin/project_invest');
    }

    /*
     * 项目问答列表页面
     */
    public function on_issue()
    {

    }

    /*
     * 项目关注列表页面
     */
    public function on_attent()
    {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $project_ids = core::R('project_id');//对应的project_id
        VI::assign('project_id', $project_ids);
        $project_info = $this->_assign_project();
        $project_id = $project_info['id'];
        $feed_list = $this->user_feed->_get_feed($project_id, 0, $perpage, $page);
        $feed_count = $this->user_feed->_get_feed($project_id, 0, -2);
        $page_html = misc::pages($feed_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/attent/project_id/' . $project_ids . '/page/%d');
        foreach ($feed_list as $key => $val) {
            $feed_list[$key]['project_invest'] = $this->project_invest->get_project_list(array('project_id' => $project_id, 'user_id' => $val['user_id']));
        }
        VI::assign('page_html', $page_html);
        VI::assign('feed_list', $feed_list);
        $this->show('admin/project_attent');
    }

    /*
     * 领投人列表页面
     */
    public function on_lead()
    {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $project_ids = core::R('project_id');//对应的project_id
        VI::assign('project_id', $project_ids);

        $project_info = $this->_assign_project();
        $project_id = $project_info['id'];

        $lead_list = $this->project_lead->select('project_id = \'' . $project_id . '\'', 0, $perpage, $page);
        $lead_count = $this->project_lead->select('project_id = \'' . $project_id . '\'', 0, -2);
        $page_html = misc::pages($lead_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/attent/project_id/' . $project_ids . '/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('lead_list', $lead_list);
        $this->show('admin/project_lead');
    }

    /*
     * 领投人新建编辑页面
     */
    public function on_lead_edit()
    {
        $id = core::R('id:int');
        $project_ids = core::R('project_id');//对应的project_id
        if ($id) {
            $lead_list = $this->project_lead->select('id = \'' . $id . '\'', 0, 0);
            VI::assign('lead_list', $lead_list);
        }
        VI::assign('id', $id);
        VI::assign('project_id', $project_ids);
        $this->show('admin/project_lead_edit');
    }

    /**
     * @url /api/project_lead/?lead[xx]=xxx
     */
    public function on_project_lead()
    {
        $leads[] = core::P('lead');
        $project_ids = $leads[0]['project_id'];
        $leads[0]['pic'] = core::P('pic');
        $leads[0]['project_id'] = url_id($project_ids, 'decode');
        // save leads
        if ($leads) {
            $project_id = (int)$leads[0]['project_id'];
            $project = $this->project->get($project_id);
            if (!$project_id) {
                $this->_message('project_not_exists');
            }
            foreach ($leads as $lead_data) {
                $img_data = $_FILES['pic'];
                unset($lead_data['pic']);
                $allow_fields = array('id', 'project_id', 'name', 'intro', 'pic', 'sortid', 'reason');
                $lead = array();
                foreach ($allow_fields as $field) {
                    $lead[$field] = $lead_data[$field];
                }
                // find lead
                if ($lead['id']) {
                    $lead['id'] = (int)$lead['id'];
                    $exists = $this->project_lead->get($lead['id']);
                    if ($exists['project_id'] != $project_id) {
                        continue;
                    }
                    // check pic twice
                    if ($exists['pic'] && is_file($this->conf['static_dir'] . $exists['pic'])) {
                        $lead['pic'] = $exists['pic'];
                    }
                } else {
                    $lead['id'] = $this->project_lead->insert($lead, 1);
                }
                // upload attachment
                if ($img_data[tmp_name]) {
                    $attachment = $this->attachment->upload_image($img_data,
                        $this->U->user_id, 'project_lead', $lead['id'],
                        '');
                    $lead['pic'] = $attachment['path'];
                }
                $lead['dateline'] = core::S('time');
                // update lead data and pic
                $this->project_lead->update($lead, $lead['id']);
            }
        }
        $this->show_message('操作成功', $this->conf['app_dir'] . ADMIN_DIR . '/project/lead/project_id/' . $project_ids);
//        $this->_message(1);
    }

    /**
     * 项目状态的更改及发送通知
     */
    public function on_update()
    {
        $project = core::R('project');
        if (isset($project['isverify'])) {
            $project['id'] = (int)$project['id'];
            $source = $this->project->get($project['id']);
            if (!$source) {
                $this->json('该项目不存在');
            }
            // get message
            $message = $project['verifymessage'];
            if ($message) {
                $event_param = array(
                    'message' => $message,
                    'project' => $source['title'],
                );
                // send event
                $this->event->add('project_message', $source['user_id'], $event_param,
                    $this->U->user_id);
            }
        }
        if ($project && $project['isverify'] == 3) {
            // 项目上线
            $project['expiretime'] = core::S('time') + $source['financingday'] * 86400;
        }
        //项目状态更改
        $this->project->update_all($project, $project['id']);
        $this->json('操作成功', 0);
    }

    /*
     * 订单管理
     */
    public function on_order_manage()
    {
        $perpage = 20;
        $page = max(core::R('page:int'), 1);
        $where_sql = ' ';
        /*// 搜索条件
        $search = core::R('search');
        $start = strtotime($search['start'] . ' 00:00:00');
        $end = strtotime($search['end'] . ' 23:59:59');
        if ($search['rank'] && $search['sort_order']) {
            $order_sql = " $search[rank] $search[sort_order]  , ";
        }
        if ($search['search'] && $search['search_value']) {
            $where_sql = ' AND ' . $search['search'] . ' like  \'%' . $search['search_value'] . '%\'';
        }
        if ($project_id) {
            $where_sql .= ' AND project_id = \'' . $project_id . '\'';
        }
        if ($search['start']) {
            $where_sql .= ' AND t1.dateline >= \'' . $start . '\'';
        }
        if ($search['end']) {
            $where_sql .= ' AND t1.dateline <= \'' . $end . '\'';
        }*/
        $invest_list = $this->user_invest->get_order_manage($where_sql, 0, $perpage, $page);
        $invest_count = $this->user_invest->get_order_manage($where_sql, 0, -2);
        $page_html = misc::pages($invest_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/project/order_manage/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('invest_list', $invest_list);
//        VI::assign('search', $search);
        $this->show('admin/project_order_manage');
    }

    /*
     * 更新项目发布方信息
     */
    public function on_project_publisher()
    {
        $id = core::P('id');
        $path = core::P('path');
        $img_path = core::P('paths');
        if ($path) {
            $path = $this->attachment->banner_path($path, 'publisher_logo');
            if ($img_path) {
                $file_dir = core::$conf['static_dir'];
                unlink($file_dir . $img_path);
            }
        } else {
            $path = $img_path;
        }
        $data = array(
            'publisher' => core::P('publisher'),
            'publishlogo' => $path,
        );
        $this->project_field->update($data, array('project_id' => $id));
        $this->json('操作成功', 0);
    }

    /*
     * 项目排序id
     */
    public function on_project_sortid()
    {
        $data = core::P('project');
        $this->project->update($data, $data['id']);
        $this->json('操作成功', 0);
    }

}

?>