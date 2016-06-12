<?php
!defined('ROOT_PATH') && exit('ROOT_PATH not defined.');

class base_common_control extends base_control {

    /**
     * @param $conf
     */
    function __construct(&$conf) {
        parent::__construct($conf);
        VI::assign('conf', $conf);
    }

    /**
     * bind list variables from query table
     *
     * @param $list
     * @param $param
     */
    function query_tables(&$list, $param){
        $field_array = array();
        $id_array = array();
        $cache_array = array();
        // build get field
        foreach($param as $table=>$val){
            if(!$field_array[$table]){
                $field_array[$table] = array();
            }
            if(is_array($val)){
                foreach($val as $id_field=>$target_field){
                    $field_array[$table][$id_field] = $target_field;
                }
            }else{
                $field_array[$table]['id'] = $val;
            }
        }

        // find ids
        foreach($list as $key=>$val){
            foreach($field_array as $table=>$fields){
                foreach($fields as $field){
                    $value = $val[$field];
                    $id_array[$field][$value] = $value;
                    $cache_array[$field.$value][] = &$list[$key];
                }
            }
        }
        // find in table
        foreach($field_array as $table=>$fields){
            $where_sql = ' 1 ';
            foreach($fields as $field=>$target_field){
                if($value){
                    $where_sql .= ' AND `'.$field.'` IN(\''.implode("','", $id_array[$target_field]).'\')';
                }
            }
            $sql = 'SELECT * FROM '.DB::table($table).' WHERE '.$where_sql;
            $query_list = DB::fetch_all($sql);
            $result_list = array();
            foreach($query_list as $key=>$val){
                foreach($cache_array[$target_field.$val[$field]] as $k=>&$v){
                    $v[$table] = $val;
                }
            }
        }
    }

    /**
     * show template
     *
     * @param string $template
     * @param string $make_file
     * @param string $charset
     */
    function show($template = '', $make_file = '', $charset = '') {
        // get setting from variable
        $setting = $this->variable->get('setting.json');
        VI::assign('setting', $setting);

        return parent::show($template, $make_file, $charset);
    }

    /**
     * show message page
     *
     * @param     $message
     * @param int $url
     */
    function show_message($message, $url = -1) {
        VI::assign('message', $message);
        VI::assign('url', $url);
        $this->show((IS_MOBILE ? 'mobile/' : '') . 'show_message');
        exit;
    }


    /**
     * json out that can debug
     */
    function show_json($data) {
        VI::assign('data', $data);
        $this->show('show_json');
        exit;
    }

    /**
     * show login form
     */
    function show_login() {
        $this->show('user/login');
        exit;
    }


}

?>