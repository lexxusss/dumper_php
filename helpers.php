<?php
/*=== DD ===*/
if (!function_exists('__d')) {
    /**
     * dump object(s)
     *
     * Possible options:
     *      'limit'  --- recursion limit for debug high-level objects
     *                      (uses just in case when 'dumper'-option has set to 'dumper'-value),
     *                   Possible values:
                            DUMP_LIMIT_1 = 1;
                            DUMP_LIMIT_2 = 2;
                            DUMP_LIMIT_3 = 3;
                            DUMP_LIMIT_4 = 4;
                            DUMP_LIMIT_5 = 5;
                            DUMP_LIMIT_6 = 6;
                            DUMP_LIMIT_7 = 7;
                            DUMP_LIMIT_8 = 8;
                            DUMP_LIMIT_9 = 9;
                            DUMP_LIMIT_10 = 10;
                            DUMP_LIMIT_20 = 20;
                            DUMP_LIMIT_30 = 30;
                            DUMP_LIMIT_50 = 50;
     *      'dumper' --- dumper function which will be dump gotten arguments,
     *                   Possible values:
                            DUMP_DUMPER = '__dumper';
                            DUMP_VAR_DUMP = 'var_dump';
                            DUMP_VAR_EXPORT = 'var_export';
                            DUMP_PRINT_R = 'print_r';
     *                   Value by default: DUMP_DUMPER;
     *
     * Examples:
     *      dd(debug_backtrace(), 'DUMP_LIMIT_5');
     *      dd($action, Yii::app()->user, 'DUMP_VAR_EXPORT');
     *      dd($_POST, $_FILES, $_GLOBAL);
     *      dd($_POST, 'DUMP_PRINT_R');
     *
     */
    function __d()
    {
        $callstack = debug_backtrace();
        $args = func_get_args();

        DDumper::dump($args, $callstack);
    }
}

