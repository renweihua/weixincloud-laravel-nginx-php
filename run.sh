#!/bin/sh
# & 表示 同时运行多个命令，&&表示只有前面的运行成功，才继续后面的命令

# 后台启动
php-fpm -D
# 关闭后台启动，hold住进程
nginx -g 'daemon off;' &

# 运行`微信云托管`
cd /wxcloudrun-wxcomponent
./main &
redis-server --requirepass '12345!@$'
