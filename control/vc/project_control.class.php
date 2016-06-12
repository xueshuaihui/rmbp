<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class project_control extends vc_common_control {
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
        $is_auth = $this->U->init($this->conf, false);
        VI::assign('user', $this->U);
        $isauth = $this->U->field('isauth');
        VI::assign('isauth',$isauth);
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
                'areas',
                'leads',
            ),
            'timeline' => array(
                'timelines',
                'leads',
            ),
            'qa' => array(
                'qas',
                'leads',
            ),
            'invest' => array(
                'invests',
                'leads',
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
            $this->show_message('project_not_found');
        }
        // check admin
        /*if ($this->U->group_id != 999) {
            if ($project['isverify'] < 2) {
                $this->show_message('project_not_open');
            }
        }
         */

        // assign project_invest
        $invest_list = $this->project_invest->get_project_list(array('project_id' => $this->project_id));
        foreach ($invest_list as $key => $val) {
            $invest_list[$key]['attachment_list'] = $this->attachment->get_by_type($val['id'], 'project_invest', '', 2);
            $invest_list[$key]['invest_num'] = $this->user_invest->get_user_invest_list(array('invest_id' => $val['id']),0,-2);//档位投资人数
            $project[invest_list]=$invest_list;
        }

        // assign project_guy
//        if (isset($fields['guys'])) {
            $project['guys'] = $this->project_guy->get_project_guy_list($this->project_id);
//        }

        // assign attachment
//        if (isset($fields['banner'])) {
            $attachment = $this->attachment->get_by_type($this->project_id, 'project', 'banner');
            $project['banner'] = $attachment;
//        }
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
        if (isset($fields['invests'])) {
            $user_invest_list = $this->user_invest->get_user_invest_list(array('project_id' => $this->project_id));
            foreach ($user_invest_list as $key => $val) {
                $user_invest_list[$key]['user'] = $this->U->get($val['user_id']);
            }
            $project['invests'] = $user_invest_list;
        }

        // assign area
//        if (isset($fields['areas'])) {
            // assign project_area
            $project['areas'] = $this->project_area->get_by_project_id($this->project_id);
//        }
        VI::assign('project', $project);

        return $project;
    }

    /*
     * 项目详情的公用方法
     */
    private function _show() {
        VI::assign('pro', $this->pro);
        $this->show('vc/project_index');
    }

    /*
     * 我们的故事
     */
    public function on_intro() {
        $project = $this->_init_project();
//        print_r($project);exit;
        $this->show('vc/project_intro');
    }

    /*
     * 项目动态
     */
    /*public function on_timeline() {
        $project = $this->_init_project();
        $this->_show();
    }*/

    /*
     * 项目问答
     */
    /*public function on_qa() {
        $project = $this->_init_project();
        // assign user data
        $project_user = $this->U->field('', $project['user_id']);
        VI::assign('project_user', $project_user);

        $this->_show();
    }8、

    /*
     * 投资列表
     */
    /*public function on_invest() {
        $project = $this->_init_project();

        $this->_show();
    }*/

}

?>