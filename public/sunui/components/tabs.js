$(document).ready(function () {
  $(".ef-tabs").on("click", ".tabs li", function() {
    let liid = $(this).attr("id");
    $(this).addClass("tabs-selected");
    $(this).siblings().removeClass("tabs-selected");

    let panel = $(".panel-htop[liid='"+ liid + "']");
    panel.css("display", "");
    panel.siblings().css("display", "none");
  })

  /**
   * 当关闭标签页时，同时移除li和对应的panel，如果关闭的是当前激活状态的
   * 标签，就激活左侧或右侧的标签。
   * 当左侧有标签时激活左侧，当右侧有标签时激活右侧，当都没有时显示空panel的状态
   *
   * 如果关闭的时候当前标签不是激活状态，则保持当前激活状态的标签不变。
   */
  $(".ef-tabs").on("click", ".ef-tabs-close", function() {
    let closeLi = $(this).closest('li');
    let closeId = closeLi.attr('id');
    let panel = $(".panel-htop[liid='"+ closeId + "']");

    if (closeLi.hasClass("tabs-selected")) {
      if (closeLi.prev().length) {
        closeLi.prev().trigger("click");
      } else {
        if (closeLi.next().length) {
          closeLi.next().trigger("click");
        }
      }
    }

    let tabsContainer = closeLi.parents(".tabs-container");
    let tabId = tabsContainer.attr('id');

    $("[id='"+ closeId +"']").remove();
    panel.remove();
    EfTabs.checkTabWidth(tabId);
  })

  $(".ef-tabs").on("click", ".tabs-scroller-left", function() {
    let tabsId = $(this).parents(".ef-tabs").attr("id");

    let tabs = $(`[id='${tabsId}'] .tabs-wrap`);
    let wideTabs = $(`[id='${tabsId}'] .tabs`);
    let totalWidth = 0;
    let leftController = $(`[id='${tabsId}'] .tabs-scroller-left`);

    $(`[id='${tabsId}'] .tabs li`).each(function () {
      totalWidth += $(this).width();
    });

    let currentTab = EfTabs.getLeftmostVisibleTab(tabsId)
    let leftTab = currentTab.prev();
    let leftControllerWidth = leftController.outerWidth();
    // 如果不是最左侧的标签页，获取左侧的标签宽度，并按照这个宽度向右滑动
    if (leftTab.hasClass('tabs-first') === false) {
      let leftTabWidth = leftTab.width();
      let wideTabsMaginLeft = parseInt(wideTabs.css('marginLeft'), 10) || 0;

      // 计算动画滚动的距离
      let scrollDistance = wideTabsMaginLeft + leftTabWidth + leftControllerWidth;

      wideTabs.animate({ marginLeft: scrollDistance }, 300);
    }

    // 如果是最左侧的标签页，将标签页向右移动，防止左侧的控制器挡住标签页左侧
    if (currentTab.hasClass('tabs-first') || leftTab.hasClass('tabs-first')) {
      wideTabs.animate({ marginLeft: leftControllerWidth }, 300);
    }
  })

  $(".ef-tabs").on("click", ".tabs-scroller-right", function() {
    let tabsId = $(this).parents(".ef-tabs").attr("id");
    let tabs = $(`[id='${tabsId}'] .tabs-wrap`);

    let wideTabs = $(`[id='${tabsId}'] .tabs`);
    let totalWidth = 0;
    let rightController = $(`[id='${tabsId}'] .tabs-scroller-right`);
    let wideTabsMaginLeft = parseInt(wideTabs.css('marginLeft'), 10) || 0;

    $(`[id='${tabsId}'] .tabs li`).each(function () {
      totalWidth += $(this).width();
    });

    let tabsWidth = tabs.width();
    let currentTab = EfTabs.getRightmostVisibleTab(tabsId)
    let rightTab;
    let rightTabWidth = 0;

    if (currentTab.hasClass('tabs-last') === false) {
      rightTab = currentTab?.next();
      rightTabWidth = rightTab.width() || 0;
    }

    let biggerThanMax = false;
    let current = (tabsWidth + Math.abs(wideTabsMaginLeft) + rightTabWidth);
    let puniness = 0;
    let rightControllerWidth = rightController.outerWidth();

    if ((current - totalWidth) > rightControllerWidth) {
      biggerThanMax = true;
      puniness = current - totalWidth;
    }

    let scrollDistance = wideTabsMaginLeft - rightTabWidth - rightControllerWidth;

    // 如果是最右侧的标签页，将标签页向左移动，防止右侧的控制器挡住标签页右侧
    if (!biggerThanMax) {
      let newScrollDistance = scrollDistance - rightControllerWidth - puniness;
      wideTabs.animate({ marginLeft:  newScrollDistance}, 300);
    }
  })
})

