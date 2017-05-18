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
默认JSON文件生成目录为Storage的`apidocs`中，可通过在`.env`中新增配置改变存储目录名称
```
SWAGGER_STORAGE_DIR=apidocs
```

生成API JSON文件后通过路由访问
```
http://example.com/api-doc
```

如果需改变路由名称则可以通过`.env`配置
```
SWAGGER_DOCS_ROUTER=api-doc
```

如果需要使用mock server 则必须安装PHP的Yaml扩展，并且启动mock server服务

```
php artisan api:swagger --mock-server-start
```

在开发阶段因为团队中每个人的环境配置不一致，API需要绑定不同的访问地址。
那么则不能用API中定义的host和basePath作为测试地址。
以便于个人开发测试，那么则可以通过`.env`配置个人的host和basePath。

```
#个人的api的host
SWAGGER_API_HOST=

#个人的api的basePath
SWAGGER_API_BASE_PATH=
```

