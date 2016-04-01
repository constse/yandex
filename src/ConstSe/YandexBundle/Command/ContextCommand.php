<?php

namespace ConstSe\YandexBundle\Command;

use ConstSe\YandexBundle\Entity\Ad;
use ConstSe\YandexBundle\Entity\Campaign;
use ConstSe\YandexBundle\Entity\Group;
use ConstSe\YandexBundle\Entity\Keyword;
use ConstSe\YandexBundle\Entity\Site;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class ContextCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('yandex:direct:synchronize')
            ->addOption('site', null, InputOption::VALUE_OPTIONAL, null, -1);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        /** @var \Doctrine\ORM\EntityManager $manager */
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var \ConstSe\YandexBundle\Entity\Repository\SiteRepository $siteRepository */
        $siteRepository = $manager->getRepository('YandexBundle:Site');

        /** @var Site $specificSite */
        $specificSite = $siteRepository->find($input->getOption('site'));
        $this->synchronize($output, $specificSite);

        set_time_limit(5);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Site $specificSite
     * @return void
     */
    protected function synchronize(OutputInterface $output, Site $specificSite = null)
    {
        $output->writeln('Start synchronization');

        /** @var \Doctrine\ORM\EntityManager $manager */
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var \ConstSe\YandexBundle\Entity\Repository\SiteRepository $siteRepository */
        $siteRepository = $manager->getRepository('YandexBundle:Site');
        /** @var \ConstSe\YandexBundle\Entity\Repository\CampaignRepository $campaignRepository */
        $campaignRepository = $manager->getRepository('YandexBundle:Campaign');
        /** @var \ConstSe\YandexBundle\Utils\YandexDirectParser $parser */
        $parser = $this->getContainer()->get('yandex.direct_parser');

        foreach ($siteRepository->findAllOrSpecific($specificSite) as $site) {
            if (!$site->getDirectLogin()) {
                $output->writeln(sprintf('Site "%s" has not Yandex.Direct login', $site->getUrl()));

                continue;
            }

            try {
                $importCampaigns = $parser->loadClientCampaigns($site->getDirectLogin());
            } catch (\Exception $e) {
                $output->writeln(
                    sprintf(
                        'Failed to load "%s" campaigns: %s in %s line %d',
                        $site->getDirectLogin(),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    )
                );

                continue;
            }

            $campaigns = $campaignRepository->findAllHierarchy($site);

            foreach ($importCampaigns as $importCampaign) {
                if (array_key_exists($importCampaign['id'], $campaigns)) {
                    $campaign = $campaigns[$importCampaign['id']];
                } else {
                    $campaign = new Campaign();
                    $campaign
                        ->setContextId($importCampaign['id'])
                        ->setSite($site);
                }

                $campaign
                    ->setActive(true)
                    ->setFinishedAt($importCampaign['finishedAt'])
                    ->setName($importCampaign['name'])
                    ->setStartedAt($importCampaign['startedAt']);
                $manager->persist($campaign);

                foreach ($importCampaign['groups'] as $importGroup) {
                    $group = $campaign->getGroups()->get($importGroup['id']);

                    if (!$group) {
                        $group = new Group();
                        $group
                            ->setCampaign($campaign)
                            ->setContextId($importGroup['id']);
                    }

                    $group->setName($importGroup['name']);
                    $campaign->addGroup($group);
                    $manager->persist($group);

                    foreach ($importGroup['ads'] as $importAd) {
                        $ad = $group->getAds()->get($importAd['id']);

                        if (!$ad) {
                            $ad = new Ad();
                            $ad
                                ->setContextId($importAd['id'])
                                ->setGroup($group);
                        }

                        $ad
                            ->setText($importAd['text'])
                            ->setTitle($importAd['title']);
                        $manager->persist($ad);
                    }

                    foreach ($importGroup['keywords'] as $importKeyword) {
                        $keyword = $group->getKeywords()->get($importKeyword['id']);

                        if (!$keyword) {
                            $keyword = new Keyword();
                            $keyword
                                ->setContextId($importKeyword['id'])
                                ->setGroup($group);
                        }

                        $keyword
                            ->setPhrase($importKeyword['phrase'])
                            ->setProductivity($importKeyword['productivity']);
                        $manager->persist($keyword);
                    }
                }
            }

            foreach ($campaigns as $campaign) {
                if (!array_key_exists($campaign->getContextId(), $importCampaigns)) {
                    $campaign->setActive(false);
                    $manager->persist($campaign);
                }
            }

            $manager->flush();
            $manager->clear('ConstSe\YandexBundle\Entity\Keyword');
            $manager->clear('ConstSe\YandexBundle\Entity\Ad');
            $manager->clear('ConstSe\YandexBundle\Entity\Group');
            $manager->clear('ConstSe\YandexBundle\Entity\Campaign');
        }

        $output->writeln('Finish synchronization');
    }
}
