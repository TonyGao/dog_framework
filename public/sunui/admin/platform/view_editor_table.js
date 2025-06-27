$(document).ready(function () {
  let alert = window.alert;
  let $canvas = $('.canvas');

    // 显示选择边框的函数
  function showSelectionBorder(cells) {
    const $cells = $(cells);
    if ($cells.length === 0) return;
    
    // 获取表格容器和表格元素
    const $tableContainer = $cells.closest('.table-container');
    const $table = $cells.closest('table');
    if ($tableContainer.length === 0 || $table.length === 0) return;
    
    // 确保容器有相对定位
    if ($tableContainer.css('position') !== 'relative') {
      $tableContainer.css('position', 'relative');
    }
    
    // 计算选区最外围的位置（相对于表格容器）
    let top = Infinity, left = Infinity, bottom = -Infinity, right = -Infinity;
    const processedCells = new Set();
    
    $cells.each(function () {
      const $cell = $(this);
      
      // 如果是隐藏的合并单元格，需要找到对应的主合并单元格
      if ($cell.attr('data-merged') || $cell.css('display') === 'none') {
        // 查找对应的主合并单元格
        const rowIndex = $cell.closest('tr').index();
        const cellIndex = $cell.index();
        const $table = $cell.closest('table');
        
        // 在同一行或之前的行中查找包含此位置的合并单元格
        let $mainCell = null;
        $table.find('tr').each(function(trIndex) {
          if (trIndex > rowIndex) return false; // 只查找当前行及之前的行
          
          $(this).find('td, th').each(function() {
            const $candidate = $(this);
            const candidateRowspan = parseInt($candidate.attr('rowspan') || 1);
            const candidateColspan = parseInt($candidate.attr('colspan') || 1);
            const candidateRowIndex = $candidate.closest('tr').index();
            const candidateCellIndex = $candidate.index();
            
            // 检查当前隐藏单元格是否在这个候选单元格的合并范围内
            if (candidateRowIndex <= rowIndex && 
                candidateRowIndex + candidateRowspan > rowIndex &&
                candidateCellIndex <= cellIndex && 
                candidateCellIndex + candidateColspan > cellIndex &&
                !$candidate.attr('data-merged')) {
              $mainCell = $candidate;
              return false; // 找到了，退出循环
            }
          });
          
          if ($mainCell) return false; // 找到了，退出外层循环
        });
        
        if ($mainCell && !processedCells.has($mainCell[0])) {
          processedCells.add($mainCell[0]);
          
          const cellPos = $mainCell.position();
          const cellTop = cellPos.top;
          const cellLeft = cellPos.left;
          const cellBottom = cellTop + $mainCell.outerHeight();
          const cellRight = cellLeft + $mainCell.outerWidth();
          
          top = Math.min(top, cellTop);
          left = Math.min(left, cellLeft);
          bottom = Math.max(bottom, cellBottom);
          right = Math.max(right, cellRight);
        }
      } else {
        // 处理可见单元格
        if (!processedCells.has(this)) {
          processedCells.add(this);
          
          const cellPos = $cell.position();
          const cellTop = cellPos.top;
          const cellLeft = cellPos.left;
          const cellBottom = cellTop + $cell.outerHeight();
          const cellRight = cellLeft + $cell.outerWidth();
          
          top = Math.min(top, cellTop);
          left = Math.min(left, cellLeft);
          bottom = Math.max(bottom, cellBottom);
          right = Math.max(right, cellRight);
        }
      }
    });
    
    // 如果没有有效的边界，直接返回
    if (top === Infinity || left === Infinity || bottom === -Infinity || right === -Infinity) {
      return;
    }
    
    // 获取表格的边界，确保边框不超出表格范围
    const tablePos = $table.position();
    const tableTop = tablePos.top;
    const tableLeft = tablePos.left;
    const tableBottom = tableTop + $table.outerHeight();
    const tableRight = tableLeft + $table.outerWidth();
    
    // 限制边框在表格范围内
    top = Math.max(top, tableTop);
    left = Math.max(left, tableLeft);
    bottom = Math.min(bottom, tableBottom);
    right = Math.min(right, tableRight);
    
    // 确保边框尺寸有效
    if (right <= left || bottom <= top) return;
    
    // 获取或创建选择边框div
    let $borderDiv = $tableContainer.find('.selection-border');
    if ($borderDiv.length === 0) {
      $borderDiv = $('<div class="selection-border"></div>');
      $tableContainer.append($borderDiv);
    }
    
    $borderDiv.css({
      position: 'absolute',
      top: top,
      left: left,
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