<?php
/**
* Scanner Status "Selector"
*
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright 2010-2014 PhpCss Team
*/

namespace PhpCss\Scanner\Status {

  use PhpCss\Scanner;

  /**
  * Scanner Status Selector recognizes token of a css selector sequence.
  */
  class Selector extends Scanner\Status
  {

    /**
    * single char tokens
    * @var array
    */
      private $_tokenChars = [
      Scanner\Token::SEPARATOR => ',',
      Scanner\Token::ATTRIBUTE_SELECTOR_START => '[',
      Scanner\Token::PARENTHESES_START => '(',
      Scanner\Token::PARENTHESES_END => ')',
      Scanner\Token::SINGLEQUOTE_STRING_START => "'",
      Scanner\Token::DOUBLEQUOTE_STRING_START => '"'
    ];

      /**
      * patterns for more complex tokens
      * @var array
      */
      private $_tokenPatterns = [
      Scanner\Token::CLASS_SELECTOR => Scanner\Patterns::CLASS_SELECTOR,
      Scanner\Token::ID_SELECTOR => Scanner\Patterns::ID_SELECTOR,
      Scanner\Token::PSEUDO_CLASS => Scanner\Patterns::PSEUDO_CLASS,
      Scanner\Token::PSEUDO_CLASS_POSITION => Scanner\Patterns::PSEUDO_CLASS_POSITION,
      Scanner\Token::PSEUDO_ELEMENT => Scanner\Patterns::PSEUDO_ELEMENT,
      Scanner\Token::IDENTIFIER => Scanner\Patterns::IDENTIFIER,
      Scanner\Token::COMBINATOR => Scanner\Patterns::COMBINATOR,
      Scanner\Token::WHITESPACE => Scanner\Patterns::WHITESPACE,
      Scanner\Token::NUMBER => Scanner\Patterns::NUMBER
    ];

      /**
      * Try to get token in buffer at offset position.
      *
      * @param string $buffer
      * @param int $offset
      * @return Scanner\Token
      */
      public function getToken(string $buffer, int $offset): ?Scanner\Token
      {
          if ($token = $this->matchCharacters($buffer, $offset, $this->_tokenChars)) {
              return $token;
          }
          if ($token = $this->matchPatterns($buffer, $offset, $this->_tokenPatterns)) {
              return $token;
          }
          return null;
      }

      /**
      * Check if token ends status
      *
      * @param Scanner\Token $token
      * @return bool
      */
      public function isEndToken(Scanner\Token $token): bool
      {
          return false;
      }

      /**
      * Get new (sub)status if needed.
      *
      * @param Scanner\Token $token
      * @return Scanner\Status|null
      */
      public function getNewStatus(Scanner\Token $token): ?Scanner\Status
      {
          switch ($token->type) {
      case Scanner\Token::SINGLEQUOTE_STRING_START:
        return new Scanner\Status\Text\Single();
      case Scanner\Token::DOUBLEQUOTE_STRING_START:
        return new Scanner\Status\Text\Double();
      case Scanner\Token::ATTRIBUTE_SELECTOR_START:
        return new Scanner\Status\Selector\Attribute();
      }
          return null;
      }
  }
}
