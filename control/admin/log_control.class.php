<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class log_control extends admin_common_control {

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
                'data_field' => 'dateline',
                'all_field' => array('user_id')
            );
            $condition = $this->search($search, $where, $order, $data);
            $where_sql = $condition['where'];
            $order_sql = $condition['order'];
        }else{
            $where_sql = ' 1 ';
            $order_sql = ' dateline DESC ';
        }
        VI::assign('search', $search);

        $log_list = $this->logs->select($where_sql, $order_sql, $perpage, $page);
        $log_list_count = $this->logs->select($where_sql, $order_sql, -2);
        $page_html = misc::pages($log_list_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/log/index/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('log_list', $log_list);
        $this->show('admin/log_index');
    }

}

?>