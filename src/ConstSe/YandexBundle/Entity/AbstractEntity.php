<?php

namespace ConstSe\YandexBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "AUTO")
     * @ORM\Column(name = "id", type = "integer")
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
