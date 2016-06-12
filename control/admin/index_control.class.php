<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class index_control extends admin_common_control {

    public function on_index() {
        $this->show('admin/index');
    }

    public function on_welcome(){
        $time_start =date('Y-m-d 00:00:00');
        $time_end =date('Y-m-d 23:59:59');
        $data['user_register_num'] = $this->user_field->select('regtime BETWEEN UNIX_TIMESTAMP(\'' . $time_start . '\') AND UNIX_TIMESTAMP( \'' . $time_end . '\')',0,-2);//今日注册用户数
        $data['invest_audit_num'] = $this->user_field->select('isauth = \'1\'', 0, -2);//投资人待审核
        $data['project_audit_num'] = $this->project->select('isverify = \'1\'', 0, -2);//项目待审核
        $data['invest_order_num'] = $this->user_invest->select('dateline BETWEEN UNIX_TIMESTAMP(\'' . $time_start . '\') AND UNIX_TIMESTAMP( \'' . $time_end . '\')',0,-2);//今日投资订单数
        $data['invest_money'] = $this->user_invest->invest_money('dateline BETWEEN UNIX_TIMESTAMP(\'' . $time_start . '\') AND UNIX_TIMESTAMP( \'' . $time_end . '\')',0);//今日投资额
        VI::assign('data',$data);
        $this->show('admin/welcome');
    }

}

?>