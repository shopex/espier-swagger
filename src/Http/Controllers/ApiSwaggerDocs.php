<?php

namespace Espier\Swagger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage as Storage;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiSwaggerDocs extends BaseController
{

    public function index(Request $request )
    {
        $files = Storage::allFiles('apidocs');
        if( !$files )
        {
            return '文档不存在，请执行命令:php artisan api:swagger';
        }

        $list = [];
        $activeUrl = null;
        foreach( $files as $file )
        {
            $title = basename($file, ".json").PHP_EOL;

            if( $title == $request->input('title') )
            {
                $activeUrl = route('espier.api-json', ['title' => $title]);
            }

            $list[] = [
                'title' => $title,
                'link'  => route('espier.api-doc', ['title' => $title])
            ];
        }

        $activeUrl = $activeUrl ? : route('espier.api-json', ['title' => $list[0]['title']]);
        return view('apiSwaggerDocs', ['list'=>$list, 'url'=>$activeUrl]);
    }

    public function getApisJson(Request $request)
    {
        $title = 'apidocs/'.$request->input('title').'.json';
        return Storage::get($title);
    }
}

