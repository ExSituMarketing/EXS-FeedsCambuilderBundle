<?php

namespace EXS\FeedsCambuilderBundle\Command;

use EXS\FeedsCambuilderBundle\Service\FeedsReader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RefreshLivePerformersCommand
 *
 * @package EXS\FeedsCambuilderBundle\Command
 */
class RefreshLivePerformersCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $style;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var FeedsReader
     */
    private $reader;

    /**
     * {@inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('feeds:cambuilder:refresh-live-performers')
            ->setDescription('Reads Cambuilder api and refreshes live performer ids in memcached.')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of performers.', 100)
            ->addOption('ttl', null, InputOption::VALUE_OPTIONAL, 'Memcached entry\'s time to live.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);

        $this->limit = $input->getOption('limit');

        if (null === $this->ttl = $input->getOption('ttl')) {
            $this->ttl = $this->getContainer()->getParameter('exs_feeds_awe.cache_ttl');
        }

        $this->reader = $this->getContainer()->get('exs_feeds_cambuilder.feeds_reader');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $performers = $this->reader->refreshLivePerformers($this->limit, $this->ttl);

        if (0 < count($performers)) {
            $this->style->block([sprintf('Cache refreshed with %d performers.', count($performers))], null, 'info');
        } else {
            $this->style->block(['Impossible to get performers information.', 'Cache not refreshed.'], null, 'error');
        }
    }
}
