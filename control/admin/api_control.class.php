<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class api_control extends admin_common_control {

    function __construct(&$conf) {
        if (!misc::form_submit()) {
            $this->_message('invalid_submit');
        }
        parent::__construct($conf);
    }

    private function _message($lang, $error = 1) {
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

    /**
     * 删除领投人
     * @url /api/remove_project_lead/?id=xxx
     */
    public function on_remove_project_lead() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        if ($this->U->group_id != 999) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        $lead = $this->project_lead->get($id);
        if ($lead) {
            unlink($this->conf['static_dir'] . $lead['pic']);
            $this->project_lead->delete($id);
            $this->_message(1);
        }else{
            $this->_message('lead_not_exists');
        }
    }
}

?>