<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class message_control extends admin_common_control {

    public function on_index() {
        $page = max(core::R('page:int'), 1);
        $perpage = 20;

        //搜索
        $search = core::R('search');
        if($search){
            $where = ' 1 ';
            $order = ' dateline DESC ';
            $data = array(
                'search_value' => '1',
                'state_field' => 'isread',
                'data_field' => 'dateline',
                'all_field' => array('fuser_id', 'truename')
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
        }else{
            $where_sql = ' 1 ';
            $order_sql = ' dateline DESC ';
        }
        VI::assign('search', $search);

        // query by user
        $uid = core::R('uid:int');
        if ($uid) {
            $where_sql .= ' AND user_id=\'' . $uid . '\'';
        }
        $message_list = $this->user_message->get_list_and_user($where_sql, $order_sql, $perpage, $page);
        $message_count = $this->user_message->get_list_and_user($where_sql, $order_sql, -2, $page);
        $page_html = misc::pages($message_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/message/index/?' . $this->http_query($search, 'search') . '&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('message_list', $message_list);
        $this->show('admin/message_index');
    }

    public function http_query($search, $field) {
        $result = array();
        foreach ($search as $key => $val) {
            //$result[] = $field . '[' . $key . ']=' . urlencode($val);
            $result[] = $field . '[' . $key . ']=' . $val;
        }
        return implode("&", $result);
    }

    /*
     * 邮箱、短信列表
     */
    public function on_send_list() {
        $page = max(core::R('page:int'), 1);
        $perpage = 20;
        $sendtype = core::R('sendtype');//email、phone

        //搜索
        $search = core::R('search');
        if($search){
            $where = ' sendtype = \'' . $sendtype . '\' ';
            $order = ' dateline DESC ';
            $data = array(
                'search_value' => '1',
                'state_field' => 'issend',
                'data_field' => 'dateline',
                'all_field' => array('user_id', 'content')
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
        }else{
            $where_sql = ' sendtype = \'' . $sendtype . '\' ';
            $order_sql = ' dateline DESC ';
        }
        VI::assign('search', $search);

        $send_list = $this->send_queue->select($where_sql, $order_sql, $perpage, $page);
        $send_count = $this->send_queue->select($where_sql, $order_sql, -2, $page);
        $page_html = misc::pages($send_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/message/send_list/sendtype/' . $sendtype . '/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('send_list', $send_list);
        VI::assign('sendtype', $sendtype);
        $this->show('admin/message_send_list');
    }

    /*
     * 短信发送队列
     */
    public function on_send_queue() {
        $page = max(core::R('page:int'), 1);
        $perpage = 20;
        $sendtype = core::R('sendtype');

        //搜索
        $search = core::R('search');
        if($search){
            $where = ' sendtype = \'' . $sendtype . '\' ';
            $order = ' dateline DESC ';
            $data = array(
                'search_value' => '1',
                'state_field' => 'issend',
                'data_field' => 'dateline',
                'all_field' => array('user_id', 'content')
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
        }else{
            $where_sql = ' sendtype = \'' . $sendtype . '\' ';
            $order_sql = ' dateline DESC ';
        }
        VI::assign('search', $search);

        $send_list = $this->send_queue->select($where_sql, $order_sql, $perpage, $page);
        $send_count = $this->send_queue->select($where_sql, $order_sql, -2, $page);
        $page_html = misc::pages($send_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/message/send_queue/sendtype/' . $sendtype . '/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('send_list', $send_list);
        VI::assign('search', $search);
        VI::assign('sendtype', $sendtype);
        $this->show('admin/message_send_queue');
    }

    /*
     * 新建、编辑短信，新建、编辑邮件页面
     */
    public function on_send_edit() {
        $id = core::R('id:int');
        $sendtype = core::R('sendtype');
        if ($id) {
            $send_list = $this->send_queue->select(array('sendtype' => $sendtype, 'id' => $id), 0, 0);
            VI::assign('send_list', $send_list);
        }
        VI::assign('id', $id);
        VI::assign('sendtype', $sendtype);
        $this->show('admin/message_send_edit');
    }

    /*
     * 新建、编辑短信的动作
     */
    public function on_edit_action() {
        $id = core::R('id:int');
        $sendtype = core::R('sendtype');
        $uids = core::R('uids');
        $data = core::P('data');
        $data['sendtime'] = strtotime($data['sendtime']);
        $data['sendtype'] = $sendtype;
        $data['dateline'] = time();
        $data['user_id'] = $this->U->user_id;
        if ($uids) {
            $data['receive_uids'] = $uids;
        }
        if ($id) {
            $this->send_queue->update($data, $id);
        } else {
            $this->send_queue->insert($data);
        }
        $this->show_message('操作成功', $this->conf['app_dir'] . ADMIN_DIR . '/message/send_list/sendtype/' . $sendtype . '/');
    }

    /*
     * 删除短信、邮箱消息
     */
    public function on_list_delete() {
        $id = core::R('id:int');
        $source = $this->send_queue->get($id);
        if (!$source) {
            $this->json('该记录不存在', 0);
        }
        $this->send_queue->delete($id);
        $this->json('操作成功', 0);
    }
}

?>