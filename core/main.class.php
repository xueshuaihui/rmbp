<?php
class main {
	// 全局设置
	static $conf;
	// 当前 url
	static $url;
	// url 根据分隔符切分出来的数组
	static $url_array;
	// rounter 初始化后，编译后的数组
	static $routers;
	//
	static $router_pattern = '#<(?<params>\w+)(?:\:(?<pattern>[^>\:]*))?(?:\:(?<method>[^>]*?))?>#is';
	
	// 初始化 wdj 框架 
	static function init(&$conf){
		self::$conf = $conf;
		self::$url = self::get_url();
		$router_set = self::init_router($conf);
		self::$router_set = $router_set;
	}
	
	// 处理 router
	static function init_router(&$conf){
		$match_set = array();
		foreach($conf['router'] as $pattern => &$sets){
			if(!$sets['name']){
				continue;
			}
			$is_match = 0;
			// 先正则提取 router 中的参数
			preg_match_all(self::$router_pattern, $pattern, $matches);
			// 将参数过滤，只留 pattern
			$complie_pattern = '#^'.preg_replace(self::$router_pattern, '($2)', $pattern).'$#s';
			// 判断当前 url 是否匹配当前 router
			if(!$is_match && preg_match($complie_pattern, self::$url, $url_matches)){
				// 取得当前的 control 和 action 
				// ps. control 必须要初始化才能下一步
				// ps. action 可以不初始化，在 control 初始化的时候进行绑定
				$sets['control'] && $_GET['c'] = $sets['control'];
				isset($sets['action']) && $_GET['a'] = $sets['action'];
				$gets = array();
				// 遍历 url 中每一个参数
				foreach($matches['params'] as $index=>$name){
					if(!isset($url_matches[$index+1])){
						continue;
					}
					$url_value = $url_matches[$index+1];
					// 取得 当前参数需要回调的方法
					$methods = explode('|', $matches['method'] ? $matches['method'][$index] : '');
					if($url_value && $methods){
						foreach($methods as $method){
							switch($method){
								case 'urldecode':
									$url_value = urldecode($url_value);
								break;
								case 'int':
									$url_value = preg_replace('#\D+#', '', $url_value);
								break;
							}
						}
					}
					$gets[$name] = $url_value; 
				}
				// 如果有取到参数
				if($gets){
					// 判断当前的参数是否有必须 require 的
					$check_require = isset($sets['require']) ? $sets['require'] : 0;
					if($check_require){
						$check_require = array_flip($check_require);
						// 缺少参数
						if($check_require && !array_intersect_key($gets, $check_require) == $check_require){
							$conf['page_setting'][404]();
							exit;
						}
					}
					// 合并到 $_GET 变量中
					$_GET = array_merge($_GET, $gets);
				}
				//mark matched
				$is_match = 1;
			}
			
			// 将 url 格式化（用于之后直接用 URL 方法生成 url）
			$url_prefix = preg_replace(self::$router_pattern, '<$1:>', $pattern);
			
			foreach($matches['params'] as &$param){
				$param = array(
					'empty' => strpos($url_prefix, '(?:<'.$param.':>') !== false ? 1 : 0,
					'name' => $param,
				);
			}
			
			self::$routers[$sets['name']] = array_merge($sets, array(
				'complie_pattern' => $complie_pattern,
				'url_prefix' => $url_prefix,
				'params' => $matches['params'],
				'patterns' => $matches['pattern'],
			));
			
			if(DEBUG){
				$_SERVER['router'] = self::$routers[$sets['name']];
			}
			// 返回当前 match 的 rounter 数组
			if($is_match){
				$match_set = &self::$routers[$sets['name']];
			}
		}
		//print_r(self::$routers);exit;
		return $match_set;
	}
	
	// 取得当前地址, 以及初始化 url_array
	static function get_url(){
		static $url = '';
		if($url == ''){
			$url = $_SERVER['REQUEST_URI'];
			while(strpos($url, '//') !== false){
				$url = str_replace('//', '/', $url);
			}
			
			// 当前网站不是根目录的话，需要去掉当前地址里的根目录数据
			$site_dir = self::$conf['app_dir'];
			$dir_len = strlen($site_dir) - 1;
			$url = substr($url, $dir_len);
			
			// ?号后的参数 parse 到 $_GET 中
			$pos = strpos($url, '?');
			$get = '';
			if($pos !== false){
				parse_str(substr($url, $pos+1), $get);
				$url = substr($url, 0, $pos);
			}
			
			// 取得地址分隔符
			$url_temp = substr($url, 1);
			$url_sp = '/';
			// 去除最后的 /
			$url_temp = substr($url_temp, -1, 1) == $url_sp ? substr($url_temp, 0, -1) : $url_temp;
			self::$url_array =  explode($url_sp, $url_temp);
			// parse query string to $_GET
			// unset control and action
			if($get){
				unset($get['a'], $get['c']);
				$_GET = array_merge($get, $_GET);
			}
		}
		return $url;
	}
	
	// 伪静态 url （性能略低，暂时直接在页面中写死链接，ps.需要再优化）
	static function url($name, $params){
		$router = &self::$routers[$name];
		$url = $router['url_prefix'];
		$keys = $router['params'];
		$replace_search = array();
		$replace_to = array();
		foreach($keys as $k=>$v){
			$pat = '<'.$v['name'].':>';
			$v = isset($params[$v['name']]) ? $params[$v['name']] : '';
			$replace_search = $pat;
			$replace_to = $v;
			$url = str_replace($pat, $v, $url);
		}
		
		// 过滤掉正则里的忽略参数
		while($match_pos = strpos($url, '(?:')){
			$url_end = substr($url, $match_pos+4);
			$end_pos = strpos($url_end, ')?');
			if($end_pos !== false){
				$url = substr($url, 0, $match_pos) . substr($url, $match_pos+4+$end_pos+2);
				
			}
		}
		/*
		while($empty_match = self::mask_match($url, '(?:(*))?', true)){
			$replace_to = substr($empty_match, 3, -2);
			if(strpos($replace_to, '<') === 0 && strpos($replace_to, ':') !== false){
				$replace_to = '';
			}
			$url = str_replace($empty_match, $replace_to, $url);
		}
		*/
		
		//过滤没有传入的参数
		/*
		while(strpos($url, '<') !== false && $empty_match = self::mask_match($url, '<(*):>', true)){
			$url = str_replace($empty_match, '', $url);
		}
		*/
		return $url;
	}
}



// 生成 URL 地址， 模板中调用：{URL('rounter_name', array())}
function URL($name, $params = array()){
	return wdj::url($name, $params);
}
?>