$(document).ready(function () {
  /** 供其他js文件共享此 alert */
  let alert = new Alert($('.canvas'));
  window.alert = alert;
  let $canvas = $('.canvas');
  hideSelectionBorder = window.viewEditor && window.viewEditor.hideSelectionBorder;

  // 使用 || 操作符保留现有属性，而不是重置整个对象
  window.viewEditor = window.viewEditor || {};
  
  clearSelection = window.viewEditor.clearSelection;
  initDraggableDroppable = window.viewEditor.initDraggableDroppable;
  
  // 绑定表格事件的公共方法
  function bindTableEvents() {
    // 重新绑定表格单元格选择事件
    $('.ef-table td, .ef-table th').off('mousedown mousemove click dblclick blur mouseover');
    
    // 处理表格单元格点击事件，点击单元格
    $canvas.off('click', '.ef-table td').on('click', '.ef-table td', function(event) {
      const $currentCell = $(this);
      const $allEditableCells = $('.ef-table .cell-content[contenteditable="true"]');

      // 同步视图编辑器工具栏的按钮状态
      if (window.viewEditor && window.viewEditor.toolbar && window.viewEditor.toolbar.syncToolbarButtonStates) {
        window.viewEditor.toolbar.syncToolbarButtonStates($currentCell);
      }
      
      // 如果点击的是当前正在编辑的单元格内容，不做任何处理
      const $contentDiv = $currentCell.find('.cell-content');
      if ($contentDiv.length && $contentDiv.attr('contenteditable') === 'true') {
        return;
      }

      // 得到当前单元格的表格
      const $table = $currentCell.closest('table');
      clearSelection($table);

      // 标记当前单元格为活动状态
      $currentCell.attr('data-cell-active', 'true');
      
      // 如果是合并单元格，还需要标记被合并隐藏的单元格
      const colspan = parseInt($currentCell.attr('colspan')) || 1;
      const rowspan = parseInt($currentCell.attr('rowspan')) || 1;
      
      if (colspan > 1 || rowspan > 1) {
        const $currentRow = $currentCell.parent();
        const currentRowIndex = $currentRow.index();
        
        // 计算当前单元格的逻辑列起始位置
        let currentLogicalCol = 0;
        $currentRow.find('td, th').each(function() {
          if (this === $currentCell[0]) {
            return false; // 找到当前单元格，停止计算
          }
          const cellColspan = parseInt($(this).attr('colspan')) || 1;
          currentLogicalCol += cellColspan;
        });
        
        // 遍历合并单元格覆盖的所有行
        for (let r = 0; r < rowspan; r++) {
          const $targetRow = $table.find('tr').eq(currentRowIndex + r);
          if ($targetRow.length) {
            let logicalColIndex = 0;
            $targetRow.find('td, th').each(function() {
              const $cell = $(this);
              const cellColspan = parseInt($cell.attr('colspan')) || 1;
              const cellRowspan = parseInt($cell.attr('rowspan')) || 1;
              
              // 检查当前单元格的逻辑列范围是否与合并单元格重叠
              const cellStartCol = logicalColIndex;
              const cellEndCol = logicalColIndex + cellColspan - 1;
              const mergedStartCol = currentLogicalCol;
              const mergedEndCol = currentLogicalCol + colspan - 1;
              
              // 如果单元格在合并区域内
              if (cellStartCol <= mergedEndCol && cellEndCol >= mergedStartCol) {
                // 如果是隐藏的合并单元格或者在合并区域内的单元格，标记为活动状态
                if ($cell.attr('data-merged') || $cell.css('display') === 'none' || 
                    (cellStartCol >= mergedStartCol && cellEndCol <= mergedEndCol && r > 0)) {
                  $cell.attr('data-cell-active', 'true');
                }
              }
              
              logicalColIndex += cellColspan;
            });
          }
        }
      }
      
      showSelectionBorder($currentCell);
    });
    
    // 重新绑定表格组件的多选事件
    $('.ef-table-component').each(function() {
      const $component = $(this);
      bindTableComponentEvents($component);
    });
  }
  
  // 绑定单个表格组件的事件
  function bindTableComponentEvents($component) {
    let isSelecting = false;
    let startCell = null;
    let selectedCells = [];
    let startRowIndex = -1;
    let startColIndex = -1;
    let isSelected = false;
    let selectedRange = null;
    let multiSelectRanges = [];
    
    // 获取单元格的行列索引
    function getCellIndex(cell) {
      const $cell = $(cell);
      const $row = $cell.parent();
      
      // 计算逻辑列索引，考虑合并单元格
      let logicalColIndex = 0;
      $row.find('td, th').each(function(index) {
        if (this === cell) {
          return false; // 找到目标单元格，停止循环
        }
        
        const $currentCell = $(this);
        const colspan = parseInt($currentCell.attr('colspan')) || 1;
        logicalColIndex += colspan;
      });
      
      return {
        row: $row.index(),
        col: logicalColIndex,
        domIndex: $cell.index() // DOM中的实际索引
      };
    }

    // 选择区域内的所有单元格
    function selectCellsInRange(startCell, endCell, isMultiSelect = false) {
      const start = getCellIndex(startCell);
      const end = getCellIndex(endCell);
      const minRow = Math.min(start.row, end.row);
      const maxRow = Math.max(start.row, end.row);
      const minCol = Math.min(start.col, end.col);
      const maxCol = Math.max(start.col, end.col);
      
      // 如果不是多选模式，清除之前的选择
      if (!isMultiSelect) {
        clearSelection($component);
      }
      
      const selectedCells = [];
      $component.find('tr').each(function(rowIndex) {
        if (rowIndex >= minRow && rowIndex <= maxRow) {
          // 计算当前行的逻辑列映射
          let logicalColIndex = 0;
          $(this).find('td, th').each(function() {
            const $cell = $(this);
            const colspan = parseInt($cell.attr('colspan')) || 1;
            const rowspan = parseInt($cell.attr('rowspan')) || 1;
            
            // 检查当前单元格的逻辑列范围是否与选择范围重叠
            const cellStartCol = logicalColIndex;
            const cellEndCol = logicalColIndex + colspan - 1;
            
            // 只选择实际可见的单元格，跳过被合并隐藏的单元格
            if (cellStartCol <= maxCol && cellEndCol >= minCol && !$cell.attr('data-merged')) {
              // 只清除没有手动设置边框的单元格
              if (!$cell.attr('data-custom-border')) {
                $cell.css({
                  'border': '1px dashed #d5d8dc',
                  'border-width': '1px',
                  'border-style': 'dashed',
                  'border-color': '#d5d8dc'
                });
              }
              
              $cell.attr('data-cell-active', 'true');
              selectedCells.push($cell[0]);
              
              // 如果是合并单元格，立即处理其覆盖的隐藏单元格
              if (colspan > 1 || rowspan > 1) {
                // 遍历合并单元格覆盖的所有行
                for (let r = 0; r < rowspan; r++) {
                  const targetRowIndex = rowIndex + r;
                  if (targetRowIndex <= maxRow) {
                    const $targetRow = $component.find('tr').eq(targetRowIndex);
                    if ($targetRow.length) {
                      let targetLogicalColIndex = 0;
                      $targetRow.find('td, th').each(function() {
                        const $targetCell = $(this);
                        const targetColspan = parseInt($targetCell.attr('colspan')) || 1;
                        
                        // 检查目标单元格的逻辑列范围是否在合并单元格覆盖范围内
                        const targetCellStartCol = targetLogicalColIndex;
                        const targetCellEndCol = targetLogicalColIndex + targetColspan - 1;
                        
                        // 如果目标单元格在合并单元格覆盖的范围内
                        if (targetCellStartCol >= cellStartCol && targetCellEndCol <= cellEndCol) {
                          // 如果是隐藏的合并单元格或在合并区域内的其他行
                          if ($targetCell.attr('data-merged') || $targetCell.css('display') === 'none' || r > 0) {
                            if (!$targetCell.attr('data-custom-border')) {
                              $targetCell.css({
                                'border': '1px dashed #d5d8dc',
                                'border-width': '1px',
                                'border-style': 'dashed',
                                'border-color': '#d5d8dc'
                              });
                            }
                            $targetCell.attr('data-cell-active', 'true');
                            selectedCells.push($targetCell[0]);
                          }
                        }
                        
                        targetLogicalColIndex += targetColspan;
                      });
                    }
                  }
                }
              }
            }
            
            logicalColIndex += colspan;
          });
        }
      });
      
      // 对整个选中区域显示选择边框
      if (selectedCells.length > 0) {
        showSelectionBorder(selectedCells);
      }
    }
    
    // 添加函数来重新渲染所有多选区域
    function renderAllSelections() {
      clearSelection($component);
      multiSelectRanges.forEach(range => {
        if (range.startCell && range.endCell) {
          selectCellsInRange(range.startCell, range.endCell, true);
        }
      });
    }
    
    // 鼠标按下时开始选择
    $component.find('td, th').off('mousedown').on('mousedown', function(e) {
      // 如果单元格处于编辑状态，允许默认的文本选择行为
      const $contentDiv = $(this).find('.cell-content');
      if (($contentDiv.length && $contentDiv.attr('contenteditable') === 'true') || 
          $(this).attr('contenteditable') === 'true') {
        // 在编辑模式下，不阻止默认行为，允许文本选择
        return;
      }
      
      // 检查是否点击在拖拽手柄上
      if ($(e.target).hasClass('column-resize-handle') || 
          $(e.target).hasClass('left-resize-handle') ||
          $(e.target).hasClass('row-resize-handle') ||
          $(e.target).hasClass('resize-handle-corner')) {
        return;
      }
      
      e.preventDefault();
      isSelecting = true;
      startCell = this;
      
      // 检查是否按住了Ctrl(Windows/Linux)或Command(macOS)键
      const isMultiSelect = e.ctrlKey || e.metaKey;
      
      if (!isMultiSelect) {
        // 单选模式：清除之前的选择
        clearSelection($component);
        multiSelectRanges = [];
      } else {
        // 多选模式：检查当前单元格是否已经在选中区域内
        const clickedCellInSelection = $(this).attr('data-cell-active') === 'true';
        if (clickedCellInSelection) {
          // 如果点击的是已选中的单元格，不清除选择，允许继续拖拽
          return;
        }
      }
      
      const indices = getCellIndex(this);
      startRowIndex = indices.row;
      startColIndex = indices.col;
      isSelected = true;
    });

    // 鼠标移动时更新选择范围
    $component.find('td, th').off('mousemove').on('mousemove', function(e) {
      if (isSelecting) {
        const isMultiSelect = e.ctrlKey || e.metaKey;
        if (isMultiSelect) {
          // 多选模式：重新渲染所有已保存的区域，然后添加当前拖拽区域
          clearSelection($component);
          // 重新渲染之前保存的所有区域
          multiSelectRanges.forEach(range => {
            selectCellsInRangeForNewComponent(range.startCell, range.endCell, true);
          });
          // 添加当前拖拽的区域
          selectCellsInRange(startCell, this, true);
        } else {
          // 单选模式：只显示当前拖拽的区域
          selectCellsInRange(startCell, this, false);
        }
      }
    });

    // 将事件绑定到组件级别
    $component.off('click').on('click', function(e) {
      const $target = $(e.target);
      // 只有当点击的不是表格内容且不是在多选模式下时才清空选区
      if (!$target.closest('.ef-table').length && !(e.ctrlKey || e.metaKey)) {
        clearSelection($component);
        isSelected = false;
        selectedRange = null;
        multiSelectRanges = [];
      }
    });
    
    // 单独处理单元格点击事件
    $component.find('td, th').off('click').on('click', function(e) {
      const isMultiSelect = e.ctrlKey || e.metaKey;
      if (!isMultiSelect && !isSelecting) {
        // 单选模式且不在拖拽状态：选中当前单元格
        clearSelection($component);
        multiSelectRanges = [];
        
        const $currentCell = $(this);
        $currentCell.attr('data-cell-active', 'true');
        
        // 如果是合并单元格，还需要标记被合并隐藏的单元格
        const colspan = parseInt($currentCell.attr('colspan')) || 1;
        const rowspan = parseInt($currentCell.attr('rowspan')) || 1;
        
        if (colspan > 1 || rowspan > 1) {
          const $currentRow = $currentCell.parent();
          const currentRowIndex = $currentRow.index();
          const $table = $currentCell.closest('table');
          
          // 计算当前单元格的逻辑列起始位置
          let currentLogicalCol = 0;
          $currentRow.find('td, th').each(function() {
            if (this === $currentCell[0]) {
              return false; // 找到当前单元格，停止计算
            }
            const cellColspan = parseInt($(this).attr('colspan')) || 1;
            currentLogicalCol += cellColspan;
          });
          
          // 遍历合并单元格覆盖的所有行
          for (let r = 0; r < rowspan; r++) {
            const $targetRow = $table.find('tr').eq(currentRowIndex + r);
            if ($targetRow.length) {
              let logicalColIndex = 0;
              $targetRow.find('td, th').each(function() {
                const $cell = $(this);
                const cellColspan = parseInt($cell.attr('colspan')) || 1;
                const cellRowspan = parseInt($cell.attr('rowspan')) || 1;
                
                // 检查当前单元格的逻辑列范围是否与合并单元格重叠
                const cellStartCol = logicalColIndex;
                const cellEndCol = logicalColIndex + cellColspan - 1;
                const mergedStartCol = currentLogicalCol;
                const mergedEndCol = currentLogicalCol + colspan - 1;
                
                // 如果单元格在合并区域内
                if (cellStartCol <= mergedEndCol && cellEndCol >= mergedStartCol) {
                  // 如果是隐藏的合并单元格或者在合并区域内的单元格，标记为活动状态
                  if ($cell.attr('data-merged') || $cell.css('display') === 'none' || 
                      (cellStartCol >= mergedStartCol && cellEndCol <= mergedEndCol && r > 0)) {
                    $cell.attr('data-cell-active', 'true');
                  }
                }
                
                logicalColIndex += cellColspan;
              });
            }
          }
        }
        
        showSelectionBorder($currentCell);
      }
    });
    
    // 双击进入编辑模式
    $component.find('th, td').off('dblclick').on('dblclick', function() {
      clearSelection($component); // 清除已有的选中效果
      const $contentDiv = $(this).find('.cell-content');
      if ($contentDiv.length) {
        $contentDiv.attr('contenteditable', 'true')
                   .css('cursor', 'text')
                   .focus();
      } else {
        $(this).attr('contenteditable', 'true')
               .css('cursor', 'text')
               .focus();
      }
    });

    // 失去焦点时退出编辑模式
    $component.find('th, td').off('blur').on('blur', function() {
      const $contentDiv = $(this).find('.cell-content');
      if ($contentDiv.length) {
        $contentDiv.attr('contenteditable', 'false')
                   .css('cursor', 'default')
                   .css('min-height', ''); // 清除min-height样式
      } else {
        $(this).attr('contenteditable', 'false')
               .css('cursor', 'default');
      }
    });
    
    // 鼠标松开时结束选择
    $(document).off('mouseup.tableComponent').on('mouseup.tableComponent', function(e) {
      if (isSelecting && startCell) {
        const isMultiSelect = e.ctrlKey || e.metaKey;
        if (isMultiSelect) {
          // 多选模式：保存当前选择区域
          const endCell = document.elementFromPoint(e.clientX, e.clientY);
          if (endCell && $(endCell).closest('.ef-table').length) {
            const currentRange = {
              startCell: startCell,
              endCell: endCell
            };
            multiSelectRanges.push(currentRange);
            // 重新渲染所有选择区域
            renderAllSelections();
          }
        } else {
          // 单选模式：清除多选区域
          multiSelectRanges = [];
        }
        if (selectedCells.length > 0) {
          selectedRange = {
            startCell: startCell,
            endCell: selectedCells[selectedCells.length - 1]
          };
        }
      }
      isSelecting = false;
      startCell = null;
    });
  }

  // 给canvas添加点击事件，处理空白区域点击
  $canvas.on('click', function(event) {
    // 检查点击的是否是canvas本身（空白区域）
    if (event.target === this) {
      // 清除所有表格单元格的选中状态（包括th和td元素）
      $('.ef-table th, .ef-table td').each(function() {
        const $cell = $(this);
        // 只清除没有手动设置边框的单元格
        if (!$cell.attr('data-custom-border')) {
          $cell.css({
            'border': '1px dashed #d5d8dc', 
            'border-top': '1px dashed #d5d8dc',
            'border-bottom': '1px dashed #d5d8dc',
            'border-left': '1px dashed #d5d8dc',
            'border-right': '1px dashed #d5d8dc'
          });
        }

        $cell.css({'outline': 'none'});  // 清除临时选取外部的蓝色边框
        $cell.removeAttr('data-cell-active');
      });
      
      // 隐藏所有选择边框图层
      hideSelectionBorder();
      
      // 移除所有section的active状态
      $('.section').removeClass('active');
    }
  });

  // 处理表格单元格点击事件，点击单元格
  $canvas.on('click', '.ef-table td', function(event) {
    const $currentCell = $(this);
    const $allEditableCells = $('.ef-table .cell-content[contenteditable="true"]');
    const $contentDiv = $currentCell.find('.cell-content');

    // 同步视图编辑器工具栏的按钮状态
    window.viewEditor.toolbar.syncToolbarButtonStates($currentCell);
    
    // 如果点击的是当前正在编辑的单元格内容，不做任何处理
    if ($contentDiv.length && $contentDiv.attr('contenteditable') === 'true') {
      return;
    }

    // 得到当前单元格的表格
    const $table = $currentCell.closest('table');
    clearSelection($table);

    // 标记当前单元格为活动状态
    $currentCell.attr('data-cell-active', 'true');
    
    // 如果是合并单元格，还需要标记被合并隐藏的单元格
    const colspan = parseInt($currentCell.attr('colspan')) || 1;
    const rowspan = parseInt($currentCell.attr('rowspan')) || 1;
    
    if (colspan > 1 || rowspan > 1) {
      const $currentRow = $currentCell.parent();
      const currentRowIndex = $currentRow.index();
      const currentColIndex = $currentCell.index();
      
      // 遍历合并单元格覆盖的所有逻辑位置
      for (let r = 0; r < rowspan; r++) {
        const $targetRow = $table.find('tr').eq(currentRowIndex + r);
        if ($targetRow.length) {
          let logicalColIndex = 0;
          $targetRow.find('td, th').each(function() {
            const $cell = $(this);
            const cellColspan = parseInt($cell.attr('colspan')) || 1;
            
            // 检查当前单元格的逻辑列范围是否与合并单元格重叠
            if (logicalColIndex >= currentColIndex && logicalColIndex < currentColIndex + colspan) {
              // 如果是隐藏的合并单元格，标记为活动状态
              if ($cell.attr('data-merged') || $cell.css('display') === 'none') {
                $cell.attr('data-cell-active', 'true');
              }
            }
            
            logicalColIndex += cellColspan;
          });
        }
      }
    }
    
    showSelectionBorder($currentCell);
    $currentCell.focus();
    
    // 移除其他单元格的可编辑状态
    $allEditableCells.removeAttr('contenteditable');
    
    // 触发单元格选择变化事件，更新split-cells按钮状态
    $(document).trigger('cell-selection-changed');
  });
  
  // 添加双击事件处理，使单元格可编辑
  $canvas.on('dblclick', '.ef-table td', function(event) {
    const $currentCell = $(this);
    const $contentDiv = $currentCell.find('.cell-content');
    
    // 设置当前单元格为可编辑状态
    if ($contentDiv.length) {
      $contentDiv.attr('contenteditable', 'true');
      $contentDiv.css('min-height', '30px'); // 添加最小高度以解决光标纵向居中问题
      $currentCell.attr('data-cell-active', 'true');
      $contentDiv.focus();
      
      // 将光标定位到内容中间位置
      if (window.getSelection && document.createRange) {
        const range = document.createRange();
        const contentElement = $contentDiv[0];
        
        // 如果有文本内容，将光标定位到文本中间
        if (contentElement.textContent.length > 0) {
          const textLength = contentElement.textContent.length;
          const midPoint = Math.floor(textLength / 2);
          
          // 创建文本节点范围
          if (contentElement.firstChild && contentElement.firstChild.nodeType === Node.TEXT_NODE) {
            range.setStart(contentElement.firstChild, midPoint);
            range.setEnd(contentElement.firstChild, midPoint);
          } else {
            range.setStart(contentElement, 0);
            range.setEnd(contentElement, 0);
          }
        } else {
          // 如果没有内容，将光标定位到元素开始位置
          range.setStart(contentElement, 0);
          range.setEnd(contentElement, 0);
        }
        
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
      }
    } else {
      // 兼容旧格式
      $currentCell.attr('contenteditable', 'true');
      $currentCell.attr('data-cell-active', 'true');
      $currentCell.focus();
      
      if (window.getSelection && document.createRange) {
        const range = document.createRange();
        const cellElement = $currentCell[0];
        
        // 如果有文本内容，将光标定位到文本中间
        if (cellElement.textContent.length > 0) {
          const textLength = cellElement.textContent.length;
          const midPoint = Math.floor(textLength / 2);
          
          // 创建文本节点范围
          if (cellElement.firstChild && cellElement.firstChild.nodeType === Node.TEXT_NODE) {
            range.setStart(cellElement.firstChild, midPoint);
            range.setEnd(cellElement.firstChild, midPoint);
          } else {
            range.setStart(cellElement, 0);
            range.setEnd(cellElement, 0);
          }
        } else {
          // 如果没有内容，将光标定位到元素开始位置
          range.setStart(cellElement, 0);
          range.setEnd(cellElement, 0);
        }
        
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
      }
    }
  });
  
  // 添加键盘事件处理，当单元格处于编辑状态时按回车键取消编辑
  $canvas.on('keydown', function(event) {
    const contentDiv = $(this).find('.ef-table .cell-content[contenteditable="true"]');
    let currentCell = $(contentDiv).parent();

    if (contentDiv.length > 0 && contentDiv.attr('contenteditable') === 'true') {
      // 如果按下的是esc，取消编辑
      if (event.key === 'Escape' || event.keyCode === 27) {
        event.preventDefault();
        
        // 取消编辑状态
        contentDiv.removeAttr('contenteditable');
        currentCell.removeAttr('contenteditable');
        hideSelectionBorder();
        contentDiv.blur();
        currentCell.blur();
        
        // 恢复到单击时的状态
        currentCell.attr('data-cell-active', 'true');
        showSelectionBorder(currentCell);
      }
    }
  });

  // 初始化页面时调用一次初始化函数
  initDraggableDroppable();

  // 页面加载时添加全局CSS规则来覆盖jQuery UI自动添加的position: relative样式
  if ($('#ui-draggable-override-style').length === 0) {
    $('<style id="ui-draggable-override-style">')
      .prop('type', 'text/css')
      .html('.ui-draggable:not(.being-dragged) { position: static !important; }')
      .appendTo('head');
  }

  let preventBlur = false;

  // 当 [contenteditable="true"] 失去焦点时，检查标志来决定是否隐藏
  $(document).on('blur', '[contenteditable="true"]', function () {
    if (!preventBlur) {
      $(this).closest('.ef-text-component')
        .css('border', 'none')
        .find('.ef-component-labels').hide();
    }
    // 重置标志
    preventBlur = false;
  });
  
  // 当 [contenteditable="true"] 获得焦点时显示
  $(document).on('focus', '[contenteditable="true"]', function () {
    $(this).closest('.ef-text-component')
      .css('border', '1px solid #116dff')
      .find('.ef-component-labels').show();
  });

  // 暴露bindTableEvents方法到全局
  window.viewEditor = window.viewEditor || {};
  Object.assign(window.viewEditor, {
    bindTableEvents: bindTableEvents,
    bindTableComponentEvents: bindTableComponentEvents
  });
});
