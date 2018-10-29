<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Organiser les champs dans des onglets
 */
class TabFieldAdder
{
    /**
     * Contient la liste des champs à afficher dans le datatable
     * @var array
     */
    private $tabContainer = null;

    /**
     * Mémorise l'onglet en cours d'alimentation
     * @var integer
     */
    private $tabIndex = -1;

    /**
     * Mémorise la colonne en cours d'alimentation
     * @var integer
     */
    private $columnIndex = -1;

    /**
     * Ajoute un onglet
     * @param string $title    Titre de l'onglet
     * @return TabFieldAdder L'instance de la class TabFieldAdder
     */
    public function addTab($title)
    {
        $this->tabIndex++;

        $this->tabContainer[$this->tabIndex]['title'] = $title;
        $this->tabContainer[$this->tabIndex]['columns'] = array();

        return $this;
    }

    /**
     * Ajoute une colonne
     * @return TabFieldAdder L'instance de la class TabFieldAdder
     */
    public function addColumn()
    {
        $this->columnIndex++;

        $this->tabContainer[$this->tabIndex]['columns'][$this->columnIndex] = array();

        return $this;
    }

    /**
     * Ajouter un champ
     * @param string $fieldName Nom du champ
     * @return TabFieldAdder L'instance de la class TabFieldAdder
     */
    public function add($fieldName)
    {
        $this->tabContainer[$this->tabIndex]['columns'][$this->columnIndex][] = $fieldName;

        return $this;
    }

    /**
     * Récupère le conteneur
     * @return array Conteneur
     */
    public function getFields()
    {
        return $this->tabContainer;
    }
}
