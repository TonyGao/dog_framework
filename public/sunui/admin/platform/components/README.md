# 组件属性模块化架构

## 概述

本目录包含各种组件的属性面板模块，每个组件类型都有独立的JavaScript文件来管理其属性界面和逻辑。这种模块化设计使得代码更易维护、扩展和重用。

## 架构设计

### 主要文件

- `view_editor_component_properties.js` - 主组件属性管理器
- `components/table_component_properties.js` - 表格组件属性模块
- `components/text_component_properties.js` - 文本组件属性模块
- `components/README.md` - 本说明文档

### 设计原则

1. **模块化**: 每个组件类型独立管理自己的属性面板
2. **动态生成**: HTML结构由JavaScript动态生成，而非硬编码在Twig模板中
3. **统一接口**: 所有组件模块都遵循相同的接口规范
4. **事件驱动**: 使用事件系统进行组件间通信

## 组件模块接口规范

每个组件属性模块都应该导出以下接口：

```javascript
window.ComponentNameProperties = {
    init: initComponentProperties,      // 初始化组件属性面板
    show: showComponentProperties,      // 显示属性面板
    hide: hideComponentProperties,      // 隐藏属性面板
    initEvents: initPropertyEvents     // 初始化事件处理
};
```

## 创建新组件属性模块

### 1. 创建组件文件

在 `components/` 目录下创建新的组件属性文件，例如 `image_component_properties.js`：

```javascript
/**
 * 图片组件属性面板
 * 负责生成图片组件的属性控制界面和相关逻辑
 */
(function() {
    
    // 生成图片属性面板HTML
    function generateImagePropertiesHTML() {
        return `
            <div id="image-properties" style="display: none;">
                <!-- 属性控件HTML -->
            </div>
        `;
    }
    
    // 初始化图片属性面板
    function initImageProperties() {
        const $componentPanel = $('.component-panel .panel-body');
        if ($componentPanel.length > 0 && $('#image-properties').length === 0) {
            $componentPanel.append(generateImagePropertiesHTML());
        }
    }
    
    // 显示图片属性面板
    function showImageProperties($image) {
        $('#image-properties').show();
        loadImageProperties($image);
    }
    
    // 隐藏图片属性面板
    function hideImageProperties() {
        $('#image-properties').hide();
    }
    
    // 加载图片属性值
    function loadImageProperties($image) {
        // 实现属性值加载逻辑
    }
    
    // 初始化图片属性控件事件
    function initImagePropertyEvents() {
        // 实现事件处理逻辑
    }
    
    // 导出图片组件属性接口
    window.ImageComponentProperties = {
        init: initImageProperties,
        show: showImageProperties,
        hide: hideImageProperties,
        initEvents: initImagePropertyEvents
    };
    
    // 页面加载完成后初始化
    $(document).ready(function() {
        initImageProperties();
        initImagePropertyEvents();
    });
    
})();
```

### 2. 在主管理器中注册组件

在 `view_editor_component_properties.js` 中添加新组件的支持：

```javascript
// 在 showComponentProperties 函数中添加
else if ($component.hasClass('ef-image') || $component.find('.ef-image').length > 0) {
    showImageProperties($component.hasClass('ef-image') ? $component : $component.find('.ef-image').first());
}

// 添加显示函数
function showImageProperties($image) {
    hideAllPropertyPanels();
    if (window.ImageComponentProperties) {
        window.ImageComponentProperties.show($image);
    }
}

// 在 hideAllPropertyPanels 函数中添加
if (window.ImageComponentProperties) {
    window.ImageComponentProperties.hide();
}
```

### 3. 在模板中引入脚本

在 `editor.html.twig` 中添加脚本引用：

```html
<script src="{{ asset('sunui/admin/platform/components/image_component_properties.js') }}"></script>
```

## 现有组件模块

### 表格组件 (table_component_properties.js)

提供以下属性控制：
- 边框宽度、颜色、样式
- 单元格内边距
- 斑马纹效果
- 悬停效果
- 删除组件功能

### 文本组件 (text_component_properties.js)

提供以下属性控制：
- 字体大小、颜色
- 字体粗细
- 文本对齐方式
- 删除组件功能

## 公共工具函数

### 获取选中组件

```javascript
const selectedComponent = window.ComponentProperties?.getSelectedComponent();
```

### RGB转十六进制

```javascript
function rgbToHex(rgb) {
    if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') {
        return '#000000'; // 或其他默认颜色
    }
    
    const result = rgb.match(/\d+/g);
    if (result && result.length >= 3) {
        return '#' + ((1 << 24) + (parseInt(result[0]) << 16) + (parseInt(result[1]) << 8) + parseInt(result[2])).toString(16).slice(1);
    }
    return '#000000';
}
```

### 更新选择器值

```javascript
function updateSelectValue(selector, value) {
    const $select = $(selector);
    const $option = $select.next('.ef-select-content').find(`[data-value="${value}"]`);
    if ($option.length > 0) {
        $select.find('.ef-select-view-input').val($option.text());
        $select.find('.ef-select-view-value').text(value);
    }
}
```

## 事件系统

### 组件选择事件

- `componentSelected` - 当组件被选中时触发
- `componentDeselected` - 当组件取消选中时触发
- `componentDeleted` - 当组件被删除时触发

### 事件监听示例

```javascript
$(document).on('componentSelected', function(e, component) {
    // 处理组件选择
});

$(document).on('componentDeselected', function() {
    // 处理组件取消选择
});
```

## 最佳实践

1. **命名规范**: 组件文件命名为 `{component_name}_component_properties.js`
2. **ID规范**: HTML元素ID使用 `{component-name}-{property-name}` 格式
3. **事件委托**: 使用 `$(document).on()` 进行事件委托，避免重复绑定
4. **错误处理**: 检查依赖对象是否存在再调用其方法
5. **代码复用**: 将通用功能抽取为公共函数
6. **注释文档**: 为每个函数添加清晰的注释说明

## 扩展指南

当需要添加新的组件类型时：

1. 创建对应的组件属性文件
2. 实现标准接口方法
3. 在主管理器中注册组件
4. 在模板中引入脚本文件
5. 测试组件属性面板功能

这种模块化架构使得系统具有良好的可扩展性和可维护性，每个组件都可以独立开发和测试。