<?php

namespace FWM\CraftyComponentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FWM\CraftyComponentsBundle\Entity\Verions
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FWM\CraftyComponentsBundle\Entity\VersionsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Versions
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
     * @var boolean $value
     *
     * @ORM\Column(name="value", type="string")
     */
    protected $value; 

    /**
     * @var boolean $sha
     *
     * @ORM\Column(name="sha", type="string")
     */
    protected $sha;

    /**
     * @var string $file_content
     *
     * @ORM\Column(name="file_content", type="text", length=255, nullable=true)
     */
    private $file_content;

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

    /**
     * @var string $component
     *
     * @ORM\ManyToOne(targetEntity="Components", inversedBy="versions")
     * @ORM\JoinColumn(name="component_id", referencedColumnName="id", nullable=false)
     */
    protected $component;

    /**
     * Set from
     *
     * @param FWM\CraftyComponentsBundle\Entity\Components $component
     */
    public function setComponent(\FWM\CraftyComponentsBundle\Entity\Components $component)
    {
        $this->component = $component;
    }

    /**
     * Get from
     *
     * @return 
     */
    public function getComponent()
    {
        return $this->component;
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
     * Set sha
     *
     * @param string $sha
     */
    public function setSha($sha)
    {
        $this->sha = $sha;
    }

    /**
     * Get sha
     *
     * @return string 
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * Set value
     *
     * @param string $sha
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get sha
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set file_content
     *
     * @param string $fileContent
     */
    public function setFileContent($fileContent)
    {
        $this->file_content = $fileContent;
    }

    /**
     * Get file_content
     *
     * @return string 
     */
    public function getFileContent()
    {
        return $this->file_content;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @ORM\PrePersist
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