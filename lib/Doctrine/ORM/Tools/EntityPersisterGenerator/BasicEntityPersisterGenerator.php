<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Tools\EntityPersisterGenerator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\BasicEntityPersister;

/**
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @since   2.4
 */
class BasicEntityPersisterGenerator extends Generator
{

    private $persister;

    public function __construct(EntityManager $em, ClassMetadata $class, $namespace)
    {
        parent::__construct($em, $class, $namespace);

        $this->persister = new BasicEntityPersister($this->em, $this->class);
    }


    protected function generateConstructor()
    {
        $method   = new \ReflectionMethod($this->persister, 'getSelectColumnsSQL');
        $property = new \ReflectionProperty($this->persister, 'rsm');

        $property->setAccessible(true);
        $method->setAccessible(true);
        $method->invoke($this->persister);

        $rsm = serialize($property->getValue($this->persister));

        return sprintf('$this->rsm  = unserialize(%s);', var_export($rsm, true));
    }

    protected function generateClassName()
    {
        return sprintf('%sPersister extends \Doctrine\ORM\Persisters\BasicEntityPersister', $this->class->reflClass->getShortName());
    }

    private function generateGetSelectColumnsSQL()
    {
        $method = new \ReflectionMethod($this->persister, 'getSelectColumnsSQL');
        $method->setAccessible(true);

        $sql = $method->invoke($this->persister);

        return sprintf('return %s;', var_export($sql, true));
    }

    private function generateGetInsertSQL()
    {
        $method = new \ReflectionMethod($this->persister, 'getInsertSQL');
        $method->setAccessible(true);

        $sql = $method->invoke($this->persister);

        return sprintf('return %s;', var_export($sql, true));
    }

    protected function generateMethods()
    {
        return array(
            'getSelectColumnsSQL' => $this->generateGetSelectColumnsSQL(),
            'getInsertSQL'        => $this->generateGetInsertSQL(),
        );
    }
}