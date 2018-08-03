DIRECTORY STRUCTURE
-------------------

      commands/         相当于 控制器层
      config/           配置文件
      library/          扩展库
      models/           model层
      redis/            redis扩展插件
      runtime/          日志记录
      service/          app service层
      vendor/           contains dependent 3rd-party packages

执行
------------
~~~
路由展示
./main commands类名/函数名称 例如：./main mall 默认为 ./main mall/index 
~~~

框架用到的 插件
------------
~~~
https://packagist.org/

日志
monolog/monolog
github: https://github.com/Seldaek/monolog
数据库
catfan/medoo
github:  https://github.com/catfan/Medoo
~~~