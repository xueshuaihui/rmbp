<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015/8/6
 * Time: 0:14
 */
!defined('ROOT_PATH') && exit('Access Denied');

class sendcloud_api {

    function send($params) {
        $api_url = 'http://sendcloud.sohu.com/webapi/mail.send_template.json';
        $vars = array(
            'substitution_vars' => array(
                'to' => $params['to'],
                'sub' => $params['sub']
            ),
            'template_invoke_name' => $params['template'],
        );
        // add title
        if ($params['title']) {
            $vars['titles'] = $params['title'];
        }
        unset($params['to'], $params['sub'], $params['template']);
        $vars['substitution_vars'] = json_encode($vars['substitution_vars']);
        // post
        $post = array_merge(core::$conf['mail'], $vars);

        $data = spider::POST($api_url, $post);

        $data = json_decode($data, 1);
        if ($data['email_id_list'] && $id = array_pop($data['email_id_list'])) {
            return $id;
        } else if ($data['message'] == 'success') {
            return true;
        } else {
            return false;
        }
    }

}