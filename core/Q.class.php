<?php
class Q {
	public static function query($sql_params, $cache_key, $cache_time, $no_data_no_save = 0, $must_update = 0){
		global $conf;
		$cache_open = CACHE::opened();
		static $cache_serv_time = NULL;
		if(is_null($cache_serv_time)){
			if(isset($_SERVER['time'])){
				$cache_serv_time = $_SERVER['time'];
			}else{
				$cache_serv_time = time();
			}
		}
		$reload = true;
		$list = array();
		//补sql_前缀
		if($cache_key){
			$cache_key = $conf['app_id'].'sql_'.$cache_key;
		}
		//
		if($cache_key && $cache_time && $cache_open && !$must_update){
			$list = CACHE::get($cache_key);
			if($list && ($cache_time == -1  || $cache_serv_time - $list['cache_time'] < $cache_time)) {
				$reload = false;
				$list = $list['list'];
			}
		}
		
		//只取，不需要查询
		if($sql_params == 'get') {
			return $list;
		}
		$def_sql = array(
						'perpage' => -1, 
						'page' => 1,
						'where' => '',
						'order' => '',
					);
		$sql_params = array_merge($def_sql, $sql_params);
		// 重新加载 query
		if($reload){
			$list = DB::select($sql_params['table'], $sql_params['where'], $sql_params['order'], $sql_params['perpage'], $sql_params['page']);
			//没有数据的时候不保存
			if($no_data_no_save && !$list){
				return $list;
			}
			
			//写入
			if($cache_key && $cache_open){
				CACHE::set($cache_key,
							array(
								'cache_time'	=> $cache_serv_time, 
								'list'			=> $list
							), 
							$cache_time);
			}
		}
		return $list;
	}
	
	// delete
	public static function delete_query($key){
		global $conf;
		if(!CACHE::opened()){
			return false;
		}
		$cache_key = $conf['app_id'].'sql_'.$key;
		CACHE::delete($cache_key);
	}
}

?>