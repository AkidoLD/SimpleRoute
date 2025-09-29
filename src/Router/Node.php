<?php

namespace SimpleRoute\Router;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

//External lib
use Ramsey\Uuid\Uuid;
use SimpleRoute\Exceptions\NodeChildIsNotANode;
use SimpleRoute\Exceptions\NodeChildKeyMismatchException;
use SimpleRoute\Exceptions\NodeHandlerNotSet;

/**
 * Represents a node in a routing tree.
 *
 * A `Node` acts as a junction point in the route structure. 
 * It can hold multiple children (other `Node` instances) and an optional handler
 * that will be executed when the corresponding route is reached.
 *
 * Each `Node` has a key unique within its parent and a globally unique UUID
 * to distinguish it from other nodes that may share the same key.
 *
 * Implements:
 * - `Countable` : to count the number of children.
 * - `ArrayAccess` : to access children as an array.
 * - `IteratorAggregate` : to iterate over children.
 *
 * Typical usage:
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
     * The key used to store this Node inside another Node (as its child).
     * @var string
     */
    private string $key;

    /**
     * The unique identifier of this Node.
     * 
     * Useful when you have multiple Nodes with the same key.
     * 
     * @var string (read-only)
     */
    private readonly string $uuid;

    /**
     * The children of this Node.
     * 
     * @var Node[]
     */
    private array $children;

    /** 
     * The handler attached to this Node.
     * 
     * If a handler has been attached, you can execute it
     * with the execute() method or the __invoke() implementation.
     * 
     * @var callable|null 
     */
    private $handler = null;

    public function __construct(string $key, ?callable $handler = null) {
        $this->key = $key;
        $this->setHandler($handler);
        $this->uuid = Uuid::uuid4()->toString();
        $this->children = [];
    }

    /**
     * Get the key of this Node.
     * 
     * @return string The key of this Node.
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * Get the unique identifier (UUID) of this Node.
     * 
     * @return string The UUID of this Node.
     */
    public function getUuid(): string {
        return $this->uuid;
    }
    
    /**
     * Add a single child to this Node.
     *
     * The child is stored using its key.
     *
     * @param Node $child The Node instance to add as a child.
     * @return void
     */
    public function addChild(Node $child): void {
        $this->children[$child->getKey()] = $child;
    }

    /**
     * Add multiple children to this Node.
     *
     * Iterates over the given array and adds each element as a child.
     *
     * @param Node[] $children An array of Node instances.
     * @throws NodeChildIsNotANode If any element is not a Node instance.
     * @return void
     */ 
    public function addChildren(array $children): void {
        foreach ($children as $child) {
            if (!($child instanceof Node)) {
                throw new NodeChildIsNotANode("All children must be instances of Node");
            }
            $this->addChild($child);
        }
    }
    
    /**
     * Attach a handler to this Node.
     *
     * @param callable|null $handler The handler to attach, or null to remove it.
     * @return void
     */
    public function setHandler(?callable $handler): void {
        $this->handler = $handler;
    }

    /**
     * Get the handler attached to this Node.
     *
     * @return callable|null The handler if attached, or null otherwise.
     */
    public function getHandler(): ?callable {
        return $this->handler;
    }

    /**
     * Check if a handler had been attache to the Node
     * 
     * @return bool
     */
    public function hadHandler(): bool{
        return $this->handler !== null;
    }
    
    /**
     * Get a child Node by its key.
     * 
     * @param string $key The key of the child Node.
     * @return ?Node The child Node if found, or null otherwise.
     */
    public function getChild(string $key): ?Node {
        return $this->children[$key] ?? null;
    }

    /**
     * Get all children of this Node.
     * 
     * @return Node[] Array of child Nodes.
     */
    public function getChildren(): array {
        return $this->children;
    }

    /**
     * Check if this Node is a leaf (has no children).
     * 
     * @return bool True if the Node has no children, false otherwise.
     */
    public function isLeaf(): bool {
        return empty($this->children);
    }

    /**
     * Count the number of children of this Node.
     * 
     * @return int The number of children.
     */
    public function childrenCount(): int {
        return count($this->children);
    }

    /**
     * Execute the callback handler of this Node.
     * 
     * @param mixed ...$args Arguments to pass to the callback.
     * @throws NodeHandlerNotSet If no handler is attached to the Node.
     * @return mixed The return value of the callback.
     */
    public function execute(...$args) {
        if ($this->handler === null) {
            throw new NodeHandlerNotSet("This Node has no callback handler");
        }
        return ($this->handler)(...$args);
    }

    /**
     * An alias of the execute() method.
     * 
     * @param mixed ...$args Arguments to pass to the handler.
     * @return mixed The return value of the handler.
     */
    public function __invoke(...$args) {
        return $this->execute(...$args);
    }

    // Implementation of Stringable
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
     * Sets a child Node at the given offset.
     *
     * This method implements ArrayAccess::offsetSet, allowing you to add a child
     * Node to this Node using array syntax.
     * 
     * Behavior:
     * - The `$value` must always be an instance of `Node`.
     * - The `$offset` is optional. If provided, it **must** match the key of the Node.
     *   Otherwise, a `NodeChildKeyMismatchException` will be thrown.
     * - If `$offset` is null, the child Node will be stored using its key automatically.
     *
     * Example usage:
     *   $node[$child->getKey()] = $child; // recommended
     *   $node[] = $child;                 // works, key is taken from child
     *
     * @param mixed $offset The key to store the child at (must match the child's key if provided).
     * @param mixed $value The Node instance to add as a child.
     * 
     * @throws NodeChildIsNotANode If $value is not a Node instance.
     * @throws NodeChildKeyMismatchException If the $offset does not match the Node's key.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if (!($value instanceof Node)) {
            throw new NodeChildIsNotANode(
                'Error: Only instances of Node can be added as children.'
            );
        }
        
        if ($offset !== null && $offset !== $value->getKey()) {
            throw new NodeChildKeyMismatchException(
                'Error: The offset must match the key of the Node.'
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
