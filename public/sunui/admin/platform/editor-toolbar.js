/**
 * 视图编辑器工具栏交互功能
 * 包含工具栏按钮交互和视图保存功能
 */
$(document).ready(function() {
  // 初始化Alert组件
  let alert = new Alert($('.app-content-container'));
  
  // 获取所有工具栏按钮并添加点击事件
  $('.toolbar-btn').on('click', function() {
    // 对于需要切换状态的按钮（如粗体、斜体等）
    if (['fa-bold', 'fa-italic', 'fa-underline', 'fa-align-left', 'fa-align-center',
         'fa-align-right', 'fa-border-all'].some(cls => $(this).find('i').hasClass(cls))) {
      $(this).toggleClass('active');
    }
    
    // 在这里可以添加按钮的具体功能实现
    const buttonTitle = $(this).attr('title');
    console.log(`点击了 ${buttonTitle} 按钮`);
    
    // 示例：根据按钮类型执行不同操作
    const iconClass = $(this).find('i').attr('class');
    
    if (iconClass.includes('fa-rotate-left')) {
      // 撤销操作
      console.log('执行撤销操作');
    } else if (iconClass.includes('fa-rotate-right')) {
      // 重做操作
      console.log('执行重做操作');
    }
    // 其他按钮功能可以在这里继续实现...
  });
  
  // 处理下拉选择框变化
  $('.toolbar-select[title="字体选择"]').on('change', function() {
    console.log(`选择了字体: ${$(this).val()}`);
    // 实现字体更改逻辑
  });
  
  $('.toolbar-select[title="字号选择"]').on('change', function() {
    console.log(`选择了字号: ${$(this).val()}px`);
    // 实现字号更改逻辑
  });
  
  // 保存按钮点击事件
  $('#save-view-button').on('click', function() {
    saveView();
  });

  /**
   * 保存视图函数
   * 获取视图ID和canvas HTML内容，发送到后端API
   */
  function saveView() {
    // 显示加载状态
    showLoading();
    
    try {
      // 从URL中提取视图ID
      const url = window.location.pathname;
      const viewId = url.substring(url.lastIndexOf('/') + 1);
      
      // 获取canvas的HTML内容
      const canvasHtml = $('#canvas').html();
      
      // 发送AJAX请求到后端API
      ajax({
        url: '/api/admin/platform/view/save',
        method: 'POST',
        contentType: 'application/json',
        data: {
          viewId: viewId,
          canvasHtml: canvasHtml
        },
        success: function(response) {
          hideLoading();
          if (response.code === 200) {
            alert.success('视图保存成功', { percent: '40%', title: "保存成功", closable: true });
          } else {
            alert.error('保存失败: ' + response.message, { percent: '40%', title: "保存失败", closable: true });
          }
        },
        error: function(xhr, status, error) {
          hideLoading();
          let errorMsg = '保存视图时发生错误';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          }
          console.error('保存视图失败: ' + errorMsg);
          alert.error(errorMsg, { percent: '40%', title: "请求错误", closable: true });
        }
      });
    } catch (e) {
      hideLoading();
      alert.error('保存视图时发生错误: ' + e.message, { percent: '40%', title: "请求错误", closable: true });
      console.error('保存视图错误', e);
    }
  }

  /**
   * 显示加载状态
   */
  function showLoading() {
    // 如果页面中有加载指示器，可以在这里显示
    // 如果没有，可以创建一个简单的加载指示器
    if ($('#loading-indicator').length === 0) {
      $('body').append('<div id="loading-indicator" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;"><div style="background-color: white; padding: 20px; border-radius: 5px;">正在保存...</div></div>');
    } else {
      $('#loading-indicator').show();
    }
  }

  /**
   * 隐藏加载状态
   */
  function hideLoading() {
    $('#loading-indicator').hide();
  }
  
  $('.toolbar-btn.font-bold').on('click', function() {
    // 切换按钮状态
    $(this).toggleClass('active');
    
    // 获取当前活跃的section中的表格单元格
    const activeSection = $('#canvas .section.active');
    const activeCell = activeSection.find('td[data-cell-active="true"]');
    
    if (activeCell.length > 0) {
      // 如果单元格内容已经加粗，则移除加粗效果
      if (activeCell.css('font-weight') === '700' || activeCell.css('font-weight') === 'bold') {
        activeCell.css('font-weight', 'normal');
      } else {
        // 否则添加加粗效果
        activeCell.css('font-weight', 'bold');
      }
    }
  });
});