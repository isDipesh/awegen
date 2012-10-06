<?php

//collection of helper methods
class Awecms {

    const version = '0.4b';

    public static $tmp;

    public static function powered($stats = false) {
        echo Yii::t('app', 'Powered by') . ' ';
        echo '<a href="http://github.com/awecms" target="_blank">AweCMS</a>.<br />';
        //show page stats when in development mode or when explicitly asked
        if ($stats || YII_DEBUG) {
            echo 'Page generated in ' . round((microtime(TRUE) - YII_BEGIN_TIME), 4) . ' seconds using ' . round(memory_get_peak_usage(true) / 1048576, 2) . ' MB of memory!';
        }
    }

    public static function getPrimaryKey($ar) {
        if (is_numeric($ar))
            return $ar;
        $pk = $ar->getTableSchema()->primaryKey;
        if (is_array($pk)) {
            $pk = $pk[0];
        }

        return $pk;
    }

    public static function getSiteName() {
        if ($name = Settings::get('site', 'name'))
            return $name;
        return Yii::app()->name;
    }

    public static function getTitlePrefix() {
        return ' | ' . Awecms::getSiteName();
    }

    public static function pluralize($singular, $plural, $count) {
        if (!is_integer($count))
            $count = count($count);
        if ($count == 1)
            return $singular;
        return $plural;
    }

