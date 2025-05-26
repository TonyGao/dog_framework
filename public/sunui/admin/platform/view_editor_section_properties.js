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
        const propertyName = $parent.prev('label').text().toLowerCase().replace(' ', '-');
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
        
        const alignItems = $content.css('align-items') || 'center';
        $('.justify-align-controls').last().find('.align-button').removeClass('active')
            .filter(`[data-value="${alignItems}"]`).addClass('active');
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
});