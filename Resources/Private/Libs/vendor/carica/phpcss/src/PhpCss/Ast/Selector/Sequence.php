<?php

namespace PhpCss\Ast\Selector {

  use PhpCss\Ast;

  class Sequence extends Ast\Selector
  {

    /**
     * @var Simple[]
     */
      public $simples = [];
      public $combinator;

      /**
       * @param Simple[] $simples
       * @param Combinator|null $combinator
       */
      public function __construct(array $simples = [], Combinator $combinator = null)
      {
          $this->simples = $simples;
          $this->combinator = $combinator;
      }

      /**
       * Accept visitors, because this element has children, enter and leave are called.
       *
       * @param Ast\Visitor $visitor
       * @return void|null
       */
      public function accept(Ast\Visitor $visitor): void
      {
          if ($visitor->visitEnter($this)) {
              foreach ($this->simples as $simple) {
                  $simple->accept($visitor);
              }
              if (isset($this->combinator)) {
                  $this->combinator->accept($visitor);
              }
              $visitor->visitLeave($this);
          }
      }
  }
}
