<?php

!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class index_control extends base_common_control {

    function __construct(&$conf) {
        parent::__construct($conf);
    }

    public function on_index() {
        // select from project isverify >=2
        $temp_list = $this->project->select(' isverify>=2 and isverify!=6 ', 'sortid DESC,dateline DESC', 10);
        $project_list = array();
        foreach ($temp_list as $project) {
            $this->project->format($project);
            $project_ids[] = $project['id'];
            $project_list[$project['id']] = $project;
        }
        unset($temp_list);

        // get banner from attachment
        if ($project_ids) {
            $attachment_list = $this->attachment->get_by_ids($project_ids, 'project', 'banner');
            foreach ($attachment_list as $attachment) {
                $project_list[$attachment['idsource']]['banner'] = $attachment;
            }
        }
        VI::assign('project_list', $project_list);
        $banner_list = $this->attachment->select(array('idtype' => 'index_banner'), ' type asc');
        VI::assign('banner_list', $banner_list);

        //最新资讯
        $article_message = $this->article->get_article(" WHERE a.type='news' AND category='资讯' ORDER BY dateline desc limit 0,5");
        VI::assign('article_message',$article_message);

        //政策动态
        $article_policy = $this->article->get_article(" WHERE a.type='news' AND category='政策动态' ORDER BY dateline desc limit 0,5");
        VI::assign('article_policy',$article_policy);

        $this->show('index');
    }
}

?>