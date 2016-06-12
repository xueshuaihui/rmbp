<?php
/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-09-16
 * Time: 15:54
 */
define('MAX_TIME', 86400 * 7);

class qr_control extends base_common_control {

    function on_index() {
        $id = core::R('project_id');
        $project_id = url_id($id . 'decode');
        if ($project_id) {
            $url = $this->conf['mobile_url'] . 'project/detail/' . $id . '/';
            $this->_process('pro', $url);
        }
    }

    function _process($pre, $url) {
        list(, $server_time) = explode(' ', microtime(1));
        $dir = ROOT_PATH . '/static/qr/';
        $md5 = md5(strtolower($url));
        $dir = $dir . substr($md5, 0, 2) . '/';
        $filename = $dir . $pre . '_' . $md5 . '.png';
        $filetime = @filemtime($filename);

        if (!$filetime) {
            !is_dir($dir) && mkdir($dir, 0777, 1);
            $errorCorrectionLevel = 'H';
            $matrixPointSize = 8;
            $this->qrcode;
            qrcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        } else {
            if ($server_time - filemtime($filename) > 3600) {
                touch($filename);
            }
        }
        $expire = 86400 * 30;
        header("Pragma: cache");
        header("Last-Modified:" . date('r'));
        header("Expires: " . date('r', time() + $expire));
        header("Cache-Control: static,max-age=$expire");
        header("content-type: image/png");
        readfile($filename);
    }
}
