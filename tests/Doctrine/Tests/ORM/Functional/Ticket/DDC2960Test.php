<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;

/**
 * @group DDC-2960
 */
class DDC2960Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    /**
     * @expectedException Doctrine\ORM\Mapping\MappingException
     * @expectedExceptionMessage Association field "Doctrine\Tests\ORM\Functional\Ticket\DDC2960UserInfo#extra" is configured as part of the second-level cache, but the entity "Doctrine\Tests\ORM\Functional\Ticket\DDC2960ExtraUserInfo" is not.
     */
    public function testTicket()
    {
        $this->_em->getClassMetadata(DDC2960UserInfo::CLASSNAME);
    }
}

/**
 * @Entity
 * @Cache
 */
class DDC2960UserInfo
{
    const CLASSNAME = __CLASS__;

    /**
     * @Id
     * @Column
     */
    protected $id;

    /**
     * @Cache
     * @OneToOne(targetEntity="DDC2960ExtraUserInfo", mappedBy="info")
     */
    protected $extra;
}

/**
 * @Entity
 */
class DDC2960ExtraUserInfo
{
    /* !! Without @Cache ANNOTATION on ExtraUserInfo !! */
    const CLASSNAME = __CLASS__;

    /**
     * @Id
     * @OnToOne
     * @OneToOne(targetEntity="DDC2960UserInfo", inversedBy="extra")
     */
    protected $info;

    /**
     * @Column
     */
    protected $extra;
}
