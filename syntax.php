<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

/**
 * Explain Terms and Definitions
 *
 * It works like acronym.conf, but for any term (even with more than
 * one word).
 *
 * Evaluates conf/explain.conf which is in the following syntax:
 *
 * [<WHITESPACE>]term<TAB>explanation<TAB>link1<TAB>link2
 *
 * WHITESPACE:  If term starts with a whitespace character (Tab, Space, …),
 *              it is considered case-sensitive
 * term:        the term to explain
 * explanation: a short description of the term
 * link1:       link as URL or wiki page (a:b:c) to the definition
 * link2:       link as URL or wiki page (a:b:c) to alternative definition
 *
 * If the first link points to the current page, the second link is used
 *
 * @license  GPL
 * @author   Marc Wäckerlin <marc@waeckerlin.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 * @author   Andreas Gohr <gohr@cosmocode.de>
 */
class syntax_plugin_explain extends DokuWiki_Syntax_Plugin {

    private $map;
    private $links;

    function getType() {
        return 'substition';
    }

    function getSort() {
        return 239; // before 'acronym'
    }

    function syntax_plugin_explain() {
        // "static" not allowed in PHP4?!?
        //if (isset($keys[0]) return; // evaluate at most once
        $lines = @file(DOKU_CONF.'explain.conf');
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $i = (trim(substr($line, 0, 1)) !== '');
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode("\t", $line);
            if ($i) $parts[0] = utf8_strtolower($parts[0]);
            $this->map[$parts[0]] = array('desc'   => $parts[1],
                    'target' => $this->link(array_slice($parts, 2)),
                    'i'      => $i);
        }
    }

    function link($targets) {
        foreach($targets as $target) {
            $_ret = $this->_link($target);
            if ($_ret !== '') {
                break;
            }
        }
        return $_ret;
    }

    function _link($target) {
        /* Match an URL. */
        static $url = '^https?://';
        // '^(http://)?[-_[:alnum:]]+[-_.[:alnum:]]*\.[a-z]{2}'
        // '(/[-_./[:alnum:]&%?=#]*)?';
        if (ereg($url, $target))
            return $target;

        /* Match an internal link. */
        list($id, $hash) = split('#', $target, 2);
        global $ID;

        $_ret = '';
        if($ID != $id) {
            $_ret .= wl($id);
        }
        if($hash != '') {
            $_ret .= '#'.$hash;
        }
        return $_ret;
    }

    function connectTo($mode) {
        if (count($this->map) === 0)
            return;

        $re = '(?<=^|\W)(?i:'.
                join('|', array_map('preg_quote_cb', array_keys($this->map))).
                ')(?=\W|$)';

        $this->Lexer->addSpecialPattern($re, $mode, 'plugin_explain');
    }

    function handle($match, $state, $pos, &$handler) {
        // Supply the matched text in any case.
        $data = array('content' => $match);

        if(isset($this->map[$match])){
            // we have an exact match
            $data += $this->map[$match];
            // we no longer need this entry bc we match 1st occurance only
            unset($this->map[$match]);
        }else{
            // try case insensitive matching
            $lmatch = utf8_strtolower($match);
            if(isset($this->map[$lmatch]) && $this->map[$lmatch]['i']){
                $data += $this->map[$lmatch];
                // we no longer need this entry bc we match 1st occurance only
                unset($this->map[$lmatch]);
            }
        }

        // did we see this link before?
        if($data['target']){
            if($this->links[$data['target']]){
                // yes, we had that one. don't link handle it
                unset($data['desc']);
            }else{
                // no it's new. remember it
                $this->links[$data['target']] = 1;
            }
        }

        return $data;
    }

    function render($format, &$renderer, $data) {
        if(!isset($data['desc'])) {
            $renderer->doc .= hsc($data['content']);
            return true;
        }
        $renderer->doc .= '<a class="explain"';
        if(($data['target']) !== '') {
            $renderer->doc .= ' href="' . hsc($data['target']) . '"';
        }
        if ($data['desc'] !== '') {
            $renderer->doc .= 'onmouseover="plugin_explain(this,\''.md5($data['content']).'\',\''.hsc($data['desc']).'\')"';
        }

        $renderer->doc .= '>' . hsc($data['content']);
        $renderer->doc .= '</a>';
        return true;
    }
}
