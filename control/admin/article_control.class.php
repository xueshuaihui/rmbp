<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class article_control extends admin_common_control {

    /*
     * 文章、帮助分类 页面显示
     */
    public function on_category() {
        $type = core::R('type');
        $category_list = $this->article_category->get_article_category(array('type' => $type));
        VI::assign('category_list', $category_list);
        VI::assign('type', $type);
        $this->show('admin/article_category');
    }

    /*
     *文章、帮助列表 页面显示
     */
    public function on_list() {
        $type = core::R('type');
        $page = max(core::R('page:int'), 1);
        $perpage = 20;
        $article_list = $this->article->get_list_by_type($type, '', $perpage, $page);
        VI::assign('article_list', $article_list);
        $article_count = $this->article->get_list_by_type($type, '', -2);
        $page_html = misc::pages($article_count, $perpage, $page, $this->conf['app_dir'] . ADMIN_DIR . '/article/list/type/' . $type . '/page/%d');
        VI::assign('page_html', $page_html);
        VI::assign('type', $type);
        $this->show('admin/article_list');

    }

    /*
     * 文章、帮助分类的 新建、编辑页面显示
     */
    public function on_category_edit() {
        $type = core::R('type');
        $id = core::R('id:int');
        if ($id) {
            $category = $this->article_category->get_article_category(array('id' => $id), 0, 0);
            VI::assign('category', $category);
        }
        VI::assign('type', $type);
        VI::assign('id', $id);
        $this->show('admin/category_edit');
    }

    /*
     *文章、帮助列表的 新建、编辑页面显示
     */
    public function on_article_edit() {
        $type = core::R('type');
        $id = core::R('id:int');
        if ($id) {
            $article = $this->article->get($id);
            $label = json_decode($article['label'], 1);
            VI::assign('label', $label);
            VI::assign('article', $article);
        }
        $article_select = $this->article_category->get_article_category(array('type' => $type));
        VI::assign('article_select', $article_select);

        if ($type == 'news') {
            $article_select2 = $this->article_category->get_article_category(array('type' => 'label'));
            VI::assign('article_select2', $article_select2);
        }
        VI::assign('id', $id);
        VI::assign('type', $type);
        $this->show('admin/article_edit');
    }

    /*
     * 文章、帮助分类的 新建、编辑动作
     */
    public function on_category_action() {
        $id = core::P('id');
        $type = core::P('type');
        if ($id) {
            $data = array(
                'category' => core::P('category'),
                'sortid' => core::P('sortid'),
                'disabled' => core::P('disabled')
            );
            $this->article_category->update_article_category($data, array('id' => $id));
        } else {
            $data = array(
                'type' => core::P('type'),
                'category' => core::P('category'),
                'sortid' => core::P('sortid'),
                'disabled' => core::P('disabled')
            );
            $this->article_category->add_article_category($data);
        }
        $this->show_message('操作成功', $this->conf['app_dir'] . ADMIN_DIR . '/article/category/type/' . $type);
        exit;
    }

    /*
     * 文章、帮助列表的 新建、编辑动作
     */
    public function on_acticle_action() {
        $id = core::P('id');
        $type = core::P('type');
        $path = core::P('path');
        $img_path = core::P('paths');
        $label = core::P('label') ? explode(',', str_replace(' ', ',', core::P('label'))) : array();
        if ($label) {
            foreach ($label as $label_val) {
                $label_val = trim($label_val);
                if (!$label_val) {
                    continue;
                }
                $res = $this->article_category->select(array('category' => $label_val, 'type' => 'label'), 0, 0);
                if (!$res) {
                    $this->article_category->insert(array('category' => $label_val, 'type' => 'label'));
                }
                $res_label = $this->article_category->select(array('category' => $label_val, 'type' => 'label'), 0, 0);
                if ($res_label) {
                    $labels[$res_label['id']] = $res_label['category'];
                }
            }
        }
        $label_ids = array_keys($labels);
        $label_text = core::json_encode($labels);
        $keys = core::P('keys');
        if ($path) {
            $path = $this->attachment->banner_path($path, 'article_banner');
            if ($img_path) {
                $file_dir = core::$conf['static_dir'];
                unlink($file_dir . $img_path);
            }
        } else {
            $path = $img_path;
        }
        if ($id) {
            $data = array(
                'category_id' => core::P('category_id'),
                'title' => core::P('title'),
                'content' => core::P('editorValue'),
                'disabled' => core::P('disabled'),
                'updatetime' => time(),
                'sortid' => core::P('sortid'),
                'source' => core::P('source'),
                'sourcename' => core::P('sourcename'),
                'label' => $label_text,
                'keys' => $keys,
                'type' => $type,
                'path' => $path,
            );
            $article = $this->article->get($id);
            //所属标签下的文章数量+1
            $ids_list = $this->article_tag->select(array('article_id' => $id), 0, -1, 0);
            $label_old = array();
            $label_new = $label_ids;
            foreach ($ids_list as $article_tag) {
                $label_old[] = $article_tag['category_id'];
            }
            $minus = array_diff($label_old, $label_new);//已去掉标签
            $plus = array_diff($label_new, $label_old);//后添加标签
            //编辑时文章去掉的标签 文章数量-1
            $remove_ids = array();
            foreach ($minus as $val) {
                if ($val) {
                    $res = $this->article_category->select(array('id' => $val), 0, 0);
                    $articlenum = $res['articlenum'] - 1;
                    $this->article_category->update(array('articlenum' => $articlenum), array('id' => $val));
                    $remove_ids[] = $val;
                }
            }
            if ($remove_ids) {
                $sql = 'DELETE FROM ' . DB::table('article_tag') . ' WHERE article_id=\'' . $id . '\' AND category_id IN(\'' . implode("','", $remove_ids) . '\')';
                DB::query($sql);
            }
            //编辑时文章添加的标签 文章数量+1
            $sortid = $this->article->get_sortid($article);
            foreach ($plus as $val) {
                if ($val) {
                    $res = $this->article_category->select(array('id' => $val), 0, 0);
                    $articlenum = $res['articlenum'] + 1;
                    $this->article_category->update(array('articlenum' => $articlenum), array('id' => $val));
                    $this->article_tag->replace(array('category_id' => $val, 'article_id' => $id, 'sortid' => $sortid));
                }
            }
            $this->article->update_article($data, core::P('id'));
        } else {
            $data = array(
                'title' => core::P('title'),
                'user_id' => $this->U->user_id,
                'sortid' => core::P('sortid'),
                'category_id' => core::P('category_id'),
                'disabled' => core::P('disabled'),
                'content' => core::P('editorValue'),
                'dateline' => time(),
                'updatetime' => '0',
                'source' => core::P('source'),
                'sourcename' => core::P('sourcename'),
                'label' => $label_text,
                'keys' => $keys,
                'type' => $type,
                'path' => $path,
            );
            $sortid = $this->article->get_sortid($data);
            $id = $this->article->add_article($data);
            //所属标签下的文章数量+1
            if ($label_ids) {
                $sql = 'UPDATE  ' . DB::table('article_category') . ' SET articlenum=articlenum+1 WHERE id IN(\'' . implode("','", $label_ids) . '\')';
                DB::query($sql);
                foreach ($label_ids as $category_id) {
                    $article_tag = array(
                        'article_id' => $id,
                        'category_id' => $category_id,
                        'sortid' => $sortid,
                    );
                    $this->article_tag->insert($article_tag);
                }
            }
        }
        $this->show_message('操作成功', $this->conf['app_dir'] . ADMIN_DIR . '/article/list/type/' . $type);
        exit;
    }

    /*
     * 删除文章
     */
    public function on_delete_article() {
        $id = core::R('id:int');
        $source = $this->article->get($id);
        if (!$source) {
            $this->json('该记录不存在', 0);
        }
        //删除文章对应的banner图片
        $res = $this->article->select(array('id' => $id), 0, 0);
        if ($res['path']) {
            $file_dir = core::$conf['static_dir'];
            unlink($file_dir . $res['path']);
        }
        //删除该文章对应的article_tag表数据
        $this->article_tag->delete(array('article_id' => $id));
        //删除该文章
        $this->article->delete($id);
        $this->json('操作成功', 0);
    }

    /*
    * 删除文章分类
    */
    public function on_delete_article_category() {
        $id = core::R('id:int');
        $source = $this->article_category->get($id);
        if (!$source) {
            $this->json('该记录不存在', 0);
        }
        $this->article_category->delete($id);
        $this->json('操作成功', 0);
    }

}

?>