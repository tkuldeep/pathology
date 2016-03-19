<?php

namespace PathologyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Report
 *
 * @ORM\Table(name="report")
 * @ORM\Entity(repositoryClass="PathologyBundle\Repository\ReportRepository")
 */
class Report
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
     * @var array
     *
     * @ORM\Column(name="testReports", type="json_array")
     */
    private $testReports;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reports")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id")
     */
    private $patient;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var datetime
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

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
     * Set testReports
     *
     * @param array $testReports
     * @return Report
     */
    public function setTestReports($testReports)
    {
        $this->testReports = $testReports;

        return $this;
    }

    /**
     * Get testReports
     *
     * @return array
     */
    public function getTestReports()
    {
        return $this->testReports;
    }

    /**
     * Set patient
     *
     * @param \PathologyBundle\Entity\User $patient
     * @return Report
     */
    public function setPatient(\PathologyBundle\Entity\User $patient = null)
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Get patient
     *
     * @return \PathologyBundle\Entity\User
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Report
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Report
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
