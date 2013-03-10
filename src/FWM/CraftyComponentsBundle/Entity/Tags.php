<?php

namespace FWM\CraftyComponentsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FWM\CraftyComponentsBundle\Entity\Tags
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FWM\CraftyComponentsBundle\Entity\TagsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Tags
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
     * @var string $component
     *
     * @ORM\ManyToOne(targetEntity="Components", inversedBy="tags")
     * @ORM\JoinColumn(name="component_id", referencedColumnName="id", nullable=false)
     */
    protected $component;

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
     * Set component
     *
     * @param FWM\CraftyComponentsBundle\Entity\Components $component
     */
    public function setComponent(\FWM\CraftyComponentsBundle\Entity\Components $component)
    {
        $this->component = $component;
    }

    /**
     * Get component
     *
     * @return FWM\CraftyComponentsBundle\Entity\Components 
     */
    public function getComponent()
    {
        return $this->component;
    }
}