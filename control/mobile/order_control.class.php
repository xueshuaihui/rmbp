<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class order_control extends mobile_common_control {

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

        if ($this->U->field('isauth') == 1) {
            $this->show_message('您正在认证投资人，请耐心等待审核');
        } else
            if ($this->U->field('isauth') != 2) {
                $this->show_message('请先认证投资人，再开始支持', $this->conf['app_dir'] . 'user/certificate/');
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
//        print_r($invest);
//        print_r($project);
//        print_r($address_list);
//        print_r($region_list);exit;
        $this->show('mobile/order/index.htm');
    }

    public function on_pay_way() {
        $this->show('mobile/order/pay_way.htm');
    }

    public function on_account_info() {
        VI::assign('user', $this->U);
        $this->show('mobile/order/account_info.htm');
    }

}

?>