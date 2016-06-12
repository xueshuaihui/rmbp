<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class var_control extends admin_common_control {

    public function on_index() {
        $this->_save();

        $this->show('admin/var_index');
    }

    public function _save() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $setting = core::P('setting');
            $this->variable->set('setting', json_encode($setting));
        }
    }

    /*
     * banner图显示页面
     */
    public function on_banner() {
        $banner_list = $this->attachment->select(array('idtype' => 'index_banner'), ' type asc');
        VI::assign('banner_list', $banner_list);
        $this->show('admin/var_banner');
    }

    /*
     * 新增、编辑banner图显示页面
     */
    public function on_banner_edit() {
        $id = core::R('id:int');
        if ($id) {
            $list = $this->attachment->select(array('id' => $id), 0, 0);
            VI::assign('list', $list);
        }
        $this->show('admin/var_banner_edit');
    }

    /*
     * banner图新增、编辑动作
     */
    public function on_update_banner() {
        $img_data = $_FILES['pic'];
        $data = core::P('data');
        $data['dateline'] = time();
        if ($img_data['name']) {
            $this->attachment->upload_image($img_data, $this->U->user_id, 'index_banner', $data['idsource'], $data['type'], '', $data['description'], 1, $data['id']);
        } else {
            $this->attachment->update($data, $data['id']);
        }
        $this->show_message('操作成功', $this->conf['app_dir'] . ADMIN_DIR . '/var/banner/');
    }

    /*
     * 删除banner图
     */
    public function on_delete_banner() {
        $id = core::R('id');
        $source = $this->attachment->get($id);
        if (!$source) {
            $this->json('该记录不存在', 0);
        }
        //删除banner图片
        $res = $this->attachment->select(array('id' => $id), 0, 0);
        if ($res['path']) {
            $file_dir = core::$conf['static_dir'];
            unlink($file_dir . $res['path']);
        }

        $this->attachment->delete($id);
        $this->json('操作成功', 0);
    }

}

?>