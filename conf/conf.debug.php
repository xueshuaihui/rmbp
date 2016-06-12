<?php

/**************************************************************************************************
 * 【注意】：
 * 1. 请不要使用 Windows 的记事本编辑此文件！此文件的编码为UTF-8编码，不带有BOM头！
 * 2. 建议使用UEStudio, Notepad++ 类编辑器编辑此文件！
 ***************************************************************************************************/

function get_url_abpath() {
    return substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
}

$app_dir = get_url_abpath() . '/';
$app_dir_reg = preg_quote($app_dir);
$app_host = 'rmbplus.com';
return array(
    //db support： mysql/pdo_mysql/pdo_sqlite(数据库支持:mysql/pdo_mysql/pdo_sqlite)
    'db' => array(
        'mysql' => array(

            /*
            'host' => '123.57.240.34:6033',
                'user' => 'root',
                'pass' => 'Wuhuaguo789',
                'name' => 'rmbplus',
                'charset' => 'utf8',
                'tablepre' => 'plus_',
                'engine' => 'MYISAM',
            */
                'host' => '192.168.1.6',
                'user' => 'root',
                'pass' => '',
                'name' => 'rmbplus',
                'charset' => 'utf8',
                'tablepre' => 'plus_',
                'engine' => 'MYISAM',
        ),
        //other example
        /*
        'pdo_mysql' => array(
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => '',
            'name' =>  'test',
            'charset' => 'utf8',
            'tablepre' => 'bbs_',
            'engine'=> 'MYISAM',
        ),
        'pdo_sqlite' => array(
            'host' => ROOT_PATH.'data/tmp/sqlite_test.db',
            'tablepre' => 'bbs_',
        ),
        */
    ),
    // cache support: memcache/file(缓存支持：memcache/文件缓存)
    'cache' => array(/*
		'memcache' => array(
			'host' => '127.0.0.1:11211',
			'pre' => 'bbs_',
		),
		'file' => array(
			'dir' => ROOT_PATH.'data/cache77249931090a22fc749938d2f45b2f72/',
			'pre' => 'bbs_',
		),
		*/
    ),

    // 唯一识别ID
    'app_id' => 'rmbplus',

    //网站名称
    'app_name' => 'Plus众筹平台',

    // cookie 前缀
    'cookie_pre' => 'rp',

    // cookie 域名
    'cookie_domain' => '.rmbplus.com',//.rmbplus.cn

    //是否开启 gzip
    'gzip' => 0,

    // 应用的绝对路径： 如: http://www.domain.com/bbs/
    'app_url' => 'http://' . $app_host . '/',

    // 应用的所在路径： 如: http://www.domain.com/bbs/
    'app_dir' => $app_dir,

    // CDN 缓存的静态域名，如 http://static.domain.com/
    'static_url' => 'http://' . $app_host . '/static/',

    // 手机地址
    'mobile_url' => 'http://m.rmbplus.com/',
    // 心元项目地址
    'vc_url' => 'cv.rmbplus.',

    // CDN 本地缓存的静态目录，如 http://static.domain.com/
    'static_dir' => ROOT_PATH . 'static/',

    // 应用内核扩展目录，一些公共的库需要打包进 _runtime.php （减少io）
    'core_path' => ROOT_PATH . 'core/',

    // 模板使用的目录，按照顺序搜索，这样可以支持风格切换,结果缓存在 tmp/bbs_xxx.htm.php
    'view_path' => array(ROOT_PATH . 'view/v2/', ROOT_PATH . 'view/'),
//    'view_path' => array(/*ROOT_PATH . 'view/v2/', */ROOT_PATH . 'view/'),

    // 数据模块的路径，按照数组顺序搜索目录
    'model_path' => array(ROOT_PATH . 'model/'),

    // 自动加载 model 的配置， 在 model_path 中未找到 modelname 的时候尝试扫描此项, modelname=>array(tablename, primarykey, maxcol)
    'model_map' => array(),


    // 业务控制层的路径，按照数组顺序搜索目录，结果缓存在 tmp/bbs_xxx_control.class.php
    'control_path' => array(ROOT_PATH . 'control/'),

    // 临时目录，需要可写，可以指定为 linux /dev/shm/ 目录提高速度, 支持 file_put_contents() file_get_contents(), 不支持 fseek(),  SAE: saekv://
    'tmp_path' => ROOT_PATH . 'data/tmp/',

    // 日志目录，需要可写
    'log_path' => ROOT_PATH . 'data/log/',

    // 服务器所在的时区
    'timeoffset' => '+8',

    // 模板支持 static 插件，支持 scss、css、js 打包
    'tpl' => array(
        'plugins' => array(
            'tpl_static' => FRAMEWORK_PATH . 'plugin/tpl_static.class.php',
        ),
    ),

    // 邮件设置
    'mail' => array(
        'api_user' => 'wuhuaguo_test_3ygBbv',
        'api_key' => 'BGFWKaBhgbSJHbuz',
        'from' => 'support@rmbplus.com.cn',
        //'substitution_vars' => '',
        //'subject' => '',
        'template_invoke_name' => '',
        'fromname' => 'plus客服支持 <support@rmbplus.com.cn>',
        'label' => '',
        'resp_email_id' => true,
        'use_maillist' => false,
    ),

    // 手机网关
    'phone' => array(
        //'site' => '【无花果】',
        'token' => 'cdb082167f4e1f201e4d3dfe886f8a0a',
        'sid' => '0055d0b6a59be29af47aff67e6167bca',
        'appId' => 'ad62f36e318040f2b3c21f87720deabb',
        'tpl' => array(
            'register' => 11042,
            'forgot' => 11042,
            'change_phone' => 11042,
        ),
        //'templateId' => 11042,//11042 = reg ,
        'api' => 'http://www.ucpaas.com/maap/sms/code',
    ),

    'yeepay' => array(
        'p1_MerId' => '10012527030',
        'merchantKey' => '2g7x99nG034m4982e677jmSrJ935y59oIn025P41Wg0Ih89gUKQ14663hC5Y',
    ),
    // 开启rewrite
    'url_rewrite' => 0,

    //url rewrite params
    'rewrite_info' => array(
        'comma' => '/', // options: / \ - _  | . ,
        'ext' => '/',// for example : .htm
    ),
    'str_replace' => array(),

    'reg_replace' => array(),
);
	