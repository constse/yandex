<?php

namespace ConstSe\YandexBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "ConstSe\YandexBundle\Entity\Repository\SiteRepository")
 * @ORM\Table(name = "yandex_direct_sites")
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class Site extends AbstractEntity
{
    /**
     * @var ArrayCollection|Campaign[]
     */
    protected $campaigns;

    /**
     * @ORM\Column(name = "direct_login", type = "string", nullable = true)
     * @var string
     */
    protected $directLogin;

    /**
     * @ORM\Column(name = "url", type = "string", length = 100, unique = true)
     * @var string
     */
    protected $url;

    public function __construct()
    {
        $this->campaigns = new ArrayCollection();
    }

    /**
     * @param Campaign $campaign
     * @return $this
     */
    public function addCampaign(Campaign $campaign)
    {
        if (!$this->hasCampaign($campaign)) {
            $this->campaigns->add($campaign);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|Campaign[]
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * @return string
     */
    public function getDirectLogin()
    {
        return $this->directLogin;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param Campaign $campaign
     * @return bool
     */
    public function hasCampaign(Campaign $campaign)
    {
        return $this->campaigns->contains($campaign);
    }

    /**
     * @param Campaign $campaign
     * @return $this
     */
    public function removeCampaign(Campaign $campaign)
    {
        $this->campaigns->removeElement($campaign);

        return $this;
    }

    /**
     * @param string $directLogin
     * @return $this
     */
    public function setDirectLogin($directLogin)
    {
        $this->directLogin = $directLogin;

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
