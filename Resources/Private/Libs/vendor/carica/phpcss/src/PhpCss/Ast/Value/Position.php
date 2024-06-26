<?php

namespace PhpCss\Ast\Value {

  use PhpCss;

  class Position extends PhpCss\Ast\Node
  {
      public $repeat = 0;
      public $add = 0;

      public function __construct(int $repeat, int $add)
      {
          $this->repeat = $repeat;
          $this->add = $add;
      }
  }
}
