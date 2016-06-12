<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015/8/6
 * Time: 0:58
 */
include ROOT_PATH . 'control/mobile/mapi_control.class.php';

class vcapi_control extends mapi_control {
    /**
     * @url /vcapi/project_meet/?project_id=x&message=x
     */
    public function on_project_meet() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

//        if ($this->U->field('isauth') != 2) {
//            $this->_message('meet_deined');
//        }

        $meet_where = array(
            'project_id' => core::R('project_id:int'),
            'user_id' => $this->U->user_id,
        );

        $meet = $this->project_meet->select($meet_where);
        if ($meet) {
            $this->_message('invest_meet_exists');
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
     * @url /vcapi/judge_meeted/?project_id=x
     */
    public function on_judge_meeted() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $meet_where = array(
            'project_id' => core::R('project_id:int'),
            'user_id' => $this->U->user_id,
        );

        $meet = $this->project_meet->select($meet_where);
        if ($meet) {
            $this->_message('invest_meet_exists');
        } else {
            $this->_message('', 0);
        }
    }

}
