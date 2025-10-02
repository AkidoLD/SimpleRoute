<?php

use PHPUnit\Framework\TestCase;
use SimpleRoute\Router\UriSlicer;

final class UriSlicerTest extends TestCase
{
    private UriSlicer $slicer;

    protected function setUp(): void
    {
        $this->slicer = new UriSlicer("/auth/login/edit");
    }

    public function testSegmentsAreParsedCorrectly(): void
    {
        $this->assertTrue($this->slicer->hasNext());
        $this->assertSame("auth", $this->slicer->next());
        $this->assertSame("login", $this->slicer->next());
        $this->assertSame("edit", $this->slicer->next());
        $this->assertFalse($this->slicer->hasNext());
        $this->assertNull($this->slicer->next());
    }

    public function testCursorPosition(): void
    {
        $this->assertSame(0, $this->slicer->cursorPosition());
        $this->slicer->next();
        $this->assertSame(1, $this->slicer->cursorPosition());
        $this->slicer->next();
        $this->assertSame(2, $this->slicer->cursorPosition());
    }

    public function testReset(): void
    {
        $this->slicer->next();
        $this->slicer->reset();
        $this->assertSame(0, $this->slicer->cursorPosition());
        $this->assertSame("auth", $this->slicer->next());
    }

    public function testGetUnusedSegments(): void
    {
        $this->slicer->next(); // consume "auth"
        $unused = $this->slicer->getUnusedSegments();
        $this->assertSame(["login", "edit"], $unused);
        $this->assertFalse($this->slicer->hasNext());
    }

    public function testInvoke(): void
    {
        $this->assertSame("auth", ($this->slicer)());
        $this->assertSame("login", ($this->slicer)());
    }

    public function testToString(): void
    {
        $this->assertSame("/auth/login/edit", (string)$this->slicer);
    }

    public function testSetURI(): void
    {
        $this->slicer->setURI("/new/path");
        $this->assertSame("/new/path", $this->slicer->getURI());
        $this->assertSame("new", $this->slicer->next());
        $this->assertSame("path", $this->slicer->next());
    }

    public function testFromSegmentsFactory(): void
    {
        $segments = ["user", "profile", "edit"];
        $slicer2 = UriSlicer::fromSegments($segments);
        $this->assertSame("/user/profile/edit", $slicer2->getURI());
        $this->assertSame("user", $slicer2->next());
        $this->assertSame("profile", $slicer2->next());
        $this->assertSame("edit", $slicer2->next());
        $this->assertFalse($slicer2->hasNext());
    }

    public function testEmptyURI(): void
    {
        $emptySlicer = new UriSlicer("");
        $this->assertFalse($emptySlicer->hasNext());
        $this->assertNull($emptySlicer->next());
        $this->assertSame("", $emptySlicer->getURI());
    }
}
