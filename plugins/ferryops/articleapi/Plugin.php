<?php namespace Ferryops\ArticleAPI;

use System\Classes\PluginBase;

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
}