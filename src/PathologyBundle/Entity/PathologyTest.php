<?php

namespace PathologyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * PathologyTest
 *
 * @ORM\Table(name="pathology_test")
 * @ORM\Entity(repositoryClass="PathologyBundle\Repository\PathologyTestRepository")
 */
class PathologyTest
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="referenceValue", type="string", length=255)
     */
    private $referenceValue;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=255)
     */
    private $unit;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="pathologyTest")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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
     * @return PathologyTest
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set referenceValue
     *
     * @param string $referenceValue
     * @return PathologyTest
     */
    public function setReferenceValue($referenceValue)
    {
        $this->referenceValue = $referenceValue;

        return $this;
    }

    /**
     * Get referenceValue
     *
     * @return string
     */
    public function getReferenceValue()
    {
        return $this->referenceValue;
    }

    /**
     * Set unit
     *
     * @param string $unit
     * @return PathologyTest
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Add users
     *
     * @param \PathologyBundle\Entity\User $users
     * @return PathologyTest
     */
    public function addUser(\PathologyBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \PathologyBundle\Entity\User $users
     */
    public function removeUser(\PathologyBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
