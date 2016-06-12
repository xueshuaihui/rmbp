<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class article_control extends base_common_control {


    function __construct(&$conf) {
        parent::__construct($conf);
        $id = core::R('a:int');
        if ($id) {
            $_GET['a'] = 'detail';
        }
    }


    /**
     * extract page id
     *
     * @return array
     */
    function get_id() {
        $gets = $_GET;
        unset($gets['a'], $gets['c']);
        foreach ($gets as $id => $val) {
            // 0000001 => 1.html
            if ($val && strpos($val, '.html') > 0) {
                $page = str_replace('.html', '', $val);
                return array('id' => $id, 'page' => $page);
            } else if (strpos($id, '.html') > 0) {
                // 0000001.html
                $id = str_replace('.html', '', $id);
                list($id, $page) = explode('_', $id);
                if (is_numeric($page) && is_numeric($id)) {
                } elseif (is_numeric($page)) {
                    $id = $page;
                    $page = 1;
                } elseif (is_numeric($id)) {
                    $id = $id;
                    $page = 1;
                } else {
                    $page = 1;
                    $id = 0;
                }
                return array('id' => $id, 'page' => $page);
            }


        }
    }

    /*
     * 文章列表
     */
    public function on_list() {
        $page = max(core::R('page:int'), 1);
        $page_url = $this->conf['app_dir'] . '?c=article-list';
        $label_id = max(core::R('label:int'), 0);
        $cid = max(core::R('cid:int'), 0);
        $type = 'news';
        $article_count = 0;
        $perpage = 10;
        $where_sql = ' 1';
        if ($label_id) {
            //取当前标签名称
            $labelname = $this->article_category->get($label_id);
            if (!$labelname['id']) {
                $this->show_message('没有找到该标签，请返回确认');
            }
            VI::assign('labelname', $labelname);

            $article_ids = $this->article_tag->select(array('category_id' => $label_id), 'sortid DESC', $perpage, $page);
            $article_count = $this->article_tag->select(array('category_id' => $label_id), 0, -2);
            $id_list = array();
            foreach ($article_ids as $article_tag) {
                $id_list[] = $article_tag['article_id'];
            }
            if ($id_list) {
                $where_sql .= ' AND id IN(\'' . implode("','", $id_list) . '\')';
            } else {
                $where_sql .= ' AND 1=2';
            }
            $page_url .= '&label_id=' . $label_id;
        } else if ($cid) {
            //取当前标签名称
            $category = $this->article_category->get($cid);
            if (!$category['id']) {
                $this->show_message('没有找到该分类，请返回确认');
            }
            VI::assign('category', $category);
            $where_sql .= ' AND `category_id`=\'' . $cid . '\'';
            $article_count = $this->article->select($where_sql, 0, -2);
            $page_url .= '&cid=' . $cid;
        } else {
            $where_sql .= ' AND `type`=\'' . $type . '\'';
            $article_count = $this->article->select($where_sql, 0, -2);
            $page_url .= '';
        }

        //所属标签下的所有文章
        $article_list = $this->article->select($where_sql, 0, $perpage, $page);
        $page_html = misc::pages($article_count, $perpage, $page, $page_url . '&page=%d');
        VI::assign('page_html', $page_html);
        VI::assign('article_list', $article_list);

        //取五个热门标签
        $hot_list = $this->article_category->hot_list(6);
        VI::assign('hot_list', $hot_list);

        $this->_right();

        $this->show('v2/article/list.htm');
    }

    /*
     * 文章详情
     */
    public function on_detail() {
        $info = $this->get_id();
        $id = $info['id'];
        $page = $info['page'];
        if (!$id) {
            $this->show_message('没有找到该文章，请返回确认');
        }
        $article = $this->article->get($id);
        if (!$article || $article['disabled']) {
            $this->show_message('没有找到该文章，请返回确认');
        }
        // format
        $article['label'] = json_decode($article['label'], 1);
        // get category
        $category = $this->article_category->get($article['category_id']);
        $article['category'] = $category['category'];

        // get content
        $page = max($page, 1);
        $contents = explode('[[page]]', $article['content']);
        if (!isset($contents[$page - 1])) {
            $this->show_message('没有找到该文章，请返回确认');
        }
        $article['content'] = $contents[$page - 1];
        $page_url = article_url($article, '%d');
        // multi page
        $page_html = misc::pages(count($contents), 1, $page, $page_url, array(
            'curr' => ' [%d] ',
            'first' => '',
            'total' => '',
            'last' => '',
        ));

        VI::assign('article', $article);
        VI::assign('page_html', $page_html);

        $this->_right();

        // update click
        $this->article->update(array('clicknum' => $article['clicknum'] + 1), array('id' => $id));

        $keys = explode(',', $article['keys']);
        $search_key = array_pop($keys);
        if ($search_key) {
            $related_list = $this->article->select(' `keys` LIKE \'' . $search_key . '%\'', 'dateline DESC', 20, 1);
        }
        if (!$related_list || count($related_list) == 1) {
            $related_list = $this->article->select(array('category_id' => $article['category_id']), 'dateline DESC', 20, 1);
        }
        VI::assign('related_list', $related_list);
        $this->show('v2/article/detail.htm');
    }

    /**
     *文章列表和详情的公共部分
     */
    public function _right() {
        //取前五个项目内容及banner
        $temp_list = $this->project->select(' isverify>=2 and isverify!=6', 'sortid DESC,dateline DESC', 5);
        $project_list = array();
        $project_ids = array();
        foreach ($temp_list as $project) {
            $project_ids[] = $project['id'];
            $project_list[$project['id']] = $project;
        }
        unset($temp_list);
        if ($project_ids) {
            $attachment_list = $this->attachment->get_by_ids($project_ids, 'project', 'banner');
            foreach ($attachment_list as $attachment) {
                $project_list[$attachment['idsource']]['banner'] = $attachment;
            }
        }
        VI::assign('project_list', $project_list);

        //取点击率最高的五个文章
        $click_list = $this->article->hot_list(10);
        VI::assign('click_list', $click_list);
    }

}

?>