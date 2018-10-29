<?php
/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 18/10/18
 * Time: 16:12
 */
namespace App\EventListener;

use KevinPapst\AdminLTEBundle\Event\KnpMenuEvent;

class KnpMenuBuilderListener
{
    public function onSetupMenu(KnpMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu->addChild('MainNavigationMenuItem', [
            'label' => 'CONFIGURATION',
            'childOptions' => $event->getChildOptions()
        ])->setAttribute('class', 'header');

        $menu->addChild('information', [
            'route' => 'information',
            'label' => 'Information civile',
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-tachometer-alt');

        $menu->addChild('about', [
            'route' => 'about',
            'label' => 'À propos',
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-tachometer-alt');

        $menu->addChild('prestations', [
            'route' => 'prestation_edit',
            'routeParameters' => array('id' => 1),
            'label' => 'Prestations',
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-tachometer-alt');

        $menu->addChild('pathologies', [
            'route' => 'pathologie_edit',
            'routeParameters' => array('id' => 1),
            'label' => 'Pathologies prises en charge',
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-tachometer-alt');

        $menu->addChild('retour', [
            'route' => 'home',
            'label' => 'Retour sur CV',
            'childOptions' => $event->getChildOptions()
        ])->setLabelAttribute('icon', 'fas fa-tachometer-alt');

    }
}