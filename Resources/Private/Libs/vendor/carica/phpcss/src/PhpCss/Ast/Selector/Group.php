<?php
/**
 * List of Css Selector Sequences.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright 2010-2014 PhpCss Team
 */

namespace PhpCss\Ast\Selector {

  use ArrayAccess;
  use ArrayIterator;
  use Countable;
  use InvalidArgumentException;
  use IteratorAggregate;
  use PhpCss\Ast;

  /**
   * List of Css Selector Sequences.
   *
   * This is the root element of a standard selector string like:
   * "element, .class"
   *
   * Because it is a list the some standard interfaces are implemented for
   * easier usage.
   */
  class Group extends Ast\Selector implements ArrayAccess, Countable, IteratorAggregate
  {
      private $_sequences = [];

      /**
       * Create the object and assign sequences if provided. They
       * can be added later of course.
       *
       * @param array $sequences
       */
      public function __construct(array $sequences = [])
      {
          foreach ($sequences as $sequence) {
              $this->offsetSet(null, $sequence);
          }
      }

      /**
       * Check if a sequence at the given position is available in the list.
       *
       * @param int $offset
       * @return bool
       * @see ArrayAccess::offsetExists()
       */
      public function offsetExists($offset): bool
      {
          return isset($this->_sequences[$offset]);
      }

      /**
       * Return the sequence at the given position.
       *
       * @param int $offset
       * @return Sequence
       * @see ArrayAccess::offsetGet()
       */
      public function offsetGet($offset): Sequence
      {
          return $this->_sequences[$offset];
      }

      /**
       * Set/Add and sequence at the given position or top the end
       *
       * @param int|null $offset
       * @param Sequence $value
       * @throws InvalidArgumentException
       * @see \ArrayAccess::offsetSet()
       */
      public function offsetSet($offset, $value): void
      {
          if (!$value instanceof Sequence) {
              throw new InvalidArgumentException(
                  sprintf(
              '$sequence is not an instance of %s but %s.',
              Sequence::class,
              is_object($value) ? get_class($value) : gettype($value)
          )
              );
          }
          if (is_null($offset)) {
              $this->_sequences[] = $value;
          } else {
              $this->_sequences[(int)$offset] = $value;
              $this->_sequences = array_values($this->_sequences);
          }
      }

      /**
       * Remove the sequence at the given position
       *
       * @param int $offset
       * @see ArrayAccess::offsetUnset()
       */
      public function offsetUnset($offset): void
      {
          unset($this->_sequences[$offset]);
          $this->_sequences = array_values($this->_sequences);
      }

      /**
       * Return the sequence list count.
       *
       * @return int
       * @see Countable::count()
       */
      public function count(): int
      {
          return count($this->_sequences);
      }

      /**
       * Return an iterator for the sequences
       *
       * @return ArrayIterator
       * @see IteratorAggregate::getIterator()
       */
      public function getIterator(): ArrayIterator
      {
          return new ArrayIterator($this->_sequences);
      }

      /**
       * Accept visitors, because this element has children, enter and leave are called.
       *
       * @param Ast\Visitor $visitor
       */
      public function accept(Ast\Visitor $visitor): void
      {
          if ($visitor->visitEnter($this)) {
              /**
               * @var Sequence $sequence
               */
              foreach ($this as $sequence) {
                  $sequence->accept($visitor);
              }
              $visitor->visitLeave($this);
          }
      }
  }
}
