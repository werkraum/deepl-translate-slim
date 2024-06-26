<?php
/**
* Scans a string and creates list of tokens.
*
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright 2010-2014 PhpCss Team
*/
namespace PhpCss {

  use UnexpectedValueException;

  /**
  * Scans a string and creates list of tokens.
  *
  * The actual result depends on the status, the status
  * class does the actual token matching and generation, the scanner handles, to loops and
  * delegations.
  */
  class Scanner
  {

    /**
    * Scanner status object
    * @var Scanner\Status
    */
      private $_status;
      /**
      * string to parse
      * @var string
      */
      private $_buffer = '';
      /**
      * current offset
      * @var int
      */
      private $_offset = 0;

      /**
      * Constructor, set status object
      *
      * @param Scanner\Status $status
      */
      public function __construct(Scanner\Status $status)
      {
          $this->_status = $status;
      }

      /**
       * Scan a string for tokens
       *
       * @param array &$target token target
       * @param string $string content string
       * @param int $offset start offset
       * @throws UnexpectedValueException
       * @return int new offset
       */
      public function scan(array &$target, string $string, int $offset = 0): int
      {
          $this->_buffer = $string;
          $this->_offset = $offset;
          while ($token = $this->_next()) {
              $target[] = $token;
              // switch back to previous scanner
              if ($this->_status->isEndToken($token)) {
                  return $this->_offset;
              }
              // check for status switch
              if ($newStatus = $this->_status->getNewStatus($token)) {
                  // delegate to subscanner
                  $this->_offset = $this->_delegate($target, $newStatus);
              }
          }
          if ($this->_offset < strlen($this->_buffer)) {
              /**
              * @todo a some substring logic for large strings
              */
              throw new Exception\InvalidCharacterException(
                  $this->_buffer,
                  $this->_offset,
                  $this->_status
              );
          }
          return $this->_offset;
      }

      /**
      * Get next token
      *
      * @return Scanner\Token|null
      */
      private function _next(): ?Scanner\Token
      {
          if (($token = $this->_status->getToken($this->_buffer, $this->_offset)) &&
          $token->length > 0) {
              $this->_offset += $token->length;
              return $token;
          }
          return null;
      }

      /**
      * Got new status, delegate to subscanner.
      *
      * If the status returns a new status object, a new scanner is created to handle it.
      *
      * @param Scanner\Token[] $target
      * @param Scanner\Status $status
      * @return int
      */
      private function _delegate(array &$target, Scanner\Status $status): int
      {
          $scanner = new self($status);
          return $scanner->scan($target, $this->_buffer, $this->_offset);
      }

      public function getStatus(): Scanner\Status
      {
          return $this->_status;
      }
  }
}
