$(document).ready(function () {
  const $canvas = $('#canvas');
  const $addSectionButton = $('.add-section-button');

  function adjustButtonPosition () {
    const canvasOffset = $canvas.offset();
    const canvasWidth = $canvas.outerWidth();

    console.log("canvasOffset.left:", canvasOffset.left); // 输出 canvas 左侧位置
    console.log("canvasWidth:", canvasWidth);             // 输出 canvas 宽度

    // 固定按钮到屏幕底部，调整按钮相对于 .canvas 的水平位置
    $addSectionButton.css({
      right: $(window).width() - (canvasOffset.left + canvasWidth) + 20 + 'px',
    });
  }

  // 页面加载时调整按钮位置
  adjustButtonPosition();

  // 窗口大小改变时调整按钮位置
  $(window).resize(function () {
    adjustButtonPosition();
  });

  // 点击 section 时激活该 section
  function activateSection (section) {
    // 移除所有 section 的 active class
    $('.section').removeClass('active');

    // 给当前点击的 section 添加 active class
    section.addClass('active');
  }

  // 给现有的 section 添加点击事件
  $canvas.on('click', '.section', function () {
    // 激活当前点击的 section
    activateSection($(this));
  });

  // 监听contenteditable为true的元素，粘贴时仅插入纯文本
  $canvas.on('paste', '[contenteditable="true"]', function (event) {
    event.preventDefault(); // 阻止默认粘贴行为
    const text = event.originalEvent.clipboardData.getData('text/plain'); // 获取纯文本
    document.execCommand('insertText', false, text); // 插入纯文本
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
              <div class="section-content">
                  <!-- 新的 Section 内容 -->
              </div>
          </div>`;
    const $newSection = $(newSectionHtml);
    $('.canvas').append($newSection);
    activateSection($newSection);
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
    // 可以添加更多组件类型
  };

  // 生成唯一ID的函数
  function generateUniqueId () {
    return Math.random().toString(36).substr(2, 9);
  }

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
      border: '2px dashed #007bff',
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

    $(document).on('mouseup.drag', function () {
      $(document).off('mousemove.drag mouseup.drag');
      placeholder.remove(); // 移除虚框
    
      // 检查放置位置
      const droppableArea = $('.item-block:hover');
      if (droppableArea.length) {
        const uniqueId = generateUniqueId();
        const html = templateConfig.template.replace(/{uniqueId}/g, uniqueId);
        const newComponent = $(html);
    
        // 将生成的组件添加到目标位置
        droppableArea.append(newComponent);
    
        // 使新添加的组件可以拖动
        makeComponentDraggable(newComponent);
    
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
    });
  });

  // 封装 draggable 初始化逻辑
  function makeComponentDraggable(element) {
    $(element).draggable({
      handle: '.ef-component-labels', // 拖拽的句柄
      containment: 'parent', // 限制拖拽范围在父级元素内
      drag: function(event, ui) {
        $(this).css({
          left: ui.position.left,
          top: ui.position.top,
          position: 'absolute'
        });
      }
    });
  }

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
});
