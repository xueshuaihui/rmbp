<?php

/**
 * Created by PhpStorm.
 * Date: 2015/8/6
 * Time: 0:58
 */
class api_control extends base_common_control {

    /**
     * @var int regcode expire time
     */
    private $regcode_expire_time = 300;


    function __construct(&$conf) {
        if (!misc::form_submit()) {
            $this->_message('invalid_submit');
        }
        parent::__construct($conf);
    }

    /**
     * output for api
     *
     * @param     $lang
     * @param int $error
     */
    function _message($lang, $error = 1) {
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
                'login_fail' => '您输入的用户名或密码错误，请重试',
                'login_first' => '请先登录后再操作',
                'no_authority' => '您没有权限编辑该项目',
                'no_project_select' => '请选择正确的项目进行发布',
                'meet_exists' => '您已经发送过约见请求',
                'invest_meet_exists' => '您已经发送过投资意向',
                'meet_deined' => '您还没有认证投资人，无法发送约见请求',
                'password_must_different_than_old' => '新密码不能和旧密码一致',
                'old_password_invalid' => '旧密码输入错误。',
                'account_error' => '请输入正确的账号',
                'content_not_exists' => '内容不存在',
                'project_not_exists' => '项目不存在',
                'project_error' => '目标项目不匹配',
                'lead_not_exists' => '领投人不存，请重试',
                'invest_num_must_large_than_one' => '投资份数至少得选择一份及一份以上',
                'invest_not_exists' => '投资档位不存在',
                'invest_not_start' => '众筹暂未开始，请返回检查',
                'invest_expire' => '众筹还未开始，或者已经过期，请返回',
                'has_un_payment' => '您还有未支付的订单，请返回支付后再创建新订单。',
                'address_not_exists' => '选中的地址不存在',
                'address_auth_error' => '您提交收货的地址不存在',
                'create_invest_error' => '创建订单失败，请重试',
                'user_not_privacy' => '您没有权限处理该操作',
                'invest_canceled' => '投资已经取消，您可以继续对该项目投资。',
                'invest_ispaied' => '投资已经支付过。',
                'invest_isrefund' => '投资已经撤资，无法取消',
                'invest_cancel_succ' => '取消投资成功，您可以继续在该项目投资',
                'project_is_not_open' => '项目暂时没有开启众筹，请稍后再试',
                'invest_refund_succ' => '您的退款申请提交成功，我们会尽快受理，预计将在24小时内将投资款退还给您。',
                'refund_must_type_reason' => '申请退款请填写相应的退款原因。',
                'invest_num_out_of_range' => '您对该项目的投资额度已达上限，不要太贪心哦，还有更多好项目等着呢',
                'invest_order_hash_expire' => '交易已经过期，请返回项目重新创建订单',
                'password_fail' => '登录密码错误，请重新输入',
                'account_email_exists' => '邮箱地址已存在，请更换',
                'account_phone_exists' => '手机号已存在，请更换',
            );
            if ($lang == 'login_first') {
                $error = 999;
            }
            $lang = isset($languages[$lang]) ? $languages[$lang] : $lang;
        } else {
            $error = 0;
        }

        $data = array(
            'message' => $lang,
            'error' => $error,
        );
        if (!core::R('ajax')) {
            $this->show_message($data['message'], $error);
        }

        switch ($format) {
            case 'json':
            default:
                echo json_encode($data);
            break;
        }
        exit;
    }

    /**
     * @url api/regcode/?type=[email|phone]&source=xxxxxxxxxxx
     */
    public function on_regcode() {
        $type = core::R('type');
        //check format
        $source = $this->_check_format($type);

        //check user_code table exists
        $user_code = $this->user_code->select(array('codetype' => $type, 'codesource' => $source), 0, 0);
        $code = '';

        if ($_SERVER['time'] - $user_code['lastsend'] < 60) {
            $this->_message('request_too_short');
        } else {
            $user_code = array(
                'codetype' => $type,
                'codesource' => $source,
                'lastsend' => core::S('time'),
                'sendcount' => $user_code['sendcount'] + 1,
            );
            if ($_SERVER['time'] > $user_code['expire']) {
                $user_code['codevalue'] = $this->_generate_code();
                $user_code['expire'] = core::S('time') + $this->regcode_expire_time;
            }
            $code = $user_code['codevalue'];
            $this->user_code->replace($user_code);
        }

        // header('Code: ' . $code);
        // $this->_message($code, 0);
        // send mail or message
        $res = 0;
        switch ($type) {
            case 'email':
                $setting = array(
                    'to' => array($source),
                    'sub' => array(
                        '%code%' => array(
                            $code,
                        ),
                    ),
                    // register template
                    'template' => 'template_register',
                );
                $res = $this->sendcloud_api->send($setting);
            break;
            case 'phone':
                $message = $code . ',' . round($this->regcode_expire_time / 60);
                $setting = array(
                    'to' => $source,
                    'param' => $message,
                    'tpl' => 'forgot',
                );
                if ($res = $this->phone_api->send($setting)) {
                    // TODO revert code
                }
            break;
        }
        $this->_message('', $res ? 0 : 1);
    }

    /**
     * 账户安全发送验证码
     * @url api/regcode2/?type=[email|phone]&source=xxxxxxxxxxx
     */
    public function on_account_safecode() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $type = core::R('type');
        if($type == 'email'){
            $source = $this->_check_format($type);
        }else if($type == 'phone'){
            $phone = core::P('source');
            if(!$phone){
                $source = $this->U->phone;
            }else{
                $source = $this->_check_format($type);
            }
        }
        //check user_code table exists
        $user_code = $this->user_code->select(array('codetype' => $type, 'codesource' => $source), 0, 0);
        $code = '';

        if ($_SERVER['time'] - $user_code['lastsend'] < 60) {
            $this->_message('request_too_short');
        } else {
            $user_code = array(
                'codetype' => $type,
                'codesource' => $source,
                'lastsend' => core::S('time'),
                'sendcount' => $user_code['sendcount'] + 1,
            );
            if ($_SERVER['time'] > $user_code['expire']) {
                $user_code['codevalue'] = $this->_generate_code();
                $user_code['expire'] = core::S('time') + $this->regcode_expire_time;
            }
            $code = $user_code['codevalue'];
            $this->user_code->replace($user_code);
        }
        $res = 0;
        switch ($type) {
            case 'email':
                $setting = array(
                    'to' => array($source),
                    'sub' => array(
                        '%code%' => array(
                            $code,
                        ),
                    ),
                    // register template
                    'template' => 'template_register',
                );
                $res = $this->sendcloud_api->send($setting);
            break;
            case 'phone':
                $message = $code . ',' . round($this->regcode_expire_time / 60);
                $setting = array(
                    'to' => $source,
                    'param' => $message,
                    'tpl' => 'change_phone',
                );
                if ($res = $this->phone_api->send($setting)) {
                    // TODO revert code
                }
            break;
        }
        $this->_message('', $res ? 0 : 1);
    }

    /**
     * check reg source format
     *
     * @param $type
     * @return string
     */
    private function _check_format($type) {
        // check format
        $source = '';
        switch ($type) {
            case 'email':
                $source = core::P('source:email');
                if (!$source) {
                    $this->_message('email_error');
                }
                $exists = $this->U->get_by_email($source);
                if ($exists) {
                    $this->_message('email_exists');
                }
            break;
            case 'phone':
                $source = core::P('source:tel');
                if (!preg_match('/^1\d{10}$/is', $source)) {
                    $this->_message('phone_error');
                }
                $exists = $this->U->get_by_phone($source);
                if ($exists) {
                    $this->_message('phone_exists');
                }
            break;
            default:
                $this->_message('unknown_request');
            break;
        }
        return $source;
    }

    /**
     * generate rand code
     *
     * @param int $length
     * @param int $type
     * @return string
     */
    private function _generate_code($length = 6, $type = 1) {
        $arr = array(0 => "0123456789abcdefghijklmnopqrstuvwxyz", 1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
        if ($type == 0) {
            array_pop($arr);
            $string = implode("", $arr);
        } elseif ($type == "-1") {
            $string = implode("", $arr);
        } else {
            $string = $arr[$type];
        }
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }

    /*
     * @url /api/forgot/?password=x&password2=x&source=xxx&code=1234
     */

    /**
     * @url api/forgotcode/?source=xxx
     */
    public function on_forgotcode() {
        $user = $this->U;
        // get source
        $source = core::P('source:email');
        // is email
        if ($source) {
            $log_user = $user->get_by_email($source);
            $type = 'email';
        } else {
            $source = core::P('source:tel');
            if ($source) {
                $log_user = $user->get_by_phone($source);
                $type = 'phone';
            }
        }

        if (!$log_user) {
            $this->_message('user_not_exists');
        }

        //check user_code table exists
        $user_code = $this->user_code->select(array('codetype' => $type, 'codesource' => $source), 0, 0);
        $code = '';

        if ($_SERVER['time'] - $user_code['lastsend'] < 60) {
            $this->_message('request_too_short');
        } else {
            $user_code = array(
                'codetype' => $type,
                'codesource' => $source,
                'lastsend' => core::S('time'),
                'sendcount' => $user_code['sendcount'] + 1,
            );
            if ($_SERVER['time'] > $user_code['expire']) {
                $user_code['codevalue'] = $this->_generate_code();
                $user_code['expire'] = core::S('time') + $this->regcode_expire_time;
            }
            $code = $user_code['codevalue'];
            $this->user_code->replace($user_code);
        }

        //header('Code: ' . $code);
        // send mail or message
        $res = 0;
        switch ($type) {
            case 'email':
                $setting = array(
                    'to' => array($source),
                    'sub' => array(
                        '%code%' => array(
                            $code,
                        ),
                        '%user%' => array(
                            $log_user['username']
                        ),
                    ),
                    // forgot template
                    'template' => 'template_forgot',
                );
                $res = $this->sendcloud_api->send($setting);
            break;
            case 'phone':
                $message = $code . ',' . round($this->regcode_expire_time / 60);
                $setting = array(
                    'to' => $source,
                    'param' => $message,
                    'tpl' => 'forgot',
                );
                if ($res = $this->phone_api->send($setting)) {
                    // TODO revert code
                }
            break;
        }
        $this->_message('', $res ? 0 : 1);
    }

    /**
     * @url /api/checkcode/?source=xxx&code=xxx
     */
    public function on_checkcode() {
        // source
        $source = core::P('source:email');
        $type = 'email';
        if (!$source) {
            $source = core::P('source:tel');
            $type = 'phone';
        }
        if (!$source) {
            $this->_message('account_error');
        }
        $code = core::P('code');
        //check user_code table exists
        if ($this->_checkcode($type, $source, $code)) {
            $this->_message(1);
        } else {
            $this->_message('invalid_regcode');
        }
    }

    /**
     * check code invalid
     *
     * @param $type
     * @param $source
     * @param $code
     * @return bool
     */
    private function _checkcode($type, $source, $code, $remove = 0) {
        $code_where = array('codetype' => $type, 'codesource' => $source);
        $user_code = $this->user_code->select($code_where, 0, 0);
        if ($user_code['expire'] >= core::S('time') && strtolower($user_code['codevalue']) == strtolower($code)) {
            if ($remove) {
                $this->user_code->delete($code_where);
            }
            return true;
        } else {
            return false;
        }
    }

    public function on_forgot() {
        $password = core::P('password');
        $password2 = core::P('password2');
        // check same
        if ($password != $password2) {
            $this->_message('password_not_same');
        }

        $source = core::P('source:email');
        $type = 'email';
        if (!$source) {
            $source = core::P('source:tel');
            $type = 'phone';
        }
        if (!$source) {
            $this->_message('account_error');
        }
        $user = array();
        switch ($type) {
            case 'email':
                $user = $this->U->get_by_email($source);
            break;
            case 'phone':
                $user = $this->U->get_by_phone($source);
            break;
        }
        if (!$user) {
            $this->_message('account_error');
        }

        $code = core::P('code');
        //check user_code table exists
        if (!$this->_checkcode($type, $source, $code)) {
            $this->_message('invalid_regcode');
        }

        // get new password
        $pass = $this->U->get_pass($password, $user['salt']);
        // update password
        $updates = array('password' => $pass);
        $this->U->update($updates, $user['uid']);

        $this->_message(1);
    }

    /**
     * user register
     *
     * @url api/register/?type=[email|phone]&username=&source=xxx&regcode=xxx&password=&password2=
     */
    public function on_register() {

        $type = core::P('type');
        $regcode = core::P('regcode');
        $password = core::P('password');
        $password2 = core::P('password2');


        if ($message = $this->U->check_password($password)) {
            $this->_message($message);
        }
        // check password
        if ($password != $password2) {
            $this->_message('password_not_same');
        }

        // check format
        $source = $this->_check_format($type);


        // check email or phone exists
        $phone = $email = '';
        switch ($type) {
            case 'email':
                $email_user = $this->U->get_by_email($source);
                if ($email_user) {
                    $this->_message('email_exists');
                }
                $email = $source;
            break;
            case 'phone':
                $phone_user = $this->U->get_by_phone($source);
                if ($phone_user) {
                    $this->_message('phone_exists');
                }
                $phone = $source;
            break;
        }

        // check regcode
        if (!$this->_checkcode($type, $source, $regcode, 1)) {
            $this->_message('invalid_regcode');

        }

        $salt = $this->U->get_salt();
        $pass = $this->U->get_pass($password, $salt);
        $uid = $this->U->register($email, $phone, $pass, $salt);
        if ($uid) {
            // generate avatar
            $this->avatar->create($uid);

            // add user auth
            $auth = $this->U->get_auth($uid, $pass);
            core::C('auth', $auth, 86400);

            $this->_message(1);
        } else {
            $this->_message('system_error');
        }
    }

    /**
     * @url api/username/?username=xxxx
     */
    public function on_username() {
        $username = core::R('username');
        $message = $this->U->check_username($username);
        if ($message) {
            $this->_message($message);
        }
        $this->_message(1);
    }

    /**
     * @url api/username/?username=xxxx
     */
    public function on_username2() {
        $username = core::R('username');
        $message = $this->U->get_by_username($username);
        if ($message) {
            $this->_message('该用户名已被注册', 1);
        } else {
            $this->_message(1);
        }
    }

    /**
     * @url api/phone/?phone=xxxx
     */
    public function on_phone() {
        $phone = core::R('phone');
        $message = $this->U->get_by_phone($phone);
        if ($message) {
            $this->_message('该手机号已被注册', 1);
        } else {
            $this->_message(1);
        }
    }

    /**
     * @url api/email/?email=xxxx
     */
    public function on_email() {
        $email = core::R('email');
        $message = $this->U->get_by_email($email);
        if ($message) {
            $this->_message('该邮箱已被注册', 1);
        } else {
            $this->_message(1);
        }
    }


    /**
     * @url /api/login/?source=xxxx&password=xxx
     */
    public function on_login() {
        $user = $this->U;
        $password = core::P('password');
        $source = core::P('source:email');
        $log_user = 0;
        if ($message = $user->check_password($password)) {
            $this->_message($message);
        }

        // is email
        if ($source) {
            $log_user = $user->get_by_email($source);
        } else {
            $source = core::P('source:tel');
            // mobile phone length is 11
            if (strlen($source) == 11) {
                $log_user = $user->get_by_phone($source);
            }
        }

        if (!$log_user) {
            $this->_message('login_not_exists');
        }

        $pass = $user->get_pass($password, $log_user['salt']);
        // check password
        if ($pass && $log_user['password'] == $pass) {
            // get auth cookie
            $auth = $user->get_auth($log_user['uid'], $pass);
            core::C('auth', $auth, 86400);

            // update login
            $data = array();
            $data['logip'] = core::ip();
            $data['logtime'] = core::S('time');
            $data['logcount'] = 'logcount+1';
            $user->update($data, $log_user['uid']);
            $this->_message(1);
        } else {
            $this->_message('login_fail');
        }
    }

    /*
     *  @url /api/region/?$pid=xxxx
     */
    public function on_region() {
        $pid = core::R('pid');
        $where_array = is_numeric($pid) ? array('pid' => $pid) : array();
        $where_array = $pid == 'hot' ? array('sortid' => 999) : array();
        $region_list = $this->region->select($where_array, 0);
        $this->_message(json_encode($region_list), 0);
    }

    /*
     *  @url /api/area/
     */
    public function on_area() {
        $area_list = $this->area->select(array(), 0);
        $this->_message(json_encode($area_list), 0);
    }

    /**
     * @url /api/remove_invest/?id=xxx
     */
    public function on_remove_invest() {
        $id = core::R('id');
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $where_array = array('id' => $id, 'user_id' => $this->U->user_id);
        if ($this->U->group_id == 999) {
            unset($where_array['user_id']);
        }
        $this->project_invest->delete($where_array);
        $this->_message(1);
    }

    /**
     * @url /api/remove_guy/?id=xxx
     */
    public function on_remove_guy() {
        $id = core::R('id:int');
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $where_array = array('id' => $id, 'user_id' => $this->U->user_id);
        if ($this->U->group_id == 999) {
            unset($where_array['user_id']);
        }
        $this->project_guy->delete($where_array);
        $this->_message(1);
    }

    public function on_remove_shop(){
        $id = core::R('id:int');
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $where_array = array('id' => $id, 'user_id' => $this->U->user_id);
        if ($this->U->group_id == 999) {
            unset($where_array['user_id']);
        }
        $this->project_shop->delete($where_array);
        $this->_message(1);
    }

    /**
     * @url /api/remove_attachment/?id=
     */
    public function on_remove_attachment() {
        $id = core::R('id:int');
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $where_array = array('id' => $id, 'user_id' => $this->U->user_id);
        if ($this->U->group_id == 999) {
            unset($where_array['user_id']);
        }
        $attachment = $this->attachment->get($id);
        if ($attachment) {
            $this->attachment->delete($where_array);
            //remove file
            unlink(ROOT_PATH . $attachment['path']);
        }
        $this->_message(1);

    }

    /**
     * @url /api/project_timeline/?project_id=x&title=x&message=x&time=x[&id=x]
     */
    public function on_project_timeline() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        // update field
        $update_fields = array(
            'project_id' => core::R('project_id:int'),
            'title' => core::R('title'),
            'message' => core::R('message'),
            'dateline' => core::R('time'),
        );
        // time field
        if ($update_fields['dateline']) {
            $update_fields['dateline'] = strtotime($update_fields['time']);
        }

        // add htmlepcialchar filter
        $update_fields = core::htmlspecialchars($update_fields);

        // update or insert
        if ($id) {
            // select from db
            $where_array = array('id' => $id, 'user_id' => $this->U->user_id);
            if ($this->U->group_id == 999) {
                unset($where_array['user_id']);
            }
            $timeline = $this->project_timeline->select($where_array);
            if (!$timeline) {
                $this->_message('no_authority');
            }
            // unset update project_id
            unset($update_fields['project_id']);
            // update
            $this->project_timeline->update($update_fields, $id);

        } else {
            if (!$update_fields['project_id']) {
                $this->_message('no_project_select');
            }
            $update_fields['dateline'] = core::S('time');
            // check project authority
            if ($this->U->group_id < 999) {
                $project = $this->project->get($update_fields['project_id']);
                if ($project['user_id'] != $this->U->user_id) {
                    $this->_message('no_authority');
                }
            }
            // add user id
            $update_fields['user_id'] = $this->U->user_id;

            // insert time line
            $id = $this->project_timeline->insert($update_fields, 1);

        }
        $this->_message($id, 0);
    }

    /**
     * @url /api/remove_project_timeline/?id=x
     */
    public function on_remove_project_timeline() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        $where_array = array('id' => $id, 'user_id' => $this->U->user_id);
        if ($this->U->group_id == 999) {
            unset($where_array['user_id']);
        }
        $timeline = $this->project_timeline->select($where_array);
        if (!$timeline) {
            $this->_message('no_authority');
        }
        $this->project_timeline->delete($id);
        $this->_message(1);
    }


    /*
     * @url /api/user_feed/?id=x&type=[project|area]
     */
    public function on_user_feed() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        $type = core::R('type');
        $unfeed = core::R('unfeed:int');
        $where_array = array('target_id' => $id, 'user_id' => $this->U->user_id, 'feedtype' => $type);
        $feed = $this->user_feed->select($where_array);
        $update_num = 0;
        if ($unfeed && $feed) {
            // remove relation
            $this->user_feed->delete($where_array);
            $update_num = -1;
        } else if (!$unfeed && !$feed) {
            // insert relation
            $where_array['dateline'] = core::S('time');
            $this->user_feed->insert($where_array, 1);
            $update_num = 1;
        }
        // update feed num
        if ($update_num) {
            switch ($type) {
                case 'project':
                    // update user field
                    $feedprojectnum = $this->U->field('feedprojectnum');
                    $feedprojectnum = max($feedprojectnum + $update_num, 0);
                    $this->U->update(array('feedprojectnum' => $feedprojectnum));
                    // update project
                    $feednum = 'feednum' . ($update_num >= 0 ? '+' : '-') . abs($update_num);
                    $this->project->update(array('feednum' => $feednum), $id);
                break;
            }
        }
        $this->_message(1);
    }

    /*
     * @url /api/user_feed2/?id=x&type=[user]
     */
    public function on_user_feed2() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');//要关注用户的user_id
        $type = core::R('type');//user
        $unfeed = core::R('unfeed:int');
        $where_array = array('target_id' => $id, 'user_id' => $this->U->user_id, 'feedtype' => $type);
        $feed = $this->user_feed->select($where_array);
        $update_num = 0;
        if ($unfeed && $feed) {
            // remove relation
            $this->user_feed->delete($where_array);
            $update_num = -1;
        } else if (!$unfeed && !$feed) {
            // insert relation
            $where_array['dateline'] = core::S('time');
            $this->user_feed->insert($where_array, 1);
            $update_num = 1;
        }
        // update feed num
        if ($update_num) {
            switch ($type) {
                case 'user':
                    //update user field
                    $feednum = $this->U->field('feednum');
                    $feednum = max($feednum + $update_num,0);
                    $this->U->update(array('feednum' => $feednum));
                    //update user
                    $fannum = 'fannum' . ($update_num >= 0 ? '+' : '-') . abs($update_num);
                    $this->U->update(array('fannum' => $fannum),$id);
                break;
            }
        }
        $this->_message(1);
    }


    /**
     * @url /api/project_feed/?id=xxx
     */
    public function on_project_feed() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        $where_array = array('target_id' => $id, 'user_id' => $this->U->user_id, 'feedtype' => 'project');
        $feed = $this->user_feed->select($where_array);
        $this->_message('', $feed ? 0 : 1);
    }

    /**
     * @url /api/feed_user/?id=xxx
     */
    public function on_feed_user() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        $where_array = array('target_id' => $id, 'user_id' => $this->U->user_id, 'feedtype' => 'user');
        $feed = $this->user_feed->select($where_array);
        $this->_message('', $feed ? 0 : 1);
    }

    /*
     * @url /api/user_feed_region/?id=1,2,3
     */
    public function on_user_feed_region() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $ids = explode(',', core::R('id'));
        // remove user all feed region
        $where_sql = array('user_id' => $this->U->user_id, 'feedtype' => 'region');
        $this->user_feed->delete($where_sql);

        if ($ids) {
            // insert feed relation
            foreach ($ids as $id) {
                if (is_numeric($id)) {
                    $data = $where_sql;
                    $data['target_id'] = $id;
                    $this->user_feed->insert($data);
                }
            }
        }

        $this->_message(1);
    }

    /**
     * @url /api/project_meet/?project_id=x&message=x
     */
    public function on_project_meet() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        if ($this->U->field('isauth') != 2) {
            $this->_message('meet_deined');
        }

        $meet_where = array(
            'project_id' => core::R('project_id:int'),
            'user_id' => $this->U->user_id,
        );

        $meet = $this->project_meet->select($meet_where);
        if ($meet) {
            $this->_message('meet_exists');
        }
        $meet_where['dateline'] = core::S('time');
        $meet_where['message'] = core::P('message');
        // add htmlepcialchar filter
        $meet_where = core::htmlspecialchars($meet_where);
        $id = $this->project_meet->insert($meet_where, 1);
        // update meetnum
        if ($id) {
            // user meetnum
            $meetnum = $this->U->field('meetnum');
            $this->U->update(array('meetnum' => $meetnum + 1));
            // project meetnum
            $project = array('meetnum' => 'meetnum+1');
            $this->project->update($project, $meet_where['project_id']);
        }
        $this->_message('', $id ? 0 : 1);
    }

    /**
     * @url /api/project_meet_exists/?project_id=x&message=x
     */
    public function on_project_meet_exists() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $meet_where = array(
            'project_id' => core::R('project_id:int'),
            'user_id' => $this->U->user_id,
        );

        $meet = $this->project_meet->select($meet_where);
        $this->_message('', $meet ? 1 : 0);
    }


    /**
     * save certificate
     *
     * @url /api/certificate/?user[truename]=xxx....
     */
    public function on_certificate() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $user_field = core::P('user');
        $user_id = $this->U->group_id == 999 ? core::R('uid:int') : $this->U->user_id;
        $user_id = $user_id ? $user_id : $this->U->user_id;
        $user = $this->U->field();

        // email check
        if (!$user['email']) {
            $user_field['email'] = core::get_gpc_value('email', $user_field['email']);
            if (!$user_field['email']) {
                $this->_message('email_error');
            }
            // query email is exists
            $exists = $this->U->get_by_email($user_field['email']);
            if ($exists) {
                $this->_message('email_exists');
            }
        } else {
            // unset email
            unset($user_field['email']);
        }

        //phone check
        if (!$user['phone']) {
            $user_field['phone'] = core::get_gpc_value('tel', $user_field['phone']);
            if (!$user_field['phone']) {
                $this->_message('phone_error' . $user_field['phone']);
            }
            // query phone is exists
            $exists = $this->U->get_by_phone($user_field['phone']);
            if ($exists) {
                $this->_message('phone_exists');
            }

            // check phone regcode
            $code_where = array('codetype' => 'phone', 'codesource' => $user_field['phone']);
            $user_code = $this->user_code->select($code_where, 0, 0);

            // check regcode is expire
            if ($_SERVER['time'] - $user_code['expire'] > $this->regcode_expire_time) {
                // delete regcode
                $this->user_code->delete($code_where);
                $this->_message('expire_regcode');
            }

            // check regcode is invalid
            $regcode = $user_field['regcode'];
            if (strtoupper($user_code['codevalue']) != strtoupper($regcode)) {
                $this->_message('invalid_regcode');
            }
            unset($user_field['regcode']);
        } else {
            // unset phone
            unset($user_field['phone']);
        }

        // check auth status
        if ($this->U->group_id == 999) {
            if (isset($_POST['isauth'])) {
                $user_field['isauth'] = core::P('isauth:int');
                // get message
                $message = core::P('message');
                if ($message) {
                    $event_param = array(
                        'message' => $message,
                        'user_id' => $user_id,
                        'truename' => $user['truename'],
                    );
                    // add authmessage
                    $user_field['authmessage'] = $message;
                    // send event
                    $this->event->add('auth_message', $user_id, $event_param, $user_id);
                }
            }
        } else {
            // wait auth or old auth status
            $user_field['isauth'] = $user['isauth'] ? $user['isauth'] : 1;
        }
        // record auth time
        if ($user_field['isauth'] == 1) {
            $user_field['authtime'] = core::S('time');
        }

        // save avatar
        $avatar = $user_field['avatar'];
        unset($user_field['avatar']);
        if ($avatar) {
            $avatar_file = avatar($user_id, '', 0);
            $avatar_dir = dirname($avatar_file);
            $static_dir = $this->conf['static_dir'];
            !is_dir($static_dir . $avatar_dir) && mkdir($static_dir . $avatar_dir, 0777, 1);
            $attachment = $this->attachment->upload_base64_image($avatar,
                $user_id, 'user', $user_id,
                'avatar', $avatar_file, '', 1);
        }

        // unsafe field
        $unsafe_field = array('emailcheck', 'phonecheck', 'user_id',
            'group_id', 'regtime', 'regip',
            'logtime', 'logip', 'logcount');
        foreach ($unsafe_field as $field) {
            unset($user_field[$field]);
        }


        // save user_field
        if ($user_field) {
            $user_field = core::htmlspecialchars($user_field);
            $this->U->update($user_field, $user_id);
        }

        // update success
        $this->_message('', 0);

    }


    /*
     * @url /api/password/?password=x&password2=x&password_new=x
     */
    public function on_password() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $password_old = core::P('password');
        $password_new = core::P('password_new');
        $password2 = core::P('password2');

        $user_id = $this->U->user_id;

        $user = $this->U->field();

        // check old password invalid
        $old_pass = $this->U->get_pass($password_old, $user['salt']);
        if ($old_pass != $user['password']) {
            $this->_message('old_password_invalid');
        }

        // check same
        if ($password_new != $password2) {
            $this->_message('password_not_same');
        }
        // check diff
        if ($password_old == $password_new) {
            $this->_message('password_must_different_than_old');
        }


        // get new password
        $pass = $this->U->get_pass($password_new, $user['salt']);
        // update password
        $updates = array('password' => $pass);
        $this->U->update($updates, $user_id);

        core::C('auth', '', -1);

        $this->_message(1);
    }

    /**
     * @url /api/message_read/?id=1,2,3,4
     */
    public function on_message_read() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $ids = explode(',', core::R('id'));
        foreach ($ids as &$id) {
            if (!is_numeric($id)) {
                unset($id);
            }
        }
        // get unread message by ids
        $messages = $this->user_message->get_by_ids($ids, $this->U->uid, 0);
        $message_count = count($messages);
        if ($message_count) {
            // update message num
            $messagenum = $this->U->field('messagenum');
            $messagenum -= $message_count;
            $messagenum = max($messagenum, 0);
            $this->U->update(array('messagenum' => $messagenum));

            // get message ids
            $message_ids = array();
            foreach ($messages as $message) {
                $message_ids[] = $message['id'];
            }
            // readed
            $this->user_message->readed($message_ids);

        }
        $this->_message(1);
    }

    /**
     * @url /api/ask/?comment[0][message]
     */
    public function on_qa() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $comment = core::P('comment');
        $project_id = core::R('project_id:int');
        $comment1 = $comment[0];
        $comment2 = $comment[1];
        $project = $this->project->get($project_id);
        if (!$project_id || !$project) {
            $this->_message('project_not_exists');
        }

        // check reply
        if ($this->U->group_id != 999 && $project['user_id'] != $this->U->user_id) {
            unset($comment2);
        }

        $isreply = $comment2['message'] ? 1 : 0;

        // filter html
        $comment1['message'] = core::htmlspecialchars($comment1['message']);
        $comment1['project_id'] = $project_id;
        if ($comment1['id']) {
            $exists_comment1 = $this->comment->get((int)$comment1['id']);
            if (!$exists_comment1) {
                $this->_message('content_not_exists');
            }
            if ($exists_comment1['project_id'] != $project['id']) {
                $this->_message('project_error');
            }
            $comment1['user_id'] = $exists_comment1['user_id'];
            $comment1['isreply'] = $exists_comment1['isreply'];
            $comment1['level'] = $exists_comment1['level'];
            $comment1['dateline'] = $exists_comment1['dateline'];
            $this->comment->update($comment1, $comment1['id']);
        } else {
            $comment1['user_id'] = $this->U->user_id;
            $comment1['isreply'] = $isreply;
            $comment1['level'] = 0;
            $comment1['dateline'] = core::S('time');
            $comment1['id'] = $this->comment->insert($comment1, 1);
        }

        if ($comment2 && $comment1['id']) {
            $comment2['pid'] = $comment1['id'];
            $comment2['message'] = core::htmlspecialchars($comment2['message']);
            $comment2['project_id'] = $project_id;
            if ($comment2['id']) {
                $exists_comment2 = $this->comment->get((int)$comment2['id']);
                if (!$exists_comment2) {
                    $this->_message('content_not_exists');
                }
                if ($exists_comment2['project_id'] != $project['id']) {
                    $this->_message('project_error');
                }
                $comment2['user_id'] = $exists_comment2['user_id'];
                $comment2['isreply'] = $exists_comment2['isreply'];
                $comment2['level'] = $exists_comment2['level'];
                $comment2['dateline'] = $exists_comment2['dateline'];

                $this->comment->update($comment2, $comment2['id']);
            } else {
                $comment2['user_id'] = $this->U->user_id;
                $comment2['isreply'] = 0;
                $comment2['level'] = 1;
                $comment2['dateline'] = core::S('time');
                $comment2['id'] = $this->comment->insert($comment2, 1);
            }
        }

        $this->_message(1);
    }


    /**
     * @url /api/remove_qa/?id=xxx
     */
    public function on_remove_qa() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        if ($this->U->group_id != 999) {
            $this->_message('login_first');
        }
        //$comment = $this->comment->get($id);
        $this->comment->delete_by_comment($id);
        $this->_message(1);
    }


    /**
     * @url /api/user_address/?id=xxx&address[name]=&address[phone]=&
     */
    public function on_user_address() {
        $is_auth = $this->U->init($this->conf, false);

        if (!$is_auth) {
            $this->_message('login_first');
        }
        $id = core::R('id:int');
        $address = core::R('address');
        core::htmlspecialchars($address);
        if ($id) {
            $exists_address = $this->user_address->get($id);
            if ($exists_address['user_id'] != $this->U->user_id) {
                $this->_message('no_authority');
            }
            $address['dateline'] = $exists_address['dateline'];
            $address['user_id'] = $exists_address['user_id'];
            $this->user_address->update($address, $id);
        } else {
            $address['dateline'] = core::S('time');
            $address['user_id'] = $this->U->user_id;
            $this->user_address->insert($address);
        }
        $this->_message(1);
    }

    /**
     * @url /api/remove_user_address/?id=xxx
     */
    public function on_remove_user_address() {

        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $id = core::R('id:int');
        $exists_address = $this->user_address->get($id);
        if ($exists_address['user_id'] != $this->U->user_id) {
            $this->_message('no_authority');
        }
        $this->user_address->delete($id);
        $this->_message(1);
    }

    /**
     * @url /api/query_user_address/?id=id1,id2,id3
     */
    public function on_query_user_address() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $ids = explode(",", core::R('id'));
        $province_id = (int)$ids[0];
        $city_id = (int)$ids[1];
        $county_id = (int)$ids[2];
