# select 组件

选择控件，主要功能为在有限的选项中选择一个或多个选项，可以通过输入方式进行快捷索引定位选项，
输入的内容出了内容本身包含的字符，特殊的内容类型还会有额外的索引方式（例如用户选择器、部门选择器
通过汉语拼音、首字母等方式索引）。

界面上大概分为两个部分：一部分为input选择输入框(以下称Input1)，一部分为下拉框（以下称Select2），
当鼠标点击Input1，弹出Select2(注意上下留白空间，当下方足够时Select2显示在下方，当下方空间不足，
Select2显示在上方)，本组件分为单选模式和多选模式。

在Input1输入关键字，然后对li进行搜索，找到并重新渲染ul内容

Select2的html构成：

```html
<span
  class="ef-select-view-single ef-select ef-select-view ef-select-view-size-medium ef-select-view-search"
  style="width: 320px;"
  id="select1"
  contentId="selectContent1"
  >
  <input class="ef-select-view-input" placeholder="Please select ...">
  <span class="ef-select-view-value ef-select-view-value-hidden"></span>
  <span class="ef-select-view-suffix">
    <span class="ef-select-view-icon">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-expand" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter" style="transform: rotate(-45deg);">
        <path d="M7 26v14c0 .552.444 1 .996 1H22m19-19V8c0-.552-.444-1-.996-1H26"></path>
      </svg>
    </span>
  </span>
</span>

<div class="ef-trigger-popup ef-trigger-position-bl"
     trigger-placement="bl"
     style="z-index: 1001; pointer-events: auto; left: 366px; top: 3736.31px; width: 320px; margin-top: 8px;"
     id="selectContent1"
     parentId="select1"
>
 <div class="ef-trigger-popup-wrapper" style="transform-origin: 0px 0px;">
  <div class="ef-trigger-content">
   <div class="ef-select-dropdown">
     <div class="ef-scrollbar ef-scrollbar-type-embed" style=""> <div class="ef-scrollbar-container ef-select-dropdown-list-wrapper">
      <ul class="ef-select-dropdown-list">
       <li class="ef-select-option"><span class="ef-select-option-content">Beijing</span></li>
       <li class="ef-select-option"><span class="ef-select-option-content">Shanghai</span></li>
       <li class="ef-select-option"><span class="ef-select-option-content">Guangzhou</span></li>
       <li class="ef-select-option ef-select-option-disabled"><span class="ef-select-option-content">Disabled</span></li>
       <li class="ef-select-option"><span class="ef-select-option-content">Shenzhen</span></li>
       <li class="ef-select-option"><span class="ef-select-option-content">Chengdu</span></li>
       <li class="ef-select-option"><span class="ef-select-option-content">Wuhan</span></li>
      </ul>
     </div>
    </div>
   </div>
  </div>
  </div>
</div>
```

Input1 span 的 contentId 为 Select2的id，Select2的parentId是Input1的id

单选模式，单击li选择Select2的值到Input1，通过点击的li上查最近的ef-trigger-popup ef-trigger-position-bl的parentId
多选模式，Select2的选项里有checkbox。在Input1寻找class的ef-select-view-input组件，并添加ef-select-view-input-hidden
的class以隐藏输入框，在Input1寻找class的ef-select-view-value组件，并删除ef-select-view-value-hidden。

允许清除，默认可清除，当单选时鼠标hover到Input1和Input1为点击激活状态将Input1的图标替换为
关闭图标。
