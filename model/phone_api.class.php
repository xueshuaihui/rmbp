<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015/8/6
 * Time: 0:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class phone_api {

    /**
     * send phone message
     *
     * @param $params
     * @return bool
     */
    function send($params) {
        $conf = core::$conf['phone'];
        $api_url = $conf['api'];
        $sid = $conf['sid'];
        $token = $conf['token'];
        $time = date("YmdHis") . '000';
        $sign = strtolower(md5($sid . $time . $token));

        $params['sign'] = $sign;
        $params['time'] = $time;
        $params['templateId'] = $conf['tpl'][$params['tpl']];
        $params = array_merge($conf, $params);
        unset($conf['tpl']);

        $data = spider::POST($api_url, $params);
        $data = json_decode($data, 1);
        if($data['resp']['respCode'] == '000000'){
            return true;
        }else{
            return false;
        }
    }

}