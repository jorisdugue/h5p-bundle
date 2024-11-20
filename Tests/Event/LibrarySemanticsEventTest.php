<?php

namespace Studit\H5PBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Studit\H5PBundle\Event\LibrarySemanticsEvent;

class LibrarySemanticsEventTest extends TestCase
{
    private array $semantics;
    private string $name;
    private int $majorVersion;
    private int $minorVersion;
    private LibrarySemanticsEvent $event;

    protected function setUp(): void
    {
        $this->semantics = ['title' => 'Example', 'description' => 'Example description'];
        $this->name = 'TestPackage';
        $this->majorVersion = 1;
        $this->minorVersion = 0;

        $this->event = new LibrarySemanticsEvent($this->semantics, $this->name, $this->majorVersion, $this->minorVersion);
    }

    public function testGetSemantics()
    {
        $this->assertEquals($this->semantics, $this->event->getSemantics(), 'The semantics should match the initialized value.');
    }

    public function testGetName()
    {
        $this->assertEquals($this->name, $this->event->getName(), 'The name should match the initialized value.');
    }

    public function testGetMajorVersion()
    {
        $this->assertEquals($this->majorVersion, $this->event->getMajorVersion(), 'The major version should match the initialized value.');
    }

    public function testGetMinorVersion()
    {
        $this->assertEquals($this->minorVersion, $this->event->getMinorVersion(), 'The minor version should match the initialized value.');
    }
}
