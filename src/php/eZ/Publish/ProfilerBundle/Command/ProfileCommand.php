<?php

namespace eZ\Publish\ProfilerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use EzSystems\PlatformInstallerBundle\Installer;

class ProfileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('profiler:run')
            ->setDescription('Run profiling')
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'Target to run against: spi or papi'
            )
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
            )
            ->addOption(
                'no-reset',
                null,
                InputOption::VALUE_NONE,
                'Skip resetting the database – based on the existing content this might cause an undefined state'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $executor = $this->getContainer()->get('ezpublish.profiler.executor.' . $input->getArgument('target'));

        $dialog = $this->getHelper('dialog');
        if (!$input->getOption('yes') &&
            !$dialog->askConfirmation($output, "<question>Really run the profiler? This will reset the database.</question>", false)) {
            return 1;
        }

        if (!file_exists($profile = $input->getArgument('profile'))) {
            $output->writeln("<error>File $profile does not exist.</error>");
            return 1;
        }

        if (!$input->getOption('no-reset')) {
            $this->resetDatabase( $output );
        }

        $output->writeln("<info>Run $profile</info>");
        include $profile;

        $output->writeln("<info>Statistics</info>");
        $output->writeln($this->getContainer()->get('ezpublish.profiler.logger')->showSummary());
    }

    /**
     * Reset database
     *
     * @return void
     */
    protected function resetDatabase(OutputInterface $output)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $parameters = $connection->getParams();
        $name = isset($parameters['path']) ? $parameters['path'] : (isset($parameters['dbname']) ? $parameters['dbname'] : false);
        unset($parameters['dbname']);

        $output->writeln("<info>Reset database $name</info>");
        $tempConnection = \Doctrine\DBAL\DriverManager::getConnection($parameters);
        $tempConnection->getSchemaManager()->dropAndCreateDatabase($name);

        $output->writeln("<info>Install schema</info>");
        $installer = new Installer\CleanInstaller($connection);
        $installer->setOutput( $output );
        $installer->importSchema();
        $installer->importData();

        return null;
    }
}
