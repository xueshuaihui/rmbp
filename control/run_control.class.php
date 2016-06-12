<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class run_control extends base_common_control {

    public function on_event() {
        register_shutdown_function(array($this, '_reset_event'));

        // get process status
        $process = $this->variable->get('proecess_event');
        if ($process) {
            log::info('event is process');
            return;
        }
        $this->variable->set('process_event', 1);


        while (true) {
            $sql = 'SELECT * FROM ' . DB::table('event') . ' WHERE issend=0 ORDER BY dateline';
            $query = DB::query($sql);
            while ($event = DB::fetch($query)) {
                list($time,) = explode('.', microtime(1));
                $success = 0;
                log::info('eventStart', $event);
                switch ($event['eventname']) {
                    default:
                        // send message from person to person
                        $id = $this->user_message->send($event['event_id'], $event['user_id'], $event['id'], $event['message'], $event['title'], $event['sortid']);
                        if ($id) {
                            $this->U->update(array('messagenum' => 'messagenum+1'), $event['event_id']);
                        }
                        $success = 1;
                    break;
                }
                if ($success) {
                    $this->event->update(array('issend' => 1, 'sendtime' => $time));
                }
            }
            sleep(5);
        }
    }

    public function on_invest() {
        register_shutdown_function(array($this, '_reset_invest'));

        // get process status
        $process = $this->variable->get('proecess_invest');
        if ($process) {
            log::info('invest is process');
            return;
        }
        $this->variable->set('process_invest', 1);

        while (true) {
            $sql = 'SELECT * FROM ' . DB::table('user_invest') . ' WHERE (ispaied=0 AND iscancel=0 AND isrefund=0) ORDER BY dateline DESC';
            $query = DB::query($sql);
            list($time,) = explode('.', microtime(1));
            while ($invest = DB::fetch($query)) {
                $payment = $invest['payment'];
                $payment_class = 'pay_' . $payment;
                try {
                    $payment_instance = $this->$payment_class;
                } catch (Exception $e) {
                    log::info('PaymentClassNotFound', $payment_class, $e);
                    continue;
                }
                $payment_instance->set_conf($this->conf);

                // query order id
                $query = $payment_instance->query($invest['id']);
                $update = array();

                // get invest data
                if ($query['data']) {
                    if ($invest['data']) {
                        $invest['data'] = json_decode($invest['data'], 1);
                    }
                    if (!$invest['data']) {
                        $invest['data'] = array();
                    }
                    foreach ($query['data'] as $key => $val) {
                        $query['data'][$key] = iconv('gbk', 'utf-8', $val);
                    }

                    if ($query['data'] && array_diff($query['data'], $invest['data'])) {
                        $update['data'] = $query['data'];
                    }
                }
                if ($query['isrefund']) {
                    if (!$invest['isrefund']) {
                        $update['isrefund'] = 2;
                    }
                } elseif ($query['iscacnel']) {
                    if (!$invest['iscancel']) {
                        $update['iscancel'] = 1;
                    }
                } elseif (!$invest['ispaied']) {
                    if ($query['ispaied']) {
                        $update['ispaied'] = 1;
                        $update['paytime'] = $invest['paytime'] ? $invest['paytime'] : $time;
                    } else {

                        if ($time - $invest['dateline'] > 86400) {
                            $update['iscancel'] = 1;
                            $update_invest = array(
                                'leftnum' => 'leftnum+' . $invest['num'],
                            );
                            $this->project_invest->update($update_invest, $invest['invest_id']);
                        }
                    }
                }
                // update invest
                if ($update) {
                    log::info('updateInvest=' . $invest['id'], $update);
                    $this->user_invest->update($update, $invest['id']);
                }
            }
            sleep(15);
        }
    }

    function on_make_region() {
        $region_list = $this->region->select(array(), ' sortid DESC');
        foreach ($region_list as &$region) {
            unset($region['sortid'], $region['code']);
        }
        file_put_contents($this->conf['static_dir'] . 'region.js', json_encode($region_list));
    }


    function on_make_avatar() {
        $user_list = $this->user->select(array(), ' uid DESC');
        foreach ($user_list as &$user) {
            if ($this->avatar->create($user['uid'])) {
                log::info('create', $user['uid']);
            }
        }
    }

    function on_update_avatar() {
        $user_list = $this->user->select(array(), ' uid DESC');
        foreach ($user_list as &$user) {
            $uid = $user['uid'];
            $avatar = 'avatar/' . substr($uid, -2) . '/' . $uid . '.jpg';
            $avatar_new = 'avatar/' . substr($uid, -2) . '/' . $uid . '.png';
            if (is_file(core::$conf['static_dir'] . $avatar)) {
                $im = imagecreatefromjpeg(core::$conf['static_dir'] . $avatar);
                imagepng($im, core::$conf['static_dir'] . $avatar_new);
                imagedestroy($im);
                @unlink(core::$conf['static_dir'] . $avatar);
            }
        }
    }

    function on_clean() {
        $file_list = $this->get_dir(ROOT_PATH . 'static/');
        foreach ($file_list as $file) {
            $base_name = basename($file);
            if (substr($base_name, 0, 1) == '_') {
                log::info($file);
                unlink($file);
            } else {
                log::info($file);
            }
        }
        $file_list = $this->get_dir(ROOT_PATH . 'data / tmp / ');
        foreach ($file_list as $file) {
            log::info($file);
            unlink($file);
        }
    }


    function get_dir($dir) {
        $file_list = misc::scandir($dir);
        $result_list = array();
        foreach ($file_list as $file) {
            $source_file = $dir . $file;
            if (is_dir($source_file)) {
                $result_list = array_merge($this->get_dir($source_file . ' / '), $result_list);
            } else {
                $result_list[] = $source_file;
            }
        }
        return $result_list;
    }

    function _reset_event() {
        // reset event
        $this->variable->set('process_event', 0);
    }

    function _reset_invest() {
        // reset invest
        $this->variable->set('process_invest', 0);
    }

}

?>