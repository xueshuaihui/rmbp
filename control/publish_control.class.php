<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class publish_control extends base_common_control {

    /**
     * @var int
     */
    private $project_id = 0;
    /**
     * @var int
     */
    private $step = 1;
    /**
     * max step for each project type
     *
     * @var int
     */
    private $max_step = 0;
    /**
     * @var array
     */
    private $project_source = array();
    private $project_shop_source = array();
    private $invest_list_source = array();

    /**
     * @param $conf
     */
    function __construct(&$conf) {

        parent::__construct($conf);
        // check auth
        $is_auth = $this->U->init($this->conf, false);
        // TODO show login form
        if (!$is_auth) {
            $this->show_login();
        }
        // assign user
        VI::assign('user', $this->U);
        // get project id
        $this->project_id = url_id(core::R('project_id'), 'decode');
        if (!$this->project_id) {
            // find last project
            $this->_find_last_project();
        } else {
            // find project by id
            $this->_find_project($this->project_id);
        }
        $max_step_array = array(
            'tmt' => 5,
            'store' => 6,
        );
        // get step
        $this->step = min(max((int)str_replace('step', '', core::R('a')), 0), 7);
        $this->max_step = $max_step_array[$this->project_source['projecttype']];

        $guy = $this->project_guy->select(array('project_id' => $this->project_id), 0, 0);
        $invest = $this->project_invest->select(array('project_id' => $this->project_id), 0, 0);
        VI::assign_value('guy', $guy);
        VI::assign_value('invest', $invest);

        // assign project id
        VI::assign_value('project_id', $this->project_id);
    }

    /**
     * find last project
     */
    private function _find_last_project() {
        $this->project_source = $this->project->select(array('user_id' => $this->U->user_id, 'isverify' => 0), ' dateline DESC', 0);
        if ($this->project_source) {
            $this->project_id = $this->project_source['id'];
            VI::assign('project', $this->project_source);
        }
    }

    /**
     * find project
     */
    private function _find_project($project_id) {
        if ($project_id) {
            $this->project_source = $this->project->get($project_id);
            if ($this->U->group_id != 999) {
                if ($this->project_source['isverify'] > 0) {
                    $this->show_message('项目已经上线或正在审核中，暂时无法操作');
                } else if ($this->project_source['isverify'] < 1 && $this->project_source['user_id'] != $this->U->user_id) {
                    $this->show_message('您无权编辑该项目');
                }
            }
            VI::assign('project', $this->project_source);

            $this->project_shop_source = $this->project_shop->select(array('project_id' => $this->project_id));
            VI::assign('shop_list', $this->project_shop_source);

            $this->invest_list_source = $this->project_invest->select(array('project_id' => $this->project_id), 0, 0);
            VI::assign('invest_list', $this->invest_list_source);
        }
    }


    /**
     * assign project field
     */
    private function _assign_field() {
        // assign field
        $project_field = $this->project_field->get($this->project_id);
        VI::assign('project_field', $project_field);
    }


    /**
     * step1 is index page
     */
    public function on_index() {
        $this->_show();
    }

    /**
     * step 1
     */
    public function on_step1() {

        // save post auto
        $this->_save_post();

        if ($this->project_id) {
            $area_list = $this->area->select(array('areatype' => $this->project_source['projecttype']), 0);
            VI::assign('area_list', $area_list);
            // assign project field
            if ($this->project_source) {
                // get all project_area id
                $area_ids = $this->project_area->get_by_project_id($this->project_id);
                $area_lists = array();
                foreach ($area_ids as $area) {
                    $area_lists[$area['area_id']] = 1;
                }
                VI::assign('area_ids', $area_lists);
                $this->_assign_field();
            }
            // assign project banner
            $this->project_source['banner'] = $this->attachment->get_by_type($this->project_id, 'project', 'banner');
            $this->project_source['qr_code'] = $this->attachment->get_by_type($this->project_id, 'project', 'qr_code');
            if ($this->project_source['openday']) {
                $dates = explode('-', date('Y-m-d', $this->project_source['openday']));
                $this->project_source['year'] = $dates['0'];
                $this->project_source['month'] = $dates['1'];
                $this->project_source['day'] = $dates['2'];
            }
        }
        $this->_show();
    }

    /**
     * step 2
     */
    public function on_step2() {
        //save
        $this->_save_post();
        $this->_assign_field();
        $this->_show();
    }

    /**
     * receive post data
     */
    private function _save_post() {
        if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            return;
        }
        // publish by each variable
        $project = core::P('project');
        $invests = core::P('invests');
        $guys = core::P('guys');
        $shops = core::P('shops');
        $area_ids = array_diff(explode(',', core::P('area_ids')), array(''));
        $pics = core::P('pics');//项目banner图
        $qr_code = core::P('qr_code');//二维码图片
        $dates = core::P('dates');//开业时间
        // submit verify
        // unset don't allow field
        if ($project) {
            unset($project['id']);
            if ($this->U->group_id != 999) {
                unset($project['status'], $project['sortid'], $project['expiretime']);
            }
        }

        if ($project) {
            // TODO check all field invalid
            foreach ($project as $key => &$val) {
                $html_encode = 1;
                switch ($key) {
                    case 'title':
                        if (!$val) {
                            $this->show_message('请输入正确的标题');
                        }
                    break;
                    case 'description':
                        $val = check_html($val);
                        $html_encode = 0;
                    break;
                    case 'financingplan':
                        $val = check_html($val);
                        $html_encode = 0;
                    break;
                }
                // html encode
                if ($html_encode) {
                    $val = core::htmlspecialchars($val);
                }
            }

            // check project verify
            if ($this->U->group_id != 999) {
                unset($project['verifymessage']);
                if(isset($project['isverify'])) {
                    if (is_numeric($project['isverify'])) {
                        $project['isverify'] = $project['isverify'] == 1 ? 1 : 0;
                    } else {
                        $project['isverify'] = 0;
                    }
                }
            } else {
                if (isset($project['isverify'])) {
                    // get message
                    $message = $project['verifymessage'];
                    if ($message) {
                        $event_param = array(
                            'message' => $message,
                            'project' => $this->project_source['title'],
                        );
                        // send event
                        $this->event->add('project_message', $this->project_source['user_id'], $event_param,
                            $this->U->user_id);
                    }
                }
                if ($project && $project['isverify'] == 3) {
                    // 项目上线
                    $project['expiretime'] = core::S('time') + $this->project_source['financingday'] * 86400;
                }
            }
            // bind default value
            if (!$this->project_source) {
                $project['user_id'] = $this->U->user_id;
                $project['dateline'] = core::S('time');
            }

            if ($dates) {
                if ($dates['year']) {
                    $project['openday'] = strtotime($dates['year'] . '-' . $dates['month'] . '-' . $dates['day']);
                }
            }
        }

        // create project if its not exists project
        if (!$this->project_id) {
            // check project banner
            if ($this->step == 2) {
                if (!$pics['project']) {
                    $this->show_message('请上传正确的项目图片');
                }
//                if (!$qr_code['project']) {
//                    $this->show_message('请上传正确的二维码图片');
//                }
            }
            // insert project
            if ($project) {
                $this->project_id = $this->project->insert_all($project, 1);
                if (!$this->project_id) {
                    $this->show_message('system_error');
                }
            }
            // upload attachment
            $attachment = $this->attachment->upload_base64_image($pics['project'],
                $this->U->user_id, 'project', $this->project_id,
                'banner');

            // upload attachment
            $attachment = $this->attachment->upload_base64_image($qr_code['project'],
                $this->U->user_id, 'project', $this->project_id,
                'qr_code');
            // find project & assign
            $this->_find_project($this->project_id);

            // 公司二维码
            $projectnum = $this->U->field('projectnum');
            $this->U->update(array('projectnum' => $projectnum + 1));

            // reassign project_id
            VI::assign_value('project_id', url_id($this->project_id));
        } else {
            // update project
            if ($project) {
                $this->project->update_all($project, $this->project_id);
                // find project & assign
                if ($this->step < $this->max_step) {
                    $this->_find_project($this->project_id);
                }
            }
            if ($pics['project']) {
                // upload attachment
                $attachment = $this->attachment->upload_base64_image($pics['project'],
                    $this->U->user_id, 'project', $this->project_id,
                    'banner');
            }
            if ($qr_code['project']) {
                // 公司二维码
                $attachment = $this->attachment->upload_base64_image($qr_code['project'],
                    $this->U->user_id, 'project', $this->project_id,
                    'qr_code');
            }
        }

        if (!$this->project_id) {
            $this->show_message('没有选择项目，请返回重试');
        }

        // save invest
        if ($invests) {
            $invest = $invests;

            $invest['project_id'] = $this->project_id;
            unset($invest['user_id'], $invest['leftnum']);

            if ($this->project_source['projecttype'] == 'store') {
                $invest['price'] = ($this->project_source['valuation'] - $this->project_source['existfinancing']) / $invest['returnnum'];
            }
            $invest['price'] = $invest['price'] * 10000;

            // is there sell out some num
            $used_num = 0;
            if (!$invest['id']) {
                $invest['user_id'] = $this->U->user_id;
                $invest['id'] = $this->project_invest->insert($invest, 1);
            } else {
                // old used num
                $exists = $this->project_invest->get($invest['id']);
                $used_num = $exists['returnnum'] - $exists['leftnum'];
            }
//            $invest['returnnum'] = ceil((($this->project_source['maxfinancing'] * 10000)) / $invest['price']);
//            if ($invest['returnnum'] < $invest['maxnum']) {
//                $invest['maxnum'] = $invest['returnnum'];
//            }
            // get left sell num
            if($invest['leftnum']){
                $invest['leftnum'] = max($invest['returnnum'] - $used_num, 0);
            }
            if($invest['bonusexpect']){
                $invest['bonusexpect'] = $invest['bonusexpect']/10000;
            }
            $this->project_invest->update($invest, $invest['id']);
        }

        // save guys
        if ($guys) {
            foreach ($guys as $guy) {
                $img_data = $guy['pic'];
                unset($guy['pic']);
                // bind project_id
                $guy['project_id'] = $this->project_id;

                //unset dont allow field
                unset($invest['user_id']);

                // find guy
                if ($guy['id']) {
                    $exists = $this->project_guy->get($guy['id']);
                    if ($exists['project_id'] != $this->project_id) {
                        continue;
                    }
                    // check pic twice
                    if ($exists['pic'] && is_file($this->conf['static_dir'] . $exists['pic'])) {
                        $guy['pic'] = $exists['pic'];
                    }
                } else {
                    $guy['id'] = $this->project_guy->insert($guy, 1);
                    $guy['user_id'] = $this->U->user_id;
                }
                // upload attachment
                if ($img_data) {
                    if (strpos($img_data, 'data:') === 0) {
                        $attachment = $this->attachment->upload_base64_image($img_data,
                            $this->U->user_id, 'project_guy', $guy['id'],
                            '');
                        $guy['pic'] = $attachment['path'];
                    }/*else{
                        $guy['pic'] = substr($img_data,25);
                    }*/
                }
                $guy['dateline'] = core::S('time');
                // update guy data and pic
                $this->project_guy->update($guy, $guy['id']);
            }
        }

        // save shops
        if ($shops) {
            foreach ($shops as $shop) {
                $shop['project_id'] = $this->project_id;
                $shop['dateline'] = core::S('time');
                if ($shop['id']) {
                    $exists = $this->project_shop->get($shop['id']);
                    if ($exists['project_id'] != $this->project_id) {
                        continue;
                    }
                } else {
                    $shop['id'] = $this->project_shop->insert($shop, 1);
                    $shop['user_id'] = $this->U->user_id;
                }
                $this->project_shop->update($shop, $shop['id']);
            }
        }

        // save area ids relation
        if ($area_ids) {
            $area_ids = array_flip(array_flip($area_ids));
            // max selection
            $area_ids = array_slice($area_ids, 0, 3);
            // remove all relation
            $this->project_area->delete(array('project_id' => $this->project_id));
            foreach ($area_ids as $area_id) {
                if (!is_numeric($area_id)) {
                    continue;
                }
                $area_data = array(
                    'project_id' => $this->project_id,
                    'area_id' => $area_id,
                );
                $this->project_area->insert($area_data);
            }
        }
    }

    /**
     * step 3
     */
    public function on_step3() {
        //save
        $this->_save_post();
        $this->_assign_field();
        // find all invest list
        $invest_list = $this->project_invest->select(array('project_id' => $this->project_id), 0, 0);
        VI::assign('invest_list', $invest_list);
        $this->_show();
    }

    /**
     * step 4
     */
    public function on_step4() {
        //save
        $this->_save_post();
        $this->_assign_field();
        //find all guys list
        $guy_list = $this->project_guy->select(array('project_id' => $this->project_id), 'id ASC');
        foreach ($guy_list as &$guy) {
            if ($guy['pic']) {
                $attachment = $this->attachment->get_by_type($guy['id'], 'project_guy', '');
                if ($attachment) {
                    $guy['pic'] = array('url' => $attachment['path'], 'id' => $attachment['id']);
                }
            }
            if (!isset($guy['pic']['url'])) {
                $guy['pic'] = array();
            }
        }
        VI::assign('guy_list', $guy_list);

        //$this->project_shop_source = $this->project_shop->select(array('project_id' => $this->project_id));
        //VI::assign('shop_list', $this->project_shop_source);
        $this->_show();

    }

    /**
     * step 5
     */
    public function on_step5() {
        //save
        $this->_save_post();
        $this->_assign_field();

        $this->invest_list_source = $this->project_invest->select(array('project_id' => $this->project_id), 0, 0);
        VI::assign('invest_list', $this->invest_list_source);

        $this->_show();
    }

    /**
     * step 6
     */
    public function on_step6() {
        //save
        $this->_save_post();

        $this->_show();
    }

    /**
     * assign step & show
     */
    private function _show() {
        switch ($this->project_source['projecttype']) {
            case 'tmt':
            break;
            default:
                $this->project_source['projecttype'] = 'store';
            break;
        }
        // index
        if (!$this->step) {
            $this->show('publish_index');
            exit;
        } else {
            VI::assign('step', $this->step);
            $template_dir = 'publish_' . $this->project_source['projecttype'] . '/';
            $template_file = 'index';
            $this->show($template_dir . $template_file);
        }


    }

}


?>