<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class project_control extends base_common_control {
    /**
     * @var int|mixed
     */
    private $project_id = 0;

    private $project_fields_show = array();

    /**
     * @param $conf
     */
    function __construct(&$conf) {
        parent::__construct($conf);
        // check auth
        if ($this->U->init($this->conf, false)) {
            //判断是否登录 登录=1
            VI::assign('user', $this->U);
        }

        $isauth = $this->U->field('isauth');
        VI::assign('isauth', $isauth);//用户是否认证

        // get project by id
        $this->project_id = core::R('project_id');
        if (!$this->project_id) {
            $gets = array_keys($_GET);
            while ($gets) {
                $key = array_pop($gets);;
                $this->project_id = url_id($key, 'decode');

                if (is_numeric($this->project_id) && $this->project_id > 0) {
                    break;
                }
            }
        }
        //get action
        $this->pro = core::R('a');
        $field_show = array(
            'detail' => array(
                'guys',
            ),
            'financial' => array(
                core::R('ajax') ? '' : 'timelines',
                'shops',
            ),
            'return' => array(
                core::R('ajax') ? '' : 'timelines',
            ),
            'plan' => array(
                'leads',
            ),
            'qa' => array(
                'qas',
                core::R('ajax') ? '' : 'leads',
            ),
            'invest' => array(
                'u_invests',
                core::R('ajax') ? '' : 'leads',
                core::R('ajax') ? '' : 'timelines',
            ),
            'timeline' => array(
                'timelines',
                core::R('ajax') ? '' : 'leads',
            ),
        );
        // assign fields
        foreach ($field_show[$this->pro] as $field) {
            $this->project_fields_show[$field] = 1;
        }
    }

    /*
     * 项目详情
     */
    public function on_detail() {
        $project = $this->_init_project();
        $this->_show();
    }

    /**
     * init project
     *
     * @return mixed
     */
    private function _init_project() {
        if ($this->project_id) {
            $project = $this->project->get_fields($this->project_id);
        }
        $fields = $this->project_fields_show;
        // project not found
        if (!$project) {
            $this->show_message('没有找到对应项目，请返回确认');
        }
        // check admin
        if ($this->U->group_id != 999) {
            if ($project['isverify'] < 2 && $project['user_id'] != $this->U->user_id) {
                $this->show_message('项目没有开放，请稍后访问');
            }
        }

        VI::assign('user_id', $this->U->user_id);

        // assign project_invest
//        $invest_list = $this->project_invest->get_project_list(array('project_id' => $this->project_id));
//        VI::assign('invest_list', $invest_list);
        //二维码图片
        $project['qr_code'] = $this->attachment->select(array('idsource' => $this->project_id, 'idtype' => 'project', 'type' => 'qr_code'), 0, 0);

        //新 assign invest
        $project['invest'] = $this->project_invest->select(array('project_id' => $this->project_id), 0, 0);

        $project['invest']['buy_num'] = $this->user_invest->select(array('project_id' => $this->project_id), 0, -2);//已投资份数

        // assign project_guy
        if (isset($fields['guys'])) {
            $project['guys'] = $this->project_guy->get_project_guy_list($this->project_id);
        }

        //assign shop
        if (isset($fields['shops'])) {
            $project['shops'] = $this->project_shop->select(array('project_id' => $this->project_id));
        }

        // assign attachment
        if (isset($fields['banner'])) {
            $attachment = $this->attachment->get_by_type($this->project_id, 'project', 'banner');
            $project['banner'] = $attachment;
        }
        // assign project lead
        if (isset($fields['leads'])) {
            $project['leads'] = $this->project_lead->select(array('project_id' => $this->project_id));
        }
        // assign timeline
        if (isset($fields['timelines'])) {
            $project['timelines'] = $this->project_timeline->get_timeline_list($this->project_id);
        }
        // assign qa
        if (isset($fields['qas'])) {
            $project['qas'] = $this->comment->get_project_comment($this->project_id);
        }

        // assign invests
        if (isset($fields['u_invests'])) {
            $user_invest_list = $this->user_invest->get_user_invest_list(array(
                'project_id' => $this->project_id,
                'ispaied' => 1,
                'isrefund' => 0));
            foreach ($user_invest_list as $key => $val) {
                $user_invest_list[$key]['user'] = $this->U->get($val['user_id']);
            }
            $project['invests'] = $user_invest_list;
        }

        if ($project['projecttype'] == 'store') {
            $region_ids = array();
            if ($project['province_id']) {
                $region_ids[] = $project['province_id'];
            }
            if ($project['city_id']) {
                $region_ids[] = $project['city_id'];
            }
            $region_list = $this->region->get_by_ids($region_ids);
            foreach ($region_list as $region) {
                $project['regions'][$region['id']] = $region;
            }
            unset($region_list);
        }

        // assign area
        // assign project_area
        $project['areas'] = $this->project_area->get_by_project_id($this->project_id);

        // assign field
        if (isset($fields['field'])) {
            $project['field'] = $this->project_field->select(array('project_id' => $this->project_id), 0, 0);
        }

//        echo '<pre>';
//        print_r($project);exit;
        VI::assign('project', $project);
        return $project;
    }

    /*
     * 项目详情的公用方法
     */
    private function _show() {
        VI::assign('pro', $this->pro);
        $this->show('project_index');
    }

    /*
     * 项目动态
     */
    public function on_timeline() {
        $project = $this->_init_project();
        $this->_show();
    }

    /**
     * 计划
     */
    public function on_plan() {
        $project = $this->_init_project();
        $this->_show();
    }

    /*
     * 项目问答
     */
    public function on_qa() {
        $project = $this->_init_project();
        // assign user data
        $project_user = $this->U->field('', $project['user_id']);
        VI::assign('project_user', $project_user);

        $this->_show();
    }

    /*
     * 投资列表
     */
    public function on_invest() {
        $project = $this->_init_project();

        $this->_show();
    }

    /**
     * 经营状况
     */
    public function on_financial() {
        $project = $this->_init_project();
        $this->_show();
    }

    /**
     * 投资回报
     */
    public function on_return() {
        $project = $this->_init_project();
        $this->_show();
    }

}

?>