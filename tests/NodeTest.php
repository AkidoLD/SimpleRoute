<?php

use PHPUnit\Framework\TestCase;
use SimpleRoute\Exceptions\Node\NodeChildIsNotANodeException;
use SimpleRoute\Exceptions\Node\NodeChildKeyMismatchException;
use SimpleRoute\Exceptions\Node\NodeException;
use SimpleRoute\Exceptions\Node\NodeHandlerNotSetException;
use SimpleRoute\Router\Node;

class NodeTest extends TestCase
{
    /** 
     * Test that multiple children can be added to a node
     */
    public function testCanAddChildren()
    {
        $root = new Node('root');
        $node1 = new Node('node_1');
        $node2 = new Node('node_2');

        $root->addChildren([$node1, $node2]);

        $this->assertSame($node1, $root->getChild('node_1'));
        $this->assertSame($node2, $root->getChild('node_2'));
    }

    /**
     * Test that a node cannot be added as a child to itself
     */
    public function testCannotAddNodeToItself()
    {
        $root = new Node('root');

        $this->expectException(NodeException::class);
        $root->addChild($root);
    }

    /**
     * Test that adding a non-Node as a child throws an exception
     */
    public function testAddNotANodeAsChild()
    {
        $root = new Node('root');
        $this->expectException(NodeChildIsNotANodeException::class);

        $root[] = 'not a node';
    }

    /**
     * Test that adding a child with an offset that does not match the key throws an exception
     */
    public function testAddingChildWithOffsetThatDoesNotMatchKey()
    {
        $root = new Node('root');
        $testNode = new Node('test_node');

        $this->expectException(NodeChildKeyMismatchException::class);

        $root['node'] = $testNode;
    }

    /**
     * Test that adding a child with a duplicate key overwrites the previous child
     */
    public function testAddingChildWithDuplicateKeyOverwritesPrevious()
    {
        $root = new Node('root');
        $node1 = new Node('child');
        $node2 = new Node('child');

        $root[] = $node1;
        $root[] = $node2;

        $this->assertSame($node2, $root->getChild('child'));
        $this->assertNotSame($node1, $root->getChild('child'));
    }

    /**
     * Test removing a child from a node
     */
    public function testRemoveChild()
    {
        $root = new Node('root');
        $node = new Node('node');

        $root[] = $node;
        unset($root[$node->getKey()]);

        $this->assertNull($root->getChild($node->getKey()), "The child Node should be removed from the parent");
        $this->assertEquals(0, $root->childrenCount(), "The parent Node should have no children after removal");
    }

    /**
     * Test removing a non-existent child throws an exception
     */
    public function testRemoveNonExistentChildThrowsException()
    {
        $root = new Node('root');

        $this->expectException(NodeChildKeyMismatchException::class);

        unset($root['node']);
    }

    /**
     * Test that the parent of a child is set correctly
     */
    public function testParentIsSetCorrectly()
    {
        $root = new Node('root');
        $child = new Node('child');

        $root->addChild($child);

        $this->assertSame($root, $child->getParent());
    }

    /**
     * Test executing a node's handler returns the expected value
     */
    public function testExecuteHandler()
    {
        $root = new Node('root', fn() => true);
        $this->assertTrue($root->execute(), "The handler has been executed successfully");
    }

    /**
     * Test executing a node without a handler throws an exception
     */
    public function testExecuteNodeWithoutHandler()
    {
        $root = new Node('root');

        $this->expectException(NodeHandlerNotSetException::class);

        $root->execute();
    }
}
