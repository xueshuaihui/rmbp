<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-09-01
 * Time: 20:02
 */
class mobile_common_control extends base_common_control {

    /**
     * login
     *
     * @param $conf
     */
    function __construct(&$conf) {
        parent::__construct($conf);
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
        $this->show('mobile/show_message');
        exit;
    }
    
    /**
     * show login form
     */
    function show_login() {
        $this->show('mobile/user/login');
        exit;
    }

}

?>