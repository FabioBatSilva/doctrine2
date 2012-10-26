<?php

namespace Doctrine\Tests\ORM\Persisters\Generator;

use Doctrine\ORM\Persisters\Generator\BasicEntityPersisterGenerator;

require_once __DIR__ . '/../../../TestInit.php';

/**
 * @group DDC-1889
 */
class BasicEntityPersisterGeneratorTest extends \Doctrine\Tests\OrmFunctionalTestCase
{

    private $namespace;

    public function setUp()
    {
        $this->namespace = uniqid("doctrine_");

        parent::setUp();
    }

    public function testGenerate()
    {
        $metadata   = $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsAddress');
        $generator  = new BasicEntityPersisterGenerator($this->_em, $metadata, $this->namespace);
        $code       = $generator->generate();
        $filename   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.php';

        file_put_contents($filename , $code);

        include $filename;

        unlink($filename);

        $class      = $this->namespace . '\CmsAddressPersister';
        $persister  = new $class($this->_em, $metadata);

        $this->assertInstanceOf('\Doctrine\ORM\Persisters\BasicEntityPersister', $persister);
    }
}