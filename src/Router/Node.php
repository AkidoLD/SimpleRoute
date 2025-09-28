<?php

namespace SimpleRoute\Router;

use App\Exceptions\NodeException;
use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Ramsey\Uuid\Uuid;

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
        $this->handler = $handler;
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
     * @throws \InvalidArgumentException If any element is not a Node instance.
     * @return void
     */ 
    public function addChildren(array $children): void {
        foreach ($children as $child) {
            if (!($child instanceof Node)) {
                throw new InvalidArgumentException("All children must be instances of Node");
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
     * @throws NodeException If no handler is attached to the Node.
     * @return mixed The return value of the callback.
     */
    public function execute(...$args) {
        if (!$this->handler) {
            throw new NodeException("This Node has no callback handler");
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

    public function offsetSet(mixed $offset, mixed $value): void {
        if (!($value instanceof Node)) {
            throw new InvalidArgumentException(
                'Error: Only instances of Node can be added as children.'
            );
        }
        if ($offset === null) {
            $this->addChild($value);
        } else {
            $this->children[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->children[$offset]);
    }

    // Implementation of IteratorAggregate
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->children);
    }
}
