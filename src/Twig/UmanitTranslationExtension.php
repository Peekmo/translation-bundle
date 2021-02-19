<?php

namespace Umanit\TranslationBundle\Twig;

use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UmanitTranslationExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    /**
     * @var array
     */
    private $locales;

    /**
     * UmanitTranslationExtension constructor.
     *
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getTests()
    {
        return [
            new \Twig\TwigTest('translatable', function ($object) {
                return $object instanceof TranslatableInterface;
            }),
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getGlobals(): array
    {
        return [
            'locales' => $this->locales,
        ];
    }

}
