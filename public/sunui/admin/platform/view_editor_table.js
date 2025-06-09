$(document).ready(function () {
  let alert = window.alert;
  let $canvas = $('.canvas');

    // 显示选择边框的函数
  function showSelectionBorder(cells) {
    const $cells = $(cells);
    if ($cells.length === 0) return;
    
    // 获取表格容器
    const $tableContainer = $cells.closest('.table-container');
    if ($tableContainer.length === 0) return;
    
    // 确保容器有相对定位
    if ($tableContainer.css('position') !== 'relative') {
      $tableContainer.css('position', 'relative');
    }
    
    // 计算选区最外围的位置
    let top = Infinity, left = Infinity, bottom = -Infinity, right = -Infinity;
    $cells.each(function () {
      const rect = this.getBoundingClientRect();
      top = Math.min(top, rect.top);
      left = Math.min(left, rect.left);
      bottom = Math.max(bottom, rect.bottom);
      right = Math.max(right, rect.right);
    });
    
    // 将边界转为相对于容器的位置
    const containerRect = $tableContainer[0].getBoundingClientRect();
    
    // 获取或创建选择边框div
    let $borderDiv = $tableContainer.find('.selection-border');
    if ($borderDiv.length === 0) {
      $borderDiv = $('<div class="selection-border"></div>');
      $tableContainer.append($borderDiv);
    }
    
    $borderDiv.css({
      position: 'absolute',
      top: top - containerRect.top,
      left: left - containerRect.left,
      width: right - left,
      height: bottom - top,
      border: '2px solid rgb(0, 123, 255)',
      'background-color': 'rgba(0, 123, 255, 0.1)',
      'pointer-events': 'none',
      'z-index': 1000,
      display: 'block'
    });
  }

  // 隐藏选择边框的函数
  function hideSelectionBorder(container) {
    if (container) {
      container.find('.selection-border').hide();
    } else {
      $('.selection-border').hide();
    }
  }

  window.viewEditor = window.viewEditor || {};
  Object.assign(window.viewEditor, {
    hideSelectionBorder: hideSelectionBorder,
    showSelectionBorder: showSelectionBorder,
  })
})