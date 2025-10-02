<?php

use PHPUnit\Framework\TestCase;
use SimpleRoute\Router\NodeTree;
use SimpleRoute\Router\Node;
use SimpleRoute\Exceptions\NodeTree\NodeIsRootNodeException;
use SimpleRoute\Exceptions\NodeTree\NodeNotInTreeException;

class NodeTreeTest extends TestCase
{
    private Node $root;
    private Node $child1;
    private Node $child2;
    private Node $grandChild;
    private NodeTree $tree;

    protected function setUp(): void
    {
        $this->root = new Node('root');
        $this->child1 = new Node('child1');
        $this->child2 = new Node('child2');
        $this->grandChild = new Node('grandChild');

        // Build tree
        $this->root->addChild($this->child1);
        $this->root->addChild($this->child2);
        $this->child1->addChild($this->grandChild);

        $this->tree = new NodeTree($this->root);
    }

    public function testTraceNodeParentReturnsCorrectKeys()
    {
        $expected = ['child1'];
        $result = iterator_to_array(NodeTree::traceNodeParent($this->grandChild, $this->root));
        $this->assertEquals($expected, $result);

        // Direct child of root
        $result2 = iterator_to_array(NodeTree::traceNodeParent($this->child1, $this->root));
        $this->assertEquals([], $result2);

        // Node is root -> exception
        $this->expectException(NodeIsRootNodeException::class);
        iterator_to_array(NodeTree::traceNodeParent($this->root, $this->root));
    }

    public function testGetPathExcludesRoot()
    {
        $path = $this->tree->getPath($this->grandChild);
        $this->assertEquals(['child1'], $path);

        $path2 = $this->tree->getPath($this->child1);
        $this->assertEquals([], $path2);
    }

    public function testContainsNode()
    {
        $this->assertTrue($this->tree->contains($this->root));
        $this->assertTrue($this->tree->contains($this->child1));
        $this->assertTrue($this->tree->contains($this->grandChild));

        $outside = new Node('outside');
        $this->assertFalse($this->tree->contains($outside));
    }

    public function testGetPathThrowsIfNodeNotInTree()
    {
        $outside = new Node('outside');
        $this->expectException(NodeNotInTreeException::class);
        $this->tree->getPath($outside);
    }

    public function testTraceNodeParentThrowsIfStopNodeNotInChain()
    {
        $outside = new Node('outside');
        $this->child1->addChild($outside);

        $stopNode = new Node('fakeRoot');
        $this->expectException(NodeNotInTreeException::class);
        iterator_to_array(NodeTree::traceNodeParent($outside, $stopNode));
    }

    public function testGetPathOfTheRootNode(){
        $this->expectException(NodeIsRootNodeException::class);
        $this->tree->getPath($this->root);
    }
}
