/**
 * Section属性面板的JavaScript功能
 */
$(document).ready(function() {
    // 属性组折叠/展开功能
    $('.property-group-header').on('click', function() {
        const $header = $(this);
        const $content = $header.next('.property-group-content');
        
        $header.toggleClass('collapsed');
        if ($header.hasClass('collapsed')) {
            $content.slideUp(300);
        } else {
            $content.slideDown(300);
        }
    });
    
    // 滑块与输入框同步
    function syncSliderAndInput(sliderId, inputId) {
        const $slider = $('#' + sliderId);
        const $input = $('#' + inputId);
        
        $slider.on('input', function() {
            $input.val($slider.val());
            updateSectionProperty(sliderId, $slider.val());
        });
        
        $input.on('input', function() {
            $slider.val($input.val());
            updateSectionProperty(sliderId, $input.val());
        });
    }
    
    // 初始化所有滑块与输入框的同步
    syncSliderAndInput('width-slider', 'width-value');
    syncSliderAndInput('min-height-slider', 'min-height-value');
    syncSliderAndInput('columns-slider', 'columns-value');
    syncSliderAndInput('rows-slider', 'rows-value');
    syncSliderAndInput('page-width-slider', 'page-width-value');
    
    // 初始化时，确保section元素能够适应section-content的宽度
    // $('.section').each(function() {
    //     const $section = $(this);
    //     const $sectionContent = $section.find('.section-content');
    //     // 确保section-content的宽度变化能够正确反映在section元素上
    //     const contentWidth = $sectionContent.width();
    //     if (contentWidth) {
    //         $section.css('min-width', contentWidth + 'px');
    //     }
    // });
    
    // 单位选择器点击事件
    $('.unit-selector').on('click', function(e) {
        e.stopPropagation();
        const $selector = $(this);
        const $span = $selector.find('span');
        const $dropdown = $selector.find('.unit-dropdown');

        // 关闭其他打开的下拉菜单
        $('.unit-selector').not($selector).removeClass('active');
        // 切换当前下拉菜单
        $selector.toggleClass('active');
    });

    // 点击单位选项时更新单位
    $('.unit-dropdown .unit-option').on('click', function(e) {
        e.stopPropagation();
        const $option = $(this);
        const $selector = $option.closest('.unit-selector');
        const $span = $selector.find('span');
        const $widthInput = $('#width-value');
        
        // 更新单位文本
        const newUnit = $option.data('unit');
        $span.text(newUnit);
        $selector.removeClass('active');
        
        // 如果是宽度单位变更，需要处理最大值限制
        if ($selector.closest('.property-item').find('#width-slider').length > 0) {
            const $slider = $('#width-slider');
            const currentValue = parseInt($widthInput.val());
            
            if (newUnit === 'px') {
                // 获取页面最大宽度
                const pageMaxWidth = parseInt($('#page-width-value').val()) || 1200;
                
                // 如果当前值超过最大宽度，则设置为最大宽度
                if (currentValue > pageMaxWidth) {
                    $widthInput.val(pageMaxWidth);
                    $slider.val(pageMaxWidth);
                }
                
                // 更新滑块最大值
                $slider.attr('max', pageMaxWidth);
            } else if (newUnit === '%') {
                // 恢复百分比的最大值
                $slider.attr('max', 100);
                
                // 如果当前值超过100%，则设置为100%
                if (currentValue > 100) {
                    $widthInput.val(100);
                    $slider.val(100);
                }
            }
            
            // 更新section属性
            updateSectionProperty('width-slider', $widthInput.val());
        }
    });

    // 点击页面其他地方时关闭所有下拉菜单
    $(document).on('click', function() {
        $('.unit-selector').removeClass('active');
    });
    
    // 链接图标点击事件（用于同步行列间距）
    $('.link-icon').on('click', function() {
        const $icon = $(this).find('i');
        const $columnGap = $('#column-gap');
        const $rowGap = $('#row-gap');
        
        $icon.toggleClass('fa-link fa-link-slash');
        
        // 如果是链接状态，则同步两个值
        if ($icon.hasClass('fa-link')) {
            $rowGap.val($columnGap.val());
            updateSectionProperty('row-gap', $columnGap.val());
        }
    });
    
    // 对齐按钮点击事件
    $('.align-button').on('click', function() {
        const $button = $(this);
        const $parent = $button.parent();
        
        // 移除同组中其他按钮的active类
        $parent.find('.align-button').removeClass('active');
        // 为当前按钮添加active类
        $button.addClass('active');
        
        // 获取对齐值并更新section属性
        const alignValue = $button.data('value');
        const $label = $parent.prev('label');
        let propertyName = $label.text().toLowerCase().replace(/\s+/g, '-');
        
        // 特殊处理一些属性名称映射
        if (propertyName === 'justify-items') {
            propertyName = 'justify-items';
        } else if (propertyName === 'align-items') {
            propertyName = 'align-items';
        }
        
        updateSectionProperty(propertyName, alignValue);
    });
    
    // 选择框变更事件
    $('.property-select').on('change', function() {
        const $select = $(this);
        const propertyName = $select.attr('id');
        const propertyValue = $select.val();
        
        updateSectionProperty(propertyName, propertyValue);
    });
    
    // 网格轮廓开关事件
    $('#grid-outline').on('change', function() {
        const isChecked = $(this).prop('checked');
        updateSectionProperty('grid-outline', isChecked);
    });
    
    // 更新Section属性的函数
    function updateSectionProperty(property, value) {
        // 获取当前激活的section元素
        const $activeSection = $('.section.active');
        
        if ($activeSection.length === 0) {
            console.warn('没有激活的Section');
            return;
        }
        
        // 根据不同属性应用不同的样式或类
        let $sectionContent;
        switch(property) {                
            case 'content-width':
                // 设置内容宽度作为DOM属性而不是内联样式
                $sectionContent = $activeSection.find('.section-content');
                $sectionContent.attr('data-content-width', value);
                
                // 根据内容宽度类型应用相应的样式
                if (value === 'full-width') {
                    $sectionContent.css('width', '100%');
                } else if (value === 'boxed') {
                    // 使用boxed选项时应用默认宽度
                    $sectionContent.css('width', '1140px');
                }
                break;
                
            case 'width-slider':
                // 设置宽度
                $sectionContent = $activeSection.find('.section-content');
                const unit = $('#width-value').closest('.input-with-unit').find('.unit-selector span').text();
                
                // 如果是px单位，确保不超过页面最大宽度
                if (unit === 'px') {
                    const pageMaxWidth = parseInt($('#page-width-value').val()) || 1200;
                    if (parseInt(value) > pageMaxWidth) {
                        value = pageMaxWidth;
                        $('#width-value').val(value);
                        $('#width-slider').val(value);
                    }
                }
                
                // 将宽度应用到section-content元素
                const widthValue = value + unit;
                $sectionContent.css('width', widthValue);
                
                // 确保section元素能够适应section-content的宽度变化
                // 使用setTimeout确保在DOM更新后获取正确的宽度
                setTimeout(function() {
                    const contentWidth = $sectionContent.outerWidth();
                    if (contentWidth) {
                        // 更新section元素的宽度，使其比section-content宽度大10px
                        $activeSection.css('width', 'auto');
                        // $activeSection.css('min-width', (contentWidth + 10) + 'px');
                        
                        // 调整canvas对齐方式
                        // 定义内部函数来调整canvas对齐
                        function adjustCanvasAlignment() {
                            const $canvas = $('.canvas');
                            const sectionContentWidth = $sectionContent.outerWidth();
                            const canvasWidth = $canvas.width();
                            
                            if (sectionContentWidth > canvasWidth) {
                                $canvas.css('align-items', 'flex-start');
                            } else {
                                $canvas.css('align-items', 'center');
                            }
                        }
                        
                        // 调用函数调整canvas对齐
                        adjustCanvasAlignment();
                    }
                }, 0);
                break;
                
            case 'min-height-slider':
                // 设置最小高度
                $activeSection.find('.section-content').css('min-height', value + 'px');
                break;
                
            case 'grid-outline':
                // 显示/隐藏网格轮廓
                if (value) {
                    $activeSection.find('.item-block').css('border', '1px dashed #d5d8dc');
                } else {
                    $activeSection.find('.item-block').css('border', 'none');
                }
                break;
                
            case 'columns-slider':
                // 设置列数
                $activeSection.find('.section-content').css('grid-template-columns', `repeat(${value}, 1fr)`);
                break;
                
            case 'rows-slider':
                // 设置行数
                $activeSection.find('.section-content').css('grid-template-rows', `repeat(${value}, 1fr)`);
                break;
                
            case 'column-gap':
                // 设置列间距
                $activeSection.find('.section-content').css('column-gap', value + 'px');
                break;
                
            case 'row-gap':
                // 设置行间距
                $activeSection.find('.section-content').css('row-gap', value + 'px');
                break;
                
            case 'auto-flow':
                // 设置自动流动方向
                $activeSection.find('.section-content').css('grid-auto-flow', value);
                break;
                
            case 'justify-items':
                // 设置水平对齐方式
                $activeSection.find('.section-content').css('justify-items', value);
                
                // 同时控制表格的margin对齐
                const $tables = $activeSection.find('.ef-table');
                $tables.each(function() {
                    const $table = $(this);
                    switch(value) {
                        case 'start':
                            $table.css({
                                'margin-left': '0',
                                'margin-right': 'auto',
                                'width': ''
                            });
                            break;
                        case 'center':
                            $table.css({
                                'margin-left': 'auto',
                                'margin-right': 'auto',
                                'width': ''
                            });
                            break;
                        case 'end':
                            $table.css({
                                'margin-left': 'auto',
                                'margin-right': '0',
                                'width': ''
                            });
                            break;
                        case 'stretch':
                            $table.css({
                                'margin-left': '0',
                                'margin-right': '0',
                                'width': '100%'
                            });
                            break;
                    }
                });
                break;
                
            case 'align-items':
                // 设置垂直对齐方式
                $activeSection.find('.section-content').css('align-items', value);
                break;
                
            case 'page-width':
                // 设置页面最大宽度
                $('#canvas').css('max-width', value + 'px');
                
                // 更新所有使用px单位的section宽度
                $('.section').each(function() {
                    const $section = $(this);
                    const $content = $section.find('.section-content');
                    const widthStyle = $content.css('width');
                    
                    // 检查是否使用px单位
                    if (widthStyle && widthStyle.endsWith('px')) {
                        const currentWidth = parseInt(widthStyle);
                        if (currentWidth > value) {
                            $content.css('width', value + 'px');
                        }
                    }
                });
                break;
        }
        
        // 保存更改到数据属性，以便后续可以序列化保存
        if (property !== 'content-width') {
            $activeSection.data(property, value);
        }
    }
    
    // 当section被激活时，更新属性面板的值
    $(document).on('click', '.section', function() {
        const $section = $(this);
        
        // 移除其他section的active类
        $('.section').removeClass('active');
        // 为当前section添加active类
        $section.addClass('active');
        
        // 显示section属性面板
        showSectionProperties($section);
    });
    
    // 显示section属性的函数
    function showSectionProperties($section) {
        // 获取section的当前属性值
        const $content = $section.find('.section-content');
        
        // 更新属性面板中的值
        // 容器布局
        let layout = 'normal';
        if ($content.css('display') === 'grid') {
            layout = 'grid';
        } else if ($content.css('display') === 'flex') {
            layout = 'flex';
        }
        $('#container-layout').val(layout);
        
        // 内容宽度 - 从DOM属性中获取
        let contentWidth = $content.attr('data-content-width') || 'custom';
        if (!$content.attr('data-content-width')) {
            // 如果没有设置属性，尝试从样式中推断
            const width = $content.css('width');
            if (width === '100%') {
                contentWidth = 'full-width';
            } else if (width === '1140px') {
                contentWidth = 'fixed-width';
            }
        }
        $('#content-width').val(contentWidth);
        
        // 宽度
        const width = $content.css('width');
        let widthValue = 100;
        let widthUnit = '%';
        
        if (width) {
            if (width.endsWith('px')) {
                widthValue = parseInt(width);
                widthUnit = 'px';
            } else if (width.endsWith('%')) {
                widthValue = parseInt(width);
                widthUnit = '%';
            }
        }
        
        // 先设置单位和最大值
        $('#width-value').closest('.input-with-unit').find('.unit-selector span').text(widthUnit);
        
        // 根据单位设置滑块最大值
        if (widthUnit === 'px') {
            const pageMaxWidth = parseInt($('#page-width-value').val()) || 1200;
            $('#width-slider').attr('max', pageMaxWidth);
        } else {
            $('#width-slider').attr('max', 100);
        }
        
        // 最后设置值
        $('#width-slider').val(widthValue);
        $('#width-value').val(widthValue);
        
        // 最小高度
        const minHeight = parseInt($content.css('min-height')) || 216;
        $('#min-height-slider').val(minHeight);
        $('#min-height-value').val(minHeight);
        
        // 网格轮廓
        const hasOutline = $content.find('.item-block').css('border') !== 'none';
        $('#grid-outline').prop('checked', hasOutline);
        
        // 列数
        const columns = $content.css('grid-template-columns')?.split(' ').length || 3;
        $('#columns-slider').val(columns);
        $('#columns-value').val(columns);
        
        // 行数
        const rows = $content.css('grid-template-rows')?.split(' ').length || 2;
        $('#rows-slider').val(rows);
        $('#rows-value').val(rows);
        
        // 间距
        const columnGap = parseInt($content.css('column-gap')) || 67;
        const rowGap = parseInt($content.css('row-gap')) || 26;
        $('#column-gap').val(columnGap);
        $('#row-gap').val(rowGap);
        
        // 自动流动
        const autoFlow = $content.css('grid-auto-flow') || 'row';
        $('#auto-flow').val(autoFlow);
        
        // 对齐方式
        const justifyItems = $content.css('justify-items') || 'start';
        $('.justify-align-controls').first().find('.align-button').removeClass('active')
            .filter(`[data-value="${justifyItems}"]`).addClass('active');
        
        // 获取真实的align-items值，不使用默认值
        const alignItems = $content.css('align-items');
        $('.justify-align-controls').last().find('.align-button').removeClass('active');
        if (alignItems && alignItems !== 'normal') {
            $('.justify-align-controls').last().find('.align-button')
                .filter(`[data-value="${alignItems}"]`).addClass('active');
        }
    }
    
    // 页面宽度变更事件
    $('#page-width-value').on('input', function() {
        const pageWidth = $(this).val();
        updateSectionProperty('page-width', pageWidth);
        
        // 如果当前选中的section宽度单位是px，更新滑块最大值
        const $activeSection = $('.section.active');
        if ($activeSection.length > 0) {
            const widthUnit = $('#width-value').closest('.input-with-unit').find('.unit-selector span').text();
            if (widthUnit === 'px') {
                $('#width-slider').attr('max', pageWidth);
                
                // 如果当前值超过新的最大值，则更新
                const currentWidth = parseInt($('#width-value').val());
                if (currentWidth > pageWidth) {
                    $('#width-value').val(pageWidth);
                    $('#width-slider').val(pageWidth);
                    updateSectionProperty('width-slider', pageWidth);
                }
            }
        }
    });
    
    // 表格属性事件处理
    
    // 边框宽度滑块事件
    $('#table-border-width').on('input', function() {
        const value = $(this).val();
        $('#table-border-width-value').val(value);
        updateTableProperty('border-width', value + 'px');
    });
    
    $('#table-border-width-value').on('input', function() {
        const value = $(this).val();
        $('#table-border-width').val(value);
        updateTableProperty('border-width', value + 'px');
    });
    
    // 边框颜色事件
    $('#table-border-color').on('change', function() {
        const value = $(this).val();
        updateTableProperty('border-color', value);
    });
    
    // 边框样式事件
    $('#table-border-style').on('change', function() {
        const value = $(this).val();
        updateTableProperty('border-style', value);
    });
    
    // 单元格内边距滑块事件
    $('#table-cell-padding').on('input', function() {
        const value = $(this).val();
        $('#table-cell-padding-value').val(value);
        updateTableProperty('cell-padding', value + 'px');
    });
    
    $('#table-cell-padding-value').on('input', function() {
        const value = $(this).val();
        $('#table-cell-padding').val(value);
        updateTableProperty('cell-padding', value + 'px');
    });
    
    // 条纹行开关事件
    $('#table-stripe-rows').on('change', function() {
        const isChecked = $(this).prop('checked');
        updateTableProperty('stripe-rows', isChecked);
    });
    
    // 悬停效果开关事件
    $('#table-hover-effect').on('change', function() {
        const isChecked = $(this).prop('checked');
        updateTableProperty('hover-effect', isChecked);
    });
    
    // 删除组件按钮事件
    $('#delete-component-btn').on('click', function() {
        $('#delete-confirm-modal').show();
    });
    
    // 确认删除事件
    $('#confirm-delete-btn').on('click', function() {
        deleteSelectedComponent();
        $('#delete-confirm-modal').hide();
    });
    
    // 取消删除事件
    $('#cancel-delete-btn, .modal-close').on('click', function() {
        $('#delete-confirm-modal').hide();
    });
    
    // 点击模态框背景关闭
    $('#delete-confirm-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // 更新表格属性的函数
    function updateTableProperty(property, value) {
        // 获取当前选中的表格组件
        const $selectedTable = $('.ef-table.selected');
        
        if ($selectedTable.length === 0) {
            console.warn('没有选中的表格组件');
            return;
        }
        
        switch(property) {
            case 'border-width':
                $selectedTable.css('border-width', value);
                $selectedTable.find('td, th').css('border-width', value);
                break;
                
            case 'border-color':
                $selectedTable.css('border-color', value);
                $selectedTable.find('td, th').css('border-color', value);
                break;
                
            case 'border-style':
                $selectedTable.css('border-style', value);
                $selectedTable.find('td, th').css('border-style', value);
                break;
                
            case 'cell-padding':
                $selectedTable.find('td, th').css('padding', value);
                break;
                
            case 'stripe-rows':
                if (value) {
                    $selectedTable.addClass('table-striped');
                    // 添加条纹样式
                    $selectedTable.find('tbody tr:nth-child(even)').css('background-color', '#f8f9fa');
                } else {
                    $selectedTable.removeClass('table-striped');
                    $selectedTable.find('tbody tr').css('background-color', '');
                }
                break;
                
            case 'hover-effect':
                if (value) {
                    $selectedTable.addClass('table-hover');
                } else {
                    $selectedTable.removeClass('table-hover');
                }
                break;
        }
    }
    
    // 删除选中组件的函数
    function deleteSelectedComponent() {
        const $selectedComponent = $('.ef-table.selected, .ef-text.selected, .ef-image.selected');
        
        if ($selectedComponent.length === 0) {
            console.warn('没有选中的组件');
            return;
        }
        
        // 移除组件
        $selectedComponent.remove();
        
        // 隐藏属性面板
        $('.properties-panel').hide();
        
        console.log('组件已删除');
    }
    
    // 检查是否选中表格组件并显示/隐藏表格属性
    function checkTableSelection() {
        const $selectedTable = $('.ef-table.selected');
        const $tableProperties = $('#table-properties');
        
        if ($selectedTable.length > 0) {
            $tableProperties.show();
            loadTableProperties($selectedTable);
        } else {
            $tableProperties.hide();
        }
    }
    
    // 加载表格属性到面板
    function loadTableProperties($table) {
        // 边框宽度
        const borderWidth = parseInt($table.css('border-width')) || 1;
        $('#table-border-width').val(borderWidth);
        $('#table-border-width-value').val(borderWidth);
        
        // 边框颜色
        const borderColor = $table.css('border-color') || '#dee2e6';
        $('#table-border-color').val(rgbToHex(borderColor));
        
        // 边框样式
        const borderStyle = $table.css('border-style') || 'solid';
        $('#table-border-style').val(borderStyle);
        
        // 单元格内边距
        const cellPadding = parseInt($table.find('td, th').first().css('padding')) || 8;
        $('#table-cell-padding').val(cellPadding);
        $('#table-cell-padding-value').val(cellPadding);
        
        // 条纹行
        const hasStripes = $table.hasClass('table-striped');
        $('#table-stripe-rows').prop('checked', hasStripes);
        
        // 悬停效果
        const hasHover = $table.hasClass('table-hover');
        $('#table-hover-effect').prop('checked', hasHover);
    }
    
    // RGB转十六进制颜色
    function rgbToHex(rgb) {
        if (rgb.startsWith('#')) return rgb;
        
        const result = rgb.match(/\d+/g);
        if (!result || result.length < 3) return '#dee2e6';
        
        return '#' + result.slice(0, 3).map(x => {
            const hex = parseInt(x).toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
    }
    
    // 监听组件选择变化
    $(document).on('click', '.ef-table', function() {
        const $this = $(this);
        setTimeout(function() {
            checkTableSelection();
            // 触发组件选择事件
            $(document).trigger('componentSelected', [$this[0]]);
        }, 100);
    });
    
    $(document).on('click', '.ef-text, .ef-image', function() {
        const $this = $(this);
        setTimeout(function() {
            $('#table-properties').hide();
            // 触发组件选择事件
            $(document).trigger('componentSelected', [$this[0]]);
        }, 100);
    });
    
    // 监听画布点击，取消组件选择
    $(document).on('click', '.canvas', function(e) {
        // 如果点击的不是组件，则取消选择
        if (!$(e.target).closest('.ef-table, .ef-text, .ef-image').length) {
            $(document).trigger('componentDeselected');
        }
    });
});