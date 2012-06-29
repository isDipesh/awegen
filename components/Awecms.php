<?php

//collection of helper methods
class Awecms {

    public static function getPrimaryKey($ar) {
        return $ar->primaryKey;
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
        return ucwords(trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
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

    public static function formatUrl($url, $inNewTab = false) {
        $value = $url;
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
            $url = 'http://' . $url;
        $htmlOptions = array();
        if ($inNewTab)
            $htmlOptions['target'] = '_blank';
        return CHtml::link(CHtml::encode($value), $url, $htmlOptions);
    }

}