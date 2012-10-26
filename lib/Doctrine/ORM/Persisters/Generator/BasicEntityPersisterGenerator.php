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

namespace Doctrine\ORM\Persisters\Generator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Persisters\BasicEntityPersister;

/**
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @since   2.4
 */
class BasicEntityPersisterGenerator extends PersisterGenerator
{

    private $persister;

    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->persister = new BasicEntityPersister($this->em, $this->class);
        $initialize      = $this->getAccessibleMethod($this->persister, 'getSelectColumnsSQL');

        $initialize->invoke($this->persister);
    }

    private function generateResultSetMapping(ResultSetMapping $rsm)
    {
        $code[] = '$this->rsm = new \Doctrine\ORM\Query\ResultSetMapping();';

        foreach ($rsm as $property => $value) {

            if (is_array($value) && empty($value)) {
                continue;
            }

            $string = var_export($value, true);
            $code[] = sprintf('$this->rsm->%s = %s;', $property, $string);
        }

        return $code;
    }

    protected function generateConstructor()
    {
        $rsm  = $this->getPropertyValue($this->persister, 'rsm');
        $code = $this->generateResultSetMapping($rsm);

        return implode(PHP_EOL . str_repeat(' ', 8), $code);
    }

    protected function generateProperties()
    {
        $selectJoinSql    = $this->getPropertyValue($this->persister, 'selectJoinSql');
        $selectColumnList = $this->getPropertyValue($this->persister, 'selectColumnListSql');

        $properties[] = sprintf('protected $selectColumnListSql = %s;', var_export($selectColumnList, true));
        $properties[] = sprintf('protected $selectJoinSql = %s;', var_export($selectJoinSql, true));
        
        return $properties;
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
            'getInsertSQL' => $this->generateGetInsertSQL(),
        );
    }

    protected function getParentClass()
    {
        return '\Doctrine\ORM\Persisters\BasicEntityPersister';
    }
}