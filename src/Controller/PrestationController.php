<?php

namespace App\Controller;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 17/10/18
 * Time: 10:54
 */

class PrestationController extends CrudController
{

    protected $title = 'Prestations'; // Titre affiché dans les vues
    protected $entityName = 'Information'; // L'entité de Doctrine qui sert à construire les vues
    protected $formName = 'InformationPrestationsType';
    protected $formOptions = null;
    protected $manager = 'default';

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $crud = $this->get('app_crud.crud');
        $crud->setParentTwig('administration/layout.html.twig');
        $crud->setThemeTwig(array('form/prototype.html.twig'));
        $crud->setTitle('Prestations');
        $crud->setConnection('default');
        $crud->setEntityName('Information');
        $crud->setFormName('InformationPrestationsType');
        $crud->setJoinSeparator('<br>');
        $crud->getIndexFieldAdder()
            ->add('prestations');

        parent::initialize();
    }

    /**
     * {@inheritdoc}
     */
    protected function sourceQuery($repository) : array
    {
        return $repository->findOrCreateOne();
    }

    /**
     * {@inheritdoc}
     */
    protected function hydrateEntity($entity, bool $new, bool $submitted)
    {
        if (!$new) {
            if (!$submitted) {
                $this->collections[0] = new ArrayCollection();
                foreach ($entity->getPrestations() as $prestation) {
                    $this->collections[0]->add($prestation);
                }
            } else {
                foreach ($this->collections[0] as $prestation) {
                    if (false === $entity->getPrestations()->contains($prestation)) {
                        $this->getDoctrine()->getManager('default')->remove($prestation);
                    }
                }

            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function hasRightCreate() : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasRightView($entity) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasRightUpdate($entity) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasRightDelete($entity) : bool
    {
        return false;
    }

}