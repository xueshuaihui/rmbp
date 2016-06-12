<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-08-19
 * Time: 21:05
 */
!defined('ROOT_PATH') && exit('Access Denied');

class attachment extends base_db {
    /**
     * @var string
     */
    private $config_file = 'conf/conf.upload.json';

    /**
     * @var array
     */
    private $config = array();


    function __construct() {
        parent::__construct('attachment', 'id');
    }


    public function get_by_ids($ids, $idtype, $type = '', $limit = -1) {
        if(is_array($ids)){
            $where_array = 'idtype=\'' . $idtype . '\' AND idsource IN(\'' . implode("','", $ids) . '\')';
        }else{
            $where_array = 'idtype=\'' . $idtype . '\' AND idsource =\'' . $ids . '\'';
        }

        if ($type) {
            $where_array .= ' AND `type`=\'' . $type . '\'';
        }
        if ($limit > 0) {
            $limit = ' LIMIT ' . $limit;
        }
        $attachment_list = $this->select($where_array, 0, $limit);
        return $attachment_list;
    }

    /**
     * get by project id and type
     *
     * @param     $id
     * @param     $type
     * @param int $perpage
     * @return mixed
     */
    public function get_by_type($id, $idtype, $type, $perpage = 0) {
        $where_array = array('idtype' => $idtype, 'idsource' => $id);
        if ($type) {
            $where_array['type'] = $type;
        }
        $attachment = $this->select($where_array, 0, $perpage);
        return $attachment;
    }

    /**
     * generate attachment path
     *
     * @param $idtype
     * @param $type
     * @return string
     */
    public function get_path($idtype, $type) {
        $static_dir = core::$conf['static_dir'];
        $dir = 'upload/' . $idtype . '/';
        !is_dir($static_dir . $dir) && mkdir($static_dir . $dir, 0777, 1);
        $file = ($type ? $type . '_' : '') . core::S('time') . rand(0, 9999);
        return $dir . $file;
    }


    /**
     * upload image
     *
     * @param        $data
     * @param        $user_id
     * @param        $idtype
     * @param        $idsource
     * @param        $type
     * @param string $path
     * @param string $description
     * @param int    $check_exists
     * @param        $id
     * @return array
     */
    public function upload_image($data, $user_id, $idtype, $idsource, $type, $path = '', $description = '', $check_exists = 1, $id = '') {
        return $this->upload_base64_image($data, $user_id, $idtype, $idsource, $type, $path, $description, $check_exists,$id);
    }

    /**
     * upload by base64
     *
     * @param        $data
     * @param        $user_id
     * @param        $idtype
     * @param        $idsource
     * @param        $type
     * @param string $path
     * @param string $description
     * @param int    $check_exists
     * @param        $id
     * @return array
     */
    public function upload_base64_image($data, $user_id, $idtype, $idsource, $type, $path = '', $description = '', $check_exists = 1,$id = '') {
        // check upload_image
        if (is_string($data)) {
            if (strpos($data, 'data:') === 0) {
                list(, $data) = explode(',', $data, 2);
            }
            $file_body = base64_decode($data);
        } else if (is_array($data)) {
            $file_body = file_get_contents($data['tmp_name']);
        }
        $file_ext = $this->get_img_ext($file_body);
        $file_dir = core::$conf['static_dir'];
        $attachment = array(
            'isimage' => 1,
            'filesize' => strlen($file_body),
            'dateline' => core::S('time'),
            'type' => $type,
            'idtype' => $idtype,
            'idsource' => $idsource,
            'user_id' => $user_id,
            'description' => $description,
            'path' => $path ? $path : $this->get_path($idtype, $type) . '.' . $file_ext,
        );
        switch ($file_ext) {
            case 'gif':
            case 'jpg':
            case 'png':
            case 'bmp':
            case 'ico':
            break;
            default:
                return false;
            break;
        }
        if ($check_exists) {
            if($id){
                $exists = $this->select(array('id' => $id),0,0);
            }else{
                $exists = $this->get_by_type($idsource, $idtype, $type);
            }
            if ($exists) {
                unlink($file_dir . $exists['path']);
            }
        }
        // put content
        file_put_contents($file_dir . $attachment['path'], $file_body);

        // check update or insert
        if ($exists) {
            $this->update($attachment, array('id' => $exists['id']));
            $attachment['id'] = $exists['id'];
        } else {
            $attachment['id'] = $this->insert($attachment, 1);
        }
        return $attachment;
    }

    //文章banner
    public function banner_path($data,$pic_type){
        if (is_string($data)) {
            if (strpos($data, 'data:') === 0) {
                list(, $data) = explode(',', $data, 2);
            }
            $file_body = base64_decode($data);
        } else if (is_array($data)) {
            $file_body = file_get_contents($data['tmp_name']);
        }
        $file_ext = $this->get_img_ext($file_body);
        $file_dir = core::$conf['static_dir'];
        $path = $this->get_path($pic_type, '') . '.' . $file_ext;

        file_put_contents($file_dir . $path, $file_body);
        return $path;
    }

    /**
     * get image type by data
     *
     * @param $data
     * @return string
     */
    private function get_img_ext($data) {
        $tempDir = sys_get_temp_dir();
        $tempExtension = '.upload';
        $tempFile = tempnam($tempDir, $tempExtension);
        $tempStream = fopen($tempFile, "w");
        fwrite($tempStream, $data);
        fclose($tempStream);
        $image_info = getimagesize($tempFile);
        unlink($tempFile);
        $image_type = $image_info[2];
        switch ($image_type) {
            case IMAGETYPE_GIF:
                return 'gif';
            case IMAGETYPE_JPEG:
                return 'jpg';
            case IMAGETYPE_PNG:
                return 'png';
            case IMAGETYPE_BMP:
                return 'bmp';
            case IMAGETYPE_ICO:
                return 'ico';
            default:
                return '';
        }
    }


}