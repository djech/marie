<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * CrudController controller.
 */
abstract class CrudController extends Controller
{
    // Error text
    private $textAddSuccess = 'Ajout effectué avec succès !';
    private $textUpdateSuccess = 'Mise à jour effectuée avec succès !';
    private $textAddError = 'Une erreur de saisie empêche l\'ajout !';
    private $textUpdateError = 'Une erreur de saisie empêche la mise à jour !';
    private $textUniqueConstraintViolation = 'L\'enregistrement a échouée car un champ dans ce formulaire ne respecte pas l\'unicité par rapport aux autres enregistrements !';
    private $textForeignKeyConstraintViolationHelp = 'L\'enregistrement que vous essayez de supprimer porte l\'identifiant n° %s et est référencé dans la table "%s". Attention, d\'autres tables peuvent également en contenir !';
    private $textNotNullConstraintViolation = 'Tous les champs marqués d\'une étoile rouge doivent être remplis !';
    private $textErrorForeignKeyConstraint = 'La suppression de l\'enregistrement n\'est pas autorisée tant que d\'autres enregistrements y font référence !';

    // Chemins calculés automatiquement dans le contructeur
    private $bundlePath = null;
    private $entityPath = null;
    private $formPath = null;

    // Routes calculées automatiquement dans le contructeur
    private $baseRoute = null;
    protected $indexRoute = null;
    protected $newRoute = null;
    protected $showRoute = null;
    protected $editRoute = null;
    protected $deleteRoute = null;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->initialize();
    }

    /**
     * Initialisation appelée à chaque instantiation du contrôleur.
     * La particularité par rapport au constructeur est que à ce moment là l'objet du contrôleur est complet
     * et donc l'objet $this->container bien rempli.
     */
    protected function initialize()
    {
        $classPath = get_class($this);
        $crud = $this->get('app_crud.crud');

        // Chemins - Impose que le controller soit dans un répertoire "Controller"
        $this->bundlePath = substr($classPath, 0, strpos($classPath, '\Controller'));
        if (!isset($this->entityPath)) {
            $this->entityPath = $this->bundlePath.'\Entity\\'.$crud->getEntityName();
        }
        if (!isset($this->formPath)) {
            $this->formPath = $this->bundlePath.'\Form\\'.$crud->getFormName();
        }

        // Routes - Impose une route qui correspond au nom de du contrôleur tout en minuscule
        $classPathExplode = explode('\\', $classPath);
        $className = end($classPathExplode);
        $this->baseRoute = strtolower(str_replace('Controller', '', $className));
        $this->indexRoute = $this->baseRoute.'_index';
        $this->routeToSource = $this->baseRoute.'_source';
        $this->newRoute = $this->baseRoute.'_new';
        $this->showRoute = $this->baseRoute.'_show';
        $this->editRoute = $this->baseRoute.'_edit';
        $this->deleteRoute = $this->baseRoute.'_delete';
    }

    /**
     * Liste toutes les entités
     * @return Response
     */
    public function indexAction() : Response
    {
        $crud = $this->get('app_crud.crud');

        $formOptions = $crud->getFormInformation($this->createForm($this->formPath, null, $crud->getFormOptions()));

        $colNumber = 0;
        $columnDef = array(array('targets'=>$colNumber++, 'searchable'=>false, 'sortable'=>false)); // Première colonne non triable

        $fields = $crud->getIndexFieldAdder()->getFields();
        foreach ($fields as $field) {
            // Traitement des colonnes qui contiennent des dates
            if (isset($formOptions[$field['name']])
                && ($formOptions[$field['name']]['type'] === 'DateType'
                || $formOptions[$field['name']]['type'] === 'DateTimeType')
                || $field['type'] === 'date'
            ) {
                $columnDef[] = array('targets'=>$colNumber, 'type'=>'date-euro');
            }

            $colNumber++;
        }

        return $this->render($crud->getIndexTwig(), array(
            'parent_twig' => $crud->getParentTwig(),
            'title' => $crud->getTitle(),
            'fields' => $fields,
            'form_options' => $formOptions,
            'route_new' => $this->newRoute,
            'right_create' => $this->hasRightCreate(),
            'column_def' => $columnDef,
            'ajax_source' => $this->routeToSource,
            'displayActions' => $crud->getDisplayActions()
        ));
    }

    /**
     * Données source pour datatable
     * @return Response JsonResponse
     */
    public function sourceAction() : JsonResponse
    {
        $crud = $this->get('app_crud.crud');
        $manager = $this->getDoctrine()->getManager($crud->getConnection());

        $data = array();

        $formOptions = $crud->getFormInformation($this->createForm($this->formPath, null, $crud->getFormOptions()));

        //
        // Fonction prévue pour être redéfinie par le développeur
        //
        $entities = $this->sourceQuery($manager->getRepository($this->entityPath));

        // Construction des données
        foreach ($entities as $entity) {
            $line = array();

            // Actions
            if ($crud->getDisplayActions() === true) {
                $line[] = trim($this->render($crud->getActionTwig(), array(
                    'entity' => $entity,
                    'route_new' => $this->newRoute,
                    'route_show' => $this->showRoute,
                    'route_edit' => $this->editRoute,
                    'right_view' => $this->hasRightView($entity),
                    'right_update' => $this->hasRightUpdate($entity),
                    'use_copy' => $crud->getUseCopy()
                ))->getContent());
            }

            // Valeurs
            $fields = $crud->getIndexFieldAdder()->getFields();
            foreach ($fields as $field) {
                $params = array('type' => null, 'multiple' => null, 'choices' => null);

                // Recherche d'informations issues du formulaire
                if (isset($formOptions[$field['name']])) {
                    $params['type'] = $formOptions[$field['name']]['type'];
                    $params['multiple'] = $formOptions[$field['name']]['multiple'];
                    $params['choices'] = $formOptions[$field['name']]['choices'];
                }

                $value = call_user_func(array($entity, 'get'.ucfirst($field['name'])));
                if (is_callable($field['callback'])) {
                    $params['value'] = $field['callback']($value);
                } else {
                    $params['value'] = $value;
                }

                $params['join_separator'] = $crud->getJoinSeparator();
                $params['filter'] = $field['filter'];

                $line[] = trim($this->render($crud->getFilterTwig(), $params)->getContent());
            }
            $data[] = $line;
        }

        $response = new JsonResponse();
        $response->setData(array(
            'aaData' => $data
        ));

        return $response;
    }

    /**
     * Crée une nouvelle entité
     * @param  Request
     * @param  int      $id      Identifiant de l'entité à cloner
     * @return Response
     */
    public function newAction(Request $request, int $id = null) : Response
    {
        // Ajout interdit
        if (!$this->hasRightCreate()) {
            throw new AccessDeniedException();
        }

        $crud = $this->get('app_crud.crud');
        $session = $this->get('session');
        $manager = $this->getDoctrine()->getManager($crud->getConnection());

        if ($crud->getUseCopy() === false || $id === null) {
            $entity = new $this->entityPath();
        } else {
            $entity = clone $crud->convertToEntity($id, $this->entityPath, $crud->getConnection());
        }

        //
        // Fonction prévue pour être redéfinie par le développeur
        //
        $this->hydrateEntity($entity, true, false);

        $form = $this->createForm($this->formPath, $entity, $crud->getFormOptions());
        $form->handleRequest($request);

        // On enregistre le referer au premier affichage du formulaire en vue de la future validation
        if ($crud->getUseReferer() && !$form->isSubmitted()) {
            $session->set('_app_referer', $request->headers->get('referer'));
        }

        if ($form->isSubmitted()) {
            if ($form->isValid() && $this->isValid($form, true)) {

                //
                // Fonction prévue pour être redéfinie par le développeur
                //
                $this->hydrateEntity($entity, true, true);

                try {
                    $manager->persist($entity);
                    $manager->flush($entity);

                    $this->addFlash('success', $this->textAddSuccess);

                    // Redirection vers le referer
                    if ($crud->getUseReferer() && $session->has('_app_referer')) {
                        return $this->redirect($session->remove('_app_referer'));
                    }

                    return $this->redirectToRoute($this->showRoute, array('id' => $entity->getId()));
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('danger', $this->textUniqueConstraintViolation);
                } catch (NotNullConstraintViolationException $e) {
                    $this->addFlash('danger', $this->textNotNullConstraintViolation);
                }
            } else {
                $this->addFlash('danger', $this->textAddError);
            }
        }

        return $this->render($crud->getNewTwig(), array(
            'parent_twig' => $crud->getParentTwig(),
            'themes_twig' => $crud->getThemeTwig(),
            'title' => $crud->getTitle(),
            'form' => $form->createView(),
            'tab_fields' => $crud->getFormTabFieldAdder()->getFields()
        ));
    }

    /**
     * Aperçu d'une entité existante
     * @param  Request
     * @param  int      $id Identifiant de l'entité
     * @return Response
     */
    public function showAction(Request $request, int $id) : Response
    {
        $crud = $this->get('app_crud.crud');
        $session = $this->get('session');

        $entity = $crud->convertToEntity($id, $this->entityPath, $crud->getConnection());

        // Droits gérés finements
        if (!$this->hasRightView($entity)) {
            throw new AccessDeniedException();
        }

        $formDelete = $this->createDeleteForm($entity);
        $formOptions = $crud->getFormInformation($this->createForm($this->formPath, null, $crud->getFormOptions()));

        return $this->render($crud->getShowTwig(), array(
            'parent_twig' => $crud->getParentTwig(),
            'title' => $crud->getTitle(),
            'entity' => $entity,
            'form_options' => $formOptions,
            'delete_form' => $formDelete->createView(),
            'route_index' => $this->indexRoute,
            'route_new' => $this->newRoute,
            'route_edit' => $this->editRoute,
            'right_create' => $this->hasRightCreate(),
            'right_update' => $this->hasRightUpdate($entity),
            'right_delete' => $this->hasRightDelete($entity),
            'join_separator' => $crud->getJoinSeparator(),
            'referer' => $crud->getUseReferer(),
            'add_delete_texte' => $crud->getAdditionalDeleteText()
        ));
    }

    /**
     * Affiche un formulaire pour éditer une entité existante
     * @param  Request  $request
     * @param  int      $id      Identifiant de l'entité
     * @return Response
     */
    public function editAction(Request $request, int $id) : Response
    {
        $crud = $this->get('app_crud.crud');
        $session = $this->get('session');
        $manager = $this->getDoctrine()->getManager($crud->getConnection());

        $entity = $crud->convertToEntity($id, $this->entityPath, $crud->getConnection());

        // Droits gérés finements
        if (!$this->hasRightUpdate($entity)) {
            throw new AccessDeniedException();
        }

        //
        // Fonction prévue pour être redéfinie par le développeur
        //
        $this->hydrateEntity($entity, false, false);

        // Création des formulaires
        $deleteForm = $this->createDeleteForm($entity);
        $editForm = $this->createForm($this->formPath, $entity, $crud->getFormOptions());
        $editForm->handleRequest($request);

        // On enregistre le referer au premier affichage du formulaire en vue de la future suppression
        if ($crud->getUseReferer() && !$editForm->isSubmitted()) {
            $session->set('_app_referer', $request->headers->get('referer'));
        }

        if ($editForm->isSubmitted()) {
            if ($editForm->isValid() && $this->isValid($editForm, false)) {

                //
                // Fonction prévue pour être redéfinie par le développeur
                //
                $this->hydrateEntity($entity, false, true);

                try {
                    $manager->flush();

                    $this->addFlash('success', $this->textUpdateSuccess);

                    // Redirection vers la même page
                    $this->redirect($request->getUri());
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('danger', $this->textUniqueConstraintViolation);
                } catch (NotNullConstraintViolationException $e) {
                    $this->addFlash('danger', $this->textNotNullConstraintViolation);
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash('danger', $this->textErrorForeignKeyConstraint);

                    $key = $crud::subStr($e->getMessage(), 'Key (id)=(', ')');
                    $table = $crud::subStr($e->getMessage(), 'from table "', '"');
                    $this->addFlash('warning', sprintf($this->textForeignKeyConstraintViolationHelp, $key, $table));
                }
            } else {
                $this->addFlash('danger', $this->textUpdateError);
            }
        }

        return $this->render($crud->getEditTwig(), array(
            'parent_twig' => $crud->getParentTwig(),
            'themes_twig' => $crud->getThemeTwig(),
            'title' => $crud->getTitle(),
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'right_delete' => $this->hasRightDelete($entity),
            'tab_fields' => $crud->getFormTabFieldAdder()->getFields(),
            'add_delete_texte' => $crud->getAdditionalDeleteText()
        ));
    }

    /**
     * Supprime une entité
     * @param  Request  $request
     * @param  int      $id      Identifiant de l'entité
     * @return Response
     */
    public function deleteAction(Request $request, int $id) : Response
    {
        $crud = $this->get('app_crud.crud');
        $session = $this->get('session');

        $entity = $crud->convertToEntity($id, $this->entityPath, $crud->getConnection());

        // Droits gérés finements
        if (!$this->hasRightDelete($entity)) {
            throw new AccessDeniedException();
        }

        $form = $this->createDeleteForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manager = $this->getDoctrine()->getManager($crud->getConnection());
                $this->deleteQuery($manager, $entity);

                $manager->flush($entity);
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->get('logger')->error($e->getMessage());
                $this->addFlash('danger', $crud->getTitle().': '.$this->textErrorForeignKeyConstraint);

                $key = $crud::subStr($e->getMessage(), 'Key (id)=(', ')');
                $table = $crud::subStr($e->getMessage(), 'from table "', '"');
                $this->addFlash('warning', sprintf($this->textForeignKeyConstraintViolationHelp, $key, $table));
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        // Redirection vers le referer
        if ($crud->getUseReferer() && $session->has('_app_referer')) {
            return $this->redirect($session->remove('_app_referer'));
        }

        return $this->redirectToRoute($this->indexRoute);
    }

    /**
     * Crée un formulaire pour supprimer une entité
     * @param Entity $entity L'entité
     * @return Form Le formulaire
     */
    protected function createDeleteForm($entity) : Form
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($this->deleteRoute, array('id' => $entity->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Requête de récupération des enregistrements de la vue liste
     * @param object $repository Repository de l'Entity Manager
     * @return array Liste des entitées
     */
    protected function sourceQuery($repository) : array
    {
        return $repository->findAll();
    }

    /**
     * Requête de suppression de l'entité
     * @param  EntityManager $manager L'entity manager
     * @param  Entity $entity L'entité
     */
    protected function deleteQuery(EntityManager $manager, $entity)
    {
        $manager->remove($entity);
    }

    /**
     * Donner la possibilité d'hydrater l'entité
     * @param  Entity $entity L'entité
     * @param  bool $new  true = nouvel enregistrement, false = enregistrement existant
     * @param  bool $submitted  true = formulaire soumis et valide, false = formulaire non soumis
     */
    protected function hydrateEntity($entity, bool $new, bool $submitted)
    {
        // Rien à faire
    }

    /**
     * Donner la possibilité d'ajouter des contraintes de validation
     * @param  Form $form Le formulaire
     * @param  bool $new  true = nouvel enregistrement, false = enregistrement existant
     * @return bool true ou false
     */
    protected function isValid(Form $form, bool $new) : bool
    {
        return true;
    }

    /**
     * Donner la possibilité de fixer le droit de création sous condition à définir
     * @return boolean true ou false
     */
    protected function hasRightCreate() : bool
    {
        return true;
    }

    /**
     * Donner la possibilité de fixer le droit de consultation sous condition à définir
     * @return boolean true ou false
     */
    protected function hasRightView($entity) : bool
    {
        return true;
    }

    /**
     * Donner la possibilité de fixer le droit de modification par entité
     * @param  Entity $entity L'entité
     * @return boolean true ou false
     */
    protected function hasRightUpdate($entity) : bool
    {
        return true;
    }

    /**
     * Donner la possibilité de fixer le droit de suppression par entité
     * @param  Entity $entity L'entité
     * @return boolean true ou false
     */
    protected function hasRightDelete($entity) : bool
    {
        return true;
    }
}
