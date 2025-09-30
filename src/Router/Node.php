<?php

namespace SimpleRoute\Router;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

// External libs
use Ramsey\Uuid\Uuid;
use SimpleRoute\Exceptions\Node\NodeChildIsNotANodeException;
use SimpleRoute\Exceptions\Node\NodeChildKeyMismatchException;
use SimpleRoute\Exceptions\Node\NodeHandlerNotSetException;
use SimpleRoute\Exceptions\Node\NodeSelfReferenceException;

/**
 * Represents a node in a routing tree.
 *
 * A `Node` acts as a junction point in the route structure.  
 * It can hold multiple children (other `Node` instances) and an optional handler
 * that will be executed when the corresponding route is reached.
 *
 * Each `Node` has a key unique within its parent, and a globally unique UUID
 * to distinguish it from other nodes that may share the same key.
 *
 * Implements:
 * - `Countable`: allows counting the number of children.
 * - `ArrayAccess`: allows accessing children as an array.
 * - `IteratorAggregate`: allows iterating over children.
 *
 * Example usage:
 * ```php
 * $node = new Node('auth');
 * $node->setHandler(fn() => echo "Authentication");
 * $child = new Node('login');
 * $node->addChild($child);
 * $node('param1'); // executes the handler
 * ```
 *
 * This class is mainly used by `NodeTree` and `Router` to dynamically
 * build and traverse a routing tree.
 *
 * @package SimpleRoute\Router
 */
class Node implements Countable, ArrayAccess, IteratorAggregate {
    /**
     * The key used to identify this Node within its parent.
     * @var string
     */
    private string $key;

    /**
     * The unique identifier (UUID) of this Node.
     *
     * Useful when multiple nodes share the same key.
     *
     * @var string (read-only)
     */
    private readonly string $uuid;

    /**
     * The parent of this Node.
     *
     * Automatically set when this Node is added as a child of another Node.
     *
     * @var Node|null
     */
    private ?Node $parent;

    /**
     * The children of this Node.
     *
     * @var Node[]
     */
    private array $children;

    /** 
     * The handler (callback) attached to this Node.
     *
     * If defined, the handler can be executed with `execute()`  
     * or by invoking the Node directly (`__invoke()`).
     *
     * @var callable|null 
     */
    private $handler = null;

    public function __construct(string $key, ?callable $handler = null) {
        $this->key = $key;
        $this->setHandler($handler);
        $this->uuid = Uuid::uuid4()->toString();
        $this->children = [];
        $this->parent = null;
    }

    /**
     * Get the key of this Node.
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * Get the UUID of this Node.
     */
    public function getUuid(): string {
        return $this->uuid;
    }
    
    /**
     * Get the parent of this Node.
     *
     * @return Node|null The parent Node if set, otherwise null.
     */
    public function getParent(): ?Node {
        return $this->parent;
    }

    /**
     * Add a child Node.
     *
     * @param Node $child The Node to add.
     * @throws NodeSelfReferenceException If attempting to add the Node to itself.
     */
    public function addChild(Node $child): void {
        if ($child->getUuid() === $this->uuid) {
            throw new NodeSelfReferenceException('A Node cannot be its own child.');
        }
        $child->parent = $this;
        $this->children[$child->getKey()] = $child;
    }

    /**
     * Add multiple children Nodes.
     *
     * @param Node[] $children Array of Node instances.
     * @throws NodeChildIsNotANodeException If any element is not a Node instance.
     */
    public function addChildren(array $children): void {
        foreach ($children as $child) {
            if (!($child instanceof Node)) {
                throw new NodeChildIsNotANodeException("All children must be instances of Node.");
            }
            $this->addChild($child);
        }
    }
    
    /**
     * Attach a handler (callback) to this Node.
     */
    public function setHandler(?callable $handler): void {
        $this->handler = $handler;
    }

    /**
     * Get the handler attached to this Node.
     *
     * @return callable|null The handler if set, otherwise null.
     */
    public function getHandler(): ?callable {
        return $this->handler;
    }

    /**
     * Check if this Node has a handler.
     */
    public function hadHandler(): bool {
        return $this->handler !== null;
    }
    
    /**
     * Get a child Node by its key.
     *
     * @param string $key The child Node key.
     * @return Node|null The child Node if found, otherwise null.
     */
    public function getChild(string $key): ?Node {
        return $this->children[$key] ?? null;
    }

    /**
     * Get all children of this Node.
     *
     * @return Node[]
     */
    public function getChildren(): array {
        return $this->children;
    }

    public function removeChild(string $key){
        if(!isset($this->children[$key])){
            throw new NodeChildKeyMismatchException("The Node with the key : $key is not found");
        }
        unset($this->children[$key]);
    }
    /**
     * Check if this Node is a leaf (has no children).
     */
    public function isLeaf(): bool {
        return empty($this->children);
    }

    /**
     * Count the number of children.
     */
    public function childrenCount(): int {
        return count($this->children);
    }

    /**
     * Execute the handler attached to this Node.
     *
     * @param mixed ...$args Arguments passed to the handler.
     * @throws NodeHandlerNotSetException If no handler is defined.
     * @return mixed The result of the handler.
     */
    public function execute(...$args) {
        if ($this->handler === null) {
            throw new NodeHandlerNotSetException("The node with key '{$this->key}' has no handler defined.");
        }
        return ($this->handler)(...$args);
    }

    /**
     * Alias of `execute()`.  
     * Allows invoking the Node like a function.
     */
    public function __invoke(...$args) {
        return $this->execute(...$args);
    }

    public function __toString(): string {
        return "Node(key: {$this->key}, uuid: {$this->uuid})";
    }

    // Implementation of Countable
    public function count(): int {
        return $this->childrenCount();
    }

    // Implementation of ArrayAccess
    public function offsetGet(mixed $offset): ?Node {
        return $this->getChild($offset);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->children[$offset]);
    }

    /**
     * Set a child Node using array syntax.
     *
     * Behavior:
     * - `$value` must be a `Node`.
     * - If `$offset` is provided, it must match the Node’s key.
     * - If `$offset` is null, the Node’s key will be used automatically.
     *
     * @param mixed $offset The child key (optional).
     * @param mixed $value The Node to add.
     * @throws NodeChildIsNotANodeException If $value is not a Node.
     * @throws NodeChildKeyMismatchException If $offset does not match the Node’s key.
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if (!($value instanceof Node)) {
            throw new NodeChildIsNotANodeException(
                'Error: Only Node instances can be added as children.'
            );
        }
        
        if ($offset !== null && $offset !== $value->getKey()) {
            throw new NodeChildKeyMismatchException(
                'Error: The offset must match the Node’s key.'
            );
        }
    
        $this->children[$value->getKey()] = $value;
    }
    
    public function offsetUnset(mixed $offset): void {
        unset($this->children[$offset]);
    }

    // Implementation of IteratorAggregate
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->children);
    }
}
