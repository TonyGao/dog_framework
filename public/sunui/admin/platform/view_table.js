/**
 * 表格行高和列宽拖拽调整功能
 */
$(document).ready(function() {
  // 初始化表格调整大小功能
  initTableResize();

  // 启动观察器，监听 ef-table 的插入
  const observer = new MutationObserver(function(mutationsList) {
    for (const mutation of mutationsList) {
      for (const node of mutation.addedNodes) {
        const $node = $(node);
        if ($node.hasClass('ef-table') || $node.find('.ef-table').length > 0) {
          setTimeout(() => {
            initTableResize();
          }, 100);
        }
      }
    }
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });

  /**
   * 初始化表格调整大小功能
   */
  function initTableResize() {
    // 移除现有的调整手柄，避免重复
    $('.column-resize-handle, .row-resize-handle, .resize-handle-corner').remove();
    
    // 为每个表格添加调整大小功能
    $('.ef-table').each(function() {
      const $table = $(this);
      const $tableComponent = $table.closest('.ef-table-component');
      
      // 确保表格组件有相对定位，以便正确放置调整手柄
      $tableComponent.css('position', 'relative');
      
      // 为每个单元格添加调整手柄
      $table.find('td, th').each(function() {
        const $cell = $(this);
        
        // 添加列宽调整手柄（所有单元格）
        // 移除条件限制，使所有单元格都有列宽调整手柄
        const $colHandle = $('<div class="column-resize-handle"></div>');
        $cell.append($colHandle);
        
        // 绑定列宽调整事件
        $colHandle.on('mousedown', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const startX = e.pageX;
          const startWidth = $cell.outerWidth();
          const columnIndex = $cell.index();
          
          // 添加拖拽状态类
          $tableComponent.addClass('resizing');
          $(this).addClass('dragging');
          
          // 创建辅助线
          const $guide = $('<div class="resize-guide vertical"></div>');
          const cellOffset = $cell.offset();
          
          // 计算辅助线的位置，使其与单元格右边界对齐
          $guide.css({
            left: cellOffset.left + startWidth,
            // 不设置top和height，让CSS的100vh生效
          });
          
          // 将辅助线添加到body，使其能够跨越整个视口
          $('body').append($guide);
          
          // 鼠标移动事件
          $(document).on('mousemove.columnResize', function(e) {
            const diffX = e.pageX - startX;
            const newWidth = Math.max(30, startWidth + diffX);
            $guide.css('left', cellOffset.left + newWidth);
          });
          
          // 鼠标释放事件
          $(document).on('mouseup.columnResize', function(e) {
            $(document).off('mousemove.columnResize mouseup.columnResize');
            
            const diffX = e.pageX - startX;
            const newWidth = Math.max(30, startWidth + diffX);
            
            // 应用新宽度到该列的所有单元格
            $table.find('tr').each(function() {
              $(this).children('td, th').eq(columnIndex).css('width', newWidth + 'px');
            });
            
            // 移除拖拽状态和辅助线
            $tableComponent.removeClass('resizing');
            $colHandle.removeClass('dragging');
            $guide.remove();
          });
        });
        
        // 添加行高调整手柄（所有单元格）
        // 移除条件限制，使所有单元格都有行高调整手柄
        const $rowHandle = $('<div class="row-resize-handle"></div>');
        $cell.append($rowHandle);
        
        // 绑定行高调整事件
        $rowHandle.on('mousedown', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const $row = $cell.parent();
          const startY = e.pageY;
          const startHeight = $row.outerHeight();
          
          // 添加拖拽状态类
          $tableComponent.addClass('resizing');
          $(this).addClass('dragging');
          
          // 创建辅助线
          const $guide = $('<div class="resize-guide horizontal"></div>');
          const rowOffset = $row.offset();
          
          // 计算辅助线的位置，使其与行底部对齐
          $guide.css({
            top: rowOffset.top + startHeight,
            // 不设置left和width，让CSS的100vw生效
          });
          
          // 将辅助线添加到body，使其能够跨越整个视口
          $('body').append($guide);
          
          // 鼠标移动事件
          $(document).on('mousemove.rowResize', function(e) {
            const diffY = e.pageY - startY;
            const newHeight = Math.max(20, startHeight + diffY);
            $guide.css('top', rowOffset.top + newHeight);
          });
          
          // 鼠标释放事件
          $(document).on('mouseup.rowResize', function(e) {
            $(document).off('mousemove.rowResize mouseup.rowResize');
            
            const diffY = e.pageY - startY;
            const newHeight = Math.max(20, startHeight + diffY);
            
            // 应用新高度到行
            $row.css('height', newHeight + 'px');
            
            // 移除拖拽状态和辅助线
            $tableComponent.removeClass('resizing');
            $rowHandle.removeClass('dragging');
            $guide.remove();
          });
        });
        
        // 添加角落调整手柄（所有单元格）
        // 移除条件限制，使所有单元格都有角落调整手柄
        const $cornerHandle = $('<div class="resize-handle-corner"></div>');
        $cell.append($cornerHandle);
        
        // 绑定角落调整事件
        $cornerHandle.on('mousedown', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const $row = $cell.parent();
          const startX = e.pageX;
          const startY = e.pageY;
          const startWidth = $cell.outerWidth();
          const startHeight = $row.outerHeight();
          const columnIndex = $cell.index();
          
          // 添加拖拽状态类
          $tableComponent.addClass('resizing');
          $(this).addClass('dragging');
          
          // 创建水平辅助线
          const $hGuide = $('<div class="resize-guide horizontal"></div>');
          const rowOffset = $row.offset();
          
          // 计算水平辅助线的位置，使其与行底部对齐
          $hGuide.css({
            top: rowOffset.top + startHeight,
            // 不设置left和width，让CSS的100vw生效
          });
          $('body').append($hGuide);
          
          // 创建垂直辅助线
          const $vGuide = $('<div class="resize-guide vertical"></div>');
          const cellOffset = $cell.offset();
          
          // 计算垂直辅助线的位置，使其与单元格右边界对齐
          $vGuide.css({
            left: cellOffset.left + startWidth,
            // 不设置top和height，让CSS的100vh生效
          });
          $('body').append($vGuide);
          
          // 鼠标移动事件
          $(document).on('mousemove.cornerResize', function(e) {
            const diffX = e.pageX - startX;
            const diffY = e.pageY - startY;
            const newWidth = Math.max(30, startWidth + diffX);
            const newHeight = Math.max(20, startHeight + diffY);
            
            $hGuide.css('top', rowOffset.top + newHeight);
            $vGuide.css('left', cellOffset.left + newWidth);
          });
          
          // 鼠标释放事件
          $(document).on('mouseup.cornerResize', function(e) {
            $(document).off('mousemove.cornerResize mouseup.cornerResize');
            
            const diffX = e.pageX - startX;
            const diffY = e.pageY - startY;
            const newWidth = Math.max(30, startWidth + diffX);
            const newHeight = Math.max(20, startHeight + diffY);
            
            // 应用新宽度到该列的所有单元格
            $table.find('tr').each(function() {
              $(this).children('td, th').eq(columnIndex).css('width', newWidth + 'px');
            });
            
            // 应用新高度到行
            $row.css('height', newHeight + 'px');
            
            // 移除拖拽状态和辅助线
            $tableComponent.removeClass('resizing');
            $cornerHandle.removeClass('dragging');
            $hGuide.remove();
            $vGuide.remove();
          });
        });
      });
    });
  }

  // 监听窗口大小变化，更新调整手柄位置
  $(window).on('resize', function() {
    initTableResize();
  });
});