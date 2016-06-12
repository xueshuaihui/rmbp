<?php
/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-09-16
 * Time: 15:54
 */
define('MAX_TIME', 86400 * 7);

class sitemap_control extends base_common_control {

    function on_index() {
        //问答链接
        $sql = 'SELECT id,dateline FROM `plus_article` WHERE type=\'news\' LIMIT 0,500';
        $res = DB::fetch_all($sql);
        $list = array();
        foreach($res as $val){
            $list[] = array('url' =>'/help/'.$val['id'].'/', 'dateline'=> $val['dateline']);
        }

        //项目链接
        $sql2 = 'SELECT id,dateline FROM `plus_project` WHERE isverify IN (2,3,4,5,6) LIMIT 0,500';
        $res2 = DB::fetch_all($sql2);
        foreach($res2 as $val2){
            $list[] = array('url' =>'/project/detail/'.url_id($val2['id']).'/', 'dateline'=> $val['dateline']);
        }

        //文章链接
        $sql3 = 'SELECT id,dateline FROM `plus_article` WHERE type=\'news\' LIMIT 0,1000';
        $res3 = DB::fetch_all($sql3);
        foreach($res3 as $val3){
            $list[] = array('url' =>article_url($val3), 'dateline'=> $val['dateline']);
        }
        VI::assign('list',$list);

        $this->show('sitemap');
    }


}
