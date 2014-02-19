<?php

namespace TweedeGolf\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator as BaseDoctrineEntityGenerator;

class DoctrineEntityGenerator extends BaseDoctrineEntityGenerator
{
    protected function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();
        // $entityGenerator->setClassToExtend('TweedeGolf\OkoaBundle\Behavior\Persistable');
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        return $entityGenerator;
    }
}