//        if (!$province_id || !$city_id) {
//            $this->_message('');
//        }

        $region_list = array(
            0 => array(),
            1 => array(),
            2 => array(),
        );

        // add top region
        $ids[] = 0;
        foreach ($this->region->get_childrens($ids) as $region) {
            unset($region['sortid'], $region['code']);
            if ($region['pid'] == 0) {
                $region['select'] = $region['id'] == $province_id ? 1 : 0;
                $region_list[0][] = $region;
            } else if ($region['pid'] == $province_id) {
                $region['select'] = $region['id'] == $city_id ? 1 : 0;
                $region_list[1][] = $region;
            } else if ($region['pid'] == $city_id) {
                $region['select'] = $region['id'] == $county_id ? 1 : 0;
                $region_list[2][] = $region;
            }
        }

        $this->_message(json_encode($region_list), 0);
    }

    /**
     * @url /api/more_message/?
     */
    public function on_more_message() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $page = max(core::R('page:int'), 1);
        $perpage = 6;

        $count = $this->user_message->get_list($this->U->user_id, -1, -2);
        $list = array();
        if ($count) {
            $list = $this->user_message->get_list($this->U->user_id, -1, $perpage, $page);
        }
        $this->_message(json_encode($list), 0);
    }

    /**
     * @url /api/order/?
     */
    public function on_order() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        if ($this->U->field('isauth') != 2) {
            $this->_message('user_not_privacy');
        }

        $invest_source = core::P('invest');
        $invest = array();
        foreach (array('project_id', 'invest_id', 'address_id', 'num',) as $val) {
            $invest[$val] = (int)$invest_source[$val];
        }
        if (!$invest['num']) {
            $this->_message('invest_num_must_large_than_one');
        }

        $user_id = $this->U->user_id;

        $invest['dateline'] = core::S('time');
        $invest['status'] = 0;
        $invest['data'] = '';
        $invest['payment'] = 'yeepay';
        $invest['user_id'] = $user_id;


        $project_id = &$invest['project_id'];

        if (!$project_id) {
            $this->_message('project_not_exists');
        }

        $project = $this->project->get($project_id);
        if (!$project) {
            $this->_message('project_not_exists');
        }
        // 未开始众筹
        if ($project['isverify'] != 3) {
            $this->_message('invest_not_start');
        }
        // 没到时间或者过期
        if ($project['expiretime'] <= core::S('time')) {
            $this->_message('invest_expire');
        }
        // 超募
        /*
        if ($project['superraise']) {
            $this->_message();
        }
        */

        // check unpay
        $exists_invest = $this->user_invest->get_unpay_by_project($user_id, $project_id);

        if ($exists_invest) {
            $this->_message('has_un_payment', $this->conf['app_dir'] . 'user/invested/');
        }

        // check invest
        $invest_id = &$invest['invest_id'];
        if (!$invest_id) {
            $this->_message('invest_not_exists');
        }

        //
        $project_invest = $this->project_invest->get($invest_id);
        if (!$project_invest || $project_invest['project_id'] != $project_id) {
            $this->_message('invest_not_exists');
        }

        // check count of invest
        $exists_count = $this->user_invest->get_paied_count_by_project($user_id, $project_id);

        // out of count
        if ($project_invest['maxnum'] && $project_invest['maxnum'] < $exists_count + $invest['num']) {
            $this->_message('invest_num_out_of_range', -1);
        }

        // 份额众筹完毕
        if ($project_invest['leftnum'] < 1 || $project_invest['leftnum'] < $invest['num']) {
            $this->_message('left_over');
        }


        $address_id = &$invest['address_id'];
        if ($address_id) {
            $address = $this->user_address->get($address_id);;
            if (!$address) {
                $this->_message('address_not_exists');
            }
            // address is not behind current user
            if ($address['user_id'] != $user_id) {
                $this->_message('address_auth_error');
            }
        }

        $invest['price'] = $invest['num'] * $project_invest['price'];

        // get invest id
        $invest_id = $this->user_invest->insert($invest, 1);

        // system error
        if (!$invest_id) {
            $this->_message('create_invest_error');
        }

        // update invest leftnum
        $num = $project_invest['leftnum'] - $invest['num'];
        $this->project_invest->update(array('leftnum' => $num), $project_invest['id']);

        $payment = 'yeepay';
        $pay_post = array(
            'id' => $invest_id,
            'price' => $invest['price'] * $invest['num'],
            'title' => $project['title'],
            'type' => 'invest',
            'desc' => $this->U->field('truename') . ' 支付订单(' . $invest_id . '),' . $project_invest['price'] . '*' . $invest['num'] . '=' . $invest['price'] . ' RMB',
            'user_id' => $this->U->user_id,

            'callback' => (IS_MOBILE ? $this->conf['mobile_url'] : $this->conf['app_url']) . 'api/payback/payment/' . $payment . '/FORM_HASH/' . misc::form_hash() . '/',
        );
        $payment_class = 'pay_' . $payment;
        $payment_instance = $this->$payment_class;
        $payment_instance->set_conf($this->conf);
        $payment_data = $payment_instance->get_form_array($pay_post);

        VI::assign('payment', $payment_data);

        $this->show('pay_form');
    }

    /**
     * @url /api/order_continue/
     */
    public function on_order_continue() {

        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $invest_id = core::R('id:int');
        $invest = $this->user_invest->get($invest_id);
        if ($invest['user_id'] != $this->U->user_id) {
            $this->_message('user_not_privacy');
        }
        $project_invest = $this->project_invest->get($invest['invest_id']);
        $project = $this->project->get($invest['project_id']);
        //
        if ($project['isverify'] != 3 && $project['expiretime'] <= core::S('time')) {
            $this->_message('project_is_not_open');
        }
        if (core::S('time') - $invest['dateline'] > 86400) {
            // cancel order
            $update = array();
            $update['iscancel'] = 1;
            $this->user_invest->update($update, $invest_id);
            // reset project_invest
            $update_invest = array(
                'leftnum' => 'leftnum+' . $invest['num'],
            );
            $this->project_invest->update($update_invest, $invest['invest_id']);

            $this->_message('invest_order_hash_expire');
        }
        //
        $payment = $invest['payment'];
        $pay_post = array(
            'id' => $invest_id,
            'price' => $invest['price'],
            'title' => $project['title'],
            'type' => 'invest',
            'desc' => $this->U->field('truename') . ' 支付订单(' . $invest_id . '),'
                . ($invest['num'] > 1 ? $project_invest['price'] . '*' . $invest['num'] . '=' : '') . $invest['price'] . ' RMB',
            'user_id' => $this->U->user_id,
            'callback' => (IS_MOBILE ? $this->conf['mobile_url'] . 'm' : $this->conf['app_url']) . 'api/payback/payment/' . $payment . '/FORM_HASH/' . misc::form_hash() . '/',
        );
        $payment_class = 'pay_' . $payment;
        $payment_instance = $this->$payment_class;
        $payment_instance->set_conf($this->conf);
        $payment_data = $payment_instance->get_form_array($pay_post);

        VI::assign('payment', $payment_data);

        $this->show('pay_form');

    }

    /**
     * @url /api/payback/?
     */
    function on_payback() {
        $payment = core::R('payment');
        $payment_class = 'pay_' . $payment;
        $payment_instance = $this->$payment_class;
        $payment_instance->set_conf($this->conf);
        $result = $payment_instance->response($this);
        $return_url = (IS_MOBILE ? $this->conf['mobile_url'] : $this->conf['app_url']) . 'user/invested/';
        if ($result['data'] && $result['id'] && $result['succ']) {
            $invest = $this->user_invest->get($result['id']);
            // check price equal db price
            if (!D_BUG && $result['price'] != $invest['price']) {
                $this->show_message('支付失败，您实际支付（' . $result['price'] . '）和待支付（' . $invest['price'] . '）的费用不一致，请检查', (IS_MOBILE ? $this->conf['mobile_url'] : $this->conf['app_url']) . 'user/invested/');
            }
            // update ispaied flag
            if (!$invest['ispaied'] || !$invest['data']) {
                $update = array(
                    'data' => $result['data'],
                    'ispaied' => 1,
                    'paytime' => core::S('time'),
                );
                $this->user_invest->update($update, $result['id']);

                // update project
                $project = $this->project->get($invest['project_id']);
                $update = array();
                $update['totalfinancing'] += $invest['price'];
                if ($project['totalfinancing'] >= $project['minfinancing']) {
                    $update['superraise'] = 1;
                }
                $this->project->update_all($update, $invest['project_id']);
            }

            // response
            if ($result['response']) {
                echo $result['response'];
                exit;
            } else {
                $this->show_message('支付成功，返回投资页面', $return_url);
            }
        } else {
            echo('PayError:' . $result['message'] . ' | <a href="' . $return_url . '">GoBack</a>');
            exit;
        }
    }

    /**
     * @url /api/pay_cacnel/
     */
    function on_order_cancel() {
        $id = core::R('id:int');
        if (!$id) {
            $this->_message('user_not_privacy');
        }

        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $invest = $this->user_invest->get($id);
        if ($invest['user_id'] != $this->U->user_id) {
            $this->_message('user_not_privacy');
        }

        if ($invest['iscancel']) {
            $this->_message('invest_canceled');
        }

        if ($invest['ispaied']) {
            $this->_message('invest_ispaied');
        }

        if ($invest['isrefund']) {
            $this->_message('invest_isrefund');
        }

        // 标记
        $update = array(
            'iscancel' => 1,
        );
        $this->user_invest->update($update, $id);

        // 恢复
        $update = array(
            'leftnum' => 'leftnum+' . $invest['num'],
        );
        $this->project_invest->update($update, $invest['invest_id']);

        $this->_message('invest_cancel_succ', 0);
    }


    /**
     * @url /api/pay_refund/?id=xxx&reason=xxx
     */
    function on_order_refund() {
        $id = core::R('id:int');
        $reason = htmlspecialchars(trim(core::R('reason')));
        if (!$id) {
            $this->_message('user_not_privacy');
        }
        if (!$reason) {
            $this->_message('refund_must_type_reason');
        }

        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $invest = $this->user_invest->get($id);
        if ($invest['user_id'] != $this->U->user_id) {
            $this->_message('user_not_privacy');
        }

        if ($invest['iscancel']) {
            $this->_message('invest_canceled');
        }

        if ($invest['ispaied']) {
            //$this->_message('invest_ispaied');
        }

        if ($invest['isrefund']) {
            $this->_message('invest_isrefund');
        }

        // 众筹时间判断
        $project = $this->project->get($invest['project_id']);
        //
        if ($project['isverify'] != 3 && $project['expiretime'] <= core::S('time')) {
            $this->_message('project_is_not_open');
        }

        // 标记
        $update = array(
            'isrefund' => 1,
            'refundtime' => core::S('time'),
            'refundreason' => $reason,
        );
        $this->user_invest->update($update, $id);

        // 恢复
        $update = array(
            'leftnum' => 'leftnum+' . $invest['num'],
        );
        $this->project_invest->update($update, $invest['invest_id']);

        $this->_message('invest_refund_succ', 0);
    }

    /**
     * @url /api/get_unpay/?project_id
     */
    function on_get_unpay() {
        $project_id = core::R('project_id:int');
        if (!$project_id) {
            $this->_message('user_not_privacy');
        }

        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $exists_invest = $this->user_invest->get_unpay_by_project($this->U->user_id, $project_id);
        if ($exists_invest) {
            $this->_message('has_un_payment', 1);
        } else {
            $project_invest = $this->project_invest->select(array('project_id' => $project_id), 0, 0, 0);
            if ($project_invest && $project_invest['maxnum']) {
                $exists_count = $this->user_invest->get_paied_count_by_project($this->U->user_id, $project_id);
                if ($exists_count >= $project_invest['maxnum']) {
                    $this->_message('invest_num_out_of_range', -1);
                }
            }
            $this->_message('', 0);
        }
    }

    /*
     * 判断密码是否正确
     */
    function on_is_password(){
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $type = core::P('type');
        $password = core::P('password');

        $log_user = $this->U->get($this->U->user_id);
        $pass = $this->U->get_pass($password, $log_user['salt']);
        // check password
        if ($pass && $log_user['password'] != $pass) {
            $this->_message('password_fail');
        }

        //判断是否绑定手机号，如绑定则判断验证码
        if($log_user['phone']){
            if($type == 'phone'){
                $source = $this->U->phone;
                $code = core::P('regcode');
                //判断验证码
                if (!$this->_checkcode($type, $source, $code, 1)) {
                    $this->_message('invalid_regcode');
                }
            }
        }
        $this->_message(encode::encrypt($pass.$log_user['email'].$log_user['phone']), 0);
    }

    /*
     * 修改邮箱
     */
    function on_update_email(){
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $log_user = $this->U->get($this->U->user_id);
        $type = 'email';
        $code = core::P('regcode');
        $source = core::P('email');
        //判断邮箱是否存在
        $exists = $this->U->get_by_email($source);
        if ($exists) {
            $this->_message('account_email_exists');
        }
        //判断密码是否正确
        $md5_pass = core::P('md5_pass');
        $pass = encode::encrypt($log_user['password'].$log_user['email'].$log_user['phone']);
        if($md5_pass != $pass){
            $this->_message('unknown_request');
        }
        //判断验证码
        if (!$this->_checkcode($type, $source, $code, 1)) {
            $this->_message('invalid_regcode');
        }
        //发送邮件到老邮箱
        $setting = array(
            'to' => array($this->U->email),
            'sub' => array(
                '%code%' => array(
                    '邮箱已经取消绑定',
                ),
            ),
            // register template
            'template' => 'template_register',
        );
        $this->sendcloud_api->send($setting);

        $this->user->update(array('email' => $source),$this->U->user_id);//修改邮箱地址
        if (misc::form_submit()) {
            core::C('auth', '', -1);
        }
        $this->_message(1);
    }

    /*
     * 修改手机号
     */
    function on_update_phone(){
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }
        $log_user = $this->U->get($this->U->user_id);
        $type = 'phone';
        $code = core::P('regcode');
        $source = core::P('phone');
        //判断手机号是否存在
        $exists = $this->U->get_by_phone($source);
        if ($exists) {
            $this->_message('account_phone_exists');
        }
        //判断密码是否正确
        $md5_pass = core::P('md5_pass');
        $pass = encode::encrypt($log_user['password'].$log_user['email'].$log_user['phone']);
        if($md5_pass != $pass){
            $this->_message('unknown_request');
        }
        //判断验证码
        if (!$this->_checkcode($type, $source, $code, 1)) {
            $this->_message('invalid_regcode');
        }
        $this->user->update(array('phone' => $source),$this->U->user_id);//修改手机号
        if (misc::form_submit()) {
            core::C('auth', '', -1);
        }
        $this->_message(1);
    }

}