<?php

namespace Umanit\TranslationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableCRUDController extends CRUDController
{
    /**
     * Translate an entity
     *
     * @return RedirectResponse
     */
    public function translate()
    {
        $request = $this->getRequest();

        $id     = $request->get($this->admin->getIdParameter());
        $locale = $request->get('newLocale');
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $newObject = $this->admin->getModelManager()->findOneBy(\get_class($object), [
            'tuuid'  => $object->getTuuid(),
            'locale' => $locale,
        ]);

        if (empty($newObject)) {
            $this->admin->checkAccess('edit', $object);

            $newObject = $this->container->get('umanit_translation.translation.entity_translator')->translate($object, $locale);
            $this->container->get('doctrine')->getManager()->persist($newObject);
            $this->container->get('doctrine')->getManager()->flush();

            $this->addFlash('sonata_flash_success', 'Translated successfully!');
        }

        return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $newObject->getId()]));
    }
}
