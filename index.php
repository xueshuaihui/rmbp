<?php
// cli mode
if (isset($argc) && $argc) {
    // env 最后一个参数
    $_SERVER['ENV'] = array_pop($argv);
    $argc -= 1;
    define('IS_CMD', 1);
}

$_SERVER['ENV'] = isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'debug';
// 调试模式: 0:关闭; 1:调试模式; 参数开启调试, URL中带上：rmbplus_debug
define('DEBUG', ((isset($argc) && $argc) || strstr($_SERVER['REQUEST_URI'], 'rmbplus_debug')) ? 1 : 0);
// 站点根目录
define('ROOT_PATH', dirname(__FILE__) . '/');
// 框架的物理路径
define('FRAMEWORK_PATH', ROOT_PATH . 'mzphp/');
// 404
$page_setting = array(
    404 => function ($control = '') {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        include('404.htm');
        exit;
    },
);

if (!($conf = include(ROOT_PATH . 'conf/conf.' . $_SERVER['ENV'] . '.php'))) {
    $page_setting[404]();
}

// 错误页面设置
$conf['page_setting'] = isset($conf['page_setting']) ? array_merge($page_setting, $conf['page_setting']) : $page_setting;

$conf['env'] = $_SERVER['ENV'];

// 手机版本
if (isset($_SERVER['HTTP_HOST'])) {
    if ($conf['mobile_url'] && strpos($conf['mobile_url'], '://' . $_SERVER['HTTP_HOST']) !== false) {
        define('CONTROL_PATH', './control/mobile/');
        define('IS_MOBILE', 1);
    } elseif ($conf['vc_url'] && strpos('//' . $_SERVER['HTTP_HOST'], $conf['vc_url']) !== false) {
        define('CONTROL_PATH', './control/vc/');
    }
}
if(!defined('IS_MOBILE')){
    define('IS_MOBILE', 0);
}
if (defined('CONTROL_PATH')) {
    $conf['control_path'] = array(CONTROL_PATH);
}
// 核心扩展目录
if (isset($conf['core_path'])) {
    define('FRAMEWORK_EXTEND_PATH', $conf['core_path']);
}

// 临时目录
define('FRAMEWORK_TMP_PATH', $conf['tmp_path']);

// 日志目录
define('FRAMEWORK_LOG_PATH', $conf['log_path']);

//扩展核心目录（该目录文件会一起打包入 runtime.php 文件）
//define('FRAMEWORK_EXTEND_PATH', ROOT_PATH.'model/');

// 包含核心框架文件，转交给框架进行处理。
include FRAMEWORK_PATH . 'mzphp.php';

core::run($conf);
?>