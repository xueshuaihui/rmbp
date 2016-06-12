<?php
!defined('FRAMEWORK_PATH') && exit('FRAMEWORK_PATH not defined.');

class index_control extends vc_common_control {

    function __construct(&$conf) {
        parent::__construct($conf);
    }

    public function on_index() {
        //isverify=-3

        $area_list = $this->project_area->group_by_area_id('p.isverify IN(-3,-4)');
        $area_ids = array();
        $tmp_area_list = array();
        foreach ($area_list as $area) {
            $area_ids[] = $area['area_id'];
            $tmp_area_list[$area['area_id']] = array(
                'area_id' => $area['area_id'],
                'count' => $area['C'],
            );
        }
        $temp_list = $this->area->select(array('id' => $area_ids));

        foreach ($temp_list as $area) {
            $tmp_area_list[$area['id']] = array_merge($area, $tmp_area_list[$area['id']]);
        }

        $area_list = $tmp_area_list;


        $region_list = array();
        $temp_list = $this->project->select(array('isverify' => array(-3, -4)), 'sortid DESC,dateline DESC', -1);
        $project_list = array();
        foreach ($temp_list as $project) {
            $project_ids[] = $project['id'];
            $this->project->format($project);
            $project_list[$project['id']] = $project;
            $region_list[$project['region']] = 1;
        }
        unset($temp_list);

        $area_project_list = $this->project_area->select(array('project_id' => $project_ids));
        foreach ($area_project_list as $k => $v) {
            $area_ids = &$project_list[$v['project_id']]['area_ids'];
            if (!$area_ids) {
                $area_ids = array();
            }
            $area_ids[] = $v['area_id'];
        }

        // get banner from attachment
        if ($project_ids) {
            $attachment_list = $this->attachment->get_by_ids($project_ids, 'project', 'banner');
            foreach ($attachment_list as $attachment) {
                $project_list[$attachment['idsource']]['banner'] = $attachment;
            }
        }

        VI::assign('project_list', $project_list);
        VI::assign('area_list', $area_list);
        VI::assign('region_list', $region_list);

        $this->show('vc/index');
    }
}

?>