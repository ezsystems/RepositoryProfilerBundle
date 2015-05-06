<?php

namespace eZ\Publish\ProfilerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ProfileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('profiler:run')
            ->setDescription('Run profiling')
            ->addArgument(
                'profile',
                InputArgument::REQUIRED,
                'Profile to run'
            )
            ->addOption(
                'yes',
                null,
                InputOption::VALUE_NONE,
                'Force run, which will reset the current database.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');
        if (!$input->getOption('yes') &&
            !$dialog->askConfirmation($output, "<question>Really run the profiler? This will reset the database.</question>", false)) {
            return 1;
        }

        if (!file_exists($profile = $input->getArgument('profile'))) {
            $output->writeln("<error>File $profile does not exist.</error>");
            return 1;
        }

        $output->writeln("Run $profile");

        $container = $this->getContainer();
        include $profile;
        $output->writeln($logger->showSummary());
    }
}
