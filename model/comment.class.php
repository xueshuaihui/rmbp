<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015/8/29
 * Time: 16:22
 */
!defined('ROOT_PATH') && exit('Access Denied');

class comment extends base_db {

    function __construct() {
        parent::__construct('comment', 'id');
    }


    /**
     * get project comment
     *
     * @param $project_id
     * @return array
     */
    function get_project_comment($project_id) {
        $sql = 'SELECT * FROM ' . DB::table('comment') .
            ' WHERE project_id=\'' . $project_id . '\'  AND `level`=0 AND `isreply`=1';
        $comment_list = array();
        $comment_pids = array();
        $query = DB::query($sql);
        while ($comment = DB::fetch($query)) {
            $comment_pids[] = $comment['id'];
            $comment_list[$comment['id']] = $comment;
        }
        if ($comment_pids) {
            $sql = 'SELECT * FROM ' . DB::table('comment') .
                ' WHERE project_id=\'' . $project_id . '\' AND `level`=1 AND pid IN(\'' . implode("','", $comment_pids) . '\')';
            $query = DB::query($sql);
            while ($comment = DB::fetch($query)) {
                $comment_list[$comment['pid']]['reply'] = $comment;
            }
        }
        return $comment_list;

    }


    /**
     * remove comment
     * @param $id
     */
    function delete_by_comment($id){
        $sql = 'DELETE FROM '.DB::table('comment').' WHERE id=\''.$id.'\'';
        DB::query($sql);
        $sql = 'DELETE FROM '.DB::table('comment').' WHERE pid=\''.$id.'\'';
        DB::query($sql);
    }

}