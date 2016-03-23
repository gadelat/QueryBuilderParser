<?php

namespace gadelat\test\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @Entity
 */
class Opportunity {

    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Heading
     *
     * @var string
     *
     * @Column(type="text", nullable=true)
     *
     */
    protected $heading;

    /**
     * One or more sectors (ID)
     *
     * @ManyToMany(targetEntity="Sector", inversedBy="opportunities", cascade={"persist"})
     * @JoinTable(name="opportunity_sector_lookup")
     */
    protected $sectors;

}
