<?php

namespace Espier\Swagger\Console\Commands;

use Illuminate\Console\Command;
class SwaggerApiDocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:swagger {--help? : Display this help message.}
                                        {--setup : 安装Swagger相关服务.}
                                        {--mock-server-start : 启动Swagger Mock Server.}
                                        {--output= : Path to store the generated documentation.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documentation from annotated controllers';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'api:swagger';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if( $this->option('setup') )
        {
            $this->SetupSwagger();
        }
        elseif( $path = $this->option('output') )
        {
            $swagger = \Swagger\scan(base_path().'/'.$path);
            $title   = $swagger->info->title;
            $version = $swagger->info->version;
            if( config('swagger.host') )
            {
                $swagger->host = config('swagger.host');
            }

            if( config('swagger.base_path') )
            {
                $swagger->basePath = config('swagger.base_path');
            }

            $file = config('swagger.storage_dir').'/'.$title.'['.$version.'].json';
            app('filesystem')->put($file, $swagger);
            $this->info('Written to Storage '.$file);
        }
        elseif( $this->option('mock-server-start') )
        {
            if( !function_exists("yaml_emit")  )
            {
                throw new \RuntimeException(
                    sprintf("当前PHP未安装Yaml扩展，请先安装 http://php.net/manual/zh/book.yaml.php")
                );
            }

            $this->mockServer();
            $swaggerMockDir =  __DIR__ .'/../../../mock/';
            system('cd '. $swaggerMockDir. '&& swagger project start -m');
        }
        else
        {
            throw new \InvalidArgumentException(
                sprintf("php artisan api:swagger --output=/path/to/project")
            );
        }
    }

    private function mockServer()
    {
        $files = app('filesystem')->allFiles(config('swagger.storage_dir'));
        if( !$files )
        {
            throw new \InvalidArgumentException(
                sprintf("文档不存在，请执行命令:php artisan api:swagger")
            );
        }

        $apiPathsData = [];
        $definitions = [];
        foreach( $files as $file )
        {
            $json =  app('filesystem')->get($file);
            $apidata = json_decode($json, true);

            foreach( $apidata['paths'] as $key=>$apiPath )
            {
                $controller = stristr($key, '{', true) ? : $key;
                $apidata['paths'][$key]['x-swagger-router-controller'] = $controller;
            }

            if( $apiPathsData )
            {
                array_push($apiPathsData, $apidata['paths']);
            }
            else
            {
                $apiPathsData = $apidata['paths'];
            }

            if( $definitions )
            {
                array_push($definitions, $apidata['definitions']);
            }
            else
            {
                $definitions = $apidata['definitions'];
            }
        }

        $apiDesc = [
            'swagger' => '2.0',
            'info' => [
                'title' => 'Mock Server',
                'description' => '',
                'version' => '1.0.0',
            ],
            'host' => 'localhost:10010',
            'basePath' => '/',
            'schemes' => ['http'],
            'paths' => $apiPathsData,
            'definitions' => $definitions,
        ];

        $apiYaml = yaml_emit($apiDesc);

        $swaggerMockConfig =  __DIR__ .'/../../../mock/api/swagger/swagger.yaml';
        file_put_contents($swaggerMockConfig, $apiYaml);
    }

    private function SetupSwagger()
    {
        $resourcesPath = resource_path();
        if( !is_dir($resourcesPath.'/views/') )
        {
            mkdir($resourcesPath.'/views/', 0777, true);
        }

        $swaggerDocsController = __DIR__ . '/../../views/apiSwaggerDocs.blade.php';
        copy($swaggerDocsController, $resourcesPath.'/views/apiSwaggerDocs.blade.php');

        $this->info('Copy Swagger UI Views to resources');

        $this->recurse_copy(__DIR__ . '/../../../public/swagger-ui', base_path().'/public/swagger-ui');

        $this->info('Copy Swagger UI css and js  to public');

        $findSwaggerCommand = exec('type swagger');
        if( !stristr($findSwaggerCommand, '/bin/swagger') )
        {
            system('npm install -g swagger');
        }

        //安装mock server
        $swaggerMockDir =  __DIR__ .'/../../../mock/';
        system('cd '. $swaggerMockDir. '&& npm install');

        $this->recurse_copy($swaggerMockDir.'/Mock.js/mock/', $swaggerMockDir.'/node_modules/swagger-tools/lib/mock/');
        copy($swaggerMockDir.'/Mock.js/swagger-router.js', $swaggerMockDir.'/node_modules/swagger-tools/middleware/swagger-router.js');
        $this->info('Setup Swagger Mock Server OK');

        $this->info('Setup Swagger Server OK');
    }

    private function recurse_copy($src,$dst)
    {
        $dir = opendir($src);

        if( !is_dir($dst) )
        {
            mkdir($dst);
        }

        while(false !== ( $file = readdir($dir)) )
        {
            if (( $file != '.' ) && ( $file != '..' ))
            {
                if ( is_dir($src . '/' . $file) )
                {
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else
                {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}

