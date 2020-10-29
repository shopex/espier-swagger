<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Espier API测试&文档</title>
  <link rel="stylesheet" href="{{ asset('swagger-ui/css/style.css',true) }}" media="screen" type="text/css" />
  <link rel="stylesheet" href="{{ asset('swagger-ui/swagger-ui.css',true) }}" type="text/css">
   <style> #nav {
        float:left;
        width : 18%;
        margin-right: 5px;
    }
    #section {
        float:left;
        width : 80%;
        background:#FFF;
    }
</style>
</head>
<body>
  <div class="container">
    <nav id="nav">
        <ul class="mcd-menu">
            <li>
                <a href="#">
                    <i class="fa fa-home"></i>
                    <strong>API文档</strong>
                </a>
            </li>
            <?php foreach($list as $row) {?>
            <li>
            <a href="<?php echo $row['link']?>" <?php if($row['active']){?>class="active" <?php }?>>
                    <i class="fa fa-edit"></i>
                    <strong><?php echo $row['title']?></strong>
                </a>
            </li>
            <?php }?>
        </ul>
    </nav>
    <div id="section">
        <div id="swagger-ui"></div>
        <script src="{{ asset('swagger-ui/swagger-ui-bundle.js',true) }}"> </script>
        <script src="{{ asset('swagger-ui/swagger-ui-standalone-preset.js',true) }}"> </script>
        <script>
        window.onload = function() {
          // Build a system
          const ui = SwaggerUIBundle({
            url: "<?php echo urldecode($url);?>",
            dom_id: '#swagger-ui',
            presets: [
              SwaggerUIBundle.presets.apis,
              SwaggerUIStandalonePreset
            ],
            plugins: [
              SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
          })

          window.ui = ui
        }
        </script>
    </div>
    <style> .swagger-ui .wrapper { width : 96%; } </style>
    </div>
</body>
</html>

