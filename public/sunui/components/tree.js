$(document).ready(function () {
  $("body").on("click", ".common-tree-wrapper .sub-tree-content .arrow-icon", function (event) {
    // 阻止事件冒泡
    event.stopPropagation();
    // 找到最近的 item-content 容器，然后找到里面的 .tree-indent 和 .sub-tree-content
    const parentItem = $(this).closest('.item-content');
    parentItem.nextAll(".tree-indent, .sub-tree-content").toggle(0);

    let icon = $(this).find('i');
    if (icon.hasClass("fa-caret-down")) {
      icon.removeClass("fa-caret-down").addClass("fa-caret-right");
    } else {
      icon.removeClass("fa-caret-right").addClass("fa-caret-down");
    }
  })
})