<?php
/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Rad\Utils;


abstract class StringUtils {

    private function __construct() {
        
    }

    /**
     * 
     * Check if is an email adresse.
     *
     * @pre1 <code>$email="toto@toto.com";</code>
     * @post1 <code>$result=true;</code>
     * @pre2 <code>$email="toto@toto";</code>
     * @post2 <code>$result=false;</code>
     * @pre3 <code>$email="toto-bu.gogo@toto.co.uk";</code>
     * @post3 <code>$result=true;</code>
     * @pre4 <code>$email="toto- bu.gogo@toto.co.uk";</code>
     * @post4 <code>$result=false;</code>
     * @param string $email
     *
     * @return bool
     */
    public static function isEMail($email) {
        return (boolean) !(filter_var($email, FILTER_VALIDATE_EMAIL) === false);
    }

    /**
     * Check if param is url.
     * @pre1 <code>$url="http://www.google.fr/index.html";</code>
     * @post1 <code>$result=true;</code>
     * @pre2 <code>$url="toto@toto.com";</code>
     * @post2 <code>$result=false;</code>
     * @pre3 <code>$url="www.google.fr/";</code>
     * @post3 <code>$result=false;</code>
     * @pre4 <code>$url="http://www.google.fr";</code>
     * @post4 <code>$result=true;</code>
     * @param string $url
     *
     * @return bool
     */
    public static function isURL($url) {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return (boolean) !(filter_var($url, FILTER_VALIDATE_URL) === false);
    }

    /**
     * Quick debug var.
     * @pre1 <code>$var=true;</code>
     * @post1 <code>$result=null;</code>
     * @param any $var
     */
    public static function qd($var) {
        error_log(print_r($var, true));
    }

    /**
     * 
     * @pre1 <code>$str="toto%";</code>
     * @post1 <code>$result="toto ";</code>
     * @pre2 <code>$str="toto[%";</code>
     * @post2 <code>$result="toto ";</code>
     * 
     * Remove all special chars from string 
     * only let alpha and digit.
     *
     * @param string $str
     *
     * @return type
     */
    public static function removeSpecialChars($str) {
        return preg_replace('#[^A-Za-z0-9_-]+#', ' ', $str);
    }

    /**
     * @pre1 <code>$str="abcd";</code>
     * @post1 <code>$result="abcd";</code>
     * 
     * @pre2 <code>$str="éàçù";</code>
     * @post2 <code>$result="eacu";</code>
     * 
     * @param string $str
     * @return string
     */
    public static function removeAccents($str) {
        $str = str_replace(' & ', ' ', $str);
        $str = htmlentities($str);
        $str = preg_replace('#&([A-Za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-Za-z]{2})(?:lig);#', '\1', $str);
        // Supprimer tout le reste
        $str = preg_replace('#&[^;]+;#', '', $str);
        return $str;
    }

    /**
     * @pre1 <code>$text="BonJour Monde";</code>
     * @post1 <code>$result="bonjour-monde";</code>
     * 
     * @pre2 <code>$text="bonjour-monde";</code>
     * @post2 <code>$result="bonjour-monde";</code>
     * 
     * @param string $text
     * @return string
     */
    public static function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    /**
     * @pre1 <code>$haystack = "totoleheros";$needle="toto";</code>
     * @post1 <code>$result=true;</code>
     * @pre2 <code>$haystack = "boboleheros";$needle="toto";</code>
     * @post2 <code>$result=false;</code>
     * @pre3 <code>$haystack = "totoleheros";$needle="heros";</code>
     * @post3 <code>$result=false;</code>
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * @pre1 <code>$haystack = "totoleheros";$needle="heros";</code>
     * @post1 <code>$result=true;</code>
     * @pre2 <code>$haystack = "boboletoto";$needle="heros";</code>
     * @post2 <code>$result=false;</code>
     * @pre3 <code>$haystack = "totoleheros";$needle="toto";</code>
     * @post3 <code>$result=false;</code>
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * Check if a string contains another string.
     * @pre1 <code>$haystack = "totoleheros";$needle="Heros";$cs=false;</code>
     * @post1 <code>$result=true;</code>
     * @pre2 <code>$haystack = "totoleHeros";$needle="heros";$cs=false;</code>
     * @post2 <code>$result=true;</code>
     * @pre3 <code>$haystack = "totoleHeros";$needle="heros";$cs=true;</code>
     * @post3 <code>$result=false;</code>
     * @pre4 <code>$haystack = "totoleheros";$needle="Heros";$cs=true;</code>
     * @post4 <code>$result=false;</code>
     * @pre5 <code>$haystack = "boboletoto";$needle="heros";$cs=false;</code>
     * @post5 <code>$result=false;</code>
     * @param  string $haystack
     * @param  string $needle
     * @param boolean $cs
     * 
     * @return boolean
     */
    public static function strContains($haystack, $needle, $cs = false) {
        if ($cs) {
            return strpos($haystack, $needle) !== false;
        } else {
            return stripos($haystack, $needle) !== false;
        }
    }

    /**
     * Parse comment, used only when generate php
     * @param string $comments
     */
    public static function parseComments($comments) {
        $ret = array();
        //trim(str_replace(array('/', '*', '**'), '', substr($comments, 0, strpos($comments, '@'))));
        $comments = str_replace(array('/*', '*', '**'), '', $comments);
        $array_comments = explode("\n", $comments);
        foreach ($array_comments as $k => $line) {
            $line = trim($line);
            if (self::startsWith($line, "@")) {
                $params = explode(" ", $line);
                $c = trim(str_replace("@", "", array_shift($params)));
                $ret[$c] = $params;
            }
        }
        return $ret;
    }

    /**
     * @return string
     */
    public static function toXML($mixed, $root = "root") {
        $array = null;
        if (is_object($mixed)) {
            $array = get_object_vars($mixed);
            $root = str_replace("model\\", "", strtolower(get_class($mixed)));
        } else {
            $array = $mixed;
        }
        $ret = "<" . $root . ">";
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $ret .= self::toXML($val, $key);
            } else if (is_object($val)) {
                $class = str_replace("model\\", "", strtolower(get_class($val)));
                $ret .= self::toXML($val, $class);
            } else {
                $ret .= '<' . $key . '>';
                $ret .= $val;
                $ret .= '</' . $key . '>';
            }
        }
        $ret .= "</" . $root . ">";
        return $ret;
    }

    /**
     * 
     * @param string $pattern
     * @param array $input
     * @param int $flags
     * @return array
     */
    public function preg_grep_keys($pattern, array $input, $flags = 0) {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * 
     * @param type $line
     * @param type $tab
     * @return type
     */
    public static function printLn($line = "", $tab = 0) {
        $c = "";
        for ($i = 0; $i < $tab; $i++) {
            $c .= "\t";
        }
        return $c . $line . "\n";
    }

}
