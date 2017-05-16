<?php
namespace Espier\Swagger\Providers;

use Illuminate\Support\ServiceProvider;
use Espier\Swagger\Console\Commands\SwaggerApiDocsCommand;

class SwaggerServiceProvider extends ServiceProvider
{

    /**
	 * boot process
	 */
	public function boot()
    {
        $this->app->group(['namespace' => 'Espier\Swagger\Http\Controllers'], function ($app) {
            $app->get('/espier/api-doc.html', ['as' => 'espier.api-doc', 'uses' => 'ApiSwaggerDocs@index']);
            $app->get('/espier/api-json', ['as' => 'espier.api-json', 'uses' => 'ApiSwaggerDocs@getApisJson']);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.api.swagger', function()
        {
            return new SwaggerApiDocsCommand;
        });

        $this->commands(
            'command.api.swagger'
        );
    }
}
