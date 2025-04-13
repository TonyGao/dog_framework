$(document).ready(function () {
  // 设置 toolbar-box 为可排序并允许跨行拖拽
  $(".sortable-toolbar-box").sortable({
      connectWith: ".sortable-toolbar-box",  // 允许跨 toolbar-box 拖拽
      items: ".sortable-toolbar-wrap",      // 设置可以拖拽的元素是 toolbar-wrap
      placeholder: "sortable-placeholder",  // 设置占位符样式
      forcePlaceholderSize: true,           // 强制占位符显示
      tolerance: "pointer",                // 调整拖拽时的容忍区域
      revert: true                         // 动画回弹效果
  }).disableSelection();  // 禁止选中文本，确保拖拽正常工作

  // 设置 toolbar-wrap 内部的排序和跨 toolbar-box 拖拽
  $(".sortable-toolbar-wrap").sortable({
      connectWith: ".sortable-toolbar-wrap",  // 允许跨 toolbar-wrap 拖拽
      items: ".toolbar-content",             // 设置可以拖拽的内容是 toolbar-content
      placeholder: "sortable-placeholder",   // 设置占位符样式
      revert: true                           // 动画回弹效果
  });

  // 在拖拽开始时，为拖拽元素添加自定义样式
  $(".sortable-toolbar-wrap").on("sortstart", function (event, ui) {
      ui.item.addClass("dragging");  // 为拖拽项添加 .dragging 样式
  });

  // 拖拽结束后移除自定义样式
  $(".sortable-toolbar-wrap").on("sortstop", function (event, ui) {
      ui.item.removeClass("dragging");  // 移除 .dragging 样式
  });
});
