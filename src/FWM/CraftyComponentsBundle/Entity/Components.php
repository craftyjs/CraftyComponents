<?php

namespace FWM\CraftyComponentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * FWM\CraftyComponentsBundle\Entity\Components
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FWM\CraftyComponentsBundle\Entity\ComponentsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Components
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string $author_name
     *
     * @ORM\Column(name="author_name", type="string", length=255)
     */
    private $author_name;

    /**
     * @var string $author_url
     *
     * @ORM\Column(name="author_url", type="string", length=255)
     */
    private $author_url;

    /**
     * @var string $license_type
     *
     * @ORM\Column(name="license_type", type="string", length=255)
     */
    private $license_type;

    /**
     * @var string $license_url
     *
     * @ORM\Column(name="license_url", type="string", length=255)
     */
    private $license_url;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string $homepage
     *
     * @ORM\Column(name="homepage", type="string", length=255)
     */
    private $homepage;

    /**
     * @var string $repoUrl
     *
     * @ORM\Column(name="repo_url", type="string", length=255)
     */
    private $repoUrl;

    /**
     * @var array $versions
     *
     * @ORM\OneToMany(targetEntity="Versions", mappedBy="component")
     */
    private $versions;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive;

    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return array 
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author_name
     *
     * @param string $authorName
     */
    public function setAuthorName($authorName)
    {
        $this->author_name = $authorName;
    }

    /**
     * Get author_name
     *
     * @return string 
     */
    public function getAuthorName()
    {
        return $this->author_name;
    }

    /**
     * Set author_url
     *
     * @param string $authorUrl
     */
    public function setAuthorUrl($authorUrl)
    {
        $this->author_url = $authorUrl;
    }

    /**
     * Get author_url
     *
     * @return string 
     */
    public function getAuthorUrl()
    {
        return $this->author_url;
    }

    /**
     * Set license_type
     *
     * @param string $licenseType
     */
    public function setLicenseType($licenseType)
    {
        $this->license_type = $licenseType;
    }

    /**
     * Get license_type
     *
     * @return string 
     */
    public function getLicenseType()
    {
        return $this->license_type;
    }

    /**
     * Set license_url
     *
     * @param string $licenseUrl
     */
    public function setLicenseUrl($licenseUrl)
    {
        $this->license_url = $licenseUrl;
    }

    /**
     * Get license_url
     *
     * @return string 
     */
    public function getLicenseUrl()
    {
        return $this->license_url;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set homepage
     *
     * @param string $homepage
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    /**
     * Get homepage
     *
     * @return string 
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set repoUrl
     *
     * @param string $repoUrl
     */
    public function setRepoUrl($repoUrl)
    {
        $this->repoUrl = $repoUrl;
    }

    /**
     * Get repoUrl
     *
     * @return string 
     */
    public function getRepoUrl()
    {
        return $this->repoUrl;
    }

    /**
     * @ORM\prePersist
     */
    public function setCreatedAtValue()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @ORM\prePersist
     */
    public function setIsActiveValue()
    {
        $this->setIsActive(true);
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}