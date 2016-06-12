m<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class user_control extends mobile_common_control {

    function __construct(&$conf) {
        parent::__construct($conf);
        VI::assign('user', $this->U);
    }

    /**
     * register
     */
    public function on_register() {
        $is_auth = $this->U->init($this->conf, false);
        if ($is_auth) {
            misc::R('/');
        }
        $this->show('mobile/user/register');
    }

    /**
     * logout
     */
    public function on_logout() {
        if (misc::form_submit()) {
            core::C('auth', '', -1);
            //$this->show('mobile/user/logout', $this->conf['app_dir']);
            $this->show('mobile/user/logout', $this->conf['app_dir']);
        } else {
            $this->show_message('ErrorRequest');
        }
    }


    /**
     * online
     */
    public function on_online() {
        $is_auth = $this->U->init($this->conf, false);

        //assign user_field
        if ($is_auth) {
            VI::assign('user_id', $this->U->user_id);
        }
        VI::assign('user', $this->U);
        $this->show('mobile/user/online');
    }

    /**
     * bind user feed list
     */
    public function _bind_user_feed($uid = 0) {
        // get feed region
        $uid = $uid ? $uid : $this->U->user_id;
        $region_list = $this->user_feed->select(array('user_id' => $uid, 'feedtype' => 'region'));
        $region_ids = array();
        foreach ($region_list as $feed) {
            $region_ids[] = $feed['target_id'];
        }
        VI::assign_value('region_ids', implode(',', $region_ids));
    }

    /**
     * bind user area list
     */
    public function _bind_user_area($uid = 0) {
        // assign area list
        $area_list = $this->area->select(array(), 0);
        VI::assign('area_list', $area_list);

        // get feed area
        $uid = $uid ? $uid : $this->U->user_id;
        $areas = $this->user_feed->select(array('user_id' => $uid, 'feedtype' => 'area'));
        $area_feed = array();
        foreach ($areas as $area) {
            $area_feed[$area['target_id']] = 1;
        }

        VI::assign_value('area_feed', $area_feed);
    }

    /**
     * forgot password
     */
    public function on_forgot() {
        $this->show('mobile/user/forgot');
    }

    /**
     * forgot password
     */
    public function on_index() {
        $this->on_center();
    }

    /**
     * user center
     */
    public function on_center() {
        $this->_init_user_center();

        $area_list = $this->user_feed->get_feed($this->U->user_id, 'area');
        $user_area = array();
        foreach ($area_list as $area) {
            $user_area[$area['id']] = $area;
        }
        VI::assign('user_area', $user_area);
        unset($area_list);

        $user_region = $this->user_feed->get_feed($this->U->user_id, 'region');
        VI::assign('user_region', $user_region);


        $area_list = $this->area->select(array(), 0);
        VI::assign('area_list', $area_list);


        $this->_show_user_center();
    }

    /**
     * user center
     */
    public function on_certificate() {
        $this->_init_user_center();

        $uid = $this->U->user_id;

        // check isauth
        if ($this->U->field('isauth') > 0) {
            $this->show_message('已提交认证投资人申请，请等待审核', $this->conf['app_dir'] . 'user/invested/');
        }

        $this->_bind_user_area($uid);
        $this->_bind_user_feed($uid);

        $this->show('mobile/user/certificate');
    }

    /**
     * init user center
     */
    public function _init_user_center() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->show_login();
            exit;
        }

        //assign user_field
        $user_data = $this->U->field();
        VI::assign('user_data', $user_data);

    }

    /**
     *  login
     */
    public function on_login() {
        $is_auth = $this->U->init($this->conf, false);
        if ($is_auth) {
//            misc::R('/');
            misc::R('/user/invested/');

        }
        $this->show('mobile/user/login');
    }

    /**
     * show user center
     */
    public function _show_user_center() {
        $a_user = core::R('a');
        VI::assign('a_user', $a_user);
        $this->show('mobile/user/index');
        exit;
    }

    /**
     * user invested
     */
    public function on_invested() {
        $this->_init_user_center();
        //assign user_invest
        $user_invest = $this->user_invest->get_user_invest_list(array('user_id' => $this->U->user_id));
        foreach ($user_invest as $key => $val) {
            //project
            $user_invest[$key]['project'] = $this->project->get($val['project_id']);
        }
        VI::assign('user_invest', $user_invest);
//        echo $this->U->username;exit;
        $this->_show_user_center();
    }

    /**
     * user financing
     */
    public function on_financing() {
        $this->_init_user_center();

        $project = $this->project->get_by_uid($this->U->user_id);
        foreach ($project as $key => $val) {
            $project[$key]['invest_num'] = $this->user_invest->get_invest_num($val['id']);
        }
        VI::assign('project', $project);

        $this->_show_user_center();
    }

    /**
     * user message
     */
    public function on_message() {
        $this->_init_user_center();

        $page = max(core::R('page:int'), 1);
        $perpage = 20 ;
        //$this->U->user_id = 13;
        $count = $this->user_message->get_list($this->U->user_id, -1, -2);
        $list = array();
        if ($count) {
            $list = $this->user_message->get_list($this->U->user_id, -1, $perpage, $page);
        }
        $page_count = ceil($count / $perpage);

        VI::assign('list', $list);
        VI::assign('count', $count);
        VI::assign('page', $page);
        VI::assign('perpage', $perpage);
        VI::assign('page_count', $page_count);
        $this->_show_user_center();
    }

    /**
     * user password
     */
    public function on_password() {
        $this->_init_user_center();
        $this->_show_user_center();
    }


}

?>