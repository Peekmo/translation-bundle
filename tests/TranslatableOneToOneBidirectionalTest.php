<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalChild;
use AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalParent;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableOneToOneBidirectionalTest extends AbstractBaseTest
{
    const TARGET_LOCALE = 'fr';

    /** @test */
    public function it_can_translate_simple_value()
    {
        $child  = new TranslatableOneToOneBidirectionalChild();
        $parent = new TranslatableOneToOneBidirectionalParent();

        $parent->setSimpleChild($child);
        $child->setSimpleParent($parent);

        $this->em->persist($parent);

        $parentTranslation = $this->translator->translate($parent, self::TARGET_LOCALE);

        $this->em->persist($parentTranslation);
        $this->em->flush();

        $this->assertIsTranslation($parent, $parentTranslation);
        $this->assertAttributeContains(self::TARGET_LOCALE, 'locale', $parentTranslation->getSimpleChild());
    }

    /** @test */
    public function it_cannot_share_translatable_entity_value_amongst_translations()
    {
        $this->expectException(\ErrorException::class);

        $child  = new TranslatableOneToOneBidirectionalChild();
        $parent = new TranslatableOneToOneBidirectionalParent();

        $parent->setSharedChild($child);
        $child->setSharedParent($parent);

        $this->translator->translate($parent, self::TARGET_LOCALE);
    }

    /** @test */
    public function it_can_empty_translatable_entity_value()
    {
        $child  = new TranslatableOneToOneBidirectionalChild();
        $parent = new TranslatableOneToOneBidirectionalParent();

        $parent->setEmptyChild($child);
        $child->setEmptyParent($parent);

        $this->em->persist($parent);
        $this->em->persist($child);

        $parentTranslation = $this->translator->translate($parent, self::TARGET_LOCALE);

        $this->em->persist($parentTranslation);
        $this->em->flush();

        $this->assertIsTranslation($parent, $parentTranslation);

        $this->assertEquals(null, $parentTranslation->getEmptyChild());
    }

    /**
     * Assert a translation is actually a translation.
     *
     * @param TranslatableInterface $source
     * @param TranslatableInterface $translation
     */
    protected function assertIsTranslation(TranslatableInterface $source, TranslatableInterface $translation)
    {
        $this->assertAttributeContains(self::TARGET_LOCALE, 'locale', $translation);
        $this->assertAttributeContains($source->getTuuid(), 'tuuid', $translation);
        $this->assertNotEquals(spl_object_hash($source), spl_object_hash($translation));
    }
}