    public static function array_to_object($array) {
        $obj = new stdClass;
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $obj->{$k} = Awecms::array_to_object($v); //RECURSION
            } else {
                $obj->{$k} = $v;
            }
        }
        return $obj;
    }

    //removes submit and selection data from POST
    public static function removeMetaFromPost($post) {
        foreach ($post as $key => $item) {
            //unset values for submit buttons - yt0, yt1, yt2, ...
            if (preg_match("/^yt\d+$/", $key))
                unset($post[$key]);
            //unset selectors
            if (preg_match("/^selector_+/", $key))
                unset($post[$key]);
        }
        return $post;
    }

    public static function generateFriendlyName($name) {
        return ucwords(trim((str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
    }

    public static function getCamelCase($str) {
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return lcfirst($str);
    }

    public static function isUrl($url) {
        /* Make sure it's a properly formatted URL. */
        // From: http://www.daniweb.com/web-development/php/threads/290866
        // Scheme
        $url_regex = '^(https?|s?ftp\:\/\/)|(mailto\:)';
        // User and password (optional)
        $url_regex .= '([a-z0-9\+!\*\(\)\,\;\?&=\$_\.\-]+(\:[a-z0-9\+!\*\(\)\,\;\?&=\$_\.\-]+)?@)?';
        // Hostname or IP
        // http://x = allowed (ex. http://localhost, http://routerlogin)
        $url_regex .= '[a-z0-9\+\$_\-]+(\.[a-z0-9\+\$_\-]+)*';
        // http://x.x = minimum
        // $url_regex .= "[a-z0-9\+\$_\-]+(\.[a-z0-9+\$_\-]+)+";
        // http://x.xx(x) = minimum
        // $url_regex .= "([a-z0-9\+\$_\-]+\.)*[a-z0-9\+\$_\-]{2,3}";
        // use only one of the above
        // Port (optional)
        $url_regex .= '(\:[0-9]{2,5})?';
        // Path (optional)
        // $urlregex .= '(\/([a-z0-9\+\$_\-]\.\?)+)*\/?';
        // GET Query (optional)
        $url_regex .= '(\?[a-z\+&\$_\.\-][a-z0-9\;\:@\/&%=\+\$_\.\-]*)?';
        // Anchor (optional)
        // $urlregex .= '(\#[a-z_\.\-][a-z0-9\+\$_\.\-]*)?$';
        return preg_match('/' . $url_regex . '/i', $url);
    }

    public static function typeOf($var) {
        if (is_string($var)) {

            if (preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/', $var))
                return 'email';
            //if url
            if (Awecms::isUrl($var)) {
                //check for image url
                // Parse the url into individual components
                $url_parse = parse_url($var);
                // could be any kind of weird site like an ftp or something, restrict to http and https
                if (($url_parse['scheme'] == 'http') || ($url_parse['scheme'] == 'https')) {
                    // basename() strips off any preceding directories
                    if (isset($url_parse["path"]))
                        $file = pathinfo(basename($url_parse["path"]));
                    if (isset($file['extension']) && in_array($file['extension'], array('jpg', 'png', 'gif', 'jpeg'))) {
                        return 'image_url';
                    }
                }
                return 'url';
            }
            if (strlen($var) > 99)
                return 'textarea';
            return 'textfield';
        }
        return (gettype($var));
    }

    public static function getSelections($post) {
        $return = array();
        foreach ($post as $key => $item) {
            //only process selectors
            if (preg_match("/^selector_(.+)/", $key, $matches)) {
                $return[] = $matches[1];
            }
        }
        return $return;
    }

    public static function generatePairs($array) {
        $return = array();
        foreach ($array as $item) {
            $return[$item] = Awecms::generateFriendlyName($item);
        }
        return $return;
    }

    public static function getControllerIdFromClassName($className) {
        return strtolower(str_replace('Controller', '', $className));
    }

    public static function doesTableExist($tableName) {
        $tableExists = new CDbCommand(Yii::app()->getDb(), "
            show tables like '$tableName'
        ");
        try {
            $exists = $tableExists->queryColumn();
        } catch (Exception $e) {
            $exists = false;
        }
        return $exists ? true : false;
    }

    public static function getScriptName($path) {
        return basename($path, ".php");
    }

    public static function getControllerId($path) {
        return lcfirst(str_replace('Controller', '', basename($path, ".php")));
    }

    public static function formatUrl($url, $inNewTab = false) {
        $value = $url;
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
            $url = 'http://' . $url;
        $htmlOptions = array();
        if ($inNewTab)
            $htmlOptions['target'] = '_blank';
        return CHtml::link(CHtml::encode($value), $url, $htmlOptions);
    }

    public static function getInPair($array) {
        $arrayPair = array();
        foreach ($array as $a) {
            $arrayPair[$a] = ucfirst($a);
        }
        return $arrayPair;
    }

    public static function getModulesInPair() {
        return self::getInPair(Yii::app()->metadata->getModules());
    }

    public static function getModulesWithPath() {
        $arrayPair = array();
        foreach (Yii::app()->metadata->getModules() as $a) {
            $arrayPair['/' . $a] = ucfirst($a);
        }
        return $arrayPair;
    }

    public static function getAllActions() {
        $fullActions = array();
        foreach (Yii::app()->metadata->getModules() as $module) {
            foreach (Yii::app()->metadata->getControllers($module) as $controller) {
//                foreach (Yii::app()->metadata->getActions($controller, $module) as $action) {
//                    print_r($action);
//                }
            }
        }
        die();
        return $fullActions;
    }

    public static function getControllers($module) {
        return array_map('self::getControllerId', Yii::app()->metadata->getControllers($module));
    }

    public static function getControllersInPair($module) {
        $controllers = array_map('self::getControllerId', Yii::app()->metadata->getControllers($module));
        $newControllers = array();
        foreach ($controllers as $controller) {
            if (Yii::app()->getModule($module) && strtolower(Yii::app()->getModule($module)->defaultController) == strtolower($controller)) {
                $default = ucfirst($controller) . ' (Default)';
                $newControllers = array_merge(array($controller => $default), $newControllers);
            } else {
                $newControllers[$controller] = ucfirst($controller);
            }
        }
        return $newControllers;
    }

    public static function getAllActionsInPair() {
        
    }

    public static function getActionsInPair() {
        
    }

    public static function filterMenu($menu) {
        $path = self::removeAdmin(Yii::app()->request->pathInfo);
        foreach ($menu as $key => $item) {
            if (!isset($item['url'][0]))
                continue;
            if ($item['url'][0] == $path) {
                $menu[$key]['url'] = null;
            }
        }
        return $menu;
    }

    public static function removeAdmin($path) {
        if (substr($path, 0, 6) == 'admin/') {
            return substr($path, 5);
        }
        return $path;
    }

    //sort array of objects by an attribute
    //adapted from http://www.algorithmist.com/index.php/Quicksort_non-recursive.php
    public static function quickSort($array, $attribute = 'title') {
        if (!count($array))
            return $array;
        $cur = 1;
        $stack[1]['l'] = 0;
        $stack[1]['r'] = count($array) - 1;
        do {
            $l = $stack[$cur]['l'];
            $r = $stack[$cur]['r'];
            $cur--;

            do {
                $i = $l;
                $j = $r;
                $tmp = $array[(int) ( ($l + $r) / 2 )];

                // partion the array in two parts.
                // left from $tmp are with smaller values,
                // right from $tmp are with bigger ones
                do {
                    while ($array[$i]->$attribute < $tmp->$attribute)
                        $i++;

                    while ($tmp->$attribute < $array[$j]->$attribute)
                        $j--;

                    // swap elements from the two sides
                    if ($i <= $j) {
                        $w = $array[$i];
                        $array[$i] = $array[$j];
                        $array[$j] = $w;

                        $i++;
                        $j--;
                    }
                } while ($i <= $j);

                if ($i < $r) {
                    $cur++;
                    $stack[$cur]['l'] = $i;
                    $stack[$cur]['r'] = $r;
                }
                $r = $j;
            } while ($l < $r);
        } while ($cur != 0);
        return $array;
    }

    //builds tree structure from flat array of objects with parent_id
    //adapted from http://stackoverflow.com/questions/4843945/php-tree-structure-for-categories-and-sub-categories-without-looping-a-query
    public static function buildTree($items) {

        $children = array();

        foreach ($items as $item) {
            $parent_id = ($item->parent_id) ? $item->parent_id : 0;
            $children[$parent_id][] = $item;
        }

        foreach ($items as $item)
            if (isset($children[$item->id]))
                $item->children = $children[$item->id];
        if (count($children))
            return $children[0];
        return array();
    }

    public static function getAllChildren($a) {
//        print_r($a);
        $results = array();
        if (is_array($a)) {
            foreach ($a as $item) {
                $results[] = $item;
                $results = array_merge($results, self::getAllChildren($item));
            }
        } elseif (isset($a->children)) {
            $results[] = $a;
            $results = array_merge($results, self::getAllChildren($a->children));
        } else {
            $results[] = $a;
        }
        return $results;
    }

    public static function summarize($str, $len = 500) {
        $str = str_replace(array('<p>', '</p>', '<br>', '<br />', '<br/>'), ' ', $str);
        $stripped = strip_tags($str, '<br>');
        $str = substr($stripped, 0, $len);
        if (strlen($stripped) > $len + 25)
            $str .= "...";
        else
            $str .= substr($stripped, $len);
        return $str;
    }

    public static function rglob($path = '', $pattern = '*', $flags = 0) {
        $paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
        $files = glob($path . $pattern, $flags);
        foreach ($paths as $path) {
            $files = array_merge($files, self::rglob($path, $pattern, $flags));
        }
        return $files;
    }

    public static function directoryToArray($directory, $recursive) {
        $array_items = array();
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory . "/" . $file)) {
                        if ($recursive) {
                            $array_items = array_merge($array_items, self::directoryToArray($directory . "/" . $file, $recursive));
                        }
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    } else {
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }
        return $array_items;
    }

}