<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/23
 * Time: 1:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class event extends base_db {

    function __construct() {
        parent::__construct('event', 'id');
    }

    /**
     * send event
     *
     * @param       $event
     * @param       $status
     * @param array $params
     * @param int   $user_id
     */
    function send($event, $event_id, $params = array(), $user_id = 0) {
        switch ($event) {
            case 'trade':
                switch ($params['status']) {
                    case -3://过期
                        $params['message'] = '您提交的订单因为没有交易，已经到了分红时间，因此已被取消。';
                    break;
                    case -2://交易失敗
                        $params['message'] = '交易失败。';
                    break;
                    case -1://未通过
                        $params['message'] = '您的交易未通过。';
                    break;
                    case 1://通过
                        $params['message'] = '您的交易通过';
                    break;
                    case 2://交易中
                        $params['message'] = '您的交易通过，正在交易中。';
                    break;
                    case 3://交易完成
                        $params['message'] = '您的交易完成。';
                    break;
                    case 4://交易成功
                        $params['message'] = '您的交易成功';
                    break;
                }
            break;
            case 'user_trade':
                switch ($params['status']) {
                    case -2://未通过
                        $params['message'] = '您的购买未通过:%notifymessage%';
                    break;
                    case -1://失败
                        $params['message'] = '您的购买失败';
                    break;
                    case 1://交易中
                        $params['message'] = '您的购买协商交易中';
                    break;
                    case 2://交易成功
                        $params['message'] = '您的购买成功';
                    break;
                }
            break;
            case 'invest_change'://转让给目标用户的消息
                $params['message'] = '您的交易已经成功';
            break;
        }
        if ($params['message']) {
            return $this->add($event, $event_id, $params, $user_id);
        }
        return false;
    }

    /**
     * create event that will send message in server crontab
     *
     * @param       $eventname
     * @param       $event_id
     * @param array $params
     * @param int   $user_id
     * @return array
     */
    function add($eventname, $event_id, $params = array(), $user_id = 0) {
        // format message
        $message = $params['message'];
        unset($params['message']);
        if ($params) {
            if (is_string($params)) {
                $message = str_replace('%%', $params, $message);
            } else {
                foreach ($params as $k => $v) {
                    $message = str_replace('%' . $k . '%', $v, $message);
                }
            }
        }

        $event = array(
            'eventname' => $eventname,
            'event_id' => $event_id,
            'message' => $message,
            'user_id' => $user_id,
            'dateline' => core::S('time'),
        );

        $event['id'] = $this->insert($event, 1);

        return $event;
    }
}