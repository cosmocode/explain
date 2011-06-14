<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

/**
 * Disable the plugin on certian pages
 *
 * @license  GPL
 * @author   Andreas Gohr <gohr@cosmocode.de>
 */
class syntax_plugin_explain_noexplain extends DokuWiki_Syntax_Plugin {

    function getType() {
        return 'substition';
    }

    function getSort() {
        return 239; // before 'acronym'
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOEXPLAIN~~', $mode, 'plugin_explain_noexplain');
    }

    function handle($match, $state, $pos, &$handler) {
        return array();
    }

    function render($format, &$renderer, $data) {
        if($format == 'metadata'){
            $renderer->meta['plugin']['explain'] = 'disable';
        }
        return true;
    }
}
