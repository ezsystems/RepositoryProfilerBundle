<?php
namespace eZ\Publish\Profiler\Executor;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Profiler\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Task;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Executor\PAPI\CreateActorVisitor;
use eZ\Publish\Profiler\Executor\PAPI\SubtreeActorVisitor;
use eZ\Publish\Profiler\Aborter;


class PAPI extends Executor
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param array $constraints
     * @param \eZ\Publish\Profiler\Aborter $aborter
     */
    public function __construct( Repository $repository, $constraints, Logger $logger = null, Aborter $aborter = null )
    {
        parent::__construct( $constraints, $logger, $aborter );

        $this->repository = $repository;

        $this->createActorVisitor = new CreateActorVisitor(
            $this->repository->getContentTypeService(),
            $this->repository->getContentService()
        );

        $this->subtreeViewActorVisitor = new SubtreeActorVisitor(
            $this->repository->getContentService(),
            $this->repository->getLocationService(),
            $this->repository->getSearchService()
        );
    }

    /**
     * @param \eZ\Publish\Profiler\Actor $actor
     * @throws \RuntimeException if no visitor for the visited actor class could be found
     * @return void
     */
    public function visitActor( Actor $actor )
    {
        switch( true ) {
            case $actor instanceof \eZ\Publish\Profiler\Actor\Create:
                $this->createActorVisitor->visit( $actor );
            break;
            case $actor instanceof \eZ\Publish\Profiler\Actor\SubtreeView:
                $this->subtreeViewActorVisitor->visit( $actor );
                break;

            default:
                throw new \RuntimeException("No visitor for: " . get_class( $actor ));
        }
    }
}
