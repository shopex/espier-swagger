<?php

namespace Espier\Swagger\Console\Commands;

use Illuminate\Support\Facades\Storage as Storage;
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
    public function fire()
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

            $file = 'apidocs/'.$title.'['.$version.'].json';
            Storage::put($file, $swagger);
            $this->info('Written to Storage '.$file);
        }
        elseif( $this->option('mock-server-start') )
        {
            $this->mockServer();
            $swaggerMockDir =  __DIR__ .'/../../../mock/';
            system('cd '. $swaggerMockDir. '&& swagger project start -m');
        }
        else
        {
            throw new \InvalidArgumentException(
                sprintf("php artisan api:swagger --output=./path/to/project")
            );
        }
    }

    private function mockServer()
    {
        $files = Storage::allFiles('apidocs');
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
            $json =  Storage::get($file);
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

        $swaggerDocsController = __DIR__ . '/../../views/apiSwaggerDocs.php';
        copy($swaggerDocsController, $resourcesPath.'/views/apiSwaggerDocs.php');

        $this->info('Copy Swagger UI Views to resources');

        $this->recurse_copy(__DIR__ . '/../../../public/swagger-ui', base_path().'/public/swagger-ui');

        $this->info('Copy Swagger UI css and js  to public');

        $findSwaggerCommand = exec('type swagger');
        if( stristr($findSwaggerCommand, 'not found') )
        {
            system('type npm install -g swagger');
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

