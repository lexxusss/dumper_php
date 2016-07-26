<?php
if (!function_exists('dd')) {
    /**
     * dump object(s) and die
     *
     * Possible options set at the array like an argument (case sensitive):
     *      'limit'  --- recursion limit for debug high-level objects
     *                      (uses just in case when 'dumper'-option has set to 'dumper'-value),
     *                   Possible values: form 1 till INFINITY,
     *                   Value by default: 100;
     *      'dumper' --- dumper function which will be dump gotten arguments,
     *                   Possible values: 'print_r', 'var_export', 'var_dump', 'dumper'
     *                      (or user's own dumper-function),
     *                   Value by default: 'var_dump';
     *
     * Examples:
     *      dd(debug_backtrace(), ['limit' => 3, 'dumper' => 'dumper']);
     *      dd($action, Yii::app()->user, ['dumper' => 'print_r']);
     *      dd($_POST, $_FILES, $_GLOBAL);
     *      dd($_POST, ['dumper' => 'var_export']);
     *
     */
    function dd()
    {
        $callstack = debug_backtrace();
        $args = func_get_args();
        $limit = 100;
        $dumper = 'var_dump';

        parseDumpOptions($args, $limit, $dumper);

        headerDumpOutput($callstack, $limit, $dumper);
        bodyDumpOutput($args, $limit, $dumper);

        die;
    }

    function parseDumpOptions(&$args, &$limit, &$dumper)
    {
        foreach ($args as $key => $var) {
            if (is_array($var)) {
                if (array_key_exists('limit', $var) && is_int($var['limit'])) {
                    $limit = $var['limit'];
                    $removeVar = true;
                }
                if (array_key_exists('dumper', $var) && is_string($var['dumper'])) {
                    $dumper = $var['dumper'];
                    $removeVar = true;
                }

                if (isset($removeVar)) {
                    unset($args[$key], $removeVar);
                }
            }
        }
    }

    function headerDumpOutput($callstack, $limit, $dumper)
    {
        $calledFromOutput = "\r\ncalled from: " . $callstack[0]['file'] . " : " . $callstack[0]['line'];
        $dumperOutput = "\r\n<br/>[dumper function]: \"" . $dumper . "()\"";
        $nestingDepthOutput = $dumper == 'dumper' ? "\r\n<br/>[nesting depth]: " . $limit : "";

        echo "\r\n<b><strong>" . $calledFromOutput . $dumperOutput . $nestingDepthOutput . "</strong></b>";
    }

    function bodyDumpOutput($args, $limit, $dumper)
    {
        echo "\r\n<br/><pre>";
        foreach ($args as $var) {
            if ($dumper == 'dumper') {
                $dumper($var, false, $limit);
            } else {
                $dumper($var);
            }
        }
        echo "</pre>";
    }

    /**
     * Dump object
     *
     * @param $obj
     * @param bool $showCalled
     * @param integer $recursionLimit
     */
    function dumper($obj, $showCalled = true, $recursionLimit = 100)
    {
        if ($showCalled) {
            $callstack = debug_backtrace();
            echo "<strong>called from: " . $callstack[0]['file'] . " : " . $callstack[0]['line'] . "</strong>";
        }

        echo htmlspecialchars(dumperGet($obj, '', 0, $recursionLimit)) . "<br/>";
    }

    /**
     * Recursion dumper
     *
     * @param $obj
     * @param string $leftSp
     * @return bool|string
     * @param integer $recursionLevel
     * @param integer $recursionLimit
     */
    function dumperGet(&$obj, $leftSp = "", $recursionLevel = 0, $recursionLimit = 100)
    {
        if (is_array($obj)) {
            $type = "Array[" . count($obj) . "]";
        } elseif (is_object($obj)) {
            $type = "Object";
        } elseif (gettype($obj) == "boolean") {
            return $obj ? true : false;
        } else {
            return "\"$obj\"";
        }

        $buf = $type;

        if ($recursionLevel++ < $recursionLimit) {
            $leftSp .= "	";
            foreach ($obj as $k => $v) {
                if ($k !== "GLOBALS") {
                    $buf .= "\n$leftSp$k => " . dumperGet($v, $leftSp, $recursionLevel, $recursionLimit);
                }
            }
        }

        return $buf;
    }
}

/**
 * Check if the directory is empty
 *
 * @param $dir
 * @return bool
 */
function is_dir_empty($dir) {
    foreach (new DirectoryIterator($dir) as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }

        return false;
    }

    return true;
}
