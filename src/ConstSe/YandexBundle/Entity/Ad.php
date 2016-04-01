<?php

namespace ConstSe\YandexBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "ConstSe\YandexBundle\Entity\Repository\AdRepository")
 * @ORM\Table(name = "yandex_direct_ads")
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class Ad extends AbstractEntity
{
    /**
     * @ORM\Column(name = "context_id", type = "string")
     * @var string
     */
    protected $contextId;

    /**
     * @ORM\ManyToOne(targetEntity = "Group", inversedBy = "ads")
     * @ORM\JoinColumn(name = "group_id", referencedColumnName = "id")
     * @var Group
     */
    protected $group;

    /**
     * @ORM\Column(name = "text", type = "string")
     * @var string
     */
    protected $text;

    /**
     * @ORM\Column(name = "title", type = "string", nullable = true)
     * @var string
     */
    protected $title;

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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
