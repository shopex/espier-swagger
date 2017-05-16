# swagger集成到lumen

## 使用

在 bootstrap中新增

```
$app->register(Espier\Swagger\Providers\SwaggerServiceProvider::class);
```
然后执行命令，将swagger UI的资源拷贝到public目录中.

```
php artisan api:swagger --setup
```

如需生成指定目录的swagger API josn文件则使用

```
php artisan api:swagger --output=[/path/to/project];
```

生成API JSON文件后通过路由访问
```
http://example.com/espier/api-doc.html
```

如果需要使用mock server 则必须安装PHP的Yaml扩展，并且启动mock server服务

```
php artisan api:swagger --mock-server-start.
```

