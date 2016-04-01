<?php

namespace ConstSe\YandexBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "ConstSe\YandexBundle\Entity\Repository\KeywordRepository")
 * @ORM\Table(name = "yandex_direct_keywords")
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class Keyword
{
    /**
     * @ORM\Column(name = "context_id", type = "string")
     * @var string
     */
    protected $contextId;

    /**
     * @ORM\ManyToOne(targetEntity = "Group", inversedBy = "keywords")
     * @ORM\JoinColumn(name = "group_id", referencedColumnName = "id")
     * @var Group
     */
    protected $group;

    /**
     * @ORM\Column(name = "phrase", type = "string")
     * @var string
     */
    protected $phrase;

    /**
     * @ORM\Column(name = "productivity", type = "float")
     * @var float
     */
    protected $productivity;

    public function __construct()
    {
        $this->productivity = 0.0;
    }

    /**
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * @return float
     */
    public function getProductivity()
    {
        return $this->productivity;
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
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param string $phrase
     * @return $this
     */
    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * @param float $productivity
     * @return $this
     */
    public function setProductivity($productivity)
    {
        $this->productivity = $productivity;

        return $this;
    }
}
