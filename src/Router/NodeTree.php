<?php

namespace SimpleRoute\Router;

/**
 * Provides a simple way to traverse a tree of `Node` objects.
 *
 * A `NodeTree` is a helper structure that keeps track of an active node 
 * and allows you to move through the tree step by step. 
 * It is initialized with a root `Node` and always maintains 
 * a reference to the currently active `Node`.
 *
 * Features:
 * - Start traversal from a root node.
 * - Move to child nodes by key using `nextNode()` or the `__invoke()` syntax.
 * - Retrieve the active node at any time.
 *
 * Typical usage:
 * ```php
 * $root = new Node('root');
 * $child = new Node('child');
 * $root->addChild($child);
 *
 * $tree = new NodeTree($root);
 * $tree->nextNode('child'); // moves active node to $child
 * echo $tree->getActiveNode()->getKey(); // "child"
 * ```
 *
 * This class is mainly used internally by the `Router` 
 * to traverse routes when matching requests.
 *
 * @package SimpleRoute\Router
 */
class NodeTree {
    /**
     * The current active `Node` in the traversal.
     *
     * This represents the current position while moving 
     * through the `Node` tree. Starts at the root node 
     * and changes when calling {@see NodeTree::nextNode()}.
     *
     * @var Node|null
     */
    private ?Node $activeNode;

    /**
     * The root `Node` of the tree.
     *
     * This is the entry point of the traversal.
     *
     * @var Node
     */
    private Node $nodeTree;

    /**
     * Create a new `NodeTree` with a given root node.
     *
     * @param Node $nodeTree The root node of the tree
     */
    public function __construct(Node $nodeTree){
        $this->nodeTree = $nodeTree;
        $this->activeNode = $nodeTree;
    }

    /**
     * Get the currently active node.
     *
     * The active node represents the current position 
     * in the traversal of the tree.
     *
     * @return Node The current active node
     */
    public function getActiveNode(): Node{
        return $this->activeNode;
    }

    /**
     * Move to the child node with the given key.
     *
     * Updates the active node to the child matching the provided key.
     * Returns the new active node, or `null` if no child with the key exists.
     *
     * @param string $key The key of the child node to move to
     * @return Node|null The new active node, or null if not found
     */
    public function nextNode(string $key): ?Node{
        return $this->activeNode = $this->activeNode[$key];
    }

    /**
     * Get the root node of the tree.
     *
     * @return Node The root node
     */
    public function getNodeTree(): Node{
        return $this->nodeTree;
    }

    /**
     * Replace the root node and reset the active node of the tree.
     *
     * @param Node $nodeTree The new root node
     * @return void
     */
    public function setNodeTree(Node $nodeTree): void{
        $this->nodeTree = $nodeTree;
        $this->activeNode = $nodeTree;
    }

    /**
     * Shortcut to move to the next node by key.
     *
     * Equivalent to calling {@see NodeTree::nextNode()}.
     *
     * @param string $key The key of the child node to move to
     * @return Node|null The new active node, or null if not found
     */
    public function __invoke(string $key): ?Node{
        return $this->nextNode($key);
    }
}
