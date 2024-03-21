class EfTabs {

  static getLeftmostVisibleTab(tabsId) {
    let tabs = $(`[id='${tabsId}'] .tabs`);
    let tabsContainer = $(`[id='${tabsId}'] .tabs-wrap`);
    let scrollLeft = tabsContainer.scrollLeft();
    let leftmostTab = null;
    let leftmostTabPosition = Number.MAX_SAFE_INTEGER;

    tabs.find('li').each(function () {
      let tab = $(this);
      let tabPosition = tab.position().left;
      let tabWidth = tab.width();

      // 判断标签页是否在可见范围内
      if (tabPosition >= scrollLeft && tabPosition < scrollLeft + tabsContainer.width()) {
        // 找到最左侧的标签页
        if (tabPosition < leftmostTabPosition) {
          leftmostTab = tab;
          leftmostTabPosition = tabPosition;
        }
      }
    });

    return leftmostTab;
  }

  static getRightmostVisibleTab(tabsId) {
    let tabs = $(`[id='${tabsId}'] .tabs`);
    let tabsContainer = $(`[id='${tabsId}'] .tabs-wrap`);
    let scrollLeft = tabsContainer.scrollLeft();
    let rightmostTab = null;
    let rightmostTabPosition = Number.MIN_SAFE_INTEGER;

    tabs.find('li').each(function () {
      let tab = $(this);
      let tabPosition = tab.position().left;
      let tabWidth = tab.width();

      // 判断标签页是否在可见范围内
      if (tabPosition + tabWidth >= scrollLeft && tabPosition <= scrollLeft + tabsContainer.width()) {
        // 找到最右侧的标签页
        if (tabPosition + tabWidth > rightmostTabPosition) {
          rightmostTab = tab;
          rightmostTabPosition = tabPosition + tabWidth;
        }
      }
    });

    return rightmostTab;
  }

  /**
   * 如果 tabId 里已经存在 liId 了，就就转为focus到这个标签页
   */
  static addTab(tabId, liId = '', tabName = '', tabPanel = '', entityData) {

    if ($('#'+tabId+" #"+liId).length > 0) {
      $('#'+tabId+" #"+liId).click();
    } else {
      let id = liId;
      if (liId === '') {
        id = Math.random();
      }
    
      let liTemplate = `<li id="${id}">
        <span class="tabs-inner">
          <span class="tabs-title tabs-closable">${tabName}</span>
          <span class="ef-tabs-close"><i class="fa-solid fa-xmark"></i></span>
        </span>
      </li>`;
  
      let ul = $(`#${tabId} .tabs`);
      let currentTab = $(`#${tabId} .tabs .tabs-selected`);
      let currentId;
      let panelTemplate = `<div class="panel panel-htop" style="display: none;" liid="${id}">
      <div title="" style="padding: 10px;" class="panel-body panel-body-noheader panel-body-noborder" id="">
        <div title="" style="padding: 10px;" class="panel-body panel-body-noheader panel-body-noborder" id="">
          ${entityData}
        </div>
      </div>`;
      if (currentTab.length !== 0) {
        currentId = currentTab.attr('id');
        currentTab.after(liTemplate);
        $(".panel-htop[liid='"+ currentId + "']").after(panelTemplate);
      } else {
        currentId = id;
        ul.append(liTemplate);
        $(".tabs-panels").html(panelTemplate);
      }
  
      $("[id='"+ id +"']").trigger("click");
    }


    this.checkTabWidth(tabId);
  }

  static checkTabWidth(tabsId) {
    let totalWidth = 0;

    $(`[id='${tabsId}'] .tabs li`).each(function () {
      totalWidth += $(this).width();
    });

    let tabs = $(`[id='${tabsId}'] .tabs-wrap`);
    let wideTabs = $(`[id='${tabsId}'] .tabs`);
    let leftScroller = $(`[id='${tabsId}'] .tabs-scroller-left`);
    let rightScroller = $(`[id='${tabsId}'] .tabs-scroller-right`);

    // 计算动画滚动的距离
    let scrollDistance = 0;

    // 如果总宽度超出容器宽度，呈现左右的箭头按钮，并将新增的 tab 页展现在最右端
    // 并动画向左挪动其他标签页
    if (totalWidth > tabs.width()) {
      leftScroller.css("display", "flex");
      rightScroller.css("display", "flex");
      scrollDistance = totalWidth - tabs.width() + rightScroller.outerWidth() + 6;
      // 使用动画效果滚动
      wideTabs.animate({ marginLeft: -scrollDistance }, 300);
    } else {
      leftScroller.hide();
      rightScroller.hide();
      scrollDistance = 0;
      wideTabs.animate({ marginLeft: scrollDistance }, 300);
    }

    /**
     * Todo 因为关闭、新增标签页可能会将首个、末位的标签发生改变，这里需要对相应标签的class
     * 做出遍历和变更
     */
  }
}
