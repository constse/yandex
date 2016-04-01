<?php

namespace ConstSe\YandexBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "ConstSe\YandexBundle\Entity\Repository\GroupRepository")
 * @ORM\Table(name = "yandex_direct_groups")
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class Group extends AbstractEntity
{
    /**
     * @ORM\OneToMany(targetEntity = "Ad", mappedBy = "group")
     * @var Ad[]|ArrayCollection
     */
    protected $ads;

    /**
     * @ORM\ManyToOne(targetEntity = "Campaign", inversedBy = "groups")
     * @ORM\JoinColumn(name = "campaign_id", referencedColumnName = "id")
     * @var Campaign
     */
    protected $campaign;

    /**
     * @ORM\Column(name = "context_id", type = "string")
     * @var string
     */
    protected $contextId;

    /**
     * @ORM\OneToMany(targetEntity = "Keyword", mappedBy = "group")
     * @var ArrayCollection|Keyword[]
     */
    protected $keywords;

    /**
     * @ORM\Column(name = "name", type = "string")
     * @var string
     */
    protected $name;

    public function __construct()
    {
        $this->ads = new ArrayCollection();
        $this->keywords = new ArrayCollection();
    }

    /**
     * @param Ad $ad
     * @return $this
     */
    public function addAd(Ad $ad)
    {
        if (!$this->hasAd($ad)) {
            $this->ads->add($ad);
        }

        return $this;
    }

    /**
     * @param Keyword $keyword
     * @return $this
     */
    public function addKeyword(Keyword $keyword)
    {
        if (!$this->hasKeyword($keyword)) {
            $this->keywords->add($keyword);
        }

        return $this;
    }

    /**
     * @return Ad[]|ArrayCollection
     */
    public function getAds()
    {
        return $this->ads;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @return ArrayCollection|Keyword[]
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Ad $ad
     * @return bool
     */
    public function hasAd(Ad $ad)
    {
        return $this->ads->contains($ad);
    }

    /**
     * @param Keyword $keyword
     * @return bool
     */
    public function hasKeyword(Keyword $keyword)
    {
        return $this->keywords->contains($keyword);
    }

    /**
     * @param Ad $ad
     * @return $this
     */
    public function removeAd(Ad $ad)
    {
        $this->ads->removeElement($ad);

        return $this;
    }

    /**
     * @param Keyword $keyword
     * @return $this
     */
    public function removeKeyword(Keyword $keyword)
    {
        $this->keywords->removeElement($keyword);

        return $this;
    }

    /**
     * @param Campaign $campaign
     * @return $this
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @param string $contextId
     * @return $this
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
