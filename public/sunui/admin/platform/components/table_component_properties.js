/**
 * 表格组件属性面板
 * 负责生成表格组件的属性控制界面和相关逻辑
 */
(function() {
    
    // 生成表格属性面板HTML
    function generateTablePropertiesHTML() {
        return `
            <!-- 表格组件属性面板 -->
            <div id="table-properties" style="display: none;">
                <div class="property-group">
                    <div class="property-group-title">表格样式</div>
                    
                    <!-- 边框宽度 -->
                    <div class="property-item">
                        <label class="property-label">边框宽度</label>
                        <div class="property-control">
                            <input type="number" id="table-border-width" class="ef-input text" min="0" max="10" value="1" style="width: 80px;">
                            <span style="margin-left: 5px;">px</span>
                        </div>
                    </div>
                    
                    <!-- 边框颜色 -->
                    <div class="property-item">
                        <label class="property-label">边框颜色</label>
                        <div class="property-control">
                            <input type="color" id="table-border-color" value="#cccccc" style="width: 50px; height: 30px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                    </div>
                    
                    <!-- 边框样式 -->
                    <div class="property-item">
                        <label class="property-label">边框样式</label>
                        <div class="property-control">
                            <span class="ef-select-view-single ef-select ef-select-view ef-select-view-size-medium" style="width: 120px;" id="table-border-style-select">
                                <input class="ef-select-view-input" placeholder="选择样式" readonly>
                                <span class="ef-select-view-value ef-select-view-value-hidden">solid</span>
                                <span class="ef-select-view-suffix">
                                    <span class="ef-select-view-icon">
                                        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-expand" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter" style="transform: rotate(-45deg);">
                                            <path d="M7 26v14c0 .552.444 1 .996 1H22m19-19V8c0-.552-.444-1-.996-1H26"></path>
                                        </svg>
                                    </span>
                                </span>
                            </span>
                            <div class="ef-select-content" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 120px;">
                                <div class="ef-select-option" data-value="solid">实线</div>
                                <div class="ef-select-option" data-value="dashed">虚线</div>
                                <div class="ef-select-option" data-value="dotted">点线</div>
                                <div class="ef-select-option" data-value="double">双线</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 单元格内边距 -->
                    <div class="property-item">
                        <label class="property-label">单元格内边距</label>
                        <div class="property-control">
                            <input type="number" id="table-cell-padding" class="ef-input text" min="0" max="50" value="8" style="width: 80px;">
                            <span style="margin-left: 5px;">px</span>
                        </div>
                    </div>
                    
                    <!-- 斑马纹 -->
                    <div class="property-item">
                        <label class="property-label">斑马纹</label>
                        <div class="property-control">
                            <button type="button" role="switch" aria-checked="false" class="ef-switch ef-switch-type-circle" id="table-stripe-rows">
                                <span class="ef-switch-handle">
                                    <span class="ef-switch-handle-icon"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- 悬停效果 -->
                    <div class="property-item">
                        <label class="property-label">悬停效果</label>
                        <div class="property-control">
                            <button type="button" role="switch" aria-checked="false" class="ef-switch ef-switch-type-circle" id="table-hover-effect">
                                <span class="ef-switch-handle">
                                    <span class="ef-switch-handle-icon"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 删除组件按钮 -->
                <div class="property-group" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                    <button class="btn red medium long" id="delete-component-btn">
                        <i class="fa-solid fa-trash-can"></i> 删除组件
                    </button>
                </div>
            </div>
        `;
    }
    
    // 初始化表格属性面板
    function initTableProperties() {
        // 将HTML插入到组件面板中
        const $componentPanel = $('.component-panel .panel-body');
        if ($componentPanel.length > 0) {
            // 检查是否已经存在表格属性面板
            if ($('#table-properties').length === 0) {
                $componentPanel.append(generateTablePropertiesHTML());
            }
        }
    }
    
    // 显示表格属性面板
    function showTableProperties($table) {
        // 确保组件面板是可见的
        $('.component-panel').show();
        
        // 切换到组件标签页
        const $componentTab = $('.tabs-nav li').eq(1); // 组件标签页是第二个
        if ($componentTab.length > 0) {
            // 移除其他标签的active状态
            $('.tabs-nav li').removeClass('tabs-active');
            $('.panel').hide();
            
            // 激活组件标签页
            $componentTab.addClass('tabs-active');
            $('.component-panel').show();
        }
        
        $('#table-properties').show();
        loadTableProperties($table);
    }
    
    // 隐藏表格属性面板
    function hideTableProperties() {
        $('#table-properties').hide();
    }
    
    // 加载表格属性值
    function loadTableProperties($table) {
        // 边框宽度
        const borderWidth = parseInt($table.css('border-width')) || 1;
        $('#table-border-width').val(borderWidth);
        
        // 边框颜色
        const borderColor = rgbToHex($table.css('border-color')) || '#cccccc';
        $('#table-border-color').val(borderColor);
        
        // 边框样式
        const borderStyle = $table.css('border-style') || 'solid';
        updateSelectValue('#table-border-style-select', borderStyle);
        
        // 单元格内边距
        const cellPadding = parseInt($table.find('td, th').first().css('padding')) || 8;
        $('#table-cell-padding').val(cellPadding);
        
        // 斑马纹（检查是否有nth-child样式）
        const hasStripes = $table.hasClass('table-striped') || $table.find('tr:nth-child(even)').css('background-color') !== 'rgba(0, 0, 0, 0)';
        updateSwitchState('#table-stripe-rows', hasStripes);
        
        // 悬停效果
        const hasHover = $table.hasClass('table-hover');
        updateSwitchState('#table-hover-effect', hasHover);
    }
    
    // 初始化表格属性控件事件
    function initTablePropertyEvents() {
        // 边框宽度
        $(document).on('input', '#table-border-width', function() {
            const selectedComponent = window.ComponentProperties?.getSelectedComponent();
            if (selectedComponent) {
                updateTableProperty('border-width', $(this).val() + 'px');
            }
        });
        
        // 边框颜色
        $(document).on('change', '#table-border-color', function() {
            const selectedComponent = window.ComponentProperties?.getSelectedComponent();
            if (selectedComponent) {
                updateTableProperty('border-color', $(this).val());
            }
        });
        
        // 边框样式选择器
        initBorderStyleSelect();
        
        // 单元格内边距
        $(document).on('input', '#table-cell-padding', function() {
            const selectedComponent = window.ComponentProperties?.getSelectedComponent();
            if (selectedComponent) {
                updateTableCellPadding($(this).val() + 'px');
            }
        });
        
        // 斑马纹开关
        $(document).on('click', '#table-stripe-rows', function() {
            const selectedComponent = window.ComponentProperties?.getSelectedComponent();
            if (selectedComponent) {
                const isEnabled = $(this).attr('aria-checked') === 'true';
                toggleTableStripes(!isEnabled);
                updateSwitchState('#table-stripe-rows', !isEnabled);
            }
        });
        
        // 悬停效果开关
        $(document).on('click', '#table-hover-effect', function() {
            const selectedComponent = window.ComponentProperties?.getSelectedComponent();
            if (selectedComponent) {
                const isEnabled = $(this).attr('aria-checked') === 'true';
                toggleTableHover(!isEnabled);
                updateSwitchState('#table-hover-effect', !isEnabled);
            }
        });
        
        // 删除组件按钮
        $(document).on('click', '#delete-component-btn', function() {
            if (window.ComponentProperties?.getSelectedComponent()) {
                showDeleteModal();
            }
        });
    }
    
    // 初始化边框样式选择器
    function initBorderStyleSelect() {
        $(document).on('click', '#table-border-style-select', function(e) {
            e.stopPropagation();
            $(this).next('.ef-select-content').toggle();
        });
        
        $(document).on('click', '#table-border-style-select + .ef-select-content .ef-select-option', function(e) {
            e.stopPropagation();
            const value = $(this).data('value');
            const text = $(this).text();
            
            // 更新选择器显示
            const $select = $('#table-border-style-select');
            $select.find('.ef-select-view-input').val(text);
            $select.find('.ef-select-view-value').text(value);
            
            // 隐藏选项列表
            $(this).parent().hide();
            
            // 更新表格样式
            const selectedComponent = window.ComponentProperties?.getSelectedComponent();
            if (selectedComponent) {
                updateTableProperty('border-style', value);
            }
        });
        
        // 点击其他地方隐藏选项
        $(document).on('click', function() {
            $('.ef-select-content').hide();
        });
    }
    
    // 更新选择器值
    function updateSelectValue(selector, value) {
        const $select = $(selector);
        const $option = $select.next('.ef-select-content').find(`[data-value="${value}"]`);
        if ($option.length > 0) {
            $select.find('.ef-select-view-input').val($option.text());
            $select.find('.ef-select-view-value').text(value);
        }
    }
    
    // 更新开关状态
    function updateSwitchState(selector, enabled) {
        const $switch = $(selector);
        $switch.attr('aria-checked', enabled ? 'true' : 'false');
        if (enabled) {
            $switch.addClass('ef-switch-checked');
        } else {
            $switch.removeClass('ef-switch-checked');
        }
    }
    
    // 更新表格属性
    function updateTableProperty(property, value) {
        const selectedComponent = window.ComponentProperties?.getSelectedComponent();
        if (selectedComponent) {
            const $table = $(selectedComponent).is('table') ? $(selectedComponent) : $(selectedComponent).find('table').first();
            $table.css(property, value);
        }
    }
    
    // 更新单元格内边距
    function updateTableCellPadding(padding) {
        const selectedComponent = window.ComponentProperties?.getSelectedComponent();
        if (selectedComponent) {
            const $table = $(selectedComponent).is('table') ? $(selectedComponent) : $(selectedComponent).find('table').first();
            $table.find('td, th').css('padding', padding);
        }
    }
    
    // 切换斑马纹
    function toggleTableStripes(enabled) {
        const selectedComponent = window.ComponentProperties?.getSelectedComponent();
        if (selectedComponent) {
            const $table = $(selectedComponent).is('table') ? $(selectedComponent) : $(selectedComponent).find('table').first();
            
            if (enabled) {
                $table.addClass('table-striped');
                // 添加CSS规则
                const styleId = 'table-stripe-style';
                if ($('#' + styleId).length === 0) {
                    $('<style id="' + styleId + '">.table-striped tr:nth-child(even) { background-color: #f9f9f9; }</style>').appendTo('head');
                }
            } else {
                $table.removeClass('table-striped');
                $table.find('tr:nth-child(even)').css('background-color', '');
            }
        }
    }
    
    // 切换悬停效果
    function toggleTableHover(enabled) {
        const selectedComponent = window.ComponentProperties?.getSelectedComponent();
        if (selectedComponent) {
            const $table = $(selectedComponent).is('table') ? $(selectedComponent) : $(selectedComponent).find('table').first();
            
            if (enabled) {
                $table.addClass('table-hover');
                // 添加CSS规则
                const styleId = 'table-hover-style';
                if ($('#' + styleId).length === 0) {
                    $('<style id="' + styleId + '">.table-hover tr:hover { background-color: #f5f5f5; }</style>').appendTo('head');
                }
            } else {
                $table.removeClass('table-hover');
            }
        }
    }
    
    // 显示删除确认模态框
    function showDeleteModal() {
        $('#delete-component-modal').show();
    }
    
    // RGB转十六进制
    function rgbToHex(rgb) {
        if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') {
            return '#cccccc';
        }
        
        const result = rgb.match(/\d+/g);
        if (result && result.length >= 3) {
            return '#' + ((1 << 24) + (parseInt(result[0]) << 16) + (parseInt(result[1]) << 8) + parseInt(result[2])).toString(16).slice(1);
        }
        return '#cccccc';
    }
    
    // 导出表格组件属性接口
    window.TableComponentProperties = {
        init: initTableProperties,
        show: showTableProperties,
        hide: hideTableProperties,
        initEvents: initTablePropertyEvents
    };
    
    // 页面加载完成后初始化
    $(document).ready(function() {
        initTableProperties();
        initTablePropertyEvents();
    });
    
})();