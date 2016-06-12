<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class upload_control extends base_common_control {

    private $conf_file = 'conf/conf.upload.json';

    private $_config = array();

    /**
     * get config
     */
    public function on_config() {
        header("Access-Control-Allow-Origin: *");
        echo json_encode($this->_config());
        exit;
    }

    public function on_uploadimage() {

        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            exit;
        }

        $_conf = $this->_config();
        $config = array(
            "pathFormat" => $_conf['imagePathFormat'],
            "maxSize" => $_conf['imageMaxSize'],
            "allowFiles" => $_conf['imageAllowFiles']
        );
        $fieldName = $_conf['imageFieldName'];
        /* 生成上传实例对象并完成上传 */
        $up = $this->uploader;
        $up->setConfig($fieldName, $config, 'upload');

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        echo json_encode($up->getFileInfo());
        exit;
    }

    /**
     * get _config by json config file
     *
     * @return array|mixed
     */
    public function _config() {
        if (!$this->_config) {
            $json_body = preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(ROOT_PATH . $this->conf_file));
            $this->_config = json_decode($json_body, 1);
        }
        return $this->_config;
    }


}

?>