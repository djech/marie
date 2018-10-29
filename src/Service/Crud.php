<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Form\Form;
use Doctrine\Bundle\DoctrineBundle\Registry;
use App\Utils\CrudFieldAdder;
use App\Utils\TabFieldAdder;

/**
 * Crud
 */
final class Crud
{
    /**
     * Le service Doctrine
     * @var Registry
     */
    private $doctrine = null;

    /**
     * L'ajouteur de champs de la vue liste
     * @var CrudFieldAdder
     */
    private $crudFieldAdder = null;

    /**
     * L'organisateur de champs sous forme d'onglets
     * @var TabFieldAdder
     */
    private $tabFieldAdder = null;

    /**
     * Titre de la page
     * @var string
     */
    private $title = null;

    /**
     * Twig
     * @var string
     */
    private $parentTwig = null;
    private $indexTwig = 'crud/index.html.twig';
    private $actionTwig = 'crud/action.html.twig';
    private $filterTwig = 'crud/filter.html.twig';
    private $newTwig = 'crud/new.html.twig';
    private $showTwig = 'crud/show.html.twig';
    private $editTwig = 'crud/edit.html.twig';

    /**
     * Liste des twig pour les thèmes
     * @var array
     */
    private $themeTwig = array();

    /**
     * Nom de l'entité
     * @var string
     */
    private $entityName = null;

    /**
     * Nom du formulaire
     * @var string
     */
    private $formName = null;

    /**
     * Options à passer au formulaire lors de sa construction
     * @var array
     */
    private $formOptions = array();

    /**
     * Séparateur de collection d'entité
     * @var string
     */
    private $joinSeparator = ', ';

    /**
     * Connexion par défaut
     * @var string
     */
    private $connection = 'default';

    /**
     * Afficher la colonne action
     * @var boolean
     */
    private $displayActions = true;

    /**
     * Utiliser le referer pour quitter le crud
     * @var boolean
     */
    private $useReferer = false;

    /**
     * Afficher le bouton de recopie d'un enregistrement existant
     * @var boolean
     */
    private $useCopy = false;

    /**
     * Afficher du texte additionnel pour la suppression d'un enregistrement
     * @var string
     */
    private $additionalDeleteText = '';

    /**
     * Constructeur
     * @param Registry $doctrine Le service Doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->crudFieldAdder = new CrudFieldAdder();
        $this->tabFieldAdder = new TabFieldAdder();
    }

    /**
     * Extrait un bout de chaine compris entre des caractères à gauche et des caractères à droite
     * @param  string  $text Texte d'origine
     * @param  string  $key1 Caractères de gauche
     * @param  string  $key2 Caractères de droite
     * @return string        Texte compris entre les caractères de gauche et les caractères de droite
     */
    public static function subStr($text, $key1, $key2)
    {
        $len1 = strlen($key1);
        $pos1 = strpos($text, $key1);
        $pos2 = strpos($text, $key2, $pos1+$len1);
        if ($pos1 && $pos2) {
            return substr($text, $pos1+$len1, $pos2-($pos1+$len1));
        }

        return false;
    }

    /**
     * Récupère des informations sur les champs du formulaire
     * @param  Form $form Le formulaire
     * @return array      Les informations
     */
    public static function getFormInformation(Form $form) : array
    {
        $fieldOptions = array('id'=>array('label'=>'Clé', 'type'=>null, 'choices'=>null, 'multiple'=>null, 'mapped'=>null));

        $children = $form->all();
        foreach ($children as $fieldName => $child) {
            $config = $child->getConfig();

            $className = get_class($config->getType()->getInnerType());

            // Enlever le namespace
            if ($pos = strrpos($className, '\\')) {
                $className = substr($className, $pos + 1);
            }

            // On remplit
            $fieldOptions[$fieldName]['label'] = $config->getOption("label");
            $fieldOptions[$fieldName]['type'] = $className;
            $fieldOptions[$fieldName]['choices'] = $config->getOption('choices');
            $fieldOptions[$fieldName]['multiple'] = $config->getOption('multiple');
            $fieldOptions[$fieldName]['mapped'] = $config->getOption('mapped');
        }

        return $fieldOptions;
    }

    /**
     * Converti l'id en entité
     * @param  integer $id Identifiant de l'entité
     * @param  string $entityPath Chemin de l'entité
     * @param  string $connection Connexion utilisée
     * @return Entity Entité
     */
    public function convertToEntity(int $id, string $entityPath, string $connection)
    {
        $entity = $this->doctrine->getManager($connection)->getRepository($entityPath)->find($id);

        return $entity;
    }

    /**
     * Retourne l'objet CrudFieldAdder
     * @var CrudFieldAdder
     */
    public function getIndexFieldAdder() : CrudFieldAdder
    {
        return $this->crudFieldAdder;
    }

    /**
     * Retourne l'objet TabFieldAdder
     * @var TabFieldAdder
     */
    public function getFormTabFieldAdder() : TabFieldAdder
    {
        return $this->tabFieldAdder;
    }

    /**
     * Enregistre le twig parent
     * @param string $parentTwig Le twig parent
     */
    public function setParentTwig(string $parentTwig)
    {
        $this->parentTwig = $parentTwig;
    }

    /**
     * Retourne le twig parent
     * @return string Le twig parent
     */
    public function getParentTwig() : string
    {
        return $this->parentTwig;
    }

    /**
     * Enregistre le twig index
     * @param string $indexTwig Le twig index
     */
    public function setIndexTwig(string $indexTwig)
    {
        $this->indexTwig = $indexTwig;
    }

    /**
     * Retourne le twig index
     * @return string Le twig index
     */
    public function getIndexTwig() : string
    {
        return $this->indexTwig;
    }

