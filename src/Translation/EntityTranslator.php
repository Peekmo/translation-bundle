<?php

namespace Umanit\TranslationBundle\Translation;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\Handlers\TranslationHandlerInterface;
use Umanit\TranslationBundle\Utils\AnnotationHelper;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class EntityTranslator
{
    /**
     * @var array
     */
    protected $locales;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var TranslationHandlerInterface[]
     */
    protected $handlers;

    /**
     * @var AnnotationHelper
     */
    private $annotationHelper;

    /**
     * EntityTranslator constructor.
     *
     * @param array                    $locales
     * @param EventDispatcherInterface $eventDispatcher
     * @param AnnotationHelper         $annotationHelper
     */
    public function __construct(
        array $locales,
        EventDispatcherInterface $eventDispatcher,
        AnnotationHelper $annotationHelper
    ) {
        $this->locales          = $locales;
        $this->eventDispatcher  = $eventDispatcher;
        $this->annotationHelper = $annotationHelper;
    }

    /**
     * Translate an entity.
     *
     * @param TranslatableInterface $data
     * @param string                $locale
     *
     * @return mixed
     */
    public function translate(TranslatableInterface $data, string $locale)
    {
        return $this->processTranslation(new TranslationArgs($data, $data->getLocale(), $locale));
    }

    /**
     * Process the translation.
     *
     * @internal
     *
     * @param TranslationArgs $args
     *
     * @return mixed
     */
    public function processTranslation(TranslationArgs $args)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($args)) {
                if (null !== $args->getProperty()) {
                    if ($this->annotationHelper->isSharedAmongstTranslations($args->getProperty())) {
                        return $handler->handleSharedAmongstTranslations($args);
                    }

                    if ($this->annotationHelper->isEmptyOnTranslate($args->getProperty())) {
                        if (!$this->annotationHelper->isNullable($args->getProperty())) {
                            throw new \LogicException(sprintf(
                                'The property %s::%s can not use the @EmptyOnTranslate() annotation because it is not nullable.',
                                $args->getProperty()->class,
                                $args->getProperty()->name
                            ));
                        }

                        return $handler->handleEmptyOnTranslate($args);
                    }
                }

                return $handler->translate($args);
            }
        }

        return $args->getDataToBeTranslated();
    }

    /**
     * Service call.
     *
     * @param TranslationHandlerInterface $handler
     * @param null                        $priority
     */
    public function addTranslationHandler(TranslationHandlerInterface $handler, $priority = null)
    {
        if (null === $priority) {
            $this->handlers[] = $handler;
        } else {
            $this->handlers[$priority] = $handler;
        }
    }
}
