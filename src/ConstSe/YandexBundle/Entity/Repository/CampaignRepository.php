<?php

namespace ConstSe\YandexBundle\Entity\Repository;

use ConstSe\YandexBundle\Entity\Site;
use Doctrine\ORM\EntityRepository;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class CampaignRepository extends EntityRepository
{
    /**
     * @param Site $site
     * @return \ConstSe\YandexBundle\Entity\Campaign[]
     */
    public function findAllHierarchy(Site $site)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c, g, a, k')
            ->from('YandexBundle:Campaign', 'c', 'c.contextId')
            ->leftJoin('c.groups', 'g', null, null, 'g.contextId')
            ->leftJoin('g.ads', 'a', null, null, 'a.contextId')
            ->leftJoin('g.keywords', 'k', null, null, 'k.contextId')
            ->where('c.site = :site')
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult();
    }
}
