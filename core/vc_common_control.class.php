<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-09-01
 * Time: 20:02
 */
class vc_common_control extends base_common_control {

    /**
     * login
     *
     * @param $conf
     */
    function __construct(&$conf) {
        parent::__construct($conf);
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth && $this->U->field('group_id') < 100) {
            $this->show_login();
        }
    }

    /**
     * show message page
     *
     * @param     $message
     * @param int $url
     */
    function show_message($message, $url = -1) {
        VI::assign('message', $message);
        VI::assign('url', $url);
        $this->show('vc/show_message');
        exit;
    }

    /**
     * show login form
     */
    function show_login() {
        $this->show('vc/user/login');
        exit;
    }

}

?>