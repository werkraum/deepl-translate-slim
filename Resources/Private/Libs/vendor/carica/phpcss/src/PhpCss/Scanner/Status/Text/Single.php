<?php
/**
* Single quote string status for the scanner
*
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright 2010-2014 PhpCss Team
*/

namespace PhpCss\Scanner\Status\Text {

  use PhpCss\Scanner;

  /**
  * Single quote string status for the scanner
  */
  class Single extends Scanner\Status
  {

    /**
    * Try to get token in buffer at offset position.
    *
    * @param string $buffer
    * @param int $offset
    * @return Scanner\Token
    */
      public function getToken(string $buffer, int $offset): ?Scanner\Token
      {
          if ("'" === substr($buffer, $offset, 1)) {
              return new Scanner\Token(
                  Scanner\Token::SINGLEQUOTE_STRING_END,
                  "'",
                  $offset
              );
          }
          $tokenString = substr($buffer, $offset, 2);
          if ("\\'" === $tokenString || '\\\\' === $tokenString) {
              return new Scanner\Token(
                  Scanner\Token::STRING_ESCAPED_CHARACTER,
                  $tokenString,
                  $offset
              );
          }
          $tokenString = $this->matchPattern(
              $buffer,
              $offset,
              '([^\\\\\']+)S'
          );
          if (!empty($tokenString)) {
              return new Scanner\Token(
                  Scanner\Token::STRING_CHARACTERS,
                  $tokenString,
                  $offset
              );
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
          return
        $token->type === Scanner\Token::SINGLEQUOTE_STRING_END
      ;
      }

      /**
      * Get new (sub)status if needed.
      *
      * Returns always NULL, because a string never has a sub status
      *
      * @param Scanner\Token $token
      * @return Scanner\Status|null
      */
      public function getNewStatus(Scanner\Token $token): ?Scanner\Status
      {
          return null;
      }
  }
}