if (!function_exists('dd')) {
    /**
     * dump object(s) and die
     *
     * Possible options:
     *      'limit'  --- recursion limit for debug high-level objects
     *                      (uses just in case when 'dumper'-option has set to 'DUMP_DUMPER'-value),
     *                   Possible values:
                             DUMP_LIMIT_1 = 1;
                             DUMP_LIMIT_2 = 2;
                             DUMP_LIMIT_3 = 3;
                             DUMP_LIMIT_4 = 4;
                             DUMP_LIMIT_5 = 5;
                             DUMP_LIMIT_6 = 6;
                             DUMP_LIMIT_7 = 7;
                             DUMP_LIMIT_8 = 8;
                             DUMP_LIMIT_9 = 9;
                             DUMP_LIMIT_10 = 10;
                             DUMP_LIMIT_20 = 20;
                             DUMP_LIMIT_30 = 30;
                             DUMP_LIMIT_50 = 50;
     *      'dumper' --- dumper function which will be dump gotten arguments,
     *                   Possible values:
                             DUMP_DUMPER = '__dumper';
                             DUMP_VAR_DUMP = 'var_dump';
                             DUMP_VAR_EXPORT = 'var_export';
                             DUMP_PRINT_R = 'print_r';
     *                   Value by default: DUMP_DUMPER;
     *
     * Examples:
     *      dd(debug_backtrace(), 'DUMP_LIMIT_5');
     *      dd($action, Yii::app()->user, 'DUMP_VAR_EXPORT');
     *      dd($_POST, $_FILES, $_GLOBAL);
     *      dd($_POST, 'DUMP_PRINT_R');
     *
     */
    function dd()
    {
        $callstack = debug_backtrace();
        $args = func_get_args();

        DDumper::dump($args, $callstack);

        die;
    }

    /**
     * CVarDumper is intended to replace the buggy PHP function var_dump and print_r.
     * It can correctly identify the recursively referenced objects in a complex
     * object structure. It also has a recursive depth control to avoid indefinite
     * recursive display of some peculiar variables.
     */
    class DDumper
    {
        private static $_objects = array();
        private static $_output = '';
        private static $_args = array();
        private static $_dumper = '__dumper';
        private static $_depth = 10;

        const DUMP_DUMPER = '__dumper';
        const DUMP_VAR_DUMP = 'var_dump';
        const DUMP_VAR_EXPORT = 'var_export';
        const DUMP_PRINT_R = 'print_r';

        const DUMP_LIMIT_1 = 1;
        const DUMP_LIMIT_2 = 2;
        const DUMP_LIMIT_3 = 3;
        const DUMP_LIMIT_4 = 4;
        const DUMP_LIMIT_5 = 5;
        const DUMP_LIMIT_6 = 6;
        const DUMP_LIMIT_7 = 7;
        const DUMP_LIMIT_8 = 8;
        const DUMP_LIMIT_9 = 9;
        const DUMP_LIMIT_10 = 10;
        const DUMP_LIMIT_20 = 20;
        const DUMP_LIMIT_30 = 30;
        const DUMP_LIMIT_50 = 50;

        public static function dump($args, $callstack)
        {
            self::parseDumpOptions($args);
            self::headerDumpOutput($callstack);
            self::bodyDumpOutput();
        }

        private static function parseDumpOptions($args)
        {
            self::$_args = $args;

            $reflection = new ReflectionClass('DDumper');
            $constants = $reflection->getConstants();

            foreach (self::$_args as $key => $var) {
                if (is_string($var) && array_key_exists($var, $constants)) {
                    if (is_int($constants[$var])) { // set depth
                        self::$_depth = $constants[$var];
                    } else { // set dumper
                        self::$_dumper = $constants[$var];
                    }

                    unset(self::$_args[$key]);
                }
            }
        }

        private static function headerDumpOutput($callstack)
        {
            echo "<hr/><div style='clear: both; background-color: rgb(243, 246, 249)'>";
            $calledFromOutput = "\r\n<span style='color:rgb(116, 114, 19); text-decoration: underline dotted darkslategrey;'>called from: "
                . $callstack[0]['file'] . " : " . $callstack[0]['line'] . "</span>";
            $dumperOutput = "<span style='color:dimgray;'>\r\n<br/>[dumper function]: \"" . self::$_dumper . "()\"</span>";
            $nestingDepthOutput = self::$_dumper == '__dumper' ? "\r\n<br/><span style='color:dimgray;'>[nesting depth]: " . self::$_depth . "</span>" : "";

            echo "\r\n<b><strong>" . $calledFromOutput . $dumperOutput . $nestingDepthOutput . "</strong></b>";
            echo "</div><hr/><br/>";
        }

        private static function bodyDumpOutput()
        {
            echo "\r\n<div style='background-color: rgb(225, 234, 239); border: 2px solid grey; padding: 0px 15px'><pre>";
            foreach (self::$_args as $var) {
                $dumper = self::$_dumper;

                echo self::$dumper($var) . "<br/>";

                self::$_output = '';
            }
            echo "</div></pre>";
        }

        private static function __dumper($var)
        {
            self::dumpInternal($var, 0);

            $result = highlight_string("<?php\n" . self::$_output, true);

            self::$_output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);

            return self::$_output;
        }

        private static function var_dump($var)
        {
            var_dump($var);
        }

        private static function print_r($var)
        {
            print_r($var);
        }

        private static function var_export($var)
        {
            var_export($var);
        }

            /**
         * @param mixed $var variable to be dumped
         * @param integer $level depth level
         */
        private static function dumpInternal($var, $level)
        {
            switch (gettype($var)) {
                case 'boolean':
                    self::$_output .= $var ? 'true' : 'false';
                    break;
                case 'integer':
                    self::$_output .= "$var";
                    break;
                case 'double':
                    self::$_output .= "$var";
                    break;
                case 'string':
                    self::$_output .= "'" . addslashes($var) . "'";
                    break;
                case 'resource':
                    self::$_output .= '{resource}';
                    break;
                case 'NULL':
                    self::$_output .= "null";
                    break;
                case 'unknown type':
                    self::$_output .= '{unknown}';
                    break;
                case 'array':
                    if (self::$_depth <= $level) {
                        self::$_output .= 'array(...)';
                    } elseif(empty($var)) {
                        self::$_output .= 'array()';
                    } else {
                        $keys = array_keys($var);
                        $spaces = str_repeat(' ', $level * 4);
                        self::$_output .= "array (" . count($var) . ")\n" . $spaces . '(';
                        foreach ($keys as $key) {
                            self::$_output .= "\n" . $spaces . '    ';
                            self::dumpInternal($key, 0);
                            self::$_output .= ' => ';
                            self::dumpInternal($var[$key], $level + 1);
                        }
                        self::$_output .= "\n" . $spaces . ')';
                    }
                    break;
                case 'object':
                    if (($id = array_search($var, self::$_objects, true)) !== false) {
                        self::$_output .= get_class($var) . '#' . ($id + 1) . '(...)';
                    } elseif (self::$_depth<=$level) {
                        self::$_output .= get_class($var) . '(...)';
                    } else {
                        $id = array_push(self::$_objects, $var);
                        $className = get_class($var);
                        $members = (array) $var;
                        $spaces = str_repeat(' ', $level * 4);
                        self::$_output .= "$className#$id {" . count($members) . "}\n" . $spaces . '(';
                        foreach ($members as $key => $value) {
                            $keyDisplay = strtr(trim($key), array("\0" => ':'));
                            self::$_output .= "\n" . $spaces . "    [$keyDisplay] => ";
                            self::dumpInternal($value, $level + 1);
                        }
                        self::$_output .= "\n" . $spaces . ')';
                    }

                    break;
            }
        }
    }
}

