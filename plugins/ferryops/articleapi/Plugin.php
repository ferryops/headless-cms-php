<?php namespace Ferryops\ArticleAPI;

use System\Classes\PluginBase;
use Ferryops\ArticleAPI\Middleware\ApiKeyMiddleware;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Article API',
            'description' => 'RESTful API untuk menampilkan artikel ke aplikasi mobile',
            'author' => 'Your Name',
            'icon' => 'oc-icon-flask'
        ];
    }

    public function boot()
    {
        // Daftarkan middleware
        $this->app['router']->aliasMiddleware('api.key', ApiKeyMiddleware::class);
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Article API Settings',
                'description' => 'Manage Article API configuration',
                'category' => 'API',
                'icon' => 'oc-icon-key',
                'class' => 'Ferryops\ArticleAPI\Models\Settings',
                'order' => 500,
                'permissions' => ['ferryops.articleapi.access_settings']
            ]
        ];
    }
}
