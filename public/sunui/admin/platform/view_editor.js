$(document).ready(function () {
  let $canvas = $('.canvas');
  const $addSectionButton = $('.add-section-button');

  // 清除选择样式的函数
  function clearSelection (container) {
    container.find('th, td').css({
      'background-color': '',
      'outline': 'none',
      'border': '1px dashed #d5d8dc',  // 恢复为灰色虚线边框
      'border-width': '1px',
      'border-style': 'dashed',
      'border-color': '#d5d8dc'
    }).removeAttr('data-cell-active');
  }

  function adjustButtonPosition () {
    const canvasOffset = $canvas.offset();
    const canvasWidth = $canvas.outerWidth();

    // console.log("canvasOffset.left:", canvasOffset.left); // 输出 canvas 左侧位置
    // console.log("canvasWidth:", canvasWidth);             // 输出 canvas 宽度

    // 固定按钮到屏幕底部，调整按钮相对于 .canvas 的水平位置
    $addSectionButton.css({
      right: $(window).width() - (canvasOffset.left + canvasWidth) + 20 + 'px',
    });
  }

  // 页面加载时调整按钮位置和canvas对齐方式
  adjustButtonPosition();
  adjustCanvasAlignment();

  // 窗口大小改变时调整按钮位置
  $(window).resize(function () {
    adjustButtonPosition();
    adjustCanvasAlignment();
  });
  
  // 检查section-content宽度并调整canvas对齐方式
  function adjustCanvasAlignment() {
    const $activeSection = $('.section.active');
    if ($activeSection.length > 0) {
      const $sectionContent = $activeSection.find('.section-content');
      const sectionContentWidth = $sectionContent.outerWidth();
      const canvasWidth = $canvas.width();
      
      // 如果section-content宽度超过canvas宽度，将canvas的align-items设置为flex-start
      // 否则保持居中对齐
      if (sectionContentWidth > canvasWidth) {
        $canvas.css('align-items', 'flex-start');
      } else {
        $canvas.css('align-items', 'center');
      }
    }
  }

  // 点击 section 时激活该 section
  function activateSection (section) {
    // 移除所有 section 的 active class
    $('.section').removeClass('active');

    // 给当前点击的 section 添加 active class
    section.addClass('active');
    
    // 调整canvas对齐方式
    adjustCanvasAlignment();
  }

  // 给canvas添加点击事件，处理空白区域点击
  $canvas.on('click', function(event) {
    // 检查点击的是否是canvas本身（空白区域）
    if (event.target === this) {
      // 清除所有表格单元格的选中状态（包括th和td元素）
      $('.ef-table th, .ef-table td').css({
        'border': '1px dashed #d5d8dc', 
        'background-color': 'transparent',
        'outline': 'none',  // 清除outline属性，解决单元格蓝色边框无法清除的问题
        'border-top': '1px dashed #d5d8dc',
        'border-bottom': '1px dashed #d5d8dc',
        'border-left': '1px dashed #d5d8dc',
        'border-right': '1px dashed #d5d8dc'
      });
      // 移除所有section的active状态
      $('.section').removeClass('active');
    }
  });


  // 给现有的 section 添加点击事件
  $canvas.on('click', '.section', function (event) {
    // 阻止事件冒泡到canvas
    event.stopPropagation();
    const $section = $(this);
    const $sectionHeader = $section.find('.section-header');
    
    // 检查点击的目标元素
    const $target = $(event.target);
    
    // 如果点击的是section内的组件（表格、文本等）或其子元素，隐藏section-header
    if ($target.closest('.ef-component').length > 0 || $target.closest('.item-block').length > 0) {
      $sectionHeader.hide();
    } else {
      // 如果点击的是section的空白区域，显示section-header
      $sectionHeader.show();
    }
    
    // 激活当前点击的 section
    activateSection($section);
  });

  // 监听contenteditable为true的元素，粘贴时仅插入纯文本
  $canvas.on('paste', '[contenteditable="true"]', function (event) {
    event.preventDefault(); // 阻止默认粘贴行为
    const text = event.originalEvent.clipboardData.getData('text/plain'); // 获取纯文本
    document.execCommand('insertText', false, text); // 插入纯文本
  });

  // 处理表格单元格点击事件
  $canvas.on('click', '.ef-table td', function(event) {
    const $currentCell = $(this);
    const $allEditableCells = $('.ef-table td[contenteditable="true"]');
    
    // 如果点击的是当前正在编辑的单元格，不做任何处理
    if ($currentCell.attr('contenteditable') === 'true') {
      return;
    }

    // 得到当前单元格的表格
    const $table = $currentCell.closest('table');
    clearSelection($table);

    // 移除此表格的所有单元格的data-cell-active属性
    // const $allCells = $('.ef-table td');
    // $allCells.attr('data-cell-active', 'false');
    $currentCell.attr('data-cell-active', 'true');
    $currentCell.css({
      'background-color': '#f0f8ff',
      'outline': '1px solid #007bff'
    });
    $currentCell.focus();
    
    // 移除其他单元格的可编辑状态
    $allEditableCells.removeAttr('contenteditable');
  });
  
  // 添加双击事件处理，使单元格可编辑
  $canvas.on('dblclick', '.ef-table td', function(event) {
    const $currentCell = $(this);
    
    // 设置当前单元格为可编辑状态
    $currentCell.attr('contenteditable', 'true');
    $currentCell.attr('data-cell-active', 'true');
    $currentCell.focus();
    
    // 选中单元格内容，方便用户直接编辑
    if (window.getSelection && document.createRange) {
      const range = document.createRange();
      range.selectNodeContents($currentCell[0]);
      const selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange(range);
    }
  });

  // 处理添加 section 的逻辑
  $('.add-section-button').click(function () {
    const newSectionHtml = `
          <div class="section" id="${Str.generateRandomString(9)}">
              <div class="section-header">
                  <button class="btn-add"><i class="fa-solid fa-plus"></i></button>
                  <button class="btn-layout"><i class="fa-solid fa-grip"></i></button>
                  <button class="btn-close"><i class="fa-solid fa-times"></i></button>
              </div>
              <div class="section-content ui-droppable" style="width: 1140px">
                  <!-- 新的 Section 内容 -->
              </div>
          </div>`;
    const $newSection = $(newSectionHtml);
    $canvas = $('.canvas');
    $canvas.append($newSection);
    
    // 确保section元素的宽度比section-content大10px
    const $sectionContent = $newSection.find('.section-content');
    const contentWidth = $sectionContent.outerWidth();
    if (contentWidth) {
        $newSection.css('min-width', (contentWidth + 10) + 'px');
    }
    
    activateSection($newSection);
    reinitializeDroppables();
  });

  // 处理 section 的关闭逻辑
  $(document).on('click', '.btn-close', function () {
    $(this).closest('.section').remove();
  });

  $('#toggle-button').on('click', function () {
    const $panel = $('.properties-panel');
    const $button = $(this);

    $panel.toggleClass('hidden'); // 切换面板的显示状态
    $button.toggleClass('reverse'); // 切换按钮的梯形方向

    // 切换图标方向
    const icon = $button.find('i');
    if ($panel.hasClass('hidden')) {
      icon.removeClass('fa-solid fa-angle-right').addClass('fa-solid fa-angle-left');
    } else {
      icon.removeClass('fa-solid fa-angle-left').addClass('fa-solid fa-angle-right');
    }
  });

  // 打开弹窗
  let sectionId;
  $('#canvas').on('click', '.btn-add', function () {
    sectionId = $(this).closest('.section').attr('id');
    $('#structureModal').css('display', 'flex');
  });

  // 点击模态框外部关闭弹窗
  $('#structureModal').on('click', function (event) {
    if ($(event.target).is('#structureModal')) {
      $('#structureModal').hide();
    }
  });

  $('.close-icon i').on('click', function (event) {
    $('#structureModal').hide();
  })

  let full24 = `
  	<div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-24 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center;border: 1px dashed #d5d8dc">
      </div>
	  </div>
  `;

  let halfAndHalf = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-12 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc">
      </div>
      <div class="ef-col-12 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc">
      </div>
    </div>
  `;

  let trisect = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-8 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc">
      </div>
      <div class="ef-col-8 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc">
      </div>
      <div class="ef-col-8 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc">
      </div>
    </div>
  `;

  let fourEqualParts = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>  
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
    </div>
  `;

  let eightSixteen = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-8 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-16 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
  `;

  let sixteenEight = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-16 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-8 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
`;

  let sixSixTwelve = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-12 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
  `;

  let twelveSixSix = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-12 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
  `;

  let sixTwelveSix = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-12 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-6 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
  `;

  let fiveEqualParts = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px; --columns: 5;">
      <div class="ef-col ef-col-auto item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col ef-col-auto item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col ef-col-auto item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col ef-col-auto item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col ef-col-auto item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
  `;

  let sixEqualParts = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px;">
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc"></div>
    </div>
  `;

  let fourSixteenFour = `
    <div class="ef-row ef-row-align-start ef-row-justify-start" style="width: 1140px">
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-16 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
      <div class="ef-col-4 item-block" style="min-height: 68px; line-height: 68px; color: white; text-align: center; border: 1px dashed #d5d8dc""></div>
    </div>
  `;

  $('.ef-col-item').on('click', function (event) {
    $('#structureModal').hide();
    let itemId = $(this).attr('id');
    transformSection($('#' + sectionId));
    if (itemId === 'full-24') {
      $('#' + sectionId + ' .section-content').append(full24);
    }

    if (itemId === 'half-and-half') {
      $('#' + sectionId + ' .section-content').append(halfAndHalf);
    }

    if (itemId === 'trisect') {
      $('#' + sectionId + ' .section-content').append(trisect);
    }

    if (itemId === 'four-equal-parts') {
      $('#' + sectionId + ' .section-content').append(fourEqualParts);
    }

    if (itemId === 'eight-sixteen') {
      $('#' + sectionId + ' .section-content').append(eightSixteen);
    }

    if (itemId === 'sixteen-eight') {
      $('#' + sectionId + ' .section-content').append(sixteenEight);
    }

    if (itemId === 'six-six-twelve') {
      $('#' + sectionId + ' .section-content').append(sixSixTwelve);
    }

    if (itemId === 'twelve-six-six') {
      $('#' + sectionId + ' .section-content').append(twelveSixSix);
    }

    if (itemId === 'six-twelve-six') {
      $('#' + sectionId + ' .section-content').append(sixTwelveSix);
    }

    if (itemId === 'five-equal-parts') {
      $('#' + sectionId + ' .section-content').append(fiveEqualParts);
    }

    if (itemId === 'six-equal-parts') {
      $('#' + sectionId + ' .section-content').append(sixEqualParts);
    }

    if (itemId === 'four-sixteen-four') {
      $('#' + sectionId + ' .section-content').append(fourSixteenFour);
    }

    $canvas = $('.canvas');
    // 确保新添加的 item-block 能被拖拽
    reinitializeDroppables();
  })

  function transformSection (section) {
    section.css({ 'background-color': 'white' }); // 移除 background-color 样式
    section.children('.section-content').css({ 'background-color': 'white', 'min-height': '0px' }); // 清除子元素的背景色
  }

  // 定义组件模板（假设已经存在）
  let textPlaceHolder = '在此添加您的文本';
  const componentTemplates = {
    text: {
      template: `
      <div id="ef-text-comp-{uniqueId}" class="ef-component ef-text-component ef-text-comp-{uniqueId}">
        <span class="ef-component-labels ef-label-small label-above-line label-top" style="left: 0px">
          <span class="ef-label-comp-type draggable">
            <span>Text</span>
          </span>
        </span>
        <h2 class="font_2 ef-rich-text" style="font-size:64px;" contenteditable="true" data-placeholder="请输入文本">${textPlaceHolder}</h2>
      </div>`,
      width: 512, // 模板的预期宽度
      height: 68 // 模板的预期高度
    },
    // 表格组件模板
    table: {
      template: `
      <div id="ef-table-comp-{uniqueId}" class="ef-component ef-table-component ef-table-comp-{uniqueId}" style="position: static">
        <span class="ef-component-labels ef-label-small label-above-line label-top" style="left: 0px">
          <span class="ef-label-comp-type draggable">
            <span>Table</span>
          </span>
        </span>
        <table class="ef-table" ef-table-hotkeys style="width:100%; border-collapse: collapse;">
          <thead>
            <tr style="height: 30px;">
              <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">标题 1</th>
              <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">标题 2</th>
              <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">标题 3</th>
            </tr>
          </thead>
          <tbody>
            <tr style="height: 30px;">
              <td style="border: 1px solid #ddd; padding: 8px;" tabindex="0" data-cell-active="false">内容 1</td>
              <td style="border: 1px solid #ddd; padding: 8px;" tabindex="0" data-cell-active="false">内容 2</td>
              <td style="border: 1px solid #ddd; padding: 8px;" tabindex="0" data-cell-active="false">内容 3</td>
            </tr>
            <tr style="height: 30px;">
              <td style="border: 1px solid #ddd; padding: 8px;" tabindex="0" data-cell-active="false">内容 4</td>
              <td style="border: 1px solid #ddd; padding: 8px;" tabindex="0" data-cell-active="false">内容 5</td>
              <td style="border: 1px solid #ddd; padding: 8px;" tabindex="0" data-cell-active="false">内容 6</td>
            </tr>
          </tbody>
        </table>
      </div>`,
      width: 600, // 表格的预期宽度
      height: 200 // 表格的预期高度
    }
    // 可以添加更多组件类型
  };

  // 生成唯一ID的函数
  function generateUniqueId () {
    return Math.random().toString(36).substr(2, 9);
  }

  // 初始化页面时调用一次初始化函数
  initDraggableDroppable();

  // 使用事件委托，将组件设为可拖动
  $('.component-grid').on('mousedown', '.component-item', function (e) {
    e.preventDefault();

    // 获取拖拽的组件类型
    const componentType = $(this).attr('componenttype');
    const templateConfig = componentTemplates[componentType];
    if (!templateConfig) return;

    // 创建虚框，宽高与templateConfig相同
    const placeholder = $('<div class="dragging-placeholder"></div>').css({
      width: templateConfig.width,
      height: templateConfig.height,
      position: 'absolute',
      border: '1px dashed #007bff',
      backgroundColor: 'rgba(0, 123, 255, 0.1)',
      pointerEvents: 'none'
    });

    $('body').append(placeholder);

    $(document).on('mousemove.drag', function (moveEvent) {
      placeholder.css({
        top: moveEvent.pageY - placeholder.height() / 2,
        left: moveEvent.pageX - placeholder.width() / 2
      });
    });

    // 创建一个标志变量，用于防止重复处理
    let isProcessed = false;
    
    $(document).on('mouseup.drag', function () {
      // 如果已经处理过，则直接返回
      if (isProcessed) return;
      isProcessed = true;
      
      $(document).off('mousemove.drag mouseup.drag');
      placeholder.remove(); // 移除虚框

      // 检查放置位置 - 优先选择item-block，其次是section-content
      // 确保只选择一个最合适的目标容器
      let droppableArea;
      const itemBlock = $('.item-block:hover');
      const sectionContent = $('.section-content:hover');
      
      // 优先使用item-block作为放置目标
      if (itemBlock.length) {
        droppableArea = itemBlock;
      } else if (sectionContent.length) {
        droppableArea = sectionContent;
      }
      
      if (droppableArea && droppableArea.length) {
        // 如果是表格组件，显示表格模态窗口让用户输入行列数
        if (componentType === 'table') {
          // 保存当前拖放的位置和目标元素
          const dropTarget = droppableArea;
          const dropOffsetX = placeholder.offset().left - dropTarget.offset().left;
          const dropOffsetY = placeholder.offset().top - dropTarget.offset().top;

          // 显示表格行列输入模态框
          $('#tableModal').css('display', 'flex');

          // 处理确定按钮点击事件
          $('#create-table-btn').off('click').on('click', function () {
            const rows = parseInt($('#table-rows').val()) || 3;
            const cols = parseInt($('#table-cols').val()) || 3;

            // 生成表格HTML
            let tableHtml = `
            <table class="ef-table" ef-table-hotkeys 
              data-table-keys='{
                "ctrl+a": "selectAll",
                "cmd+a": "selectAll",
                "ctrl+c": "copy",
                "cmd+c": "copy",
                "ctrl+v": "paste",
                "cmd+v": "paste",
                "tab": "nextCell",
                "shift+tab": "prevCell",
                "up": "moveUp",
                "down": "moveDown",
                "left": "moveLeft",
                "right": "moveRight"
              }' 
              style="width:100%; border-collapse: collapse;">
              <tbody>
          `;
          

            // 计算单元格宽度
            const sectionContent = dropTarget.closest('.section-content');
            const sectionWidth = sectionContent.width();
            const cellWidth = Math.floor(sectionWidth / cols);
            const cellMaxWidth = cellWidth;

            // 生成表格内容
            for (let i = 0; i < rows; i++) {
              tableHtml += '<tr style="height: 30px;">';
              for (let j = 0; j < cols; j++) {
              tableHtml += `<td style="font-family: 'Microsoft YaHei', Helvetica, Tahoma, Arial, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', 'Heiti SC', 'WenQuanYi Micro Hei', sans-serif; font-size: 14px; font-weight: normal; font-style: normal; text-decoration: none; text-align: left; vertical-align: middle; background-color: transparent; padding: 1px 2px; width: ${cellWidth}px; max-width: ${cellMaxWidth}px; border: 1px dashed #d5d8dc;" contenteditable="true" tabindex="0" data-cell-active="false"></td>`;
              }
              tableHtml += '</tr>';
            }
            tableHtml += '</tbody></table>';

            // 创建表格组件
            const uniqueId = generateUniqueId();
            const componentHtml = `
              <div id="ef-table-comp-${uniqueId}" class="ef-component ef-table-component ef-table-comp-${uniqueId}" style="position: static">
                <span class="ef-component-labels ef-label-small label-above-line label-top" style="left: 0px">
                  <span class="ef-label-comp-type draggable">
                    <span>Table</span>
                  </span>
                </span>
                ${tableHtml}
              </div>`;

            const newComponent = $(componentHtml);

            // 设置组件位置并添加到目标位置
            newComponent.css({
              position: 'static !important',
              // 使用!important确保不被jQuery UI覆盖
              left: '',
              top: ''
            }).appendTo(dropTarget);

            // 使新添加的组件可以拖动
            makeComponentDraggable(newComponent);

            // 初始化表格交互功能
            /**
             * isSelecting: 一个标志变量，用于判断当前是否处于选择状态。
             * startCell: 选择开始时的单元格。
             * selectedCells: 存储已选择的单元格的数组。
             * startRowIndex: 选择开始时的行索引。
             * startColIndex: 选择开始时的列索引
             * 
             * isSelecting: 默认为false，当表格被点击时，将其设置为true。
             */
            let isSelecting = false;
            let startCell = null;
            let selectedCells = [];
            let startRowIndex = -1;
            let startColIndex = -1;

            // 获取单元格的行列索引
            function getCellIndex(cell) {
              const $cell = $(cell);
              const $row = $cell.parent();
              return {
                row: $row.index(),
                col: $cell.index()
              };
            }

            // 选择区域内的所有单元格
            function selectCellsInRange(startCell, endCell) {
              const start = getCellIndex(startCell);
              const end = getCellIndex(endCell);
              const minRow = Math.min(start.row, end.row);
              const maxRow = Math.max(start.row, end.row);
              const minCol = Math.min(start.col, end.col);
              const maxCol = Math.max(start.col, end.col);
              clearSelection(newComponent);
              newComponent.find('tr').each(function(rowIndex) {
                if (rowIndex >= minRow && rowIndex <= maxRow) {
                  $(this).find('td, th').each(function(colIndex) {
                    if (colIndex >= minCol && colIndex <= maxCol) {
                      $(this).css({
                        'background-color': '#007bff33',
                        'border': '1px dashed #d5d8dc'
                      });
                      
                      // 设置外边框
                      if (rowIndex === minRow) {
                        $(this).css('border-top', '1px solid #007bff');
                      }
                      if (rowIndex === maxRow) {
                        $(this).css('border-bottom', '1px solid #007bff');
                      }
                      if (colIndex === minCol) {
                        $(this).css('border-left', '1px solid #007bff');
                      }
                      if (colIndex === maxCol) {
                        $(this).css('border-right', '1px solid #007bff');
                      }
                    }
                  });
                }
              });
            }

            // 保持选中状态
            let isSelected = false;
            let selectedRange = null;

            // 鼠标按下时开始选择
            newComponent.find('td, th').on('mousedown', function(e) {
              e.preventDefault();
              isSelecting = true;
              startCell = this;
              clearSelection(newComponent);
              const indices = getCellIndex(this);
              startRowIndex = indices.row;
              startColIndex = indices.col;
              isSelected = true;
            });

            // 鼠标移动时更新选择范围
            newComponent.find('td, th').on('mousemove', function(e) {
              if (isSelecting) {
                selectCellsInRange(startCell, this);
              }
            });

            // 将事件绑定到组件级别
            newComponent.on('click', function(e) {
              const $target = $(e.target);
              if (!$target.closest('.ef-table').length) {
                clearSelection(newComponent);
                isSelected = false;
                selectedRange = null;
              }
            });

            // 鼠标松开时结束选择
            $(document).on('mouseup', function() {
              if (isSelecting) {
                isSelecting = false;
                if (selectedCells.length > 0) {
                  selectedRange = {
                    startCell: startCell,
                    endCell: selectedCells[selectedCells.length - 1]
                  };
                }
              }
            });

            // 禁用单元格的默认编辑功能
            newComponent.find('th, td').attr({
              'contenteditable': 'false',
              'key-press': 'enter',
              'key-event': 'dblclick',
              'key-scope': '.ef-table-component'
            }).css('cursor', 'default');

            // 双击进入编辑模式
            newComponent.find('th, td').on('dblclick', function() {
              clearSelection(newComponent); // 清除已有的选中效果
              $(this).attr('contenteditable', 'true')
                     .css('cursor', 'text')
                     .focus();
            });

            // 失去焦点时退出编辑模式
            newComponent.find('th, td').on('blur', function() {
              $(this).attr('contenteditable', 'false')
                     .css('cursor', 'default');
            });

            // 鼠标按下开始选择
            newComponent.find('th, td').on('mousedown', function(e) {
              if ($(this).attr('contenteditable') !== 'true') {
                isSelecting = true;
                startCell = this;
                clearSelection(newComponent);
                selectedCells = [this];
                $(this).css({
                  'background-color': '#f0f8ff',
                  'outline': '1px solid #007bff'
                });
                $(this).attr('data-cell-active', 'true');
                e.preventDefault();
              }
            });

            // 鼠标移动时选择单元格
            newComponent.find('th, td').on('mouseover', function() {
              if (isSelecting && $(this).attr('contenteditable') !== 'true') {
                selectCellsInRange(startCell, this);
              }
            });

            newComponent.hotkeyManager();

            // 鼠标松开结束选择
            $(document).on('mouseup', function() {
              isSelecting = false;
            });

            // 清除选择样式的函数
            // function clearSelection() {
            //   newComponent.find('th, td').css({
            //     'background-color': '',
            //     'outline': 'none',
            //     'border': '1px dashed #d5d8dc',  // 恢复为灰色虚线边框
            //     'border-width': '1px',
            //     'border-style': 'dashed',
            //     'border-color': '#d5d8dc'
            //   }).removeAttr('data-cell-active');
            // }

            // 隐藏模态窗口
            $('#tableModal').hide();

            // 设置section-content的背景色为白色
            dropTarget.css('background-color', 'white');
          });

          // 处理取消按钮点击事件
          $('#cancel-table-btn').off('click').on('click', function () {
            $('#tableModal').hide();
          });
        } else {
          // 非表格组件的处理逻辑
          const uniqueId = generateUniqueId();
          const html = templateConfig.template.replace(/{uniqueId}/g, uniqueId);
          const newComponent = $(html);
          
          // 检查目标区域是否已包含相同ID的组件，避免重复添加
          const componentId = `ef-${componentType}-comp-${uniqueId}`;
          if (droppableArea.find(`#${componentId}`).length === 0) {
            // 将生成的组件添加到目标位置
            droppableArea.append(newComponent);
          }

          // 使新添加的组件可以拖动
          makeComponentDraggable(newComponent);

          // 文本组件的特定初始化逻辑
          if (componentType === 'text') {
            // 获取新添加组件中的可编辑元素
            const editableElement = newComponent.find('[contenteditable]');

            // 添加点击事件，用户手动点击后才清空 placeholder
            editableElement.one('click', function () {
              if ($(this).text() === textPlaceHolder) {
                $(this).empty(); // 清空 placeholder
                $(this).focus(); // 聚焦在该元素上
              }
            });

            // 监听该组件中 h2 的 input 事件
            newComponent.find('[contenteditable]').on('input', function () {
              if ($(this).html() === '<br>') {
                $(this).html(''); // 清除多余的 <br> 标签
              }
            });
          }
        }
      }
    });
    
    // 应用CSS规则来覆盖jQuery UI自动添加的样式
    if ($('#ui-draggable-override-style').length === 0) {
      $('<style id="ui-draggable-override-style">')
        .prop('type', 'text/css')
        .html('.ui-draggable:not(.being-dragged) { position: static !important; }')
        .appendTo('head');
    }
  });

  // 封装 draggable 初始化逻辑
  function makeComponentDraggable (element) {
    // 先确保元素初始状态为static
    $(element).css('position', 'static !important');
    
    $(element).draggable({
      handle: '.ef-component-labels', // 拖拽的句柄
      containment: $canvas,
      helper: 'original', // 修正拼写错误
      cursor: 'move',
      opacity: 0.7,
      zIndex: 1000,
      start: function (event, ui) {
        $(this).addClass('being-dragged');
        $(ui.helper).addClass('dragging-placeholder');
        // 拖拽开始时临时移除!important
        $(this).css('position', '');
      },
      stop: function (event, ui) {
        $(this).removeClass('being-dragged');
        $(ui.helper).removeClass('dragging-placeholder');
        
        // 拖拽结束后，如果用户确实移动了元素，则设置为absolute
        // 否则恢复为static
        if (ui.position.left !== 0 || ui.position.top !== 0) {
          $(this).css({
            left: ui.position.left,
            top: ui.position.top,
            position: 'absolute',
          });
        } else {
          $(this).css('position', 'static !important');
        }
      }
    });
    
  }

  // 页面加载时添加全局CSS规则来覆盖jQuery UI自动添加的position: relative样式
  if ($('#ui-draggable-override-style').length === 0) {
    $('<style id="ui-draggable-override-style">')
      .prop('type', 'text/css')
      .html('.ui-draggable:not(.being-dragged) { position: static !important; }')
      .appendTo('head');
  }

  // 当新的 item-block 添加时重新初始化 droppable 区域
  function reinitializeDroppables () {
    // 销毁已经初始化的 droppable
    $('.item-block.ui-droppable').droppable('destroy');
    $('.section-content.ui-droppable').droppable('destroy');
    
    // 重新初始化
    initDraggableDroppable();
    
    // 确保所有组件都是可拖动的
    $('.ef-component').each(function() {
      if (!$(this).hasClass('ui-draggable')) {
        makeComponentDraggable($(this));
      }
    });
    
    // 初始化表格快捷键
    $('table[ef-table-hotkeys]').each(function() {
      const $table = $(this);
      const keyConfig = $table.attr('data-table-keys');
      if (keyConfig && typeof TableHotkeyManager !== 'undefined') {
        try {
          // 确保表格单元格有正确的属性用于导航
          $table.find('td').attr('tabindex', '0');
          TableHotkeyManager.initTableHotkeys($table, keyConfig);
        } catch (e) {
          console.error('初始化表格快捷键失败:', e);
        }
      }
    });
    
    // 应用CSS规则来覆盖jQuery UI自动添加的样式
    if ($('#ui-draggable-override-style').length === 0) {
      $('<style id="ui-draggable-override-style">')
        .prop('type', 'text/css')
        .html('.ui-draggable:not(.being-dragged) { position: static !important; }')
        .appendTo('head');
    }
  }

  // 初始化 draggable 和 droppable 逻辑
  function initDraggableDroppable () {
    // 设置 item-block 可以接收放置组件
    $('.item-block').droppable({
      accept: '.ef-component, .component-item',
      tolerance: 'pointer',
      hoverClass: 'droppable-hover',
      drop: function (event, ui) {
        const $this = $(this);
        
        // 处理从组件面板拖拽的新组件
        if (ui.draggable.hasClass('component-item')) {
          const componentType = ui.draggable.attr('componenttype');
          if (!componentType) return;
          
          // 根据组件类型创建新组件
          if (componentType === 'table') {
            // 显示表格行列输入模态框
            $('#tableModal').css('display', 'flex');
            
            // 保存当前拖放的位置和目标元素
            const dropTarget = $this;
            const dropOffsetX = ui.offset.left - dropTarget.offset().left;
            const dropOffsetY = ui.offset.top - dropTarget.offset().top;
            
            // 处理确定按钮点击事件
            $('#create-table-btn').off('click').on('click', function() {
              // ... 表格创建逻辑 ...
            });
          } else {
            // 非表格组件的处理逻辑
            const uniqueId = generateUniqueId();
            const templateConfig = componentTemplates[componentType];
            const html = templateConfig.template.replace(/{uniqueId}/g, uniqueId);
            const newComponent = $(html);
            
            // 计算放置位置
            const offsetX = ui.offset.left - $this.offset().left;
            const offsetY = ui.offset.top - $this.offset().top;
            
            // 设置位置并添加到目标
            newComponent.css({
              position: 'absolute',
              top: offsetY,
              left: offsetX
            }).appendTo($this);
            
            // 使新添加的组件可以拖动
            makeComponentDraggable(newComponent);
            
            // 隐藏模态框
            $ ('#tableModal').hide();
            
            // 文本组件的特定初始化逻辑
            if (componentType === 'text') {
              // 获取新添加组件中的可编辑元素
              const editableElement = newComponent.find('[contenteditable]');
              
              // 添加点击事件，用户手动点击后才清空 placeholder
              editableElement.one('click', function () {
                if ($(this).text() === textPlaceHolder) {
                  $(this).empty(); // 清空 placeholder
                  $(this).focus(); // 聚焦在该元素上
                }
              });
              
              // 监听该组件中 h2 的 input 事件
              newComponent.find('[contenteditable]').on('input', function () {
                if ($(this).html() === '<br>') {
                  $(this).html(''); // 清除多余的 <br> 标签
                }
              });
            }
          }
        } else if (ui.draggable.hasClass('ef-component')) {
          // 处理已存在组件的拖拽
          const offsetX = ui.offset.left - $this.offset().left;
          const offsetY = ui.offset.top - $this.offset().top;
          
          // 移动组件到新位置
          ui.draggable.detach().appendTo($this).css({
            position: 'absolute',
            top: offsetY,
            left: offsetX
          });
        }
      }
    });

    // 设置 section-content 可以接收放置组件
    $('.section-content').droppable({
      accept: '.ef-component, .component-item',
      tolerance: 'pointer',
      hoverClass: 'droppable-hover',
      drop: function (event, ui) {
        console.log('dropped');
      }
    });
    
    // 应用CSS规则来覆盖jQuery UI自动添加的样式
    if ($('#ui-draggable-override-style').length === 0) {
      $('<style id="ui-draggable-override-style">')
        .prop('type', 'text/css')
        .html('.ui-draggable:not(.being-dragged) { position: static !important; }')
        .appendTo('head');
    }
  }
});


let preventBlur = false;

// 当点击 ef-component-labels 时设置标志
$(document).on('mousedown', '.ef-component-labels', function (event) {
  preventBlur = true;
  event.stopPropagation();
});

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
