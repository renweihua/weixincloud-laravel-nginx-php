# weixincloud-laravel-nginx-php
微信云托管 laravel+nginx+php 模版

## 快速开始
前往 [微信云托管快速开始页面](https://developers.weixin.qq.com/miniprogram/dev/wxcloudrun/src/basic/guide.html)，选择相应语言的模板，根据引导完成部署。

## 本地调试
下载代码在本地调试，请参考[微信云托管本地调试指南](https://developers.weixin.qq.com/miniprogram/dev/wxcloudrun/src/guide/debug/)。

## 目录结构说明
~~~
.
├── Dockerfile                  Dockerfile 文件
├── LICENSE                     LICENSE 文件
├── README.md                   README 文件
├── conf                        配置文件
│   ├── fpm.conf                fpm 配置
│   ├── nginx.conf              nginx 配置
│   └── php.ini                 php 配置
├── run.sh                      镜像启动脚本
├── container.config.json       模板部署「服务设置」初始化配置（二开请忽略）
├── laravel                     Laravel应用
│   ├── app                         应用目录
│   ├── artisan                     artisan
│   ├── bootstrap                   框架的启动和自动载入配置
│   ├── composer.json               composer 文件
│   ├── composer.lock               composer 文件
│   ├── config                      应用所有的配置文件   
│   ├── database                    数据库迁移文件及填充文件
│   ├── public                      应用入口文件 index.php 和前端资源文件
│   ├── resources                   应用视图文件和未编译的原生前端资源文件
│   ├── routes                      应用定义的所有路由
│   ├── server.php                  命令行入口文件       
│   ├── storage                     存放框架生成的文件和缓存
│   └── webpack.mix.js
~~~

## 使用注意
如果不是通过微信云托管控制台部署模板代码，而是自行复制/下载模板代码后，手动新建一个服务并部署，需要在「服务设置」中补全以下环境变量，才可正常使用，否则会引发无法连接数据库，进而导致部署失败。
- MYSQL_ADDRESS
- MYSQL_DATABASE
- MYSQL_USERNAME
- MYSQL_PASSWORD
  以上四个变量的值请按实际情况填写。如果使用云托管内MySQL，可以在控制台MySQL页面获取相关信息。

## laravel项目调整兼容的文件
- config/database.php
  - 数据库配置支持云托管的环境变量与laravel目录下的.env文件配置

## License

[MIT](./LICENSE)
