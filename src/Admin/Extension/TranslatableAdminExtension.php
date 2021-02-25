<?php

namespace Umanit\TranslationBundle\Admin\Extension;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * SonataAdmin Extension.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableAdminExtension extends AbstractAdminExtension
{
    /**
     * @var array
     */
    private $locales;
    /**
     * @var null
     */
    private $defaultAdminLocale;

    /**
     * TranslatableAdminExtension constructor.
     *
     * @param array $locales
     * @param null  $defaultAdminLocale
     */
    public function __construct(array $locales, $defaultAdminLocale = null)
    {
        $this->locales            = $locales;
        $this->defaultAdminLocale = $defaultAdminLocale;
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function alterNewInstance(AdminInterface $admin, object $object): void
    {
        if (!$admin->id($object)) {
            $object->setLocale($this->getEditLocale($admin));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface $admin
     * @param array          $filterValues
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
        if ($this->defaultAdminLocale) {
            $filterValues['locale'] = [
                'value' => $this->defaultAdminLocale,
            ];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('locale', StringFilter::class, [
            'advanced_filter' => false,
        ], [
            'global_search' => true,
            'field_type' => ChoiceType::class,
	    'field_name' => 'locale',
            'field_options' => [
                'choices' => array_combine($this->locales, $this->locales),
            ]
        ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper): void
    {
        if ($listMapper->has('translations')) {
            $listMapper
                ->get('translations')
                ->setTemplate('@UmanitTranslation/Admin/CRUD/list_translations.html.twig')
            ;
        }

        if ($listMapper->has('_action')) {
            $actions = $listMapper->get('_action')->getOption('actions');
            if ($actions && isset($actions['edit'])) {
                // Overrides edit action
                $actions['edit'] = ['template' => '@UmanitTranslation/Admin/CRUD/list__action_edit.html.twig'];
                $listMapper->get('_action')->setOption('actions', $actions);
            }
        }
    }

    /**
     * {@inheritdoc}.
     *
     * @param AdminInterface  $admin
     * @param RouteCollectionInterface $collection
     */
    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        // Add the tranlate route
        $collection->add('translate', $admin->getRouterIdParameter().'/translate/{newLocale}', [
            '_controller' => 'umanit_translation.controller.translatable_crudcontroller::translate',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function preUpdate(AdminInterface $admin, object $object): void
    {
        // Re-set the locale to make sure the children share the same
        $object->setLocale($object->getLocale());
        parent::preUpdate($admin, $object);
    }

    /**
     * {@inheritdoc}
     *
     * @param AdminInterface      $admin
     * @param MenuItemInterface   $menu
     * @param string              $action
     * @param AdminInterface|null $childAdmin
     */
    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, string $action, AdminInterface $childAdmin = null): void
    {
        // Add the locales switcher dropdown in the edit view
        if ($action === 'edit' && $admin->id($admin->getSubject())) {

            $menu->addChild('language', [
                'label'      => 'Translate ('.$this->getEditLocale($admin).')',
                'attributes' => ['dropdown' => true, 'icon' => 'fa fa-language'],
            ]);
            foreach ($this->locales as $locale) {
                $menu['language']->addChild($locale, [
                    'uri'        => $admin->generateUrl('translate', [
                        'id'        => $admin->id($admin->getSubject()),
                        'newLocale' => $locale,
                    ]),
                    'attributes' => [
                        'icon' => \in_array($locale, $admin->getSubject()->getTranslations(), true) || $locale === $admin->getSubject()->getLocale()
                            ? 'fa fa-check'
                            : 'fa fa-plus',
                    ],
                    'current'    => $locale === $this->getEditLocale($admin),
                ]);
            }
        }
    }

    /**
     * Return the edit locale.
     *
     * @param AdminInterface $admin
     *
     * @return null|string
     */
    private function getEditLocale(AdminInterface $admin)
    {
        if ($admin->getSubject() && $admin->id($admin->getSubject())) {
            return $admin->getSubject()->getLocale();
        }

        if ($this->defaultAdminLocale) {
            return $this->defaultAdminLocale;
        }

        if ($admin->getRequest()) {
            return $admin->getRequest()->getLocale();
        }

        return 'en';
    }
}

