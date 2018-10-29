<?php

declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ExtraLoader extends Loader
{
    private $loaded = false;

    /**
     * Paramètre "crud.extra"
     * Doit être de la forme NomDuBundle:NomDuController|prefix
     * @var array
     */
    private $extra;

    /**
     * Constructeur
     * @param array $extra Paramètre "crud.extra" à définir dans services.yml
     */
    public function __construct($extra)
    {
        $this->extra = $extra;
    }

    /**
     * load
     * @param  string $resource Resource "."
     * @param  string $type     Type "extra"
     * @return RouteCollection  Collection de routes
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->extra as $extra) {
            $controllerPath = (explode(':', $extra))[0];
            $baseRoute = strtolower((explode(':', $extra))[1]);

            /**
             * Route prefix_index
             */
            $route = new Route('/'.$baseRoute.'/');
            $route->setDefaults(array('_controller' => $controllerPath.':indexAction'));
            $route->setMethods("GET");
            $routes->add($baseRoute.'_index', $route);

            /**
             * Route prefix_source
             */
            $route = new Route('/'.$baseRoute.'/source');
            $route->setDefaults(array('_controller' => $controllerPath.':sourceAction'));
            $route->setMethods(array("GET"));
            $routes->add($baseRoute.'_source', $route);

            /**
             * Route prefix_new
             */
            $route = new Route('/'.$baseRoute.'/new/{id}');
            $route->setDefaults(array('_controller' => $controllerPath.':newAction', 'id' => null));
            $route->setMethods(array("GET","POST"));
            $route->setRequirements(array('id' => '\d+'));
            $routes->add($baseRoute.'_new', $route);

            /**
             * Route prefix_show
             */
            $route = new Route('/'.$baseRoute.'/{id}');
            $route->setDefaults(array('_controller' => $controllerPath.':showAction'));
            $route->setMethods("GET");
            $route->setRequirements(array('id' => '\d+'));
            $routes->add($baseRoute.'_show', $route);

            /**
             * Route prefix_edit
             */
            $route = new Route('/'.$baseRoute.'/{id}/edit');
            $route->setDefaults(array('_controller' => $controllerPath.':editAction'));
            $route->setMethods(array("GET","POST"));
            $route->setRequirements(array('id' => '\d+'));
            $routes->add($baseRoute.'_edit', $route);

            /**
             * Route prefix_delete
             */
            $route = new Route('/'.$baseRoute.'/{id}');
            $route->setDefaults(array('_controller' => $controllerPath.':deleteAction'));
            $route->setMethods("DELETE");
            $route->setRequirements(array('id' => '\d+'));
            $routes->add($baseRoute.'_delete', $route);
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * supports Indique si la méthode load() doit être appelée ou non
     * @param  string $resource Resource "."
     * @param  string $type     Type "extra"
     * @return bool           True ou False
     */
    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}