/*=== /DD ===*/


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

/*=== Array Column ===*/

if (!function_exists('array_column')) {
    /**
     * Works both with arrays and objects
     *
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null) {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }

        if (
            !is_int($params[1]) &&
            !is_float($params[1]) &&
            !is_string($params[1]) &&
            $params[1] !== null &&
            !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (
            isset($params[2]) &&
            !is_int($params[2]) &&
            !is_float($params[2]) &&
            !is_string($params[2]) &&
            !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string)$params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int)$params[2];
            } else {
                $paramsIndexKey = (string)$params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {
            $isObject = is_object($row);

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($isObject) {
                if ($paramsIndexKey !== null &&
                    (property_exists(get_class($row), $paramsIndexKey) ||
                        array_key_exists($paramsIndexKey, get_object_vars($row)))
                ) {
                    $keySet = true;
                    $key = (string) $row->$paramsIndexKey;
                }

                if ($paramsColumnKey === null) {
                    $valueSet = true;
                    $value = $row;
                } elseif (!empty($row) && property_exists(get_class($row), $paramsIndexKey)) {
                    $valueSet = true;
                    $value = $row->$paramsColumnKey;
                }
            } else {
                if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                    $keySet = true;
                    $key = (string) $row[$paramsIndexKey];
                }

                if ($paramsColumnKey === null) {
                    $valueSet = true;
                    $value = $row;
                } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                    $valueSet = true;
                    $value = $row[$paramsColumnKey];
                }
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }

        return $resultArray;
    }
}


    /*--- Alias for array_column ---*/
if (!function_exists('arrayColumn')) {
    /**
     * Works for arrays and objects as well
     *
     * @param null $input
     * @param null $columnKey
     * @param null $indexKey
     *
     * @return array|bool|null
     */
    function arrayColumn($input = null, $columnKey = null, $indexKey = null) {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }

        if (
            !is_int($params[1]) &&
            !is_float($params[1]) &&
            !is_string($params[1]) &&
            $params[1] !== null &&
            !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (
            isset($params[2]) &&
            !is_int($params[2]) &&
            !is_float($params[2]) &&
            !is_string($params[2]) &&
            !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string)$params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int)$params[2];
            } else {
                $paramsIndexKey = (string)$params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {
            $isObject = is_object($row);

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($isObject) {
                if ($paramsIndexKey !== null &&
                    (property_exists(get_class($row), $paramsIndexKey) ||
                        array_key_exists($paramsIndexKey, get_object_vars($row)))
                ) {
                    $keySet = true;
                    $key = (string) $row->$paramsIndexKey;
                }

                if ($paramsColumnKey === null) {
                    $valueSet = true;
                    $value = $row;
                } elseif (!empty($row) && property_exists(get_class($row), $paramsIndexKey)) {
                    $valueSet = true;
                    $value = $row->$paramsColumnKey;
                }
            } else {
                if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                    $keySet = true;
                    $key = (string) $row[$paramsIndexKey];
                }

                if ($paramsColumnKey === null) {
                    $valueSet = true;
                    $value = $row;
                } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                    $valueSet = true;
                    $value = $row[$paramsColumnKey];
                }
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }

        return $resultArray;
    }
}
    /*--- /Alias for array_column ---*/

/*=== /Array Column ===*/
