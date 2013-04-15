<?php

namespace MABI\Autodocs;

include_once dirname(__FILE__) . '/markdown-extra/markdown.php';

use MABI\Parser;

class MarkdownParser implements Parser {
  function Parse($text) {
    return Markdown($text);
  }
}