<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class home_control extends base_common_control {

    /**
     * @var int|mixed
     */
    private $user_id = 0;

    function __construct(&$conf) {
        parent::__construct($conf);

        $user_id = url_id(core::R('a'), 'decode');
        if (is_numeric($user_id) && $user_id > 0) {
            $this->user_id = $user_id;
            $_GET['a'] = 'index';
        }
        if (!$this->user_id) {
            $gets = array_keys($_GET);
            while ($gets) {
                $key = array_pop($gets);;
                $this->user_id = url_id($key, 'decode');
                if (is_numeric($this->user_id) && $this->user_id > 0) {
                    break;
                }
            }
        }
        if ($this->user_id) {
            $this->assign_value('user', $this->U->field('', $this->user_id));
        }

    }

    public function _init_user_center() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->show_login();
            exit;
        }
        $user_id = url_id(core::R('user_id'), 'decode');
//        assign user_field
        $user_data = $this->U->field('', $user_id);
        if (!$user_data) {
            $this->show_message('该用户不存在');
        }
        VI::assign('user_id', $this->U->user_id);
        VI::assign('user_data', $user_data);
    }

    /**
     * show user center
     */
    public function _show_user_center() {
        $user_id = url_id(core::R('user_id'), 'decode');
        $area_list = $this->user_feed->get_feed($user_id, 'area');
        $user_area = array();
        foreach ($area_list as $area) {
            $user_area[$area['id']] = $area;
        }
        VI::assign('user_area', $user_area);//关注领域
        unset($area_list);
        $user_region = $this->user_feed->get_feed($user_id, 'region');
        VI::assign('user_region', $user_region);//关注城市
        $this->show('home/index');
        exit;
    }

    /**
     * home index
     */
    public function on_index() {
        $this->on_invest();
    }

    /**
     * home financing
     */
    public function on_financing() {
        $this->_init_user_center();
        $page = max(core::R('page:int'), 1);
        $perpage = 15;
        $user_id = url_id(core::R('user_id'), 'decode');
        $temp_list = $this->project->get_by_uid($user_id, 0, $perpage, $page);
        $temp_list_count = $this->project->get_by_uid($user_id, 0, -2);
        $page_html = misc::pages($temp_list_count, $perpage, $page, $this->conf['app_dir'] . 'home/financing/user_id/'.core::R('user_id').'/&page=%d');
        foreach ($temp_list as $key => $project) {
            $project_list[$key]['invest_num'] = $this->user_invest->get_invest_num($project['id']);
            $this->project->format($project);
            $project_list[$key] = $project;
            $attachment_list = $this->attachment->get_by_ids($project['id'], 'project', 'banner');
            foreach ($attachment_list as $attachment) {
                $project_list[$key]['banner'] = $attachment;
            }
        }
        VI::assign('page_html', $page_html);
        VI::assign('project_list', $project_list);
        $this->_show_user_center();
    }


    /**
     * home invest
     */
    public function on_invest() {
        $this->_init_user_center();
        $page = max(core::R('page:int'), 1);
        $perpage = 15;
        $user_id = url_id(core::R('user_id'), 'decode');
        //assign user_invest
        $user_invest = $this->user_invest->get_user_invest_list(array('user_id' => $user_id), ' dateline DESC', $perpage, $page);
        $user_invest_count = $this->user_invest->get_user_invest_list(array('user_id' => $user_id), ' dateline DESC', -2);
        $page_html = misc::pages($user_invest_count, $perpage, $page, $this->conf['app_dir'] . 'home/invest/user_id/'.core::R('user_id').'/&page=%d');
        foreach ($user_invest as $key => $val) {
            $user_invest[$key]['project'] = $this->project->get($val['project_id']);
            $attachment_list = $this->attachment->get_by_ids($val['project_id'], 'project', 'banner');
            foreach ($attachment_list as $attachment) {
                $user_invest[$key]['banner'] = $attachment;
            }
        }
        VI::assign('page_html', $page_html);
        VI::assign('user_invest', $user_invest);
        $this->_show_user_center();
    }

    /**
     * home feed
     */
    public function on_feed() {
        $this->_init_user_center();
        $page = max(core::R('page:int'), 1);
        $perpage = 15;
        $user_id = url_id(core::R('user_id'), 'decode');
        $attent_project = $this->user_feed->select(array('user_id' => $user_id, 'feedtype' => 'project'), 0, $perpage, $page);
        $attent_project_count = $this->user_feed->select(array('user_id' => $user_id, 'feedtype' => 'project'), 0, -2);
        $page_html = misc::pages($attent_project_count, $perpage, $page, $this->conf['app_dir'] . 'home/feed/user_id/'.core::R('user_id').'/&page=%d');

        foreach ($attent_project as $key => $val) {
            $project_list[$key]['project'] = $this->project->get($val['target_id']);
            $attachment_list = $this->attachment->get_by_ids($val['target_id'], 'project', 'banner');
            foreach ($attachment_list as $attachment) {
                $project_list[$key]['banner'] = $attachment;
            }
        }
        VI::assign('page_html', $page_html);
        VI::assign('project_list', $project_list);
        $this->_show_user_center();
    }

    /**
     * home user_feed
     */
    public function on_user_feed() {
        $this->_init_user_center();
        $user_id = url_id(core::R('user_id'), 'decode');
        //关注
        $page = max(core::R('page:int'), 1);
        $perpage = 10;
        $data = array('user_id' => $user_id, 'feedtype' => 'user');
        $user_attent = $this->user_feed->get_user_attent($data, 0, $perpage, $page);
        $user_attent_count = $this->user_feed->get_user_attent($data, 0, -2);
        $page_html = misc::pages($user_attent_count, $perpage, $page, $this->conf['app_dir'] . 'home/user_feed/user_id/'.core::R('user_id').'/&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('user_attent', $user_attent);

        $this->show('home/user_feed');
    }

    /**
     * home user_fans
     */
    public function on_user_fans() {
        $this->_init_user_center();
        $user_id = url_id(core::R('user_id'), 'decode');
        //粉丝
        $page = max(core::R('page:int'), 1);
        $perpage = 10;
        $data = array('target_id' => $user_id, 'feedtype' => 'user');
        $user_attent = $this->user_feed->get_user_attent($data, 0, $perpage, $page);
        $user_attent_count = $this->user_feed->get_user_attent($data, 0, -2);
        $page_html = misc::pages($user_attent_count, $perpage, $page, $this->conf['app_dir'] .'home/user_fans/user_id/'.core::R('user_id'). '&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('user_attent', $user_attent);
        $this->show('home/user_fans');
    }
}

?>