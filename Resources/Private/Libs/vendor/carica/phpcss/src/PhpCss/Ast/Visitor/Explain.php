<?php

/** @noinspection PhpUnused */

/**
 * An ast visitor that compiles a dom document explaining the selector
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright 2010-2014 PhpCss Team
 */

namespace PhpCss\Ast\Visitor {

  use DOMDocument;
  use DOMElement;
  use PhpCss\Ast;

  /**
   * An ast visitor that compiles a dom document explaining the selector
   */
  class Explain extends Overload
  {
      private $_xmlns = 'urn:carica-phpcss-explain-2014';

      /**
       * @var DOMDocument
       */
      private $_document;

      /**
       * @var DOMElement|DOMDocument
       */
      private $_current;

      public function __construct()
      {
          $this->clear();
      }

      /**
       * Clear the visitor object to visit another selector group
       */
      public function clear(): void
      {
          $this->_current = $this->_document = new DOMDocument();
      }

      /**
       * Return the collected selector string
       */
      public function __toString()
      {
          return (string)$this->_document->saveXml();
      }

      /**
       * @param string $name
       * @param string $content
       * @param array $attributes
       * @param string $contentType
       * @return DOMElement
       */
      private function appendElement(
          string $name,
          string $content = '',
          array $attributes = [],
          string $contentType = 'text'
      ): DOMElement {
          $result = $this->_document->createElementNS($this->_xmlns, $name);
          if (!empty($content)) {
              $text = $result->appendChild(
                  $this->_document->createElementNs($this->_xmlns, $contentType)
              );
              if (trim($content) !== $content) {
                  $text->appendChild(
                      $this->_document->createCDATASection($content)
                  );
              } else {
                  $text->appendChild(
                      $this->_document->createTextNode($content)
                  );
              }
          }
          foreach ($attributes as $attribute => $value) {
              $result->setAttribute($attribute, $value);
          }
          $this->_current->appendChild($result);
          return $result;
      }

      /**
       * @param string $content
       */
      private function appendText(string $content): void
      {
          $text = $this->_current->appendChild(
              $this->_document->createElementNs($this->_xmlns, 'text')
          );
          if (trim($content) !== $content) {
              $text->appendChild(
                  $this->_document->createCDATASection($content)
              );
          } else {
              $text->appendChild(
                  $this->_document->createTextNode($content)
              );
          }
      }

      /**
       * Set the provided node as the current element, start a
       * subgroup.
       *
       * @param $node
       * @return bool
       */
      private function start($node): bool
      {
          $this->_current = $node;
          return true;
      }

      /**
       * Move the current element to its parent element
       *
       * @return bool
       */
      private function end(): bool
      {
          $this->_current = $this->_current->parentNode;
          return true;
      }

      /**
       * Validate the buffer before visiting a Ast\Selector\Group.
       * If the buffer already contains data, throw an exception.
       *
       * @return bool
       */
      public function visitEnterSelectorGroup(): bool
      {
          $this->start($this->appendElement('selector-group'));
          return true;
      }

      /**
       * If here is already data in the buffer, add a separator before starting the next.
       *
       * @return bool
       */
      public function visitEnterSelectorSequence(): bool
      {
          if (
        $this->_current === $this->_document->documentElement &&
        $this->_current->hasChildNodes()
      ) {
              $this
          ->_current
          ->appendChild(
              $this->_document->createElementNs($this->_xmlns, 'text')
          )
          ->appendChild(
              $this->_document->createCDATASection(', ')
          );
          }
          return $this->start($this->appendElement('selector'));
      }

      /**
       * @return bool
       */
      public function visitLeaveSelectorSequence(): bool
      {
          return $this->end();
      }

      /**
       * @param Ast\Selector\Simple\Universal $universal
       * @return bool
       */
      public function visitSelectorSimpleUniversal(Ast\Selector\Simple\Universal $universal): bool
      {
          if (!empty($universal->namespacePrefix) && $universal->namespacePrefix !== '*') {
              $css = $universal->namespacePrefix . '|*';
          } else {
              $css = '*';
          }
          $this->appendElement('universal', $css);
          return true;
      }

      /**
       * @param Ast\Selector\Simple\Type $type
       * @return bool
       */
      public function visitSelectorSimpleType(Ast\Selector\Simple\Type $type): bool
      {
          if (!empty($type->namespacePrefix) && $type->namespacePrefix !== '*') {
              $css = $type->namespacePrefix . '|' . $type->elementName;
          } else {
              $css = $type->elementName;
          }
          $this->appendElement('type', $css);
          return true;
      }

      /**
       * @param Ast\Selector\Simple\Id $id
       * @return bool
       */
      public function visitSelectorSimpleId(Ast\Selector\Simple\Id $id): bool
      {
          $this->appendElement('id', '#' . $id->id);
          return true;
      }

      /**
       * @param Ast\Selector\Simple\ClassName $class
       * @return bool
       */
      public function visitSelectorSimpleClassName(Ast\Selector\Simple\ClassName $class): bool
      {
          $this->appendElement('class', '.' . $class->className);
          return true;
      }

      /**
       * @return bool
       */
      public function visitEnterSelectorCombinatorDescendant(): bool
      {
          return $this->start($this->appendElement('descendant', ' '));
      }

      /**
       * @return bool
       */
      public function visitLeaveSelectorCombinatorDescendant(): bool
      {
          return $this->end();
      }

      /**
       * @return bool
       */
      public function visitEnterSelectorCombinatorChild(): bool
      {
          return $this->start($this->appendElement('child', ' > '));
      }

      /**
       * @return bool
       */
      public function visitLeaveSelectorCombinatorChild(): bool
      {
          return $this->end();
      }

      /**
       * @return bool
       */
      public function visitEnterSelectorCombinatorFollower(): bool
      {
          return $this->start($this->appendElement('follower', ' ~ '));
      }

      /**
       * @return bool
       */
      public function visitLeaveSelectorCombinatorFollower(): bool
      {
          return $this->end();
      }

      /**
       * @return bool
       */
      public function visitEnterSelectorCombinatorNext(): bool
      {
          return $this->start($this->appendElement('next', ' + '));
      }

      /**
       * @return bool
       */
      public function visitLeaveSelectorCombinatorNext(): bool
      {
          return $this->end();
      }

      /**
       * @param Ast\Selector\Simple\Attribute $attribute
       * @return bool
       */
      public function visitSelectorSimpleAttribute(
          Ast\Selector\Simple\Attribute $attribute
      ): bool {
          $operators = [
        Ast\Selector\Simple\Attribute::MATCH_EXISTS => 'exists',
        Ast\Selector\Simple\Attribute::MATCH_PREFIX => 'prefix',
        Ast\Selector\Simple\Attribute::MATCH_SUFFIX => 'suffix',
        Ast\Selector\Simple\Attribute::MATCH_SUBSTRING => 'substring',
        Ast\Selector\Simple\Attribute::MATCH_EQUALS => 'equals',
        Ast\Selector\Simple\Attribute::MATCH_INCLUDES => 'includes',
        Ast\Selector\Simple\Attribute::MATCH_DASHMATCH => 'dashmatch',
      ];
          $this->start(
              $this->appendElement(
            'attribute',
            '',
            ['operator' => $operators[$attribute->match]]
        )
          );
          $this->appendText('[');
          $this->appendElement('name', $attribute->name);
          if ($attribute->match !== Ast\Selector\Simple\Attribute::MATCH_EXISTS) {
              $operatorStrings = [
          Ast\Selector\Simple\Attribute::MATCH_PREFIX => '^=',
          Ast\Selector\Simple\Attribute::MATCH_SUFFIX => '$=',
          Ast\Selector\Simple\Attribute::MATCH_SUBSTRING => '*=',
          Ast\Selector\Simple\Attribute::MATCH_EQUALS => '=',
          Ast\Selector\Simple\Attribute::MATCH_INCLUDES => '~=',
          Ast\Selector\Simple\Attribute::MATCH_DASHMATCH => '|=',
        ];
              $this->appendElement('operator', $operatorStrings[$attribute->match]);
              $this->appendText('"');
              $this->appendElement(
                  'value',
                  str_replace(['\\', '"'], ['\\\\', '\\"'], $attribute->literal->value)
              );
              $this->appendText('"');
          }
          $this->appendText(']');
          $this->end();
          return true;
      }

      /**
       * @param Ast\Selector\Simple\PseudoClass $class
       * @return bool
       */
      public function visitSelectorSimplePseudoClass(
          Ast\Selector\Simple\PseudoClass $class
      ): bool {
          $this->start($this->appendElement('pseudoclass'));
          $this->appendElement('name', ':' . $class->name);
          return $this->end();
      }

      /**
       * @param Ast\Selector\Simple\PseudoClass $class
       * @return bool
       */
      public function visitEnterSelectorSimplePseudoClass(
          Ast\Selector\Simple\PseudoClass $class
      ): bool {
          $this->start($this->appendElement('pseudoclass'));
          $this->appendElement('name', ':' . $class->name);
          $this->appendText('(');
          $this->start($this->appendElement('parameter'));
          return true;
      }

      /**
       * @return bool
       */
      public function visitLeaveSelectorSimplePseudoClass(): bool
      {
          $this->end();
          $this->appendText(')');
          return $this->end();
      }

      /**
       * @param Ast\Value\Literal $literal
       * @return bool
       */
      public function visitValueLiteral(
          Ast\Value\Literal $literal
      ): bool {
          $this->appendText('"');
          $this->appendElement(
              'value',
              str_replace(['\\', '"'], ['\\\\', '\\"'], $literal->value)
          );
          $this->appendText('"');
          return true;
      }

      /**
       * @param Ast\Value\Number $number
       * @return bool
       */
      public function visitValueNumber(
          Ast\Value\Number $number
      ): bool {
          $this->appendElement(
              'value',
              $number->value,
              [],
              'number'
          );
          return true;
      }

      /**
       * @param Ast\Value\Position $position
       * @return bool
       */
      public function visitValuePosition(
          Ast\Value\Position $position
      ): bool {
          $repeatsOddEven = $position->repeat === 2;
          if ($repeatsOddEven && $position->add === 1) {
              $css = 'odd';
          } elseif ($repeatsOddEven && $position->add === 0) {
              $css = 'even';
          } elseif ($position->repeat === 0) {
              $css = $position->add;
          } elseif ($position->repeat === 1) {
              $css = 'n';
              if ($position->add !== 0) {
                  $css .= $position->add >= 0
            ? '+' . $position->add : $position->add;
              }
          } else {
              $css = $position->repeat . 'n';
              if ($position->add !== 0) {
                  $css .= $position->add >= 0
            ? '+' . $position->add : $position->add;
              }
          }
          $this->appendText($css);
          return true;
      }

      /**
       * @param Ast\Value\Language $language
       * @return bool
       */
      public function visitValueLanguage(
          Ast\Value\Language $language
      ): bool {
          $this->start($this->appendElement('pseudoclass'));
          $this->appendElement('name', ':lang');
          $this->appendText('(');
          $this->start($this->appendElement('parameter'));
          $this->appendText($language->language);
          $this->end();
          $this->appendText(')');
          return true;
      }

      /**
       * @param Ast\Selector\Simple\PseudoElement $element
       * @return bool
       */
      public function visitSelectorSimplePseudoElement(
          Ast\Selector\Simple\PseudoElement $element
      ): bool {
          $this->start($this->appendElement('pseudoclass'));
          $this->appendElement('name', '::' . $element->name);
          return $this->end();
      }
  }
}
