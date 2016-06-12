<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class help_control extends base_common_control {

    private $article_id = 0;

    function __construct(&$conf) {
        parent::__construct($conf);
        $this->article_id = core::R('a:int');
        $_GET['a'] = 'index';
    }

    public function on_index() {
        $list = $this->_assign_help();

        //assign default article_id
        if (!$this->article_id) {
            $this->article_id = $list[0]['id'];
        }

        if ($this->article_id) {
            $article = $this->article->get($this->article_id);
            if (!$article) {
                $this->conf['page_setting'][404]();
            }
            VI::assign('article', $article);

        }

        $this->show('help_index.htm');
    }

    public function _assign_help() {
        $article_list = DB::select('article:id,dateline,title,content', array('disabled' => 0, 'category_id' => 35), ' sortid DESC');
        VI::assign('article_list', $article_list);
        return $article_list;
    }

}

?>