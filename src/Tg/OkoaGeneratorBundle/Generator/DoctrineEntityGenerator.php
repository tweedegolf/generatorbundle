<?php

namespace Tg\OkoaGeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator as BaseDoctrineEntityGenerator;
use Tg\OkoaGeneratorBundle\Generator\EntityGenerator;

class DoctrineEntityGenerator extends BaseDoctrineEntityGenerator
{
    protected function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();
        // $entityGenerator->setClassToExtend('Tg\OkoaBundle\Behavior\Persistable');
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(false);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        return $entityGenerator;
    }
}
