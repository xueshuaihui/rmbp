<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class index_control extends mobile_common_control {

    function __construct(&$conf) {
        parent::__construct($conf);
    }

    public function on_index() {
        // select from project isverify >=2
        $temp_list = $this->project->select(' isverify>=2 and isverify!=6 ', 'sortid DESC,dateline DESC', 10);
        $project_list = array();
        foreach ($temp_list as $project) {
            $project_ids[] = $project['id'];
            $this->project->format($project);
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

        $this->show('mobile/index');
    }
}

?>