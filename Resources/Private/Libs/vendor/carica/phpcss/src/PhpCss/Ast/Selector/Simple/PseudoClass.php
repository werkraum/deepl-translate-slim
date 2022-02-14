<?php

namespace PhpCss\Ast\Selector\Simple {

  use PhpCss\Ast;

  class PseudoClass extends Ast\Selector\Simple
  {
      public $name = '';
      public $parameter;

      public function __construct(string $name, Ast\Node $parameter = null)
      {
          $this->name = $name;
          $this->parameter = $parameter;
      }

      public function accept(Ast\Visitor $visitor): void
      {
          if ($this->parameter instanceof Ast\Node) {
              if ($visitor->visitEnter($this)) {
                  $this->parameter->accept($visitor);
                  $visitor->visitLeave($this);
              }
          } else {
              $visitor->visit($this);
          }
      }
  }
}
