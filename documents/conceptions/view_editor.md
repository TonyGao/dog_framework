# 视图编辑器

视图编辑器是为twig, form等框架MVC中国的View层而设计的可视化工具，它能用来快速构建表单、页面等最终给用户呈现的界面。

## 视图编辑器的基本功能

左侧是组件库和数据源库，中间是画布，右侧是属性面板。

![视图编辑器](/documents/conceptions/assets/view_editor.jpg)

画布由不同的section构成，可以通过点击画布右下角的加号添加section。每个section中通过点击上方的加号添加grid布局组件，也可以通过左侧的表格进行拖拽形成表格用于布局。其中表格组件可以使用上方的表格格式化工具栏进行单元格合并、拆分、文字对齐、修改字体等等操作。

每个section可以通过右侧属性面板对当前激活状态的section进行属性设置，包括如下属性：

- Content Width: Full Width(全宽模式)，Boxed(盒子模式)。
  - 全宽模式：容器内部完全铺满（适合Banner, Hero布局等视觉冲击比较强的大图、大标题区域），CSS效果是 width: 100%，不设置 max-width。可以手动设置Padding（内边距）来控制内容不要贴边。
  - 盒子模式：section的宽度有限，可以设置最大宽度，比如max-width: 1140px，并且左右有自动的margin: auto，内容在容器中间显示，适合正文、文章区域、产品介绍去等，让阅读体验更集中、更舒服。width宽度的单位包括[px、%、vw](css基本概念.md)

在每个section的btn-add被点击，或者在布局组件被拖拽到section-content时就弹出一个modal弹窗，
弹窗内容是 SELECT YOUR STRUCTURE，两行包括

第一行
1， 一行一列 24
2，一行两列 12 12
3，一行三列 8 8 8
4，一行四列 6 6 6 6
5，一行两列 9 15
6，一行两列 15 9

第二行
7，一行三列 6 6 12
8，一行三列 12 6 6
9，一行三列 6 12 6
10，一行五列 x x x x x
11, 一行六列 4 4 4 4 4 4
12，一行三列 4 16 4

这些不以文字描述，而是以图形形式表现，我准备div css的形式来展现，就用grid分割这些图形，每个都是长方形，再以这些要求用灰色的块呈现出来。



## 视图保存的基本逻辑

视图编辑器将肩负两种功能，一种是普通的视图，它类似CMS的功能，将内容（静态或动态）的呈现给用户，另一种是表单视图，它将主体是表单的视图呈现给用户。

在点击保存时，前端将canvas里的html代码通过ajax请求放到后端控制器，控制器通过特殊的处理将类似添加section按钮这种设计器的部分dom去掉，以及将特殊标记的dom作为变量来替代为twig变量和代码，视图将存为两位twig文件，一个是html.twig，一个是design.twig，第一个文件是用来存放用于Symfony框架能够识别的带有变量的twig模板文件，第二个是用来存在原始的视图编辑器里的画板html内容的twig模板文件，这个文件用于下次再次编辑。

后台处理器（Controller + Service）接收原始 HTML，进行过滤/处理

- 去掉没用的元素（比如 .add-section-button、.btn-add、.btn-layout 这种编辑器用的操作按钮）。
- 清理调试用样式（如果有）。
- 可以用 DOM 解析库（比如 Symfony 推荐的 DOMDocument，或 symfony/dom-crawler，甚至简单的 preg_replace）去做这一块。

把未来会用到的动态字段用 Twig 语法替换进去
比如：

```twig
<div>{{ dynamic_field }}</div>
```

```twig
<table>{% for row in table_data %}<tr><td>{{ row.value }}</td></tr>{% endfor %}</table>
```

这一步你可以设计一种前端约定，比如：

```html
<div data-dynamic="dynamic_field"></div>
```

后台发现 data-dynamic，就替换为

```twig 
{{ dynamic_field }} 
```

甚至更复杂的，用 data-repeat="table_data"，后台替换成 

```twig
{% for row in table_data %}...{% endfor %}
```
