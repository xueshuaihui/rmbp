<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class order_control extends base_common_control {

    private $invest_id = 0;


    function __construct(&$conf) {
        parent::__construct($conf);
        // get id
        $gets = array_keys($_GET);
        while ($gets) {
            $key = array_pop($gets);;
            $this->invest_id = url_id($key, 'decode');
            if (is_numeric($this->invest_id) && $this->invest_id > 0) {
                break;
            }
        }
        // check user auth
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->show_login();
        }
        if ($this->U->field('isauth') != 2) {
            $this->show_message('请先认证投资后再开始支持', $this->conf['app_dir'] . 'user/certificate/');
        }
    }

    public function on_index() {
        // invest id
        if (!$this->invest_id) {
            $this->show_message('众筹档位不存在，请重新选择');
        }

        // get invest detail
        $invest = $this->project_invest->get($this->invest_id);

        if ($invest['leftnum'] <= 0) {
            $this->show_message('该档位已经筹完或不存在，请另行选择。');
        }

        // get project
        $project = $this->project->get($invest['project_id']);
        if ($project['isverify'] != 3) {
            $this->show_message('该项目暂时不开放众筹，请返回重新选择。');
        }

        // 检查该用户是否有当前项目未支付完成的项目
        $exists_invest = $this->user_invest->get_unpay_by_project($this->U->user_id, $invest['project_id']);

        if ($exists_invest) {
            $this->show_message('您还有未支付的订单，请返回支付后再创建新订单。', $this->conf['app_dir'] . 'user/invested/');
        }

        //
        if ($invest['maxnum'] > 0) {
            $exists_count = $this->user_invest->get_paied_count_by_project($this->U->user_id, $invest['project_id']);
            if ($exists_count >= $invest['maxnum']) {
                $this->show_message('您对该项目的投资额度已达上限，不要太贪心哦，还有更多好项目等着呢', $this->conf['app_dir'] . 'user/invested/');
            } else {
                $invest['maxnum'] -= $exists_count;
            }
        }


        // get user address
        $region_ids = array();
        $region_list = array();
        $address_list = $this->user_address->select(array('user_id' => $this->U->user_id), ' usetime DESC');
        foreach ($address_list as &$address) {
            foreach (array('province_id', 'city_id', 'county_id') as $field) {
                if ($address[$field]) {
                    $region_ids[] = $address[$field];
                }
            }
        }
        if ($region_ids) {
            $region_db = $this->region->get_user_address($region_ids);
            foreach ($region_db as $region) {
                $region_list[$region['id']] = $region['name'];
            }
        }
        // assign variables
        VI::assign('invest', $invest);
        VI::assign('project', $project);
        VI::assign('address_list', $address_list);
        VI::assign('region_list', $region_list);

        $this->show('order/index.htm');
    }

    public function on_submit() {
        // init yeepay
        $this->pay_yeepay->set_conf($this->conf);
        $this->pay_yeepay->get_form_array($post);
    }
}

?>