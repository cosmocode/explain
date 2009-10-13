<?php
    if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/** Explain Terms and Definitions
 
    It works like acronym.conf, but for any term (even with more than
    one word).
 
    Evaluates con/explain.conf which is in the following syntax:
 
      term TAB explanation TAB wiki-link
 
    term:        regular expression of the term to explain
    explanation: a short description of the term
    wiki-link:   link in wiki syntax (A:B:C) to the definition
 
    License: GPL
    */
class syntax_plugin_explain extends DokuWiki_Syntax_Plugin {
 
  function syntax_plugin_explain() {
    // "static" not allowed in PHP4?!?
    //if (isset($keys[0]) return; // evaluate at most once
    $lines = @file(DOKU_CONF.'explain.conf');
    foreach ($lines as $line) {
      $line = trim($line);
      if (empty($line)) continue;
      $parts = explode('	', $line);
      $this->map[$parts[0]] = array(htmlspecialchars($parts[0]),
                                    htmlspecialchars($parts[1]),
                                    $this->link($parts[2], $parts[3]));
      $this->keys[] = $parts[0];
    }
    $this->pattern = join('|', $this->keys);
  }
 
  function link($target, $other) {
    global $ID;
    static $url = '^http://';
    // '^(http://)?[-_[:alnum:]]+[-_.[:alnum:]]*\.[a-z]{2}'
    // '(/[-_./[:alnum:]&%?=#]*)?';
    if (ereg($url, $target))
      return $target;
    list($id, $hash) = split('#', $target, 2);
    resolve_pageid(getNS($ID), $id, $exists);
    if ($other!='' && $ID==$id)
      if (ereg($url, $other))
        return $other;
      else {
        list($id, $hash) = split('#', $other, 2);
        resolve_pageid(getNS($ID), $id, $exists);
        return wl($id).'#'.$hash;
      }
    else
      return wl($id).'#'.$hash;
  }
 
  function getInfo() {
    return array('author' => 'Marc Wäckerlin',
                 'email'  => 'marc [at] waeckerlin [dot-org]',
                 'name'   => 'Explain',
                 'desc'   => 'Explain terms',
                 'url'    => 'http://marc.waeckerlin.org');
  }
 
  function getType() {
    return 'substition';
  }
 
  function getSort() {
    return 239; // before 'acronym'
  }
 
  function connectTo($mode) {
     if ($this->pattern!='')
       $this->Lexer->addSpecialPattern($this->pattern, $mode,
                                       'plugin_explain');
  }
 
  function handle($match, $state, $pos, &$handler) {
    return $this->map[$match];
  }
 
  function render($format, &$renderer, $data) {
    $renderer->doc .= '<a href="'.$data[2]
      .'" title="'.$data[1].'" class="explain">'
      .$data[0].'</a>';
    return true;
  }
}
?>
