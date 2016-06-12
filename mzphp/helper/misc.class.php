<?php
if (!defined('FORM_HASH_KEY')) {
    define('FORM_HASH_KEY', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'mzphp');
}

class misc {

    /**
     * redirect url
     *
     * @param     $url
     * @param int $code
     */
    public static function R($url, $code = 301) {
        ob_end_clean();
        header('Location: ' . $url, true, $code);
        exit;
    }

    /**
     * get form hash
     *
     * @param string $auth_key
     * @return string
     */
    public static function form_hash($auth_key = '') {
        return substr(md5(substr($_SERVER['time'], 0, -5) . $auth_key), 16);
    }

    /**
     * check form hash
     *
     * @param string $auth_key
     * @return bool
     */
    public static function form_submit($auth_key = '') {
        $hash = core::gpc('FORM_HASH', 'R');
        return $hash == self::form_hash($auth_key);
    }


    /**
     * return current url(include path)
     *
     * @return string
     */
    public static function get_url_path() {
        $port = core::gpc('SERVER_PORT', 'S');
        $host = core::gpc('HTTP_HOST', 'S');    // host 里包含 port
        $path = substr(core::gpc('PHP_SELF', 'S'), 0, strrpos(core::gpc('PHP_SELF', 'S'), '/'));
        $http = (($port == 443) || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')) ? 'https' : 'http';
        return "$http://$host$path/";
    }

    /**
     * get current_uri
     *
     * @return string
     */
    public static function get_uri() {
        $port = core::gpc('SERVER_PORT', 'S');
        //$portadd = $port == 80 ? '' : ':80';
        $host = core::gpc('HTTP_HOST', 'S');
        //$schme = self::gpc('SERVER_PROTOCOL', 'S');

        // [SERVER_SOFTWARE] => Microsoft-IIS/6.0
        // [REQUEST_URI] => /index.php
        // [HTTP_X_REWRITE_URL] => /?a=b
        // iis
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
        } else {
            $request_uri = $_SERVER['REQUEST_URI'];
        }
        $http = (($port == 443) || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')) ? 'https' : 'http';
        return "$http://$host" . $request_uri;
        //if(isset($_SERVER['SCRIPT_URI']) && 0) {
        //	return $_SERVER['SCRIPT_URI'];// 会漏掉 query_string, .core::gpc('QUERY_STRING', 'S');
        //}
    }

    /**
     * get human date
     *
     * @param        $timestamp
     * @param string $date
     * @return bool|string
     */
    public static function human_date($timestamp, $date = 'Y-m-d') {
        $seconds = $_SERVER['time'] - $timestamp;
        if ($seconds > 31536000) {
            return date($date, $timestamp);
        } elseif ($seconds >= 2592000) {
            return round($seconds / 2592000) . ' 月前';
        } elseif ($seconds >= 86400) {
            return round($seconds / 86400) . ' 天前';
        } elseif ($seconds >= 3600) {
            return round($seconds / 3600) . ' 小时前';
        } elseif ($seconds >= 60) {
            return round($seconds / 60) . ' 分钟前';
        } else {
            return $seconds . ' 秒前';
        }
    }

    /**
     * get human number
     * @param $num
     * @return string
     */
    public static function human_num($num) {
        $num > 100000 && $num = ceil($num / 10000) . '万';
        return $num;
    }

    /**
     * get human size
     * @param $num
     * @return string
     */
    public static function human_size($num) {
        if ($num > 1073741824) {
            return number_format($num / 1073741824, 2, '.', '') . 'G';
        } elseif ($num > 1048576) {
            return number_format($num / 1048576, 2, '.', '') . 'M';
        } elseif ($num > 1024) {
            return number_format($num / 1024, 2, '.', '') . 'K';
        } else {
            return $num . 'B';
        }
    }

    /**
     * dump hex
     * @param        $data
     * @param string $newline
     */
    public static function hexdump($data, $newline = "\n") {
        static $from = '';
        static $to = '';

        static $width = 16; // 每行宽度
        static $pad = '.';
        if ($from === '') {
            for ($i = 0; $i <= 0xFF; $i++) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width * 2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $offset = 0;
        foreach ($hex as $i => $line) {
            echo sprintf('%6X', $offset) . ' : ' . implode(' ', str_split($line, 2)) . ' [' . $chars[$i] . ']' . $newline;
            $offset += $width;
        }
    }


    public static function is_robot() {
        $robots = array('robot', 'spider', 'slurp');
        foreach ($robots as $robot) {
            if (strpos(core::gpc('HTTP_USER_AGENT', 'S'), $robot) !== FALSE) {
                return TRUE;
            }
        }
        return FALSE;
    }


    public static function is_writable($file) {
        // 主要是兼容 windows
        try {
            if (is_file($file)) {
                if (strpos(strtoupper(PHP_OS), 'WIN') !== FALSE) {
                    $fp = @fopen($file, 'rb+');
                    @fclose($fp);
                    return (bool)$fp;
                } else {
                    return is_writable($file);
                }
            } elseif (is_dir($file)) {
                $tmpfile = $file . '/____tmp.tmp';
                $n = @file_put_contents($tmpfile, 'a');
                if ($n > 0) {
                    unlink($tmpfile);
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }


    //获取文件名后缀
    public static function ext($filename) {
        return strtolower(trim(substr(strrchr($filename, '.'), 1)));
    }

    // 替代 scandir, safe_mode
    public static function scandir($dir, $exts = array()) {
        if (!is_dir($dir)) {
            return array();
        }
        //opendir is performance than scandir
        $df = opendir($dir);
        $arr = array();
        $search_ext = !empty($exts) && is_array($exts) ? 1 : 0;
        while (false !== ($file = readdir($df))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $find = false;
            if ($search_ext) {
                //check
                if (in_array(self::ext($file), $exts)) {
                    $arr[] = $file;
                }
            } else {
                $arr[] = $file;
            }
        }
        closedir($df);
        return $arr;
    }

    // 递归删除目录，这个函数比较危险，传参一定要小心
    public static function rmdir($dir, $keepdir = 0) {
        if ($dir == '/' || $dir == '../') return FALSE;// 不允许删除根目录，避免程序意外删除数据。
        if (!is_dir($dir)) return FALSE;
        substr($dir, -1, 1) != '/' && $dir .= '/';
        $files = self::scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $filepath = $dir . $file;
            if (!is_dir($filepath)) {
                try {
                    unlink($filepath);
                } catch (Exception $e) {
                }
            } else {
                self::rmdir($filepath . '/');
            }
        }
        try {
            if (!$keepdir) rmdir($dir);
        } catch (Exception $e) {
        }
        return TRUE;
    }


    //分页
    public static function pages($num = -1, $perpage, $curpage, $mpurl, $options = array()) {
        $page = 8;
        $multipage = '';
        $realpages = 1;
        $options = array_merge(array(
            'curr' => '<strong>%d</strong>',
            'first' => '首页',
            'last' => '尾页',
            'prev' => '上一页',
            'next' => '下一页',
            'totals' => '共 <strong>%d</strong> 页',
            'wrap' => '%s',
        ), $options);
        if ($num == -1 || $num > $perpage) {
            if ($num > 0) {
                $offset = 2;
                $realpages = @ceil($num / $perpage);
                $pages = $realpages;
                if ($page > $pages) {
                    $from = 1;
                    $to = $pages;
                } else {
                    $from = $curpage - $offset;
                    $to = $from + $page - 1;
                    if ($from < 1) {
                        $to = $curpage + 1 - $from;
                        $from = 1;
                        if ($to - $from < $page) {
                            $to = $page;
                        }
                    } elseif ($to > $pages) {
                        $from = $pages - $page + 1;
                        $to = $pages;
                    }
                }
            }
            $multipage = '';

            if ($num == 0) {
                $multipage .= "" . sprintf($options['curr'], $curpage) . " ";
            }
            if ($options['first']) {
                $multipage .= sprintf($options['wrap'], "<a href=\"" . sprintf($mpurl, 1) . "\">" . $options['first'] . "</a>");
            }
            if ($options['prev']) {
                if ($curpage > 1) {
                    $multipage .= sprintf($options['wrap'], "<a href=\"" . sprintf($mpurl, $curpage - 1) . "\">" . $options['prev'] . "</a>");
                }
            }
            if ($num > 0) {
                for ($i = $from; $i <= $to; $i++) {
                    if ($i == $curpage) {
                        $multipage .= sprintf($options['wrap'], "<a href='javascript:void(0);' class='current'>" . sprintf($options['curr'], $i) . "</a>");
                    } else {
                        $multipage .= sprintf($options['wrap'], "<a href=\"" . sprintf($mpurl, $i) . "\">" . sprintf(strip_tags($options['curr']), $i) . "</a>");
                    }
                }
            }

            if ($options['next']) {
                if ($curpage + 1 <= $realpages || $num == 0) {
                    $multipage .= sprintf($options['wrap'], "<a href=\"" . sprintf($mpurl, $curpage + 1) . "\">" . $options['next'] . "</a>");
                }
            }

            if ($multipage && $num > 0) {
                $multipage .= sprintf($options['wrap'], "<a href='javascript:void(0);' class='total'>" .sprintf($options['totals'], $realpages). "</a>");
            }

            if ($to < $pages || $num > 0) {
                if ($options['last']) {
                    $multipage .= sprintf($options['wrap'], "<a href=\"" . sprintf($mpurl, $pages) . "\">" . $options['last'] . "</a>");
                }
            }
        }
        return $multipage;
    }
}

?>