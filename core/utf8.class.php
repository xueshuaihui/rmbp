<?php

if(!defined('FRAMEWORK_UTF8')) {
	if(extension_loaded('mbstring')) {
		mb_internal_encoding('UTF-8');
		define('FRAMEWORK_UTF8', TRUE);
	} else {
		define('FRAMEWORK_UTF8', FALSE);
	}
}
define ('UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define ('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define ('UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF));
define ('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
define ('UTF8_BOM' , chr(0xEF) . chr(0xBB) . chr(0xBF));


class utf8 {
	public static function substr($str, $offset, $length = NULL) {
		if(FRAMEWORK_UTF8) {
			return mb_substr($str, $offset, $length, 'UTF-8');
		}
		if (self::is_ascii($str)) {
			return ($length === NULL) ? substr($str, $offset) : substr($str, $offset, $length);
		}
		
		$str    = (string) $str;
		$strlen = self::strlen($str);
		$offset = (int) ($offset < 0) ? max(0, $strlen + $offset) : $offset;
		$length = ($length === NULL) ? NULL : (int) $length;
	
		if ($length === 0 OR $offset >= $strlen OR ($length < 0 AND $length <= $offset - $strlen)) {
			return '';
		}
	
		if ($offset == 0 AND ($length === NULL OR $length >= $strlen)) {
			return $str;
		}
	
		$regex = '^';
	
		if ($offset > 0) {
			$x = (int) ($offset / 65535);
			$y = (int) ($offset % 65535);
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= ($y == 0) ? '' : '.{'.$y.'}';
		}
	
		if ($length === NULL)
		{
			$regex .= '(.*)';
		} elseif ($length > 0) {
			$length = min($strlen - $offset, $length);
	
			$x = (int) ($length / 65535);
			$y = (int) ($length % 65535);
			$regex .= '(';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= '.{'.$y.'})';
		} else {
			$x = (int) (-$length / 65535);
			$y = (int) (-$length % 65535);
			$regex .= '(.*)';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= '.{'.$y.'}';
		}
	
		preg_match('/'.$regex.'/us', $str, $matches);
		return $matches[1];
	}
	
	public static function cutstr_cn($s, $len, $more = '...') {
		$n = strlen($s);
		$r = '';
		$rlen = 0;
		
		// 32, 64
		$UTF8_1 = 0x80;
		$UTF8_2 = 0x40;
		$UTF8_3 = 0x20;
		
		for($i=0; $i<$n; $i++) {
			$c = '';
			$ord = ord($s[$i]);
			if($ord < 127) {
				$rlen++;
				$r .= $s[$i];
			} elseif(($ord & $UTF8_1)  && ($ord & $UTF8_2) && ($ord & $UTF8_3)) {
				// 期望后面的字符满足条件,否则抛弃	  && ord($s[$i+1]) & $UTF8_2
				if($i+1 < $n && (ord($s[$i+1]) & $UTF8_1)) {
					if($i+2 < $n && (ord($s[$i+2]) & $UTF8_1)) {
						$rlen += 2;
						$r .= $s[$i].$s[$i+1].$s[$i+2];
					} else {
						$i += 2;
					}
				} else {
					$i++;
				}
			}
			if($rlen >= $len) break;
		}
		
		$n > strlen($r) && $r .= $more;
	
		return $r;
	}
	
	// 安全截取，防止SQL注射
	public static function safe_substr($str, $offset, $length = NULL) {
		$str = self::substr($str, $offset, $length);
		$len = strlen($str) - 1;
		if($len >=0) {
			if($str[$len] == '\\') $str[$len] = '';
		}
		return $str;
	}
	
	public static function is_ascii($str) {
		return !preg_match('/[^\x00-\x7F]/S', $str);
	}
	
	public static function strlen($str) {
		if(FRAMEWORK_UTF8) {
			return mb_strlen($str);
		}
		if(self::is_ascii($str)) {
			return strlen($str);
		} else {
			return strlen(utf8_decode($str));
		}
	}
	
	public static function get_utf_encoding(&$text) {
		$first2 = substr($text, 0, 2);
		$first3 = substr($text, 0, 3);
		$first4 = substr($text, 0, 3);
		if ($first3 == UTF8_BOM) return 'UTF-8';
		elseif ($first4 == UTF32_BIG_ENDIAN_BOM) return 'UTF-32BE';
		elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) return 'UTF-32LE';
		elseif ($first2 == UTF16_BIG_ENDIAN_BOM) return 'UTF-16BE';
		elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) return 'UTF-16LE';
		return '';
	}
	
	//统一转换到utf8
	public static function iconv($data, $output = 'utf-8') {
		$charset = self::get_utf_encoding($data);
		if($charset){
			return mb_convert_encoding($data, $output, $charset);
		}
		$encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5', 'JIS','eucjp-win','sjis-win','EUC-JP');
		$encoded = mb_detect_encoding($data, $encode_arr);
		return mb_convert_encoding($data, $output, $encoded);
	}
}
?>