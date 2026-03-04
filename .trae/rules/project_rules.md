
1, 狗狗框架是一个基于Symfony框架的开源企业应用开发框架，致力于为企业级应用提供基础的开发底座，实现快速、高质量、功能丰富、愉快的开发过程。

## 功能概述

* [UI库](/documents/features/ui/ui.md)
* [组织架构](/documents/features/organization/org.md)
* 人员管理
  * 人员信息
* 模型和接口
  * 模型管理
  * 接口管理
* 应用程序构建器
  * 表单设计器
  * [菜单管理](/documents/features/menu/Menu.md)

### 如果涉及以下组件的创建要求，优先使用框架自身实现的这些UI库或FormType

* UI库
  * Table 表格 [table](/templates/test/table.html.twig)
  * [x] 公司选择器 [company selector](/templates/test/company_selector.html.twig)
  * [x] 单部门选择器
    * [x] 静态页面
    * [ ] 查看界面
  * [ ] 多部门选择器
    * [ ] 静态页面
    * [ ] 查看界面
  * [ ] 人员选择器
    * [ ] 静态页面
    * [ ] formtype userType
  * [x] ModelManagment 模型管理界面
  * [x] Modal 模态窗口 [modal](/templates/test/modal.html.twig)
* [x] 添加菜单命令
  * [x] 菜单模型
  * [x] 菜单静态化命令
  * [ ] 交互式菜单命令
* [ ] 组织架构图的基本构思和静态页面
* [ ] 集团、公司、部门、岗位、职务级别管理
  * [x] 公司架构（集团、公司管理）
    * [x] 树状前端界面
  * [x] 部门
    * [x] 树状部门管理界面
    * [x] 基于动态属性数据的表单
    * [ ] 新建部门
    * [ ] 修改部门
    * [ ] 停用部门
  * [ ] 岗位
  * [ ] 职务级别
* [ ] 模型动态属性的尝试
  * [x] 构建模型、属性、属性分组的模型
  * [x] 初始化 Entity 到数据库的命令行工具v0.1
  * [x] 图形界面-模型管理-添加自定义字段（字符串）
  * [ ] 图形界面-模型管理-添加自定义字段（网页）
  * [ ] 图形界面-模型管理-添加自定义字段（选项）
  * [ ] 图形界面-模型管理-添加自定义字段（人员）
  * [ ] 图形界面-模型管理-字段分组管理
* [ ] 菜单管理界面
  * [x] 首页
  * [ ] 全部收起、全部展开、批量移动、拖动移动
  * [ ] 新建菜单(批量)
  * [ ] 编辑菜单(批量)
* [ ] 应用程序构建器
  * [ ] 视图设计器
    * [ ] 布局
  * [ ] 表单设计器
    * [ ] 表单设置
* [ ] API系统
  * [ ] API系统界面设计
  * [ ] 界面实现
  * [ ] 存储后台实现
  * [ ] 生成文档
  * [ ] 生成json校验文件
* [ ] 测试系统
  * [ ] 界面功能测试
  * [ ] 冒烟测试
* [ ] Websocket通信基础库
  * [ ] 即时通知
  * [ ] 简易即时通讯
* [ ] 虚拟助手
  * [ ] 复活雨果，以Blender建模，three.js呈现
  * [ ] 实现基本的文字提问功能索引、文件上传、调用基本功能api完成功能

## 第三方库

* <https://prismjs.com/index.html>
* <https://cs.symfony.com/>
* <https://github.com/symfony/panther>
* [LeaderLine](https://anseki.github.io/leader-line/)
* [Daterangepicker](https://www.daterangepicker.com/)

## 框架自己实现的前端库，请在代码实现尽量复用以下库

* [ajax请求](/public/lib/ef/base/ajax.js)

## 涉及json格式的响应，框架自己实现了后端类，继承并使用该库

* [ApiResponse](/src/Controller/Api/ApiResponse.php)

## 不用启动开发服务器来测试

## 将生成的新的markdown文档放到目录 /documents/ 中，注意：目录名必须是 /documents/ ，不能是其他名字

## 必须优先使用 /templates/ui/ 目录中的 UI 库，必须参照 /templates/test/ 目录中的示例实现，不能直接使用第三方库，不能自己实现样式，必须使用框架自己实现的样式。如果没有对应的UI库，提醒用户

## 图标必须使用 font-awesome 图标库的免费图标