    /**
     * Enregistre le twig action
     * @param string $actionTwig Le twig action
     */
    public function setActionTwig(string $actionTwig)
    {
        $this->actionTwig = $actionTwig;
    }

    /**
     * Retourne le twig action
     * @return string Le twig action
     */
    public function getActionTwig() : string
    {
        return $this->actionTwig;
    }

    /**
     * Enregistre le twig filter
     * @param string $filterTwig Le twig filter
     */
    public function setFilterTwig(string $filterTwig)
    {
        $this->filterTwig = $filterTwig;
    }

    /**
     * Retourne le twig filter
     * @return string Le twig filter
     */
    public function getFilterTwig() : string
    {
        return $this->filterTwig;
    }

    /**
     * Enregistre le twig new
     * @param string $newTwig Le twig new
     */
    public function setNewTwig(string $newTwig)
    {
        $this->newTwig = $newTwig;
    }

    /**
     * Retourne le twig new
     * @return string Le twig new
     */
    public function getNewTwig() : string
    {
        return $this->newTwig;
    }

    /**
     * Enregistre le twig show
     * @param string $showTwig Le twig show
     */
    public function setShowTwig(string $showTwig)
    {
        $this->showTwig = $showTwig;
    }

    /**
     * Retourne le twig show
     * @return string Le twig show
     */
    public function getShowTwig() : string
    {
        return $this->showTwig;
    }

    /**
     * Enregistre le twig edit
     * @param string $editTwig Le twig edit
     */
    public function setEditTwig(string $editTwig)
    {
        $this->editTwig = $editTwig;
    }

    /**
     * Retourne le twig edit
     * @return string Le twig edit
     */
    public function getEditTwig() : string
    {
        return $this->editTwig;
    }

    /**
     * Enregistre le ou les twig theme
     * @param string $themeTwig Le ou les twig theme
     */
    public function setThemeTwig(array $themeTwig)
    {
        $this->themeTwig = $themeTwig;
    }

    /**
     * Retourne le ou les twig theme
     * @return string Le ou les twig theme
     */
    public function getThemeTwig() : array
    {
        return $this->themeTwig;
    }

    /**
     * Enregistre le titre
     * @param string $title Le titre
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Retourne le titre
     * @return string Le titre
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Enregistre le nom de l'entité
     * @param string $entityName Le nom de l'entité
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * Retourne le nom de l'entité
     * @return string Le nom de l'entité
     */
    public function getEntityName() : string
    {
        return $this->entityName;
    }

    /**
     * Enregistre le nom du formulaire
     * @param string $formName Le nom du formulaire
     */
    public function setFormName(string $formName)
    {
        $this->formName = $formName;
    }

    /**
     * Retourne le nom du formulaire
     * @return string Le nom du formulaire
     */
    public function getFormName() : string
    {
        return $this->formName;
    }

    /**
     * Enregistre les options à passer au formulaire
     * @param string $formOptions Les options à passer au formulaire
     */
    public function setFormOptions(array $formOptions)
    {
        $this->formOptions = $formOptions;
    }

    /**
     * Ajoute des options à passer au formulaire
     * @param string $formOptions Les options à passer au formulaire
     */
    public function addFormOptions(array $formOptions)
    {
        $this->formOptions = array_merge($this->formOptions, $formOptions);
    }

    /**
     * Retourne les options à passer au formulaire
     * @return string Les options à passer au formulaire
     */
    public function getFormOptions() : array
    {
        return $this->formOptions;
    }

    /**
     * Enregistre le séparateur
     * @param string $joinSeparator Le séparateur
     */
    public function setJoinSeparator(string $joinSeparator)
    {
        $this->joinSeparator = $joinSeparator;
    }

    /**
     * Retourne le séparateur
     * @return string Le séparateur
     */
    public function getJoinSeparator() : string
    {
        return $this->joinSeparator;
    }

    /**
     * Enregistre la connexion
     * @param string $connection La connexion
     */
    public function setConnection(string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Retourne la connexion
     * @return string La connexion
     */
    public function getConnection() : string
    {
        return $this->connection;
    }

    /**
     * Masque la colonne actions
     */
    public function hideDisplayActions()
    {
        $this->displayActions = false;
    }

    /**
     * Retourne le choix d'afficher ou non de la colonne action
     * @return string Le choix d'afficher ou non de la colonne action
     */
    public function getDisplayActions() : bool
    {
        return $this->displayActions;
    }

    /**
     * Utilise le referer pour quitter le crud
     */
    public function useReferer()
    {
        $this->useReferer = true;
    }

    /**
     * Retourne le choix d'utiliser le referer
     * @return bool Le choix d'utiliser le referer
     */
    public function getUseReferer() : bool
    {
        return $this->useReferer;
    }

    /**
     * Affiche le bouton de recopie d'un enregistrement existant
     */
    public function useCopy()
    {
        $this->useCopy = true;
    }

    /**
     * Retourne le choix d'utiliser la fonction de recopie d'un enregistrement existant
     * @return bool Le choix d'utiliser la fonction de recopie d'un enregistrement existant
     */
    public function getUseCopy() : bool
    {
        return $this->useCopy;
    }

    /**
     * Enregistre du texte additionnel pour la suppression d'un enregistrement
     * @param string $additionalDeleteText Texte additionnel pour la suppression d'un enregistrement
     */
    public function setAdditionalDeleteText(string $additionalDeleteText) : void
    {
        $this->additionalDeleteText = $additionalDeleteText;
    }

    /**
     * Retourne du texte additionnel pour la suppression d'un enregistrement
     * @return string Texte additionnel pour la suppression d'un enregistrement
     */
    public function getAdditionalDeleteText() : string
    {
        return $this->additionalDeleteText;
    }
}
