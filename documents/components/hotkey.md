# 快捷键设计

为了能够实现网页内必要的快捷键功能，需要系统性的设计，充分考虑系统的整体需求，而不是简单的实现一个绑定键盘快捷键或引入一个库。

首先我们得承认，相较于Delphi, VB之类的客户端开发工具，Web并没有基础的基于win32和IDE提供的快捷键功能，而是开放的为开发者提供了完全开放的（同时也是原始的）一种键盘事件处理机制。这给了Web开发很大的灵活度，但是也给了Web开发者很大的负担。不像在Delphi中那样我可以在按钮里直接设置快捷键，并且不同视窗、表单、控件可以通过聚焦等实现优先级的切换。但在Web系统中，我们将不得不从头自己考虑怎么设计一套合理的类似客户端系统那样的快捷键系统，以满足复杂软件系统的要求。

目前我们在 [jqueryplus.js](/public/lib/ef/base/jqueryplus.js) 中实现了一个简单的快捷键系统，它的基本用法是如下的html:

```html
<!-- 表格 -->
<table class="editable-table" key-autobind>
  <tr>
    <td contenteditable="true" key-press="enter" key-scope=".editable-table">点击编辑</td>
  </tr>
</table>

<!-- 表单 -->
<form class="editable-form" key-autobind>
  <input type="text" name="name" key-press="enter" key-scope=".editable-form" placeholder="按回车提交表单" />
  <input type="text" name="email" key-press="enter" key-scope=".editable-form" placeholder="按回车提交表单" />
  <button type="submit" class="submit-btn" key-press="enter" key-scope=".editable-form">提交</button>
</form>

<!-- 弹窗 -->
<div class="modal" key-autobind>
  <button key-press="enter" key-scope=".modal">确定</button>
</div>
```

## 设计思路

我们的快捷键系统设计基于以下几个核心概念：

1. **声明式绑定**：通过HTML属性来声明快捷键，使用`key-press`、`key-scope`和`key-autobind`等属性，让开发者可以直观地在模板中定义快捷键行为。

2. **作用域管理**：使用`key-scope`属性定义快捷键的生效范围，确保快捷键只在特定的上下文中生效。这对于处理多层级界面（如弹窗、表单等）的快捷键冲突非常重要。

3. **优先级控制**：通过`key-priority`属性（默认值50）来控制快捷键的优先级，数值越大优先级越高，用于解决多个快捷键绑定冲突的问题。

4. **自动绑定机制**：使用`key-autobind`属性标记需要自动处理快捷键绑定/解绑的容器，系统会在容器显示/隐藏时自动管理其中的快捷键。

## 实现细节

### 1. 快捷键管理器

快捷键管理器（HotkeyManager）是整个系统的核心，负责：

- 注册和注销快捷键
- 处理键盘事件
- 管理快捷键优先级
- 维护作用域状态

### 2. 键盘事件标准化

系统会将键盘事件标准化处理：

- 支持组合键（如 ctrl+s, cmd+shift+p 等）
- 统一按键名称（如 'escape' -> 'esc'）
- 处理跨平台差异（如 Windows 的 ctrl 和 macOS 的 cmd）

### 3. 作用域和优先级

作用域和优先级的处理逻辑：

- 检查快捷键对应元素是否可见
- 检查作用域容器是否可见
- 按优先级顺序触发事件处理

### 4. 快捷键与事件映射

系统支持两种方式定义快捷键与事件的映射关系：

- **简单映射**：使用`key-press`和`key-event`属性，适用于简单场景
- **JSON映射**：使用`key-map`属性，以JSON格式明确定义每个快捷键对应的事件类型，适用于复杂场景

### 5. 自动绑定机制

通过重写 jQuery 的 show/hide 方法实现：

- show 时自动注册作用域内的快捷键
- hide 时自动注销作用域内的快捷键

## 使用示例

### 1. 基础快捷键绑定

```html
<!-- 简单的按钮快捷键 -->
<button key-press="ctrl+s" key-scope="body">保存</button>

<!-- 支持多个快捷键 -->
<button key-press="ctrl+s,cmd+s" key-scope="body">保存</button>
```

### 2. 表单快捷键

```html
<form class="advanced-form" key-autobind>
  <!-- 表单字段快捷键 -->
  <input type="text" key-press="alt+1" key-scope=".advanced-form" placeholder="快速聚焦到此输入框" />
  
  <!-- 提交按钮快捷键 -->
  <button type="submit" key-press="ctrl+enter,cmd+enter" key-scope=".advanced-form" key-priority="60">提交</button>
  
  <!-- 取消按钮快捷键 -->
  <button type="button" key-press="esc" key-scope=".advanced-form">取消</button>
</form>
```

### 3. 模态框快捷键

```html
<div class="modal" key-autobind>
  <div class="modal-content">
    <!-- 模态框内的表单 -->
    <form>
      <input type="text" key-press="alt+1" key-scope=".modal" />
      <textarea key-press="alt+2" key-scope=".modal"></textarea>
    </form>
    
    <!-- 模态框按钮 -->
    <button key-press="enter" key-scope=".modal" key-priority="70">确定</button>
    <button key-press="esc" key-scope=".modal">取消</button>
  </div>
</div>
```

### 4. 复杂编辑器快捷键

```html
<div class="editor" key-autobind>
  <!-- 工具栏快捷键 -->
  <div class="toolbar">
    <button key-press="ctrl+b,cmd+b" key-scope=".editor">加粗</button>
    <button key-press="ctrl+i,cmd+i" key-scope=".editor">斜体</button>
    <button key-press="ctrl+u,cmd+u" key-scope=".editor">下划线</button>
  </div>
  
  <!-- 编辑区快捷键 -->
  <div class="edit-area">
    <div contenteditable="true" 
         key-press="tab,shift+tab" 
         key-scope=".editor" 
         key-priority="80">编辑区域</div>
  </div>
</div>
```

### 5. 高级表格快捷键

表格是一个复杂的交互场景，我们的快捷键系统支持表格级别和单元格级别的快捷键绑定，通过分层设计实现更灵活的快捷键控制。

#### a. 表格级别快捷键

通过`ef-table-hotkeys`属性，表格会自动启用一组标准快捷键操作。从框架2.0版本开始，以下快捷键已经成为标准功能，无需额外配置：

- **Delete键**：删除当前选中单元格的内容（会有确认提示）
- **Tab键**：移动到下一个单元格
- **Shift+Tab键**：移动到上一个单元格
- **方向键**：上下左右移动单元格焦点
- **Ctrl+A**：全选表格内容
- **Ctrl+C**：复制选中内容
- **Ctrl+V**：粘贴内容到当前选中单元格

您也可以通过`data-table-keys`属性自定义或扩展这些快捷键：

```html
<!-- 表格整体快捷键 -->
<table class="ef-table" ef-table-hotkeys >
  <tbody>
    <tr>
      <td tabindex="0">单元格1</td>
      <td tabindex="0">单元格2</td>
    </tr>
  </tbody>
</table>
```

表格级别快捷键支持以下功能：

- **选择操作**：Ctrl+A 全选表格内容
- **剪贴板操作**：Ctrl+C 复制、Ctrl+V 粘贴
- **导航操作**：Tab/Shift+Tab 在单元格间切换，方向键移动焦点
- **编辑操作**：Delete 删除当前选中单元格的内容（标准功能）

要使表格快捷键正常工作，请确保：

1. 表格添加了`ef-table-hotkeys`属性
2. 单元格设置了`tabindex="0"`属性使其可获得焦点
3. 系统会自动为点击的单元格添加`data-cell-active="true"`属性

#### b. 单元格级别快捷键

单元格可以定义自己的快捷键行为，比如双击编辑：

```html
<table class="ef-table" ef-table-hotkeys>
  <tbody>
    <tr>
      <td tabindex="0"
          key-press="enter"
          key-event="dblclick"
          key-scope=".ef-table">单元格1</td>
    </tr>
  </tbody>
</table>
```

单元格级别快捷键特点：

- 使用`tabindex="0"`使单元格可获得焦点
- 通过`key-press`定义触发按键
- 使用`key-event`指定触发的事件类型
- 通过`key-scope`限定作用范围

#### c. 标准删除功能示例

以下是使用标准删除功能的完整示例：

```html
<!-- 使用标准表格快捷键的表格 -->
<table class="ef-data-table" ef-table-hotkeys key-autobind>
  <thead>
    <tr>
      <th>姓名</th>
      <th>年龄</th>
      <th>职位</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td tabindex="0">张三</td>
      <td tabindex="0">28</td>
      <td tabindex="0">工程师</td>
    </tr>
    <tr>
      <td tabindex="0">李四</td>
      <td tabindex="0">32</td>
      <td tabindex="0">设计师</td>
    </tr>
  </tbody>
</table>
```

使用方法：

1. 点击任意单元格使其获得焦点（系统会自动添加`data-cell-active="true"`属性）
2. 按Delete键删除内容（会有确认提示）
3. 使用Tab键或方向键在单元格间导航

#### d. 最佳实践

1. **合理分层**：
   - 表格级快捷键处理整体操作（选择、复制、导航、删除等）
   - 单元格级快捷键处理局部操作（编辑、验证等）

2. **焦点管理**：
   - 确保单元格可以通过`tabindex="0"`获得焦点
   - 系统会自动为点击的单元格添加`data-cell-active="true"`属性
   - 通过CSS样式突出显示活动单元格，例如：`td[data-cell-active="true"] { background-color: #f0f8ff; }`
   - 标准的Delete键操作会自动识别并处理带有`data-cell-active="true"`属性的单元格

3. **事件处理**：
   - 使用`key-event`定义合适的事件类型
   - 避免快捷键之间的冲突
   - 合理设置作用域范围

```

当用户点击表格单元格时，系统会自动为该单元格添加`data-cell-active="true"`属性，并移除其他单元格的此属性。这样，当用户按下Enter键时，系统可以准确识别哪个单元格是当前活动的，并将其设置为可编辑状态。

## 最佳实践

1. **合理使用作用域**：
   - 为每个独立的功能区域设置合适的作用域
   - 避免作用域过大或嵌套过深

2. **优先级设置原则**：
   - 默认优先级为50
   - 全局快捷键使用较低优先级（<50）
   - 模态框等临时性UI使用较高优先级（>50）

3. **快捷键冲突处理**：
   - 使用不同的作用域隔离快捷键
   - 通过优先级控制触发顺序
   - 避免在同一作用域使用相同的快捷键
   - 对于复杂的快捷键和事件映射，优先使用`key-map`属性而非`key-press`和`key-event`组合

4. **自动绑定的使用**：
   - 为动态显示/隐藏的容器添加`key-autobind`
   - 注意避免重复绑定
   - 及时清理不需要的绑定

5. **表格单元格活动状态**：
   - 系统自动为点击的单元格添加`data-cell-active="true"`属性
   - 不需要手动管理单元格的活动状态
   - 在处理表格快捷键时，检查`data-cell-active`属性而不是依赖focus状态
   - 可以通过CSS选择器`td[data-cell-active="true"]`为活动单元格添加视觉样式
   - 标准的Delete键功能会自动处理带有`data-cell-active="true"`属性的单元格
   - 确保表格添加了`ef-table-hotkeys`属性以启用标准快捷键功能

## 动态DOM元素的快捷键绑定

对于动态添加到页面的DOM元素，我们提供了两种方式来处理快捷键绑定：

### 1. 使用事件委托（推荐）

通过在父容器上设置`key-autobind`属性，系统会自动处理其内部动态添加的元素的快捷键绑定。这是处理动态DOM最简单和推荐的方式。

```html
<!-- 动态加载的列表容器 -->
<div class="dynamic-list" key-autobind>
  <!-- 动态添加的列表项会自动继承快捷键绑定 -->
  <div class="list-item" key-press="enter" key-scope=".dynamic-list">
    <span>列表项1</span>
    <button key-press="del" key-scope=".dynamic-list">删除</button>
  </div>
</div>

<!-- 动态加载的表格 -->
<table class="dynamic-table" key-autobind>
  <tbody>
    <!-- 动态添加的行会自动继承快捷键绑定 -->
    <tr>
      <td key-press="enter" key-scope=".dynamic-table">点击编辑</td>
      <td>
        <button key-press="del" key-scope=".dynamic-table">删除行</button>
      </td>
    </tr>
  </tbody>
</table>
```

### 2. 动态元素的快捷键绑定

对于动态添加到页面的DOM元素，我们提供了两种方式来处理快捷键绑定：

#### a. 自动绑定（使用append方法）

当使用jQuery的`append`方法添加带有`key-press`属性的元素时，系统会自动为这些元素绑定快捷键，无需手动调用`hotkeyManager()`方法：

```javascript
// 动态创建元素
const newButton = $('<button>', {
  text: '新建',
  'key-press': 'ctrl+n',
  'key-scope': '.toolbar',
  'key-priority': '60'
});

// 添加到DOM树，系统会自动绑定快捷键
$('.toolbar').append(newButton);
```

#### b. 手动绑定

在某些情况下，动态添加的DOM元素可能没有自动绑定快捷键（例如使用原生DOM API或其他方法添加元素），这时可以通过调用`hotkeyManager()`方法手动绑定：

```javascript
// 使用其他方法添加DOM元素
const container = document.querySelector('.container');
const template = `
  <div class="dynamic-content" key-autobind>
    <button key-press="ctrl+s" key-scope=".dynamic-content">保存</button>
    <button key-press="esc" key-scope=".dynamic-content">取消</button>
  </div>
`;
container.innerHTML = template;

// 手动绑定快捷键
$('.dynamic-content').hotkeyManager();
```

手动绑定的一些常见场景：

1. 使用原生DOM API添加元素：

```javascript
// 使用innerHTML添加元素后需要手动绑定
document.querySelector('.container').innerHTML = template;
$('.container').hotkeyManager();
```

2. 使用第三方库动态创建内容：

```javascript
// 使用模板引擎渲染内容后需要手动绑定
const html = templateEngine.render(data);
$('.container').html(html).hotkeyManager();
```

3. AJAX加载的内容：

```javascript
$.get('/api/template', function(html) {
  $('.container').html(html).hotkeyManager();
});
```

### 最佳实践

1. **优先使用事件委托**：
   - 在父容器上使用`key-autobind`属性
   - 适用于大多数动态加载场景
   - 自动处理绑定/解绑，无需手动管理

2. **合理设置作用域**：
   - 为动态内容设置合适的作用域范围
   - 避免与静态内容的快捷键冲突

3. **注意性能优化**：
   - 避免频繁的手动绑定/解绑操作
   - 合理使用事件委托机制
   - 及时清理不再需要的快捷键绑定

## 自定义事件类型

系统支持通过`key-event`属性指定要触发的事件类型，默认为`click`。这使得快捷键系统能够支持更多的交互场景，如双击、右键等。通过自定义事件，我们可以实现更复杂的交互逻辑和数据传递。

### 基本用法

```html
<!-- 双击事件 -->
<button key-press="ctrl+d" key-event="dblclick" key-scope=".editor">双击编辑</button>

<!-- 右键事件 -->
<div key-press="ctrl+r" key-event="contextmenu" key-scope=".context-menu">右键菜单</div>

<!-- 自定义事件 -->
<div key-press="ctrl+c" key-event="custom-event" key-scope=".custom-area">触发自定义事件</div>

<!-- 表格单元格删除内容示例 -->
<table class="data-table" ef-table-hotkeys key-autobind>
  <tbody>
    <tr>
      <td tabindex="0">可删除的内容（按Delete键删除）</td>
    </tr>
  </tbody>
</table>

<!-- 说明：删除功能现在是框架的标准功能 -->
<!-- 只需要在表格上添加 ef-table-hotkeys 属性，并确保单元格有 tabindex="0" 属性 -->
<!-- 当单元格被选中（具有 data-cell-active="true" 属性）时，按Delete键即可删除内容 -->

<!-- 多事件类型支持（旧方式，不推荐用于复杂映射） -->
<td key-press="enter,delete" key-event="dblclick,contextmenu" key-scope=".ef-table">支持多种事件</td>

<!-- 多事件类型支持（新方式，推荐用于复杂映射） -->
<td key-map="{
  'enter': 'dblclick',
  'delete': 'contextmenu'
}" key-scope=".ef-table">精确映射多种事件</td>
```

### 多事件类型支持

系统支持为同一个元素绑定多个不同类型的事件。有两种方式可以实现这一点：

#### a. 简单方式（逗号分隔，不推荐用于复杂映射）

在`key-event`属性中使用逗号分隔多个事件类型：

```html
<!-- 表格单元格同时支持双击编辑和右键菜单 -->
<td key-press="enter" key-event="dblclick,contextmenu" key-scope=".ef-table">双击或右键</td>
```

这种方式简单直观，但存在一个问题：当同时有多个快捷键和多个事件类型时，无法明确指定哪个快捷键对应哪个事件类型。

#### b. JSON映射方式（推荐用于复杂映射）

为了解决上述问题，我们引入了`key-map`属性，使用JSON格式明确定义快捷键与事件类型的映射关系：

```html
<!-- 使用JSON格式明确定义快捷键与事件的映射 -->
<td key-map="{
  'enter': 'dblclick',
  'delete': 'contextmenu'
}" key-scope=".ef-table">精确映射</td>

<!-- 按钮支持多种快捷键和事件的精确映射 -->
<button key-map="{
  'ctrl+s': 'click',
  'alt+s': 'mouseover'
}" key-scope=".toolbar">保存</button>

<!-- 输入框支持复杂的快捷键和事件映射 -->
<input key-map="{
  'alt+1': 'focus',
  'alt+a': 'click'
}" key-scope=".form">精确映射输入框</input>
```

这种JSON映射方式使得元素可以对不同的快捷键触发不同的行为，并且映射关系清晰明确，大大增强了交互的灵活性和可维护性。例如，一个表格单元格可以同时支持按Enter键进行编辑和按Delete键进行删除操作，而不会混淆。

### 自定义事件定义和监听

```javascript
// 定义自定义事件处理函数
$('.custom-area').on('custom-event', function(event, data) {
  console.log('自定义事件被触发', data);
});

// 手动触发自定义事件
$('.custom-area').trigger('custom-event', { action: 'save', id: 123 });
```

### 数据传递

在快捷键触发自定义事件时，你可以通过事件处理函数传递和接收数据：

```javascript
// 在元素上存储数据
$('.custom-area').data('itemData', { id: 123, name: '示例项目' });

// 在事件处理函数中获取数据
$('.custom-area').on('custom-event', function(event) {
  const itemData = $(this).data('itemData');
  console.log('处理项目数据:', itemData);
});
```

### 最佳实践

1. **选择合适的事件类型**：
   - 使用标准DOM事件（如click、dblclick、contextmenu等）
   - 可以使用自定义事件名称，但需确保事件已正确定义
   - 避免使用可能与系统冲突的事件名称

2. **事件冲突处理**：
   - 同一个元素可以绑定多个不同事件类型的快捷键
   - 使用不同的作用域和优先级来管理事件触发顺序
   - 注意避免快捷键和原生浏览器快捷键的冲突

3. **性能考虑**：
   - 合理使用事件委托，避免过多的事件监听器
   - 及时清理不再需要的事件绑定
   - 避免在频繁更新的元素上使用复杂的事件绑定

## 快捷键调试

为了开发者方便查看输入的快捷键是否正确，我们提供了快捷键调试功能。通过在框架 .env 配置文件中设置`KEY_DEBUG=true`，你可以在前端页面查看输入的快捷键及其对应的事件类型。
