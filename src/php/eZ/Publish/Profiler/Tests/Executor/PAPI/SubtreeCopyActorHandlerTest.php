<?php

namespace eZ\Publish\Profiler\Tests\Executor\PAPI;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\SubtreeCopy;
use eZ\Publish\Profiler\Executor\PAPI\SubtreeCopyActorHandler;
use eZ\Publish\Profiler\Storage;
use PHPUnit\Framework\TestCase;

class SubtreeCopyActorHandlerTest extends TestCase
{
    public function testCanHandle()
    {
        $handler = new SubtreeCopyActorHandler(
            $this->createMock(ContentService::class),
            $this->createMock(LocationService::class)
        );

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

        $sourceLocation = new Location([
            'id' => $sourceContent->contentInfo->mainLocationId
        ]);

        $targetLocation = new Location([
            'id' => $targetContent->contentInfo->mainLocationId
        ]);

        $sourceStorage = $this->createSourceStorageMock($sourceContent);
        $targetStorage = $this->createTargetStorageMock($targetContent);

        $contentService = $this->createContentServiceMock($sourceContent, $targetContent);

        $locationService = $this->createMock(LocationService::class);
        $locationService
            ->expects($this->at(0))
            ->method('loadLocation')
            ->with($sourceContent->contentInfo->mainLocationId)
            ->willReturn($sourceLocation);

        $locationService
            ->expects($this->at(1))
            ->method('loadLocation')
            ->with($targetContent->contentInfo->mainLocationId)
            ->willReturn($targetLocation);

        $locationService
            ->expects($this->once())
            ->method('copySubtree')
            ->with($sourceLocation, $targetLocation);

        $handler = new SubtreeCopyActorHandler($contentService, $locationService);
        $handler->handle(new SubtreeCopy($sourceStorage, $targetStorage));
    }

    /**
     * @expectedException \eZ\Publish\Profiler\Actor\Handler\Exception\ActorHandlerException
     */
    public function testHandleWithMissingTarget()
    {
        $content = $this->createContent(1, 10);

        $sourceStorage = $this->createSourceStorageMock($content);
        $targetStorage = $this->createTargetStorageMock(null);

        $locationService = $this->createMock(LocationService::class);
        $locationService
            ->expects($this->never())
            ->method('copySubtree');

        $handler = new SubtreeCopyActorHandler($this->createContentServiceMock($content), $locationService);
        $handler->handle(new SubtreeCopy($sourceStorage, $targetStorage));
    }

    public function testHandleWithNothingToCopy()
    {
        $sourceStorage = $this->createSourceStorageMock(null);
        $targetStorage = $this->createTargetStorageMock(null);

        $locationService = $this->createMock(LocationService::class);
        $locationService
            ->expects($this->never())
            ->method('copySubtree');

        $handler = new SubtreeCopyActorHandler($this->createContentServiceMock(), $locationService);
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

    private function createContentServiceMock(Content $sourceContent = null, Content $targetContent = null)
    {
        $contentService = $this->createMock(ContentService::class);

        if ($sourceContent !== null) {
            $contentService->expects($this->at(0))
                ->method('loadContent')
                ->with($sourceContent->id)
                ->willReturn($sourceContent);

            if ($targetContent !== null) {
                $contentService->expects($this->at(1))
                    ->method('loadContent')
                    ->with($targetContent->id)
                    ->willReturn($targetContent);
            }
        }

        return $contentService;
    }
}
