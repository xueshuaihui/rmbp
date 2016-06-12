<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class order_control extends admin_common_control
{

    public function on_index()
    {
        $perpage = 50;
        $page = max(core::R('page:int'), 1);

        //搜索
        $search = core::R('search');
        $query_string = $this->http_query($search, 'search');
        if($search){
            $where = ' ';
            $order = 0;
            $user_ids = $this->user_id($search[search_value]);
            $data = array(
                'rank_field' => 't1.' . $search['rank'],
                'data_field' => 't1.dateline',
                'all_field' => array('t2.title', 't1.id', 't1.user_id'),
                'user_ids' => $user_ids
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
            if ($search['search'] && $search['search_value']) {
                if ($search['search'] == 'truename') {
                    $where_sql .= ' AND t1.user_id IN(\'' . implode("','", $user_ids) . '\') ';
                } else if ($search['search'] == 'title') {
                    $where_sql .= ' AND t2.' . $search['search'] . ' like  \'%' . $search['search_value'] . '%\'';
                } else {
                    $where_sql .= ' AND t1.' . $search['search'] . ' like  \'%' . $search['search_value'] . '%\'';
                }
            }
        }else{
            $where_sql = ' ';
            $order_sql = 0;
        }
        VI::assign('search', $search);

        $invest_list = $this->user_invest->get_order_manage($where_sql, $order_sql, $perpage, $page);
        $invest_count = $this->user_invest->get_order_manage($where_sql, 0, -2);
        $user_list = array();
        foreach ($invest_list as $invest) {
            if (!isset($user_list[$invest['invest_uid']])) {
                $user_list[$invest['invest_uid']] = $this->U->field('', $invest['invest_uid']);
            }
        }
        $page_html = misc::pages($invest_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/order/index/?' . $query_string . '&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('invest_list', $invest_list);
        VI::assign('user_list', $user_list);
        $this->show('admin/order_index');
    }

    public function on_update()
    {
        $invest = core::R('invest');
        $user_invest = $this->user_invest->get($invest['id']);
        if ($user_invest) {

            if ($invest['isrefund'] == 2) {
                $payment = $user_invest['payment'];
                $payment_class = 'pay_' . $payment;
                $payment_instance = new $payment_class();
                $payment_instance->set_conf($this->conf);
                $data = $user_invest['data'];
                $return = $payment_instance->refund($user_invest, $data);

                if (!$return['succ']) {
                    $this->show_message('退款失败：' . $return['message']);
                }

                if ($invest['refundmessage']) {
                    // get message
                    $message = $invest['refundmessage'];
                    if ($message) {
                        $event_param = array(
                            'message' => $message,
                            'code' => $return['code'],
                        );
                        // send event
                        $this->event->add('invest_message', $user_invest['user_id'], $event_param,
                            $this->U->user_id);
                    }
                }
            }

            $this->user_invest->update($invest, $invest['id']);


        }
        $this->show_message('操作成功！');
    }

    public function http_query($search, $field)
    {
        $result = array();
        foreach ($search as $key => $val) {
            $result[] = $field . '[' . $key . ']=' . $val;
        }
        return implode("&", $result);
    }

}

?>