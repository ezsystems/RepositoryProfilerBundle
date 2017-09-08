<?php

namespace eZ\Publish\Profiler\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\SubtreeCopy;
use eZ\Publish\Profiler\Executor\SPI\SubtreeCopyActorHandler;
use eZ\Publish\Profiler\Storage;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use PHPUnit\Framework\TestCase;

class SubtreeCopyActorHandlerTest extends TestCase
{
    public function testCanHandle()
    {
        $handler = $this->createHandler();

        $validActor = new SubtreeCopy(
            $this->createSourceStorageMock(),
            $this->createTargetStorageMock()
        );

        $invalidActor = $this->createMock(Actor::class);

        $this->assertTrue($handler->canHandle($validActor));
        $this->assertFalse($handler->canHandle($invalidActor));
    }

    public function testHandle()
    {
        $sourceContent = $this->createContent(10, 1);
        $targetContent = $this->createContent(20, 2);

        $sourceStorage = $this->createSourceStorageMock($sourceContent);
        $targetStorage = $this->createTargetStorageMock($targetContent);

        $locationHandler = $this->createMock(LocationHandler::class);
        $locationHandler
            ->expects($this->once())
            ->method('copySubtree')
            ->with($sourceContent->contentInfo->mainLocationId, $targetContent->contentInfo->mainLocationId);

        $handler = $this->createHandler($locationHandler);
        $handler->handle(new SubtreeCopy($sourceStorage, $targetStorage));
    }

    public function testHandleWithNothingToCopy()
    {
        $sourceStorage = $this->createSourceStorageMock(null);
        $targetStorage = $this->createTargetStorageMock(null);

        $locationHandler = $this->createMock(LocationHandler::class);
        $locationHandler
            ->expects($this->never())
            ->method('copySubtree');

        $handler = $this->createHandler($locationHandler);
        $handler->handle(new SubtreeCopy($sourceStorage, $targetStorage));
    }

    /**
     * @expectedException \eZ\Publish\Profiler\Actor\Handler\Exception\ActorHandlerException
     */
    public function testHandleWithMissingTarget()
    {
        $content = $this->createContent();

        $sourceStorage = $this->createSourceStorageMock($content);
        $targetStorage = $this->createSourceStorageMock(null);

        $locationHandler = $this->createMock(LocationHandler::class);
        $locationHandler
            ->expects($this->never())
            ->method('copySubtree');

        $handler = $this->createHandler($locationHandler);
        $handler->handle(new SubtreeCopy($sourceStorage, $targetStorage));
    }

    private function createContent($id = null, $mainLocationId = null)
    {
        return new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => $id,
                    'mainLocationId' => $mainLocationId
                ])
            ])
        ]);
    }

    private function createHandler(LocationHandler $locationHandler = null)
    {
        $persistenceHandler = $this->createMock(PersistenceHandler::class);
        if ($locationHandler !== null) {
            $persistenceHandler
                ->expects($this->any())
                ->method('locationHandler')
                ->willReturn($locationHandler);
        }

        return new SubtreeCopyActorHandler($persistenceHandler);
    }

    private function createSourceStorageMock(Content $content = null)
    {
        $sourceStorage = $this->createMock(Storage::class);
        if ($content !== null) {
            $sourceStorage
                ->expects($this->once())
                ->method('pull')
                ->willReturn($content);
        }

        return $sourceStorage;
    }

    private function createTargetStorageMock(Content $content = null)
    {
        $targetStorage = $this->createMock(Storage::class);
        if ($content !== null) {
            $targetStorage
                ->expects($this->once())
                ->method('get')
                ->willReturn($content);
        }

        return $targetStorage;
    }
}
