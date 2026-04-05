# 需要符合 Symfony 官方最佳实践

## 必须实现

- UI组件优先采用框架自身实现的UI库。
- 接口需要进行测试，测试通过再反馈。
- 生成的代码需要有中英文注释。

## 前端要求

- ajax请求采用 /public/lib/ef/base/ajax.js
- 图标必须使用 font-awesome 图标库的免费图标
- 禁止引入cdn库，下载到 /public/lib 目录中使用，在 /templates/base.html.twig 引入。
- 框架采用 jQuery 写法，但可以采用ES6语法。

## 后端要求

- 涉及json格式的响应，继承并使用该库 /src/Controller/Api/ApiResponse.php
- 服务已启动，不用启用服务再测试。
- Twig模板中采用标签，支持i18n，注意要自动生成英文、中文两种翻译。
- 禁止执行 php bin/console ef:entity 相关的命令。
- 禁止执行 php bin/console doctrine:schema:update --force，用 php bin/console doctrine:migrations:diff 生成迁移文件，再执行 php bin/console doctrine:migrations:migrate 进行数据库迁移。

## 文档要求

- 将生成的新的markdown文档放到目录 /documents/ 中，注意：目录名必须是 /documents/ ，不能是其他名字
- 必须优先使用 /templates/ui/ 目录中的 UI 库，必须参照 /templates/test/ 目录中的示例实现，不能直接使用第三方库，不能自己实现样式，必须使用框架自己实现的样式。如果没有对应的UI库，提醒用户
