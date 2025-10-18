<?php

use PHPUnit\Framework\TestCase;
use AkidoLd\SimpleRoute\Router\NodeTree;
use AkidoLd\SimpleRoute\Router\Node;
use AkidoLd\SimpleRoute\Exceptions\NodeTree\NodeNotInTreeException;

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

        // Build tree: root → child1 → grandChild
        //                  → child2
        $this->root->addChild($this->child1);
        $this->root->addChild($this->child2);
        $this->child1->addChild($this->grandChild);

        $this->tree = new NodeTree($this->root);
    }

    // ==================== Constructor Tests ====================

    public function testConstructorSetsRootAndActiveNode()
    {
        $newTree = new NodeTree($this->root);
        
        $this->assertSame($this->root, $newTree->getRootNode());
        $this->assertSame($this->root, $newTree->getActiveNode());
    }

    // ==================== tracePathKeys() Tests ====================
    
    public function testTracePathKeysFromGrandChildReturnsFullPath()
    {
        $result = NodeTree::tracePathKeys($this->grandChild);
        $this->assertEquals(['root', 'child1', 'grandChild'], $result);
    }

    public function testTracePathKeysFromRootReturnsOnlyRoot()
    {
        $result = NodeTree::tracePathKeys($this->root);
        $this->assertEquals(['root'], $result);
    }

    public function testTracePathKeysFromDirectChildReturnsRootAndChild()
    {
        $result = NodeTree::tracePathKeys($this->child1);
        $this->assertEquals(['root', 'child1'], $result);
    }

    public function testTracePathKeysWithStopAtRootExcludesRoot()
    {
        $result = NodeTree::tracePathKeys($this->grandChild, $this->root);
        $this->assertEquals(['child1', 'grandChild'], $result);
    }

    public function testTracePathKeysWithStopAtMiddleNodeStopsCorrectly()
    {
        $result = NodeTree::tracePathKeys($this->grandChild, $this->child1);
        $this->assertEquals(['grandChild'], $result);
    }

    public function testTracePathKeysWhenStopNodeNotInChainThrowsException()
    {
        $outsideNode = new Node('outside');
        
        $this->expectException(NodeNotInTreeException::class);
        $this->expectExceptionMessage("is not under the specified stop node");
        
        NodeTree::tracePathKeys($this->grandChild, $outsideNode);
    }

    public function testTracePathKeysWithSingleNodeTreeReturnsOnlyNode()
    {
        $singleNode = new Node('single');
        $result = NodeTree::tracePathKeys($singleNode);
        $this->assertEquals(['single'], $result);
    }

    // ==================== getPathKeys() Tests ====================

    public function testGetPathKeysFromRootReturnsEmpty()
    {
        $result = $this->tree->getPathKeys($this->root);
        $this->assertEquals([], $result);
    }

    public function testGetPathKeysFromGrandChildExcludesRoot()
    {
        $result = $this->tree->getPathKeys($this->grandChild);
        $this->assertEquals(['child1', 'grandChild'], $result);
    }

    public function testGetPathKeysFromDirectChildReturnsOnlyChild()
    {
        $result = $this->tree->getPathKeys($this->child1);
        $this->assertEquals(['child1'], $result);
    }

    public function testGetPathKeysWhenNodeNotInTreeThrowsException()
    {
        $outsideNode = new Node('outside');
        
        $this->expectException(NodeNotInTreeException::class);
        
        $this->tree->getPathKeys($outsideNode);
    }

    public function testGetPathKeysAfterAddingNodeDynamicallyWorksCorrectly()
    {
        $newChild = new Node('newChild');
        $this->grandChild->addChild($newChild);
        
        $result = $this->tree->getPathKeys($newChild);
        $this->assertEquals(['child1', 'grandChild', 'newChild'], $result);
    }

    // ==================== contains() Tests ====================

    public function testContainsWithRootNodeReturnsTrue()
    {
        $this->assertTrue($this->tree->contains($this->root));
    }

    public function testContainsWithChildNodeReturnsTrue()
    {
        $this->assertTrue($this->tree->contains($this->child1));
    }

    public function testContainsWithGrandChildNodeReturnsTrue()
    {
        $this->assertTrue($this->tree->contains($this->grandChild));
    }

    public function testContainsWithNodeNotInTreeReturnsFalse()
    {
        $outsideNode = new Node('outside');
        $this->assertFalse($this->tree->contains($outsideNode));
    }

    // ==================== getRootNode() Tests ====================

    public function testGetRootNodeReturnsCorrectNode()
    {
        $this->assertSame($this->root, $this->tree->getRootNode());
    }

    public function testGetRootNodeAfterNavigationStillReturnsRoot()
    {
        $this->tree->moveToChild('child1');
        $this->assertSame($this->root, $this->tree->getRootNode());
    }

    // ==================== setRootNode() Tests ====================

    public function testSetRootNodeReplacesRootAndResetsActiveNode()
    {
        $newRoot = new Node('newRoot');
        $this->tree->setRootNode($newRoot);
        
        $this->assertSame($newRoot, $this->tree->getRootNode());
        $this->assertSame($newRoot, $this->tree->getActiveNode());
    }

    public function testSetRootNodeAfterNavigationResetsActiveNode()
    {
        $this->tree->moveToChild('child1');
        $this->assertSame($this->child1, $this->tree->getActiveNode());
        
        $newRoot = new Node('newRoot');
        $this->tree->setRootNode($newRoot);
        
        // Active node should be reset to new root
        $this->assertSame($newRoot, $this->tree->getActiveNode());
    }

    public function testSetRootNodeInvalidatesOldTree()
    {
        $newRoot = new Node('newRoot');
        $this->tree->setRootNode($newRoot);
        
        // Old nodes should no longer be in the tree
        $this->assertFalse($this->tree->contains($this->child1));
    }

    // ==================== getActiveNode() Tests ====================

    public function testGetActiveNodeInitiallyReturnsRoot()
    {
        $newTree = new NodeTree($this->root);
        $this->assertSame($this->root, $newTree->getActiveNode());
    }

    public function testGetActiveNodeAfterNavigationReturnsCurrentNode()
    {
        $this->tree->moveToChild('child1');
        $this->assertSame($this->child1, $this->tree->getActiveNode());
    }

    // ==================== resetActiveNode() Tests ====================

    public function testResetActiveNodeResetsToRoot()
    {
        $this->tree->moveToChild('child1');
        $this->tree->resetActiveNode();
        $this->assertSame($this->root, $this->tree->getActiveNode());
    }

    public function testResetActiveNodeAfterMultipleNavigations()
    {
        $this->tree->moveToChild('child1');
        $this->tree->moveToChild('grandChild');
        $this->tree->resetActiveNode();
        
        $this->assertSame($this->root, $this->tree->getActiveNode());
    }

    // ==================== moveToChild() Tests ====================

    public function testMoveToChildWithValidKeyUpdatesActiveNode()
    {
        $this->tree->moveToChild('child1');
        $this->assertSame($this->child1, $this->tree->getActiveNode());
    }

    public function testMoveToChildWithInvalidKeyReturnsNull()
    {
        $result = $this->tree->moveToChild('nonexistent');
        $this->assertNull($result);
    }

    public function testMoveToChildCanNavigateMultipleLevels()
    {
        $this->tree->moveToChild('child1');
        $this->tree->moveToChild('grandChild');
        
        $this->assertSame($this->grandChild, $this->tree->getActiveNode());
    }

    public function testMoveToChildReturnsTheNewActiveNode()
    {
        $result = $this->tree->moveToChild('child1');
        $this->assertSame($this->child1, $result);
    }

    // ==================== __invoke() Tests ====================

    public function testInvokeMovesToChild()
    {
        $tree = $this->tree;
        $result = $tree('child1');
        
        $this->assertSame($this->child1, $result);
        $this->assertSame($this->child1, $this->tree->getActiveNode());
    }

    public function testInvokeWithInvalidKeyReturnsNull()
    {
        $tree = $this->tree;
        $result = $tree('nonexistent');
        
        $this->assertNull($result);
    }

    public function testInvokeCanChainNavigations()
    {
        $tree = $this->tree;
        $tree('child1');
        $result = $tree('grandChild');
        
        $this->assertSame($this->grandChild, $result);
    }

    // ==================== Deprecated Methods Tests ====================

    public function testNextNodeStillWorks()
    {
        @$result = $this->tree->nextNode('child1'); // @ suppresses deprecation warning
        $this->assertSame($this->child1, $result);
    }

    public function testNextNodeTriggersDeprecationWarning()
    {
        $errorReported = false;
        
        set_error_handler(function($errno, $errstr) use (&$errorReported) {
            if ($errno === E_USER_DEPRECATED && str_contains($errstr, 'nextNode() is deprecated')) {
                $errorReported = true;
            }
            return true;
        });
        
        $this->tree->nextNode('child1');
        
        restore_error_handler();
        
        $this->assertTrue($errorReported, 'Deprecation warning was not triggered');
    }

    public function testTraceNodeParentStillWorks()
    {
        @$result = NodeTree::traceNodeParent($this->grandChild, $this->root);
        $this->assertEquals(['child1', 'grandChild'], $result);
    }

    public function testTraceNodeParentTriggersDeprecationWarning()
    {
        $errorReported = false;
        
        set_error_handler(function($errno, $errstr) use (&$errorReported) {
            if ($errno === E_USER_DEPRECATED && str_contains($errstr, 'use tracePathKeys() instead')) {
                $errorReported = true;
            }
            return true;
        });
        
        NodeTree::traceNodeParent($this->grandChild, $this->root);
        
        restore_error_handler();
        
        $this->assertTrue($errorReported, 'Deprecation warning was not triggered');
    }
}