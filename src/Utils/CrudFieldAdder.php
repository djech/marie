<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Constructeur de champ spécifique au crud
 * Ceci est réalisé dans le but de faire un copier coller de l'ajout des champs au formBuilder de Symfony
 */
class CrudFieldAdder
{
    /**
     * Contient la liste des champs à afficher dans le datatable
     * @var array
     */
    private $fieldContainer = array();

    /**
     * Ajoute un champ
     * @param string $fieldName Nom du champ
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function add($fieldName) : CrudFieldAdder
    {
        $this->fieldContainer[] = array('name' => $fieldName, 'label' => null, 'type'=>null, 'filter' => null, 'callback' => null);

        return $this;
    }

    /**
     * Ajoute un label
     * @param  string         $label Le label
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function addLabel(string $label) : CrudFieldAdder
    {
        end($this->fieldContainer);
        $this->fieldContainer[key($this->fieldContainer)]['label'] = $label;

        return $this;
    }

    /**
     * Spécifie que la valeur est de type date (pour rendre la colonne triable)
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function addTypeDate() : CrudFieldAdder
    {
        end($this->fieldContainer);
        $this->fieldContainer[key($this->fieldContainer)]['type'] = 'date';

        return $this;
    }

    /**
     * Ajoute le filtre mailto
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function addFilterMailto() : CrudFieldAdder
    {
        end($this->fieldContainer);
        $this->fieldContainer[key($this->fieldContainer)]['filter'] = 'mailto';

        return $this;
    }

    /**
     * Ajoute le filtre date
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function addFilterDate() : CrudFieldAdder
    {
        end($this->fieldContainer);
        $this->fieldContainer[key($this->fieldContainer)]['filter'] = 'date';

        return $this;
    }

    /**
     * Ajoute le filtre datetime
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function addFilterDateTime() : CrudFieldAdder
    {
        end($this->fieldContainer);
        $this->fieldContainer[key($this->fieldContainer)]['filter'] = 'datetime';

        return $this;
    }

    /**
     * Pour sélectionner soi-même les champs à afficher à partir d'un champ de l'entité (cas des collections par exemple)
     * @param  string         Fonction callback
     * @return CrudFieldAdder L'instance de la class CrudFieldAdder
     */
    public function addSelect(callable $callback) : CrudFieldAdder
    {
        end($this->fieldContainer);
        $this->fieldContainer[key($this->fieldContainer)]['callback'] = $callback;

        return $this;
    }

    /**
     * Récupère le conteneur
     * @return array Conteneur
     */
    public function getFields() : array
    {
        return $this->fieldContainer;
    }
}
