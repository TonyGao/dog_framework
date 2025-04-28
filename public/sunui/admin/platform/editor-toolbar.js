/**
 * 视图编辑器工具栏交互功能
 */
$(document).ready(function() {
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
});