<?php

namespace Oro\Bundle\SecurityProBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Component\Log\OutputLogger;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class LicenseCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const COMMAND_NAME = 'oro:cron:enterprise:license';

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        // Every day at 0 hour 0 minute
        return '0 0 * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Verify enterprise license information');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger    = new OutputLogger($output);
        $serverAgent = $this->getContainer()->get('oro_securitypro.licence.server_agent');

        if (!$this->getContainer()->getParameter('enterprise_licence')) {
            $logger->warning('Enterprise license is empty');
        }

        try {
            $serverAgent->sendStatusInformation();
            $logger->notice('License information sent');
        } catch (\Exception $e) {
            $logger->critical('Could not send license information', ['exception' => $e]);
        }
    }
}
