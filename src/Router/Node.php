<?php

namespace SimpleRoute\Router;

use App\Exceptions\NodeException;
use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

class Node implements Countable, ArrayAccess, IteratorAggregate {
    private string $key;
    private array $children = [];
    /** 
     * @var callable|null 
     */
    private $handler = null;

    public function __construct(string $key, ?callable $handler = null) {
        $this->key = $key;
        $this->handler = $handler;
    }

    public function getKey(): string {
        return $this->key;
    }
    
    public function addChild(Node $child): void {
        $this->children[$child->getKey()] = $child;
    }

    public function addChildren(array $children): void {
        foreach ($children as $child) {
            if (!($child instanceof Node)) {
                throw new InvalidArgumentException("All children must be instances of Node");
            }
            $this->addChild($child);
        }
    }
    
    public function setHandler(?callable $handler): void {
        $this->handler = $handler;
    }

    public function getHandler(): ?callable {
        return $this->handler;
    }

    /**
     * Get `Node` child
     * 
     * If the Node don't have child, the method return null
     * 
     * @exception NodeException If the Node have childrens and the child key is not found
     * 
     * @param string $key
     * 
     * @return ?Node
     */
    public function getChild(string $key): ?Node {
        if(!$this->haveChildren()) {
            return null;
        }
        if(!($child = $this->children[$key] ?? null)){
            throw new NodeException("The child with the key : $key had not been found.");
        }
        return $child;
    }

    /**
     * Get all children of the Node
     * 
     * @return array
     */
    public function getChildren(): array{
        return $this->children;
    }

    /**
     * Summary of isLeaf
     * @return bool
     */
    public function haveChildren(): bool{
        return !empty($this->children);
    }

    public function childrenCount(): int{
        return count($this->children);
    }

    /**
     * Execute the callaback function
     * 
     * This method allow to execute the callback function handler
     * to the `Node`
     * 
     * @param mixed $args A list of arguments to put in the callback like is parameters 
     * 
     * @throws NodeException If not callback handler on the `Node`
     * 
     * @return mixed the return of the callback
     */
    public function execute(...$args){
        if (!$this->handler) {
            throw new NodeException("This Node don't have callback handler");
        }
        //
        return ($this->handler)(...$args);
    }

    /**
     * An alias of execute method
     * 
     * @param array $args
     * @return mixed
     */
    public function __invoke(...$args) {
        $this->execute(...$args);
    }

    //Implementation of Stringable
    public function __toString(): string {
        return " Node key : $this->key ";
    }

    // Implementation of Countable
    public function count(): int {
        return count($this->children);
    }

    // Implementation of ArrayAccess
    public function offsetGet(mixed $offset): ?Node {
        return $this->getChild($offset);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->children[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if (!($value instanceof Node)) {
            throw new InvalidArgumentException(
                'Error: Only instances of Node can be added as children.'
            );
        }
        //Check if the offset it is not set
        if ($offset === null) {
            //If the offset it is not set, use the Node key like the offset
            $this->addChild($value);
        } else {
            $this->children[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->children[$offset]);
    }

    //Implementation of IteratorAggregate
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->children);
    }
}
