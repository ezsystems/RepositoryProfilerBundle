<?php
namespace eZ\Publish\Profiler\Executor;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Logger;
use eZ\Publish\Profiler\Aborter;

class PAPI extends Executor
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository, Actor\Handler $actorHandler, Logger $logger)
    {
        parent::__construct($actorHandler, $logger);

        $this->repository = $repository;
    }

    public function run(array $constraints, Aborter $aborter)
    {
        $adminUser = $this->repository->getUserService()->loadUser(14);
        $this->repository->setCurrentUser($adminUser);

        parent::run($constraints, $aborter);
    }
}
