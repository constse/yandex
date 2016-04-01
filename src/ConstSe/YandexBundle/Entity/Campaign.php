<?php

namespace ConstSe\YandexBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "ConstSe\YandexBundle\Entity\Repository\CampaignRepository")
 * @ORM\Table(name = "yandex_direct_campaigns")
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class Campaign extends AbstractEntity
{
    /**
     * @ORM\Column(name = "active", type = "boolean")
     * @var bool
     */
    protected $active;

    /**
     * @ORM\Column(name = "context_id", type = "string")
     * @var string
     */
    protected $contextId;

    /**
     * @ORM\Column(name = "finished_at", type = "date", nullable = true)
     * @var \DateTime
     */
    protected $finishedAt;

    /**
     * @ORM\OneToMany(targetEntity = "Group", mappedBy = "campaign")
     * @var ArrayCollection|Group[]
     */
    protected $groups;

    /**
     * @ORM\Column(name = "name", type = "string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity = "Site", inversedBy = "campaigns")
     * @ORM\JoinColumn(name = "site_id", referencedColumnName = "id")
     * @var Site
     */
    protected $site;

    /**
     * @ORM\Column(name = "started_at", type = "date")
     * @var \DateTime
     */
    protected $startedAt;

    public function __construct()
    {
        $this->active = true;
        $this->groups = new ArrayCollection();
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function addGroup(Group $group)
    {
        if (!$this->hasGroup($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * @return ArrayCollection|Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function hasGroup(Group $group)
    {
        return $this->groups->contains($group);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

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
     * @param \DateTime $finishedAt
     * @return $this
     */
    public function setFinishedAt(\DateTime $finishedAt = null)
    {
        $this->finishedAt = $finishedAt;

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

    /**
     * @param Site $site
     * @return $this
     */
    public function setSite(Site $site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @param \DateTime $startedAt
     * @return $this
     */
    public function setStartedAt(\DateTime $startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }
}
