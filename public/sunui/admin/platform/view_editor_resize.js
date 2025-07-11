/**
 * 视图编辑器左侧菜单拖拽调整宽度功能
 */

$(document).ready(function() {
    let isResizing = false;
    let startX = 0;
    let startWidth = 0;
    const minWidth = 200;
    const maxWidth = 500;
    
    const $adminAside = $('.admin-aside-outer');
    const $resizeHandle = $('.resize-handle');
    const $sideMenuTwo = $('.side-menu-two');
    
    // 鼠标按下开始拖拽
    $resizeHandle.on('mousedown', function(e) {
        isResizing = true;
        startX = e.clientX;
        startWidth = $adminAside.width();
        
        // 添加拖拽状态类
        $adminAside.addClass('resizing');
        $resizeHandle.addClass('dragging');
        
        // 禁用文本选择
        $('body').addClass('no-select');
        
        // 阻止默认行为
        e.preventDefault();
    });
    
    // 鼠标移动时调整宽度
    $(document).on('mousemove', function(e) {
        if (!isResizing) return;
        
        const deltaX = e.clientX - startX;
        let newWidth = startWidth + deltaX;
        
        // 限制最小和最大宽度
        newWidth = Math.max(minWidth, Math.min(maxWidth, newWidth));
        
        // 应用新宽度
        $adminAside.css('width', newWidth + 'px');
        $sideMenuTwo.css('width', newWidth + 'px');
        
        // 保存宽度到本地存储
        localStorage.setItem('sideMenuWidth', newWidth);
    });
    
    // 鼠标松开结束拖拽
    $(document).on('mouseup', function() {
        if (isResizing) {
            isResizing = false;
            
            // 移除拖拽状态类
            $adminAside.removeClass('resizing');
            $resizeHandle.removeClass('dragging');
            
            // 恢复文本选择
            $('body').removeClass('no-select');
        }
    });
    
    // 页面加载时恢复保存的宽度
    const savedWidth = localStorage.getItem('sideMenuWidth');
    if (savedWidth) {
        const width = parseInt(savedWidth);
        if (width >= minWidth && width <= maxWidth) {
            $adminAside.css('width', width + 'px');
            $sideMenuTwo.css('width', width + 'px');
        }
    }
    
    // 添加CSS样式来禁用文本选择
    if (!$('#resize-styles').length) {
        $('<style id="resize-styles">').
            text(`
                .no-select {
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                }
                .no-select * {
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                }
            `)
            .appendTo('head');
    }
});