<?php
// 应用公共文件

/**
 * Session管理
 * @param string $name session名称
 * @param mixed $value session值
 * @return mixed
 */
function session($name = '', $value = '')
{
    if (is_null($name)) {
        // 清除
        Session::clear();
    } elseif ('' === $name) {
        return Session::all();
    } elseif (is_null($value)) {
        // 删除
        $name = config("session")['key_prefix'] . $name;
        Session::delete($name);
    } elseif ('' === $value) {
        // 判断或获取
        $name = config("session")['key_prefix'] . $name;
        return 0 === strpos($name, '?') ? Session::has(substr($name, 1)) : Session::get($name);
    } else {
        // 设置
        $name = config("session")['key_prefix'] . $name;
        Session::set($name, $value);
        // release下session有问题，先直接save
        Session::save();
    }
}

/**
 * Notes：加密函数
 * User: suchenyi
 * DateTime: 2021-11-29 17:58:29
 * @param $str 加密前的字符串
 * @return mixed
 */
function encrypt($str)
{
    //密钥
    $key       = config('system')['pass_auth'];
    $coded     = '';
    $keylength = strlen($key);

    for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength) {
        $coded .= substr($str, $i, $keylength) ^ $key;
    }
    return str_replace('=', '', base64_encode($coded));
}

/**
 * 无限极分类，实现具有父子关系的数据分类
 * @param $parent_id 子类的父类ID
 * @param $level   层级
 * @param $list   静态变量
 * @return mixed
 */
function get_Tree($arr, $pid = 0, $level = 1)
{
    static $list = array();
    foreach ($arr as $value) {
        if ($value['pid'] == $pid) {
            $value['level'] = $level;
            $list[]         = $value;
            get_Tree($arr, $value['id'], $level + 1);
        }
    }
    return $list;
}

/**
 * 数组转换字符串（以逗号隔开）
 * @param
 * @return
 * @author Michael_xu
 */
function arrayToString($array)
{
    if (!is_array($array)) {
        $data_arr[] = $array;
    } else {
        $data_arr = $array;
    }
    $data_arr = array_filter($data_arr); //数组去空
    $data_arr = array_unique($data_arr); //数组去重
    $data_arr = array_merge($data_arr);
    $string   = $data_arr ? ',' . implode(',', $data_arr) . ',' : '';
    return $string ?: '';
}

/**
 * Notes：获取IP
 * User: wang.delin
 * DateTime: 2021-11-29 17:58:39
 * @return string
 */
function getRealIP()
{
    $forwarded = request()->header("x-forwarded-for");
    if ($forwarded) {
        $ip = explode(',', $forwarded)[0];
    } else {
        $ip = request()->ip();
    }
    return $ip;
}

/**
 * provide a Java style exception trace
 * @param \Throwable $exp
 * @param ?array $seen - array passed to recursive calls to accumulate trace lines already seen
 *                     leave as NULL when calling this function
 * @return string array of strings, one entry per trace line
 * @link https://www.php.net/manual/en/exception.gettraceasstring.php#114980
 */
function trace_exception(\Throwable $exp, ?array $seen = null)
{
    if (null === $seen) {
        $seen    = [];
        $starter = '';
    } else {
        $starter = 'Caused by: ';
    }

    $result   = [];
    $file     = $exp->getFile();
    $line     = $exp->getLine();
    $trace    = $exp->getTrace();
    $prev     = $exp->getPrevious();
    $result[] = sprintf('%s%s: %s', $starter, get_class($exp), $exp->getMessage());

    while (true) {
        $current = "{$file}:{$line}";
        if (in_array($current, $seen)) {
            $result[] = sprintf(' ... %d more', count($trace) + 1);
            break;
        }

        $seen[]   = $current;
        $result[] = sprintf(
            ' at %s%s%s(%s%s%s)',
            !empty($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
            !empty($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
            !empty($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
            $file,
            $line === null ? '' : ':',
            $line === null ? '' : $line
        );

        if (empty($trace)) {
            break;
        }

        $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
        $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
        array_shift($trace);
    }

    $result = join(PHP_EOL, $result);
    if ($prev) {
        $result .= PHP_EOL . trace_exception($prev, $seen);
    }

    return $result;
}

/**
 * 获取 module，controller，action
 *
 * @return array 数组元素依次为 module, controller, action
 * @example
 * ```php
 * list($m, $c, $a) = \getMCA();
 * ```
 */
function getMCA(): array
{
    $m     = $c = $a = '';
    $route = \request()->rule()->getRoute();
    if (is_string($route) && strpos($route, '@')) {
        if (false !== ($index = strrpos($route, '\\'))) {
            $controller = substr($route, $index + 1);
            if (strpos($controller, '@')) {
                list($c, $a) = explode('@', $controller, 2);
                $m = substr($route, 0, $index);
                if (0 === stripos($m, 'app\controller\\')) {
                    $m = substr($m, 15);
                }
            }
        }
    }

    return [$m, $c, $a];
}

/**
 * Notes：获取当前时间毫秒
 * User: suchenyi
 * DateTime: 2021-11-29 17:58:39
 * @return float
 */
function msectime()
{
    list($usec, $sec) = explode(" ", microtime());
    $cn_time = date('YmdHis', time()) . round($usec * 1000);
    return $cn_time;
}
