<?php
namespace Espier\Swagger\Providers;

use Illuminate\Support\ServiceProvider;
use Espier\Swagger\Console\Commands\SwaggerApiDocsCommand;
use Doctrine\Common\Annotations\AnnotationReader as AnnotationReader;

class SwaggerServiceProvider extends ServiceProvider
{

    /**
	 * boot process
	 */
	public function boot()
    {
        $this->app->router->group(['namespace' => 'Espier\Swagger\Http\Controllers'], function ($app) {
            $app->get(config('swagger.router'), ['as' => 'espier.api-doc', 'uses' => 'ApiSwaggerDocs@index']);
            $app->get('api-json', ['as' => 'espier.api-json', 'uses' => 'ApiSwaggerDocs@getApisJson']);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //加载config
        $this->mergeConfigFrom(realpath(__DIR__.'/../config/swagger.php'), 'swagger');

        $this->app->singleton('command.api.swagger', function()
        {
            return new SwaggerApiDocsCommand;
        });

        //Entities忽略SWG
        AnnotationReader::addGlobalIgnoredNamespace('SWG');

        $this->commands(
            'command.api.swagger'
        );
    }
}
