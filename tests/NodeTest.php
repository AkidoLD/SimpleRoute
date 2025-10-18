<?php

use PHPUnit\Framework\TestCase;
use AkidoLd\SimpleRoute\Exceptions\Node\NodeChildIsNotANodeException;
use AkidoLd\SimpleRoute\Exceptions\Node\NodeChildKeyMismatchException;
use AkidoLd\SimpleRoute\Exceptions\Node\NodeHandlerNotSetException;
use AkidoLd\SimpleRoute\Exceptions\Node\NodeKeyIsEmptyException;
use AkidoLd\SimpleRoute\Exceptions\Node\NodeSelfReferenceException;
use AkidoLd\SimpleRoute\Router\Node;

class NodeTest extends TestCase
{
    // ==================== Constructor Tests ====================

    public function testConstructorSetsKey()
    {
        $node = new Node('test');
        $this->assertEquals('test', $node->getKey());
    }

    public function testConstructorWithHandler()
    {
        $handler = fn() => 'test';
        $node = new Node('test', $handler);
        
        $this->assertTrue($node->hasHandler());
        $this->assertSame($handler, $node->getHandler());
    }

    public function testConstructorWithParentAddsNodeToParent()
    {
        $root = new Node('root');
        $node = new Node('node', parent: $root);
        
        $this->assertContains($node, $root->getChildren());
        $this->assertSame($root, $node->getParent());
    }

    public function testConstructorTrimsKey()
    {
        $node = new Node('  test  ');
        $this->assertEquals('test', $node->getKey());
    }

    public function testConstructorThrowsExceptionForEmptyKey()
    {
        $this->expectException(NodeKeyIsEmptyException::class);
        new Node('');
    }

    public function testConstructorThrowsExceptionForWhitespaceKey()
    {
        $this->expectException(NodeKeyIsEmptyException::class);
        new Node('   ');
    }

    // ==================== Key Management Tests ====================

    public function testGetKeyReturnsCorrectKey()
    {
        $node = new Node('mykey');
        $this->assertEquals('mykey', $node->getKey());
    }

    // ==================== Parent Management Tests ====================

    public function testGetParentReturnsNullByDefault()
    {
        $node = new Node('test');
        $this->assertNull($node->getParent());
    }

    public function testGetParentReturnsCorrectParent()
    {
        $root = new Node('root');
        $child = new Node('child');
        $root->addChild($child);
        
        $this->assertSame($root, $child->getParent());
    }

    // ==================== Child Management Tests ====================

    public function testAddChildSetsParentCorrectly()
    {
        $root = new Node('root');
        $child = new Node('child');
        
        $root->addChild($child);
        
        $this->assertSame($root, $child->getParent());
    }

    public function testAddChildAddsToChildrenArray()
    {
        $root = new Node('root');
        $child = new Node('child');
        
        $root->addChild($child);
        
        $this->assertSame($child, $root->getChild('child'));
    }

    public function testAddChildThrowsExceptionForSelfReference()
    {
        $root = new Node('root');
        
        $this->expectException(NodeSelfReferenceException::class);
        $root->addChild($root);
    }

    public function testAddChildRemovesNodeFromPreviousParent()
    {
        $oldRoot = new Node('old_root');
        $newRoot = new Node('new_root');
        $node = new Node('node');
        
        $oldRoot->addChild($node);
        $newRoot->addChild($node);
        
        $this->assertNotContains($node, $oldRoot->getChildren());
        $this->assertContains($node, $newRoot->getChildren());
        $this->assertSame($newRoot, $node->getParent());
    }

    public function testAddChildWithDuplicateKeyOverwritesPrevious()
    {
        $root = new Node('root');
        $node1 = new Node('child');
        $node2 = new Node('child');
        
        $root->addChild($node1);
        $root->addChild($node2);
        
        $this->assertSame($node2, $root->getChild('child'));
        $this->assertNotSame($node1, $root->getChild('child'));
    }

    public function testAddChildren()
    {
        $root = new Node('root');
        $node1 = new Node('node_1');
        $node2 = new Node('node_2');
        
        $root->addChildren([$node1, $node2]);
        
        $this->assertSame($node1, $root->getChild('node_1'));
        $this->assertSame($node2, $root->getChild('node_2'));
    }

    public function testAddChildrenThrowsExceptionForNonNode()
    {
        $root = new Node('root');
        
        $this->expectException(NodeChildIsNotANodeException::class);
        $root->addChildren([new Node('test'), 'not a node']);
    }

