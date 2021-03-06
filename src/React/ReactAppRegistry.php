<?php

namespace AppBundle\React;

use AppBundle\React\App\CitizenProjectApp;
use AppBundle\React\App\IdeasWorkshopApp;
use Symfony\Component\Routing\RouteCollection;

class ReactAppRegistry
{
    /**
     * List of the apps in the project, in the form name => ReactAppInterface.
     * The name of the app will be used to create the routes names.
     *
     * @var ReactAppInterface[]
     */
    private $apps;

    public function __construct()
    {
        $this->apps = [
            'ideas_workshop' => new IdeasWorkshopApp(),
            'citizen_projects' => new CitizenProjectApp(),
        ];
    }

    public function getApp(string $appName): ?ReactAppInterface
    {
        return $this->apps[$appName] ?? null;
    }

    public function readManifest(ReactAppInterface $app): ?array
    {
        $filename = __DIR__.'/../../web/apps/'.$app->getDirectory().'/build/asset-manifest.json';

        try {
            $data = \GuzzleHttp\json_decode(file_get_contents($filename), true);
        } catch (\Exception $e) {
            return null;
        }

        $manifest = ['css' => [], 'js' => []];

        foreach ($data as $file) {
            if ('.css' === substr($file, -4)) {
                $manifest['css'][] = 'apps/'.$app->getDirectory().'/build/'.$file;
            } elseif ('.js' === substr($file, -3)) {
                $manifest['js'][] = 'apps/'.$app->getDirectory().'/build/'.$file;
            }
        }

        return $manifest;
    }

    public function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->apps as $appName => $app) {
            foreach ($app->getRoutes() as $routeName => $route) {
                $route->setDefault('_react_app', $appName);
                $route->setDefault('_controller', 'AppBundle:React:app');

                $collection->add('react_app_'.$appName.'_'.$routeName, $route);
            }
        }

        return $collection;
    }
}
