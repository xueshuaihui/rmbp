<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class trade_control extends base_common_control {

    function __construct(&$conf) {
        parent::__construct($conf);
        $_GET['a'] = $_GET['a'] ? $_GET['a'] : 'index';
    }

    /*
     * 股权交易列表页
     */
    public function on_index() {
        // check auth
        $is_login = $this->U->init($this->conf, false);//判断是否登录 登录=1
        if (!$is_login) {
            $this->show_login();
        }

        $is_auth = $this->U->field('isauth');
        if ($is_auth != 2) {
            $this->show_message('仅认证投资人可以进行股权交易。', $this->conf['app_dir'].'user/certificate/');
        }

        // fetch trade list
        $trade = $this->trade->select('status=\'2\' AND deadline >=\'' . core::S('time') . '\'');

        $this->query_tables($trade, array(
            'project' => 'project_id',
            'user_invest' => array('id' => 'user_invest_id'),
            'user_field' => array('user_id' => 'user_id'),
            'project_invest' => array('project_id' => 'project_id'),
        ));
//        echo '<pre>';
//        print_r($trade);exit;
        VI::assign('trade', $trade);
        $this->show('trade/index');
    }

    /*
     * 股权转让页面
     */
    public function on_sell() {
        // check auth
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->show_message('请先登录');
        }
        $id = core::R('id:int');
        if (!$id) {
            $this->show_message('参数错误');
        }
        $invest = $this->user_invest->get($id);
        if ($invest['user_id'] != $this->U->user_id) {
            $this->show_message('您没有权限出售该股权');
        }
        if ($invest['issell'] == 1) {
            $this->show_message('您正在出售该股权。');
        } elseif ($invest['issell'] == 2) {
            $this->show_message('您的股权已售出。');
        }
        if ($invest['iscancel'] == 1) {
            $this->show_message('您的投资已取消。');
        }
        if ($invest['ispaied'] == '0') {
            $this->show_message('您的投资未支付。');
        }
        if ($invest['isrefund'] == 1) {
            $this->show_message('您正在申请退款。');
        } elseif ($invest['isrefund'] == 2) {
            $this->show_message('您的投资已退款。');
        }
        // project info
        $project = $this->project->get($invest['project_id']);
        if ($project['istrade'] != 1) {
            $this->show_message('该项目暂时不开放交易');
        }
        // check save
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $data = core::P('trade');
            $res = $this->user->select(array('uid' => $invest['user_id']), 0, 0);
            $data['phone'] = $res['phone'];
            $data['dateline'] = time();
            $data['ip'] = core::ip();
            $data['project_id'] = $invest['project_id'];
            $data['user_id'] = $invest['user_id'];
            $data['user_invest_id'] = $invest['id'];
            $data['project_invest_id'] = $invest['invest_id'];
            $data['sellnum'] = $invest['num'];
            $data['sellmessge'] = htmlspecialchars($data['sellmessge']);
            $this->trade->insert($data, 1);
            $this->show_message('提交转让成功。', $this->conf['app_url'] . 'trade/index/');
        }
        VI::assign('project', $project);
        VI::assign('invest', $invest);
        $this->show('trade/sell');
    }

    /*
     * 股权购买页面
     */
    public function on_buy() {
        // check auth
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->show_message('请先登录');
        }
        // trade_id
        $id = core::R('id:int');
        if (!$id) {
            $this->show_message('参数错误');
        }
        $trade = $this->trade->get($id);
        if ($trade['status'] != 2) {
            $this->show_message('该股权暂时不能交易，请稍后访问');
        }
        if ($trade['user_id'] == $this->U->user_id) {
            $this->show_message('您不能购买自己出售的股权');
        }
        $user_trade = $this->user_trade->select(array('trade_id' => $id, 'user_id' => $this->U->user_id), 0, 0);
        if ($user_trade) {
            $this->show_message('您已购买过该股权');
        }
        $trade['project_invest'] = $this->project_invest->select(array('project_id' => $trade['project_id']), 0, 0);
        $trade['user_invest'] = $this->user_invest->select(array('id' => $trade['user_invest_id']), 0, 0);
        // check save
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $data['trade_id'] = $trade['id'];
            $data['user_id'] = $this->U->user_id;
            $data['dateline'] = time();
            $this->user_trade->insert($data, 1);
            $this->show_message('您的购买意向已收到，我们会在 24 小时内联系您和转让人确认交易的后续事项。', $this->conf['app_url'] . 'trade/index/');
        }
        VI::assign('trade', $trade);
        $this->show('trade/buy');

    }
}

?>