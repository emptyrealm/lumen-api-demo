# lumen api demo
Lumen+Dingo API +JWT集成，开箱即用


目前的版本lumen5.8+dingo/api2+jwt-auth1.0


api文档采用的是apidocjs,安装好apidoc命令后可用命令生成文档： apidoc -i app/Http/Controllers -o docs
文档在docs目录
仅包含token的获取、刷新、删除三个api

本例中沒有采用dingo api里使用header传版本的方式，而是在url上添加版本信息

初始使用
根据自身条件填写.env
建议使用php artisan jwt:secret重新生成JWT的密钥


如果是新项目，可直接运行命令：php artisan migrate
该命令会在你的数据库中建立users表，如果是旧项目，请不要运行该命令，参考database/migrations/的数据，手动建立对应字段并且修改对应的User模型


创建用户
php artisan user:create --name=test-user --email=test@test.com --password=123456

密码更新
php artisan user:update test@test.com --password=123456
