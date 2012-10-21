<?php

namespace Doctrine\Tests\ORM\Tools;

use Doctrine\ORM\Tools\EntityPersisterGenerator\BasicEntityPersisterGenerator;

require_once __DIR__ . '/../../../TestInit.php';

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

        $filename   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();


        file_put_contents("$filename.php" , $code);

        include "$filename.php";

        $class      = $this->namespace . '\CmsAddressPersister';
        $persister  = new $class($this->_em, $metadata);

        $this->assertInstanceOf('\Doctrine\ORM\Persisters\BasicEntityPersister', $persister);
    }
}