<?php

namespace Tg\OkoaGeneratorBundle\Generator;

use Doctrine\ORM\Tools\EntityGenerator as BaseEntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class EntityGenerator extends BaseEntityGenerator
{
    /**
     * @var string
     */
    protected $fieldVisibility = 'protected';

    /**
     * @var string
     */
    protected static $classTemplate =
'<?php

<namespace>

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Tg\OkoaBundle\Behavior\Persistable;

<entityAnnotation>
<entityClassName> extends Persistable
{
<entityBody>
}
';

    public function generateEntityClass(ClassMetadataInfo $metadata)
    {
        $placeHolders = array(
            '<namespace>',
            '<entityAnnotation>',
            '<entityClassName>',
            '<entityBody>'
        );

        $replacements = array(
            $this->generateEntityNamespace($metadata),
            $this->generateEntityDocBlock($metadata),
            $this->generateEntityClassName($metadata),
            $this->generateEntityBody($metadata)
        );

        $code = str_replace($placeHolders, $replacements, self::$classTemplate);

        return str_replace('<spaces>', $this->spaces, $code);
    }
}