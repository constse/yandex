<?php

namespace ConstSe\YandexBundle\Entity\Repository;

use ConstSe\YandexBundle\Entity\Site;
use Doctrine\ORM\EntityRepository;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class SiteRepository extends EntityRepository
{
    /**
     * @param Site $specificSite
     * @return Site[]
     */
    public function findAllOrSpecific(Site $specificSite = null)
    {
        $builder = $this->createQueryBuilder('s');

        if ($specificSite) {
            $builder
                ->where('s = :site')
                ->setParameter('site', $specificSite);
        }

        return $builder
            ->getQuery()
            ->getResult();
    }
}
