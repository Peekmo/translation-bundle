<?php

namespace Umanit\TranslationBundle\Doctrine\Annotation;

/**
 * @Annotation
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SharedAmongstTranslations
{
    /** @var bool */
    public $translate = true;
}