    // ==================== Remove Child Tests ====================

    public function testRemoveChildRemovesAndReturnsNode()
    {
        $root = new Node('root');
        $node = new Node('node');
        $root->addChild($node);
        
        $removed = $root->removeChild('node');
        
        $this->assertSame($node, $removed);
        $this->assertNull($root->getChild('node'));
    }

    public function testRemoveChildClearsParentReference()
    {
        $root = new Node('root');
        $node = new Node('node');
        $root->addChild($node);
        
        $root->removeChild('node');
        
        $this->assertNull($node->getParent());
    }

    public function testRemoveChildReturnsNullForNonExistent()
    {
        $root = new Node('root');
        $this->assertNull($root->removeChild('nonexistent'));
    }

    public function testRemoveChildReturnsNullWhenNoChildren()
    {
        $root = new Node('root');
        $this->assertNull($root->removeChild('anything'));
    }

    // ==================== Get Children Tests ====================

    public function testGetChildReturnsCorrectNode()
    {
        $root = new Node('root');
        $child = new Node('child');
        $root->addChild($child);
        
        $this->assertSame($child, $root->getChild('child'));
    }

    public function testGetChildReturnsNullForNonExistent()
    {
        $root = new Node('root');
        $this->assertNull($root->getChild('nonexistent'));
    }

    public function testGetChildrenReturnsAllChildren()
    {
        $root = new Node('root');
        $child1 = new Node('child1');
        $child2 = new Node('child2');
        
        $root->addChild($child1);
        $root->addChild($child2);
        
        $children = $root->getChildren();
        
        $this->assertCount(2, $children);
        $this->assertContains($child1, $children);
        $this->assertContains($child2, $children);
    }

    public function testGetChildrenReturnsEmptyArrayWhenNoChildren()
    {
        $root = new Node('root');
        $this->assertEmpty($root->getChildren());
    }

    // ==================== Handler Tests ====================

    public function testSetHandlerSetsHandler()
    {
        $node = new Node('test');
        $handler = fn() => 'result';
        
        $node->setHandler($handler);
        
        $this->assertSame($handler, $node->getHandler());
    }

    public function testSetHandlerCanSetNull()
    {
        $node = new Node('test', fn() => 'test');
        $node->setHandler(null);
        
        $this->assertNull($node->getHandler());
        $this->assertFalse($node->hasHandler());
    }

    public function testGetHandlerReturnsNullByDefault()
    {
        $node = new Node('test');
        $this->assertNull($node->getHandler());
    }

    public function testHasHandlerReturnsFalseByDefault()
    {
        $node = new Node('test');
        $this->assertFalse($node->hasHandler());
    }

    public function testHasHandlerReturnsTrueWhenSet()
    {
        $node = new Node('test', fn() => 'test');
        $this->assertTrue($node->hasHandler());
    }

    public function testExecuteCallsHandler()
    {
        $called = false;
        $node = new Node('test', function() use (&$called) {
            $called = true;
            return 'result';
        });
        
        $result = $node->execute();
        
        $this->assertTrue($called);
        $this->assertEquals('result', $result);
    }

    public function testExecutePassesArguments()
    {
        $node = new Node('test', fn($a, $b) => $a + $b);
        
        $result = $node->execute(5, 3);
        
        $this->assertEquals(8, $result);
    }

    public function testExecuteThrowsExceptionWhenNoHandler()
    {
        $node = new Node('test');
        
        $this->expectException(NodeHandlerNotSetException::class);
        $node->execute();
    }

    public function testInvokeCallsExecute()
    {
        $node = new Node('test', fn() => 'invoked');
        
        $result = $node();
        
        $this->assertEquals('invoked', $result);
    }

    public function testInvokePassesArguments()
    {
        $node = new Node('test', fn($x) => $x * 2);
        
        $result = $node(10);
        
        $this->assertEquals(20, $result);
    }

    // ==================== Utility Methods Tests ====================

    public function testHasChildrenReturnsFalseByDefault()
    {
        $node = new Node('test');
        $this->assertFalse($node->hasChildren());
    }

    public function testHasChildrenReturnsTrueWhenChildrenExist()
    {
        $root = new Node('root');
        $child = new Node('child');
        $root->addChild($child);
        
        $this->assertTrue($root->hasChildren());
    }

    public function testIsLeafReturnsTrueByDefault()
    {
        $node = new Node('test');
        $this->assertTrue($node->isLeaf());
    }

    public function testIsLeafReturnsFalseWhenChildrenExist()
    {
        $root = new Node('root');
        $child = new Node('child');
        $root->addChild($child);
        
        $this->assertFalse($root->isLeaf());
    }

    public function testChildrenCountReturnsZeroByDefault()
    {
        $node = new Node('test');
        $this->assertEquals(0, $node->childrenCount());
    }

    public function testChildrenCountReturnsCorrectCount()
    {
        $root = new Node('root');
        $root->addChild(new Node('child1'));
        $root->addChild(new Node('child2'));
        $root->addChild(new Node('child3'));
        
        $this->assertEquals(3, $root->childrenCount());
    }

    public function testToStringReturnsKey()
    {
        $node = new Node('mykey');
        $this->assertEquals('mykey', (string)$node);
    }

    // ==================== Countable Interface Tests ====================

    public function testCountReturnsNumberOfChildren()
    {
        $root = new Node('root');
        $root->addChild(new Node('child1'));
        $root->addChild(new Node('child2'));
        
        $this->assertCount(2, $root);
    }

    // ==================== ArrayAccess Interface Tests ====================

    public function testOffsetGetReturnsChild()
    {
        $root = new Node('root');
        $child = new Node('child');
        $root->addChild($child);
        
        $this->assertSame($child, $root['child']);
    }

    public function testOffsetGetReturnsNullForNonExistent()
    {
        $root = new Node('root');
        $this->assertNull($root['nonexistent']);
    }

    public function testOffsetExistsReturnsTrueWhenChildExists()
    {
        $root = new Node('root');
        $root->addChild(new Node('child'));
        
        $this->assertTrue(isset($root['child']));
    }

    public function testOffsetExistsReturnsFalseWhenChildDoesNotExist()
    {
        $root = new Node('root');
        $this->assertFalse(isset($root['nonexistent']));
    }

    public function testOffsetSetAddsChild()
    {
        $root = new Node('root');
        $child = new Node('child');
        
        $root[] = $child;
        
        $this->assertSame($child, $root['child']);
    }

    public function testOffsetSetWithMatchingKey()
    {
        $root = new Node('root');
        $child = new Node('child');
        
        $root['child'] = $child;
        
        $this->assertSame($child, $root['child']);
    }

    public function testOffsetSetThrowsExceptionForNonNode()
    {
        $root = new Node('root');
        
        $this->expectException(NodeChildIsNotANodeException::class);
        $root[] = 'not a node';
    }

    public function testOffsetSetThrowsExceptionForKeyMismatch()
    {
        $root = new Node('root');
        $testNode = new Node('test_node');
        
        $this->expectException(NodeChildKeyMismatchException::class);
        $root['wrong_key'] = $testNode;
    }

    public function testOffsetSetCallsAddChild()
    {
        $oldRoot = new Node('old_root');
        $newRoot = new Node('new_root');
        $child = new Node('child');
        
        $oldRoot[] = $child;
        $newRoot[] = $child;
        
        $this->assertNotContains($child, $oldRoot->getChildren());
        $this->assertContains($child, $newRoot->getChildren());
    }

    public function testOffsetUnsetRemovesChild()
    {
        $root = new Node('root');
        $child = new Node('child');
        $root->addChild($child);
        
        unset($root['child']);
        
        $this->assertNull($root['child']);
        $this->assertEquals(0, $root->childrenCount());
    }

    // ==================== IteratorAggregate Interface Tests ====================

    public function testGetIteratorReturnsArrayIterator()
    {
        $root = new Node('root');
        $this->assertInstanceOf(ArrayIterator::class, $root->getIterator());
    }

    public function testCanIterateOverChildren()
    {
        $root = new Node('root');
        $child1 = new Node('child1');
        $child2 = new Node('child2');
        
        $root->addChild($child1);
        $root->addChild($child2);
        
        $keys = [];
        foreach ($root as $key => $child) {
            $keys[] = $key;
        }
        
        $this->assertEquals(['child1', 'child2'], $keys);
    }

    public function testIteratorIncludesAllChildren()
    {
        $root = new Node('root');
        $child1 = new Node('child1');
        $child2 = new Node('child2');
        
        $root->addChild($child1);
        $root->addChild($child2);
        
        $children = [];
        foreach ($root as $child) {
            $children[] = $child;
        }
        
        $this->assertContains($child1, $children);
        $this->assertContains($child2, $children);
        $this->assertCount(2, $children);
    }
}