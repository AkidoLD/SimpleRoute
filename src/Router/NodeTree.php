<?php

namespace SimpleRoute\Router;

use SimpleRoute\Exceptions\NodeTree\NodeIsRootNodeException;
use SimpleRoute\Exceptions\NodeTree\NodeNotInTreeException;
use SimpleRoute\Exceptions\NodeTree\NodeOutOfTreeException;

class NodeTree {

    /**
     * Currently active node in the tree traversal.
     *
     * Represents the current position in the tree. Can be null if tree is empty or reset.
     *
     * @var Node|null
     */
    private ?Node $activeNode;

    /**
     * Root node of the tree.
     *
     * The entry point for the traversal.
     *
     * @var Node
     */
    private Node $rootNode;

    /**
     * Create a new NodeTree starting from a given root node.
     *
     * @param Node $rootNode
     */
    public function __construct(Node $rootNode){
        $this->rootNode = $rootNode;
        $this->activeNode = $rootNode;
    }

    /**
     * Get the currently active node.
     *
     * @return Node|null Returns the active node or null if tree has no active node
     */
    public function getActiveNode(): ?Node{
        return $this->activeNode;
    }

    /**
     * Set the activeNode to rootNode
     * 
     * Utilile when you want to restart the NodeTree travel
     * 
     * @return void
     */
    public function resetActiveNode(){
        $this->activeNode = $this->rootNode;
    }

    /**
     * Move the active node to its child with the given key.
     *
     * Updates the active node to the child matching the provided key.
     *
     * @param string $key The key of the child node to move to
     * @return Node|null The new active node, or null if no child with the key exists
     */
    public function nextNode(string $key): ?Node{
        return $this->activeNode = $this->activeNode[$key];
    }

    /**
     * Get the root node of the tree.
     *
     * @return Node The root node
     */
    public function getRootNode(): Node{
        return $this->rootNode;
    }

    /**
     * Replace the root node and reset the active node.
     *
     * @param Node $rootNode The new root node
     * @return void
     */
    public function setRootNode(Node $rootNode): void{
        $this->rootNode = $rootNode;
        $this->activeNode = $rootNode;
    }

    /**
     * Trace all parents of a node up to (but not including) the stop node.
     *
     * @param Node $node   The node whose parents are traced
     * @param Node|null $stopAt Optional node to stop at (usually the root)
     * @return array Keys of parent nodes, ordered from top → bottom
     * @throws NodeIsRootNodeException If the node itself is the stop node
     * @throws NodeNotInTreeException  If stopAt is specified but not found
     */
    public static function traceNodeParent(Node $node, ?Node $stopAt = null): array {
        if ($stopAt !== null && $node === $stopAt) {
            throw new NodeIsRootNodeException(
                "Node '{$node->getKey()}' is the root node and has no parents"
            );
        }
    
        $keys = [];
        $current = $node;
    
        while ($current !== null) {
            if ($current === $stopAt) {
                break;
            }
            $keys[] = $current->getKey();
            $current = $current->getParent();
        }
    
        if ($stopAt !== null && $current !== $stopAt) {
            throw new NodeNotInTreeException(
                "Node '{$node->getKey()}' is not under the specified stop node '{$stopAt->getKey()}'"
            );
        }
    
        return array_reverse($keys);
    }
    
    /**
     * Get the full path (keys) from root to the node (excluding root).
     *
     * @param Node $node Node to get the path for
     * @return array Path from root → node, excluding root key
     * @throws NodeNotInTreeException If node is not in this tree
     */
    public function getPath(Node $node): array {
        return self::traceNodeParent($node, $this->rootNode);
    }
    
    /**
     * Check if a node belongs to this tree.
     *
     * @param Node $node Node to check
     * @return bool True if node belongs to this tree, false otherwise
     */
    public function contains(Node $node): bool {
        if ($this->rootNode === $node) {
            return true;
        }
    
        try {
            $this->getPath($node);
            return true;
        } catch (NodeNotInTreeException) {
            return false;
        }
    }
    
    

    /**
     * Shortcut to move to the next node by key.
     *
     * Equivalent to calling nextNode().
     *
     * @param string $key
     * @return Node|null
     */
    public function __invoke(string $key): ?Node{
        return $this->nextNode($key);
    }
}
