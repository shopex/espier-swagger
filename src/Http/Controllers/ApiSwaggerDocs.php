<?php

namespace Espier\Swagger\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiSwaggerDocs extends BaseController
{

    public function index(Request $request )
    {
        $files = app('filesystem')->allFiles(config('swagger.storage_dir'));
        if( !$files )
        {
            return '文档不存在，请执行命令:php artisan api:swagger';
        }

        $list = [];
        $activeUrl = null;
        $titleActive = $request->input('title') ? false : true;
        foreach( $files as $file )
        {
            $title = basename($file, ".json").PHP_EOL;

            if( $title == $request->input('title'))
            {
                $activeUrl = route('espier.api-json', ['title' => $title]);
                $titleActive = true;
            }

            $list[] = [
                'title'  => $title,
                'active' => $titleActive,
                'link'   => route('espier.api-doc', ['title' => $title])
            ];

            $titleActive = false;
        }

        $activeUrl = $activeUrl ? : route('espier.api-json', ['title' => $list[0]['title']]);
        return view('apiSwaggerDocs', ['list'=>$list, 'url'=>$activeUrl]);
    }

    public function getApisJson(Request $request)
    {
        $title = config('swagger.storage_dir').'/'.$request->input('title').'.json';
        return app('filesystem')->get($title);
    }
}

