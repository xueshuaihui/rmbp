<?php
/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015-08-22
 * Time: 20:27
 */

/**
 * get financing percent
 *
 * @param $max
 * @param $current
 */
function get_percent($min = 0, $current = 0) {
    $percent = round($current * 100 / ($min * 10000)) / 100;
    if ($percent > 99) {
        $percent = floor($percent);
    } else {
        $percent = round($percent);
    }
    return $percent;
}

/**
 * get left time for project
 *
 * @param int $expire_time
 * @return float|int
 */
function get_left_time($expire_time = 0) {
    $left_time = $expire_time - core::S('time');
    $left_time = $left_time > 0 ? floor($left_time / 86400) : 0;
    return $left_time;
}


function avatar($uid, $attribute = '', $check_exists = 1) {
    $avatar = 'avatar/' . substr($uid, -2) . '/' . $uid . '.png';
    if ($check_exists && !is_file(core::$conf['static_dir'] . $avatar)) {
        $avatar = 'avatar/no.png';
    }
    if ($attribute) {
        $avatar = core::$conf['static_url'] . $avatar;
        return '<img src="' .$avatar . '" ' . ($attribute == 1 ? '' : $attribute) . ' />';
    } else {
        return $avatar;
    }
}

function replace_content($content, $auto_show = 2) {
    $gray_img = core::$conf['static_url'] . 'images/gray.png';
    preg_match_all('/(<img[^>]*?)src=([^>]*?>)/is', $content, $img_list);
    if (count($img_list[1]) >= $auto_show) {
        foreach ($img_list[1] as $k => $v) {
            if ($k >= $auto_show) {
                $content = str_replace($img_list[0][$k], $v . 'src="' . $gray_img . '" data-src=' . $img_list[2][$k], $content);
            }
        }
    }
    return $content;
}

//屏蔽html
function check_html($html) {
    $html = stripslashes($html);
    preg_match_all("/<([^<]+)>/is", $html, $ms);
    $searches[] = '<';
    $replaces[] = '&lt;';
    $searches[] = '>';
    $replaces[] = '&gt;';
    if ($ms[1]) {
        $allow_tags = 'img|a|font|div|table|tbody|caption|tr|td|th|br' .
            '|p|b|strong|i|u|em|span|ol|ul|li|blockquote' .
            '|object|param|embed';//允许的标签
        $ms[1] = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searches[] = "&lt;" . $value . "&gt;";
            $value = str_replace('&', '__tmp_str__', $value);
            $value = core::htmlspecialchars($value);
            $value = str_replace('__tmp_str__', '&', $value);

            $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
            $skip_keys = array(
                'onabort', 'onactivate', 'onafterprint', 'onafterupdate',
                'onbeforeactivate', 'onbeforecopy', 'onbeforecut',
                'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste',
                'onbeforeprint', 'onbeforeunload', 'onbeforeupdate',
                'onblur', 'onbounce', 'oncellchange', 'onchange',
                'onclick', 'oncontextmenu', 'oncontrolselect',
                'oncopy', 'oncut', 'ondataavailable',
                'ondatasetchanged', 'ondatasetcomplete', 'ondblclick',
                'ondeactivate', 'ondrag', 'ondragend',
                'ondragenter', 'ondragleave', 'ondragover',
                'ondragstart', 'ondrop', 'onerror', 'onerrorupdate',
                'onfilterchange', 'onfinish', 'onfocus', 'onfocusin',
                'onfocusout', 'onhelp', 'onkeydown', 'onkeypress',
                'onkeyup', 'onlayoutcomplete', 'onload', 'ontouch',
                'onlosecapture', 'onmousedown', 'onmouseenter',
                'onmouseleave', 'onmousemove', 'onmouseout',
                'onmouseover', 'onmouseup', 'onmousewheel',
                'onmove', 'onmoveend', 'onmovestart', 'onpaste',
                'onpropertychange', 'onreadystatechange', 'onreset',
                'onresize', 'onresizeend', 'onresizestart',
                'onrowenter', 'onrowexit', 'onrowsdelete',
                'onrowsinserted', 'onscroll', 'onselect',
                'onselectionchange', 'onselectstart', 'onstart',
                'onstop', 'onsubmit', 'onunload', 'javascript',
                'script', 'eval', 'behaviour', 'expression',
                'class', 'nowrap',
            );
            $skipstr = implode('|', $skip_keys);
            $value = preg_replace("/($skipstr)/i", '.', $value);
            if (!preg_match('#^[/|\s]?(' . $allow_tags . ')(\s+|$)#is', $value)) {
                $value = '';
            }
            $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
        }
    }
    $html = str_replace($searches, $replaces, $html);
    //$html = addslashes($html);

    return $html;
}

/**
 * human money
 *
 * @param        $num
 * @param string $format
 * @return string
 */
function human_money($num, $format = '%.2f%s') {
    if ($num >= 10000) {
        return str_replace('.00', '', sprintf($format, $num / 10000, '万'));
    } else {
        return str_replace('.00', '', sprintf($format, $num, '元'));
    }
}

/**
 * hash url id
 *
 * @param        $id
 * @param string $action
 * @return string
 */
function url_id($id, $action = 'encode') {
    if (!$id) {
        return '';
    }
    $re = array('/', '+', '=');
    $tr = array('@', ',', '');
    if ($action == 'encode') {
        $sid = base_convert($id, 10, 36);
        $sid = substr($id, 0, 1) . $sid;
        $sid = substr($id, -1) . base64_encode($sid);
        return str_replace($re, $tr, $sid);
    } else {
        $id = str_replace($tr, $re, $id);
        $hash_char1 = substr($id, 0, 1);
        $id = base64_decode(substr($id, 1));
        $hash_char2 = substr($id, 0, 1);
        $id = substr($id, 1);
        $sid = base_convert($id, 36, 10);
        if (!$sid) {
            return 0;
        }
        if ($hash_char2 == substr($sid, 0, 1) && $hash_char1 == substr($sid, -1)) {
            return $sid;
        } else {
            return 0;
        }
    }
}

/**
 * url num
 *
 * @param     $id
 * @param int $max_len
 * @return string
 */
function url_num($id, $max_len = 8) {
    $id_len = strlen($id . "");
    if ($id_len < $max_len) {
        return str_repeat('0', $max_len - $id_len) . $id;
    }
    return $id;
}

/**
 * get article url
 *
 * @param     $article
 * @param int $page
 * @return string
 */
function article_url($article, $page = 1) {
    if ($article['id']) {
        if ($page == 1) {
            return core::$conf['app_dir'] . 'article/' . date('Ymd', $article['dateline']) . '/' . url_num($article['id']) . '.html';
        } else {
            return core::$conf['app_dir'] . 'article/' . date('Ymd', $article['dateline']) . '/' . url_num($article['id']) . '/' . $page . '.html';
        }
    } else {
        return core::$conf['app_dir'] . 'article/list/';
    }
}

/**
 * get article desc
 *
 * @param     $desc
 * @param int $length
 * @return string
 */
function article_desc($desc, $length = 100) {
    $desc = trim(spider::no_html($desc));
    $desc = mb_substr($desc, 0, 100);
    return $desc;
}

/**
 * trade id
 *
 * @warning years only support 2036
 *
 * @param $trade
 */
function trade_id($trade, $max_length = 4) {
    $length = strlen($trade['id']);
    $time = base_convert(date('y', $trade['dateline']), 10, 36) . date('md', $trade['dateline']);
    $trade_id = base_convert($trade['id'], 10, 16);
    $trade_id = str_repeat('0', $max_length - strlen($trade_id)) . $trade_id;
    return strtoupper($time . $trade_id);
}


?>