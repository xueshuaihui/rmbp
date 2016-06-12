<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class trade_control extends admin_common_control
{

    public function on_index()
    {
        $perpage = 50;
        $page = max(core::R('page:int'), 1);
        //搜索
        $search = core::R('search');
        $query_string = $this->http_query($search, 'search');
        if($search){
            $where = ' 1 ';
            $order = 0;
            $user_ids = $this->user_id($search[search_value]);
            $data = array(
                'state_field' => 'istrade',
                'data_field' => 'expiretime',
                'all_field' => array('id', 'title', 'user_id'),
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
                    $where_sql .= ' AND ' . $search['search'] . ' like  \'%' . $search['search_value'] . '%\'';
                }
            }
        }else{
            $where_sql = ' 1 ';
            $order_sql = 0;
        }
        VI::assign('search', $search);

        $where_sql .= " AND isverify = '4' ";
        $project = $this->project->select($where_sql, $order_sql, $perpage, $page);
        $project_count = $this->project->select($where_sql, $order_sql, -2);

        $project_ids = array();
        foreach ($project as $key => $val) {
            $project_ids[] = $val['id'];
        }

        //投资人数
        $project_id_arr = implode(',', $project_ids);
        $temp_invest_num = "select count(0) AS C, project_id FROM plus_user_invest where project_id IN ('$project_id_arr')  group by project_id having(c)>0";
        $temp_invest_num = DB::fetch_all($temp_invest_num);
        $invest_num = array();
        foreach ($temp_invest_num as $val) {
            $invest_num[$val['project_id']] = $val['C'];
        }
        VI::assign('invest_num', $invest_num);

        //在转让笔数
        $temp_sell_num = "select count(0) AS C, project_id FROM plus_trade where project_id IN ('$project_id_arr')  group by project_id having(c)>0";
        $temp_sell_num = DB::fetch_all($temp_sell_num);
        $sell_num = array();
        foreach ($temp_sell_num as $val) {
            $sell_num[$val['project_id']] = $val['C'];
        }
        VI::assign('sell_num', $sell_num);

        $this->query_tables($project, array(
            'user_field' => array('user_id' => 'user_id'),
        ));

        $page_html = misc::pages($project_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/trade/index/?' . $query_string . '&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('project', $project);
        $this->show('admin/trade_index');
    }

    public function on_sell()
    {
        $status = core::R('status:int');
        $perpage = 50;
        $page = max(core::R('page:int'), 1);

        //搜索
        $search = core::R('search');
        $query_string = $this->http_query($search, 'search');
        if($search){
            $where = ' 1 ';
            $order = 'dateline DESC';
            $user_ids = $this->user_id($search[search_value]);
            $project_ids = $this->project_id($search[search_value]);
            $data = array(
                'state_field' => 'status',
                'data_field' => 'dateline',
                'user_ids' => $user_ids,
                'project_ids' => $project_ids
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
            //指定字段模糊搜索
            if ($search['search'] && $search['search_value']) {
                if ($search['search'] == 'title') {
                    $where_sql .= ' AND project_id IN(\'' . implode("','", $project_ids) . '\') ';
                } else if ($search['search'] == 'truename') {
                    $where_sql .= ' AND user_id IN(\'' . implode("','", $user_ids) . '\') ';
                } else {
                    $where_sql .= ' AND ' . $search['search'] . ' like  \'%' . $search['search_value'] . '%\'';
                }
            }
            if (!$search['search'] && $search['search_value']) {
                $where_sql .= ' AND ( `sellmessge` like  \'%' . $search['search_value'] . '%\'  OR `phone` like  \'%' . $search['search_value'] . '%\' OR project_id IN(\'' . implode("','", $project_ids) . '\') OR user_id IN(\'' . implode("','", $user_ids) . '\')) ';
            }
        }else{
            $where_sql = ' 1 ';
            $order_sql = 'dateline DESC';
        }
        VI::assign('search', $search);

        if (isset($_GET['status'])) {
            $where_sql .= ' AND status = \'' . (int)$status . '\'';
        }
        $trade = $this->trade->select($where_sql, $order_sql, $perpage, $page);
        $trade_count = $this->trade->select($where_sql, 0, -2);
        $page_html = misc::pages($trade_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/trade/sell/?' . $query_string . '&page=%d');
        VI::assign('page_html', $page_html);

        $this->query_tables($trade, array(
            'project' => 'project_id',
            'user_invest' => array('id' => 'user_invest_id'),
            'user_field' => array('user_id' => 'user_id'),
        ));

        VI::assign('status', $status);
        VI::assign('trade', $trade);
        $this->show('admin/trade_sell');
    }

    public function on_buy()
    {
        $status = core::R('status:int');
        $perpage = 50;
        $page = max(core::R('page:int'), 1);

        //搜索
        $search = core::R('search');
        $query_string = $this->http_query($search, 'search');
        if($search){
            $where = ' 1 ';
            $order = 0;
            $user_ids = $this->user_id($search[search_value]);
            $project_ids = $this->project_id($search[search_value]);
            $data = array(
                'rank_field' => 't1.' . $search[rank],
                'data_field' => 't1.dateline',
                'user_ids' => $user_ids,
                'project_ids' => $project_ids
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
            //指定字段模糊搜索
            if ($search['search'] && $search['search_value']) {
                if ($search['search'] == 'title') {
                    $where_sql .= ' AND t2.project_id IN(\'' . implode("','", $project_ids) . '\') ';
                } else if ($search['search'] == 'truename') {
                    $where_sql .= ' AND t1.user_id IN(\'' . implode("','", $user_ids) . '\') ';
                } else if ($search['search'] == 'truename2') {
                    $where_sql .= ' AND t2.user_id IN(\'' . implode("','", $user_ids) . '\') ';
                } else if ($search['search'] == 'id') {
                    $where_sql .= ' AND t1.id like  \'%' . $search['search_value'] . '%\'';
                } else {
                    $where_sql .= ' AND ' . $search['search'] . ' like  \'%' . $search['search_value'] . '%\'';
                }
            }
            if (!$search['search'] && $search['search_value']) {
                $where_sql .= ' AND ( t1.id like  \'%' . $search['search_value'] . '%\' OR t2.project_id IN(\'' . implode("','", $project_ids) . '\') OR t1.user_id IN(\'' . implode("','", $user_ids) . '\') OR t2.user_id IN(\'' . implode("','", $user_ids) . '\') ) ';
            }
        }else{
            $where_sql = ' 1 ';
            $order_sql = 0;
        }
        VI::assign('search', $search);

        if ($status) {
            $where_sql .= ' AND t1.status = \'' . $status . '\'';
        }
        if ($status == 0) {
            $where_sql .= ' AND t1.status = \'' . 0 . '\'';
        }
        $user_trade = $this->user_trade->get_buy_list($where_sql, $order_sql, $perpage, $page);
        $user_trade_count = $this->user_trade->get_buy_list($where_sql, 0, -2);
        $page_html = misc::pages($user_trade_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/trade/buy/?' . $query_string . '&page=%d');
        VI::assign('page_html', $page_html);
        $this->query_tables($user_trade, array(
            'project' => 'project_id',
            'user' => array('uid' => 'user_id2'),
            'user_field' => array('user_id' => 'user_id2'),
        ));

        $user_ids = array();
        foreach ($user_trade as $key => $val) {
            $user_ids[] = $val['user_id'];
        }
        //转让人姓名
        $temp_user_list = $this->user_field->select(' user_id IN(\'' . implode("','", $user_ids) . '\')');
        $user_list = array();
        foreach ($temp_user_list as $val) {
            $user_list[$val['user_id']] = $val['truename'];
        }
        VI::assign('user_list', $user_list);
        VI::assign('status', $status);
        VI::assign('user_trade', $user_trade);
        $this->show('admin/trade_buy');
    }

    public function http_query($search, $field)
    {
        $result = array();
        foreach ($search as $key => $val) {
            $result[] = $field . '[' . $key . ']=' . $val;
        }
        return implode("&", $result);
    }

    /*
     * 项目交易列表状态修改
     */
    public function on_update_trade()
    {
        $project = core::R('project');
        $this->project->update($project, $project['id']);
        $this->json('操作成功', 0);
    }

    /*
     * 转让状态修改
     */
    public function on_update_sell()
    {
        $trade = core::R('trade');
        $status = core::R('status');

        $exists_trade = $this->trade->get($trade['id']);
        switch ($trade['status']) {
            case 1:
                // 计算这期分红到期时间
                $project = $this->project->get($exists_trade['project_id']);
                $invest = $this->project_invest->get($exists_trade['project_invest_id']);
                if (!$project || !$invest) {
                    $this->json('操作的订单对应的项目或订单不存在，请重新提交');
                }
                //首次分红
                if (!$project['returntime']) {
                    $this->json('请先设置首次分红时间');
                }
                $dead_time = 0;
                switch ($invest['bonusrate']) {
                    case 1://月
                        $dead_time = strtotime('+1 month', $project['returntime']);
                        break;
                    case 2://季
                        $dead_time = strtotime('+3 month', $project['returntime']);
                        break;
                    case 3://年
                        $dead_time = strtotime('+1 year', $project['returntime']);
                        break;
                }
                $trade['deadline'] = $dead_time;

                break;
        }
        // update
        $this->trade->update($trade, $trade['id']);
        // send event
        $exists_trade = array_merge($exists_trade, $trade);
        $this->event->send('trade', $trade['user_id'], $exists_trade);
        $this->json('操作成功', 0);
    }

    /*
     * 购买状态修改
     */
    public function on_update_buy()
    {
        $user_trade = core::R('user_trade');
        $status = core::R('status');
        if ($user_trade['status'] == -1 || $user_trade['status'] == -2) {
            $user_trade['changedate'] = time();
        }

        $exist_trade = $this->user_trade->get($user_trade['id']);
        if ($exist_trade['status'] == 2) {
            $this->json('该订单已经成功交易，不能再更改状态');
        }

        // change and insert record
        switch ($user_trade['status']) {
            case 2:
                $target_trade = $this->trade->get($user_trade['trade_id']);
                $user_invest = $this->user_invest->get($target_trade['user_invest_id']);
                if (!$target_trade || !$user_invest) {
                    $this->json('找不到需要转让的订单，请检查是否参数错误');
                }
                // update mark for this invest order is sale
                $update = array('issell' => 2);
                $this->user_invest->update($update, $user_invest['id']);
                // insert new record for user who was bought this order
                $new_invest = array(
                    'user_id' => $exist_trade['user_id'],
                    'buy_invest_id' => $target_trade['user_invest_id'],
                    'paytime' => core::S('time'),
                    'payment' => 'trade',
                    'price' => $target_trade['sellprice'],
                    'issell' => 0,
                );
                $new_invest = array_merge($user_invest, $new_invest);
                unset($new_invest['id']);
                // insert
                $new_invest['id'] = $this->user_invest->insert($new_invest, 1);
                // send message for buy user
                $this->event->send('invest_change', $new_invest['user_id'], $new_invest);
                break;
            default:
                // send change event
                $this->event->send('user_trade', $exist_trade['user_id'], $user_trade);
                break;
        }

        $this->user_trade->update($user_trade, $user_trade['id']);
        $this->json('操作成功', 0);

    }

    /*
     * 添加第一次分红时间
     */
    public function on_update_return()
    {
        $Y = core::P('year');
        $M = core::P('month');
        $D = core::P('day');
        $data['id'] = core::P('id:int');

        $source = $this->project->get($data['id']);
        if (!$data['id'] || !$source) {
            $this->json('该项目不存在');
        }
        $data['returntime'] = strtotime($Y . '-' . $M . '-' . $D);
        $this->project->update($data, $data['id']);
        $this->json('操作成功', 0);
    }

}

?>