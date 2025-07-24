$(document).ready(function () {
    // 监听全选复选框点击事件，全选或全部取消选中
    $(".check-all").on("click", function () {
        const $checkbox = $(this).find("input");
        const checkall = $checkbox.attr("value") == '0' ? true : false;

        // 获取当前表格（假设是一个表格内的复选框）
        const $table = $(this).closest("table");

        // 如果满足条件，触发点击事件选择或取消选中所有复选框
        $table.find(".ef-checkbox").not(".check-all").each(function () {
            const $currentCheckbox = $(this).find("input");
            // 强制选中或取消所有复选框，不参考原状态
            let isChecked = $currentCheckbox.attr("value") == '0' ? false : true;
            if (checkall && !isChecked) {
                $(this).trigger("click");
            }

            if (!checkall && isChecked) {
                $(this).trigger("click");
            }
        });
    });

    // 点击字段输入框，清空默认值
    $(document).on('click', '.ef-filter-editor-field', function(event) {
        // event.stopPropagation(); // 阻止事件冒泡
        
        const $field = $(this);
        if ($field.text().trim() === '字段') {
            const height = $field.height();
            $field.css('min-height', height + 'px');
            $field.empty();
        }
    });

    // 点击操作符输入框，清空默认值
    $(document).on('click', '.ef-filter-editor-operator', function(event) {
        // event.stopPropagation(); // 阻止事件冒泡
        
        const $operator = $(this);
        if ($operator.text().trim() === '操作符') {
            const height = $operator.height();
            $operator.css('min-height', height + 'px');
            $operator.empty();
        }
    });
    
    // 点击值输入框，清空默认值
    $(document).on('click', '.ef-filter-editor-value', function(event) {
        // event.stopPropagation(); // 阻止事件冒泡
        
        const $value = $(this);
        if ($value.text().trim() === '默认值') {
            const height = $value.height();
            $value.css('min-height', height + 'px');
            $value.empty();
        }
    });

    // 点击过滤器编辑器区域时，检查并设置空字段和操作符的默认值
    // 在这里清除没必要的空值、括号聚焦状态
    $(document).on('click', '.ef-filter-condition-editor', function(e) {
        // 当点击编辑器其他区域时，清除激活元素
        if (!$(e.target).is('.ef-filter-editor-field, .ef-filter-editor-operator, .ef-filter-editor-value')) {
            $activeElement = null;
        }
        
        $(this).find('.ef-filter-editor-field').each(function() {
            // console.log('this: ', $(this));
            // console.log('$activeElement:', $activeElement);
            if ($(this).is($activeElement)) return;
            if ($(this).text().trim() === '') {
                $(this).text('字段');
            }
        });
        
        $(this).find('.ef-filter-editor-operator').each(function() {
            if ($(this).is($activeElement)) return;
            if ($(this).text().trim() === '') {
                $(this).text('操作符');
            }
        });

        $(this).find('.ef-filter-editor-value').each(function() {
            if ($(this).is($activeElement)) return;
            if ($(this).text().trim() === '') {
                $(this).text('默认值');
            }
        });

        // 如果点击的元素不是括号元素，将所有括号的hover状态移除，并且如果点击的元素是括号元素，将所有其他括号的hover状态移除
        $('.ef-filter-editor-parenthesis').removeClass('hover');
        if ($(e.target).is('.ef-filter-editor-parenthesis')) {
            $(e.target).addClass('hover');
        }

    });

    // 点击搜索按钮
    $("body").on("click", ".ef-table .search", function () {
        const id = $(this).attr("parentid");
        const $modal = $('#modal' + id);
        $modal.show();
        
        // 检查过滤器编辑器是否为空，如果为空则初始化
        const $filterEditor = $modal.find('.ef-filter-condition-editor');
        if ($filterEditor.find('.ef-filter-editor-logic-div').length === 0 && 
            $filterEditor.find('.ef-filter-empty-state').length === 0) {
            initEmptyFilterEditor($filterEditor);
        }
        
        let line = new LeaderLine(
            $('.ef-filter-entity-field[data-field="department"]')[0],
            $('.ef-filter-entity-title:contains("Department")')[0],
            {
                startPlug: 'disc',      // 起点使用圆点
                endPlug: 'arrow1',        // 终点使用圆点
                color: 'rgb(22,93,255)',  // 线条颜色
                size: 2,                // 线条粗细
                path: 'fluid',            // 使用弧线路径
                startSocket: 'left',     // 从左侧开始
                endSocket: 'left',       // 到左侧结束
            }
        );

        // 添加自定义class到最后一个leader-line元素
        $('.leader-line:last').addClass('modal-leader-line');

        // 在模态窗口关闭时销毁LeaderLine实例
        $modal.on('hide', function() {
            if (line) {
                line.remove();
                line = null;
            }
        });
    });
    
    // 初始化空的过滤器编辑器
    function initEmptyFilterEditor($editor) {
        // 添加空状态提示和添加按钮
        const $emptyState = $(`
            <div class="ef-filter-empty-state" contenteditable="false">
                <div class="ef-filter-empty-text">点击下方按钮添加过滤条件</div>
                <button class="ef-filter-add-first-condition">
                    <i class="fa-solid fa-plus"></i> 添加条件
                </button>
            </div>
        `);
        $editor.append($emptyState);
    }
    
    // 点击添加第一个条件的按钮
    $(document).on('click', '.ef-filter-add-first-condition', function() {
        const $editor = $(this).closest('.ef-filter-condition-editor');
        $editor.find('.ef-filter-empty-state').remove();
        $editor.find('.ef-filter-editor-wrapper').append(createConditionBlock());
    });
    
    // 当删除最后一个条件块时，显示空状态
    $(document).on('click', '.delete-button', function (e) {
        const $editor = $(this).closest('.ef-filter-condition-editor');
        $(this).closest('.ef-filter-editor-logic-div').remove();
        
        // 如果删除后没有条件块了，显示空状态
        if ($editor.find('.ef-filter-editor-logic-div').length === 0) {
            initEmptyFilterEditor($editor);
        }
    });

    const availableFields = ["姓名", "部门", "公司", "年龄", "入职时间"];
    const availableOperators = ["=", "!=", ">", ">=", "<", "<=","LIKE", "AND", "OR", "(", ")"];

    $(".ef-filter-condition-editor").autocomplete({
        source: function (request, response) {
            const sel = window.getSelection();
            const node = sel.anchorNode;
            const inputValue = node ? node.nodeValue || "" : "";

            if (inputValue.trim().length === 0) {
                response([]); // 如果没有输入内容，则不返回任何建议
                return;
            }

            console.log("Current Input:", inputValue.trim()); // 调试输入值
            const suggestions = availableFields.concat(availableOperators);
            response($.ui.autocomplete.filter(suggestions, inputValue.trim()));
        },
        appendTo: "body", // 避免模态窗遮挡
        minLength: 1,
        open: function (event, ui) {
            const caretCoords = getCaretCoordinates();
            $(".ui-autocomplete").css({
                position: "absolute",
                top: caretCoords.top + 20, // 光标位置 + 偏移
                left: caretCoords.left,
                zIndex: 2010,
                width: "auto",
            });
        },
        focus: function (event, ui) {
            event.preventDefault();
        },
        select: function (event, ui) {
            event.preventDefault();

            const sel = window.getSelection();
            const range = sel.getRangeAt(0);
            const $target = $(range.startContainer).closest('.ef-filter-editor-field, .ef-filter-editor-operator');

            if ($target.length) {
                $target.text(ui.item.value);
                // 在选中的元素后添加空格
                const space = document.createTextNode(" ");
                $target[0].parentNode.insertBefore(space, $target[0].nextSibling);

                // 将光标移动到空格后
                range.setStartAfter(space);
                range.setEndAfter(space);
                sel.removeAllRanges();
                sel.addRange(range);
            }

            // 关闭 autocomplete 弹窗
            $(this).autocomplete("close");
        }
    });

    function getCaretCoordinates () {
        const sel = window.getSelection();
        if (sel.rangeCount > 0) {
            const range = sel.getRangeAt(0).cloneRange();
            range.collapse(true);
            const rect = range.getBoundingClientRect();
            return {
                top: rect.top + window.scrollY,
                left: rect.left + window.scrollX,
            };
        }
        return { top: 0, left: 0 }; // 默认值
    }

    $(".ef-filter-condition-editor").on("focus", function () {
        $(this).autocomplete("search", ""); // 在焦点时触发
    });

    $(".ef-filter-condition-editor").on("input", function (event) {
        const span = $(event.target).closest('span');
        if (!span.length) return;

        const inputValue = span.text().trim();

        // 根据输入内容设置类型
        if (availableFields.includes(inputValue)) {
            span
                .removeClass("placeholder")
                .addClass("ef-filter-editor-field yellow")
                .attr("type", "field");
        } else if (availableOperators.includes(inputValue)) {
            span
                .removeClass("placeholder")
                .addClass("ef-filter-editor-operator")
                .attr("type", "operator");
        } else if (inputValue !== "") {
            span
                .removeClass("placeholder")
                .addClass("ef-filter-editor-value")
                .attr("type", "value");
        }
    });

    // 使用 Tab 键选择第一个选项
    $(".ef-filter-condition-editor").on("keydown", function (event) {
        if (event.key === "Tab") {
            const autocompleteInstance = $(this).autocomplete("instance");
            const menu = autocompleteInstance.menu;
            if (menu && menu.element.is(":visible")) {
                const firstItem = menu.element.children(":first");
                if (firstItem.length) {
                    const ui = { item: menu.element.children(":first").data("ui-autocomplete-item") };
                    autocompleteInstance._trigger("select", event, ui); // 触发 select 回调
                    event.preventDefault(); // 阻止默认 Tab 行为
                }
            }
        }
    });

    // 防止在 wrapper 中直接输入
    $(".ef-filter-editor-wrapper").on("input", function (event) {
        if (event.target === this) {
            event.preventDefault();
            return false;
        }
    });

    // 记录当前激活的编辑元素
    let $activeElement = null;
    let $currentElement = null;
    $(document).on('click', '.ef-filter-editor-field, .ef-filter-editor-operator, .ef-filter-editor-value', function() {
        $activeElement = $(this);
    });

    // 点击实体字段，将内容带入到激活的字段输入框中
    $(document).on('click', '.ef-filter-entity-field', function() {
        if ($activeElement && $activeElement.hasClass('ef-filter-editor-field')) {
            const fieldText = $(this).text().trim();
            $activeElement.text(fieldText);
            $activeElement.removeClass('placeholder');
        }
    });

    // 点击操作符列表元素，将内容带入到激活的操作符输入框中
    $(document).on('click', '.ef-filter-entity-operator', function() {
        if ($activeElement && $activeElement.hasClass('ef-filter-editor-operator')) {
            const operatorText = $(this).text().trim();
            $activeElement.text(operatorText);
            $activeElement.removeClass('placeholder');
        }
    });

    $(document).on('click', '.ef-filter-editor-field, .ef-filter-editor-operator, .ef-filter-editor-value, .ef-filter-editor-parenthesis, .ef-filter-editor-logic', function() {
        $currentElement = $(this);
        console.log('$currentElement:', $currentElement);
    });
    
    // 点击左括号操作符，在激活的字段元素左侧插入左括号
    $(document).on('click', '.ef-filter-entity-operator[data-operator="left_parenthesis"]', function() {
        if ($activeElement && $activeElement.hasClass('ef-filter-editor-field')) {
            const $logicDiv = $activeElement.closest('.ef-filter-editor-logic-div');
            $logicDiv.before(createParenthesisBlock('('));
        }
    });

    // 点击括号元素时切换active状态
    $(document).on('click', '.ef-filter-editor-parenthesis', function(e) {
        e.stopPropagation();
        // 移除所有其他括号元素的active类
        $('.ef-filter-editor-parenthesis').not(this).removeClass('active');
        // 切换当前括号元素的active类
        $(this).toggleClass('active');
    });
    
    // 点击过滤器编辑器区域的其他地方时，移除所有括号元素和逻辑块的active类
    $(document).on('click', '.ef-filter-condition-editor', function(e) {
        // 如果点击的是逻辑块元素，为其添加active类，并移除其他逻辑块的active类
        if ($(e.target).closest('.ef-filter-editor-logic-div').length) {
            $('.ef-filter-editor-logic-div').removeClass('active');
            $(e.target).closest('.ef-filter-editor-logic-div').addClass('active');
        }
        // 如果点击的不是括号元素、添加按钮或删除按钮，移除所有括号元素的active类
        if (!$(e.target).is('.ef-filter-editor-parenthesis') && 
            !$(e.target).is('.add-button') && 
            !$(e.target).is('.delete-button')) {
            $('.ef-filter-editor-parenthesis').removeClass('active');
        }
        // 如果点击的是编辑器区域但不是任何逻辑块，移除所有逻辑块的active类
        if (!$(e.target).closest('.ef-filter-editor-logic-div').length) {
            $('.ef-filter-editor-logic-div').removeClass('active');
        }
    });
    
    
    // 点击右括号操作符，在激活的值元素右侧插入右括号
    $(document).on('click', '.ef-filter-entity-operator[data-operator="right_parenthesis"]', function() {
        if ($activeElement && $activeElement.hasClass('ef-filter-editor-value')) {
            const $logicDiv = $activeElement.closest('.ef-filter-editor-logic-div');
            $logicDiv.after(createParenthesisBlock(')'));
            $logicDiv.addClass('before-parenthesis ');
        }
    });
    
    // 点击AND操作符，在当前激活的逻辑块后添加AND逻辑连接符
    $(document).on('click', '.ef-filter-entity-operator[data-operator="and"]', function() {
        if ($currentElement) {
            const $logicDiv = $currentElement.closest('.ef-filter-editor-logic-div');
            if ($logicDiv.length) {
                // 创建一个新的逻辑块，使用AND作为逻辑连接符
                const $newLogicDiv = createLogicBlock();
                $newLogicDiv.find('.ef-filter-editor-logic').text('AND');
                $logicDiv.after($newLogicDiv);
            }
        }
    });
    
    // 点击OR操作符，在当前激活的逻辑块后添加OR逻辑连接符
    $(document).on('click', '.ef-filter-entity-operator[data-operator="or"]', function() {
        if ($currentElement) {
            const $logicDiv = $currentElement.closest('.ef-filter-editor-logic-div');
            if ($logicDiv.length) {
                // 创建一个新的逻辑块，使用OR作为逻辑连接符
                const $newLogicDiv = createLogicBlock();
                $newLogicDiv.find('.ef-filter-editor-logic').text('OR');
                $logicDiv.after($newLogicDiv);
            }
        }
    });

    // 监听键盘删除事件
    $(document).on('keyup', '.ef-filter-condition-editor', function(e) {
        // 检测删除键（Backspace 或 Delete）
        if ((e.keyCode === 8 || e.keyCode === 46) && $activeElement) {
            // 如果元素内容为空
            if ($activeElement.text().trim() === '') {
                // 移除自动生成的<br>标签
                $activeElement.find('br').remove();
                $activeElement.empty();
            }
        }
    });

    // 处理输入事件
    $(document).on("input", ".ef-filter-editor-wrapper span.placeholder", function (event) {
        const inputValue = $(this).text().trim();

        // 根据输入内容设置类型
        if (availableFields.includes(inputValue)) {
            $(this)
                .removeClass("placeholder")
                .addClass("ef-filter-editor-field yellow")
                .attr("type", "field")
                .attr("contenteditable", "false"); // 设置完后禁止编辑
        } else if (availableOperators.includes(inputValue)) {
            $(this)
                .removeClass("placeholder")
                .addClass("ef-filter-editor-operator")
                .attr("type", "operator")
                .attr("contenteditable", "false"); // 设置完后禁止编辑
        } else if (inputValue !== "") {
            $(this)
                .removeClass("placeholder")
                .addClass("ef-filter-editor-value")
                .attr("type", "value")
                .attr("contenteditable", "false"); // 设置完后禁止编辑
        }
    });

    // 条件块模板
    function createConditionBlock () {
        return $(`<div class="ef-filter-editor-logic-div">
    <span class="ef-filter-editor-field yellow" contenteditable="true">字段</span>
    <span class="ef-filter-editor-operator" contenteditable="true">操作符</span>
    <span class="ef-filter-editor-value" contenteditable="true">默认值</span>
    <span class="add-button">+</span>
    <span class="delete-button">×</span>
  </div>`);
    }

    // 逻辑块模板
    function createLogicBlock () {
        return $(`<div class="ef-filter-editor-logic-div">
    <span class="ef-filter-editor-logic" contenteditable="false">AND</span>
    <span class="add-button">+</span>
    <span class="delete-button">×</span>
    <span class="transition-button" contenteditable="false"><i class="fa-solid fa-shuffle"></i></span>
  </div>`);
    }
    
    // 括号块模板
    function createParenthesisBlock (type) {
        if (type === '(') {
            typeStr = 'left_parenthesis';
        } else if (type === ')') {
            typeStr = 'right_parenthesis';
        }
        return $(`<div class="ef-filter-editor-logic-div parenthesis">
    <span class="ef-filter-editor-parenthesis" contenteditable="false" type="${typeStr}">${type}</span>
    <span class="add-button">+</span>
    <span class="delete-button">×</span>
  </div>`);
    }

    // 添加按钮功能
    $(document).on('click', '.add-button', function (e) {
        e.stopPropagation();
        const $currentBlock = $(this).closest('.ef-filter-editor-logic-div');
        const isCondition = $currentBlock.find('.ef-filter-editor-field').length > 0;

        if (isCondition) {
            $currentBlock.after(createLogicBlock(), createConditionBlock());
        } else {
            $currentBlock.after(createConditionBlock());
        }
    });

    // 删除按钮功能
    $(document).on('click', '.delete-button', function (e) {
        $(this).closest('.ef-filter-editor-logic-div').remove();
    });

    // 逻辑运算符切换
    $(document).on('click', '.transition-button', function () {
        const $logicSpan = $(this).closest('.ef-filter-editor-logic-div').find('.ef-filter-editor-logic');
        $logicSpan.text($logicSpan.text() === 'AND' ? 'OR' : 'AND');
    });

    // ========== 分页功能实现 ==========
    
    // 分页配置
    const paginationConfig = {
        defaultPageSize: 20,
        pageSizeOptions: [10, 20, 50, 100]
    };
    
    // 初始化分页功能
    function initPagination($table) {
        const $paginationBar = $table.find('.ef-pagination-bar');
        if ($paginationBar.length === 0) return;
        
        // 检查是否为服务端分页
        const isServerSide = $table.data('server-side') === true || $table.data('server-side') === 'true';
        const dataUrl = $table.data('data-url');
        
        if (isServerSide && dataUrl) {
            $table.data('is-server-side', true);
            $table.data('data-url', dataUrl);
            // 服务端分页：初始加载数据
            loadServerData($table, 1, paginationConfig.defaultPageSize);
        } else {
            // 客户端分页：使用现有逻辑
            $table.data('is-server-side', false);
            
            // 获取或设置默认分页参数
            let currentPage = parseInt($paginationBar.find('.current-page').val()) || 1;
            let pageSize = parseInt($paginationBar.find('.ef-page-size').val()) || paginationConfig.defaultPageSize;
            let totalItems = parseInt($paginationBar.data('total-items')) || 0;
            let totalPages = Math.ceil(totalItems / pageSize);
            
            // 更新分页显示
            updatePaginationDisplay($paginationBar, currentPage, pageSize, totalItems, totalPages);
        }
        
        // 绑定分页事件
        bindPaginationEvents($table, $paginationBar);
    }
    
    // 更新分页显示
    function updatePaginationDisplay($paginationBar, currentPage, pageSize, totalItems, totalPages) {
        // 更新当前页输入框
        $paginationBar.find('.current-page').val(currentPage).attr('max', totalPages);
        
        // 更新总页数
        $paginationBar.find('.total-pages').text(totalPages);
        
        // 更新项目范围显示
        const startItem = totalItems > 0 ? ((currentPage - 1) * pageSize) + 1 : 0;
        const endItem = Math.min(currentPage * pageSize, totalItems);
        $paginationBar.find('.item-range').text(`${startItem}-${endItem} items`);
        
        // 更新总项目数
        $paginationBar.find('.total-items').text(`共 ${totalItems} 条`);
        
        // 更新按钮状态
        $paginationBar.find('.first-page, .prev-page').prop('disabled', currentPage <= 1);
        $paginationBar.find('.next-page, .last-page').prop('disabled', currentPage >= totalPages);
    }
    
    // 绑定分页事件
    function bindPaginationEvents($table, $paginationBar) {
        // 首页按钮
        $paginationBar.find('.first-page').off('click').on('click', function() {
            if (!$(this).prop('disabled')) {
                goToPage($table, 1);
            }
        });
        
        // 上一页按钮
        $paginationBar.find('.prev-page').off('click').on('click', function() {
            if (!$(this).prop('disabled')) {
                const currentPage = parseInt($paginationBar.find('.current-page').val());
                goToPage($table, currentPage - 1);
            }
        });
        
        // 下一页按钮
        $paginationBar.find('.next-page').off('click').on('click', function() {
            if (!$(this).prop('disabled')) {
                const currentPage = parseInt($paginationBar.find('.current-page').val());
                goToPage($table, currentPage + 1);
            }
        });
        
        // 末页按钮
        $paginationBar.find('.last-page').off('click').on('click', function() {
            if (!$(this).prop('disabled')) {
                const totalPages = parseInt($paginationBar.find('.total-pages').text());
                goToPage($table, totalPages);
            }
        });
        
        // 页码输入框回车事件
        $paginationBar.find('.current-page').off('keypress').on('keypress', function(e) {
            if (e.which === 13) { // Enter键
                const targetPage = parseInt($(this).val());
                const totalPages = parseInt($paginationBar.find('.total-pages').text());
                
                if (targetPage >= 1 && targetPage <= totalPages) {
                    goToPage($table, targetPage);
                } else {
                    // 恢复原值
                    const currentPage = $table.data('current-page') || 1;
                    $(this).val(currentPage);
                    alert('请输入有效的页码');
                }
            }
        });
        
        // 页面大小选择变化事件
        $paginationBar.find('.ef-page-size').off('change').on('change', function() {
            const newPageSize = parseInt($(this).val());
            const currentPage = parseInt($paginationBar.find('.current-page').val());
            
            // 重新计算当前页（保持当前显示的第一条记录位置）
            const currentFirstItem = ((currentPage - 1) * $table.data('page-size')) + 1;
            const newCurrentPage = Math.ceil(currentFirstItem / newPageSize);
            
            $table.data('page-size', newPageSize);
            goToPage($table, newCurrentPage);
        });
        
        // 刷新按钮
        $paginationBar.find('.refresh').off('click').on('click', function() {
            const currentPage = parseInt($paginationBar.find('.current-page').val());
            goToPage($table, currentPage, true); // 强制刷新
        });
    }
    
    // 跳转到指定页面
    function goToPage($table, targetPage, forceRefresh = false) {
        const $paginationBar = $table.find('.ef-pagination-bar');
        const pageSize = parseInt($paginationBar.find('.ef-page-size').val()) || paginationConfig.defaultPageSize;
        const currentPage = $table.data('current-page') || 1;
        
        // 如果页面没有变化且不是强制刷新，则不执行
        if (targetPage === currentPage && !forceRefresh) {
            return;
        }
        
        // 保存当前状态
        $table.data('current-page', targetPage);
        $table.data('page-size', pageSize);
        
        // 检查是否为服务端分页
        if ($table.data('is-server-side')) {
            loadServerData($table, targetPage, pageSize);
        } else {
            // 触发自定义事件，让外部处理数据加载
            $table.trigger('ef-pagination-change', {
                page: targetPage,
                pageSize: pageSize,
                offset: (targetPage - 1) * pageSize
            });
            
            // 如果没有外部处理器，使用默认的客户端分页
            if (!$table.data('has-pagination-handler')) {
                performClientSidePagination($table, targetPage, pageSize);
            }
        }
    }
    
    // 服务端数据加载
    function loadServerData($table, page, pageSize) {
        const dataUrl = $table.data('data-url');
        const $paginationBar = $table.find('.ef-pagination-bar');
        const $tbody = $table.find('tbody');
        
        // 显示加载状态
        showLoadingState($table);
        
        // 发送AJAX请求
        $.ajax({
            url: dataUrl,
            type: 'GET',
            data: {
                page: page,
                pageSize: pageSize
            },
            dataType: 'json',
            success: function(response) {
                // 清空表格内容
                $tbody.empty();
                
                // 如果有表格配置，先更新表头
                if (response.gridConfig && response.gridConfig.columns) {
                    updateTableHeader($table, response.gridConfig.columns);
                }
                
                if (response.data && response.data.length > 0) {
                    // 获取表格配置中的列信息
                    const columns = response.gridConfig && response.gridConfig.columns ? response.gridConfig.columns : null;
                    
                    // 渲染数据行
                    response.data.forEach(function(item, index) {
                        const row = columns ? buildDynamicRow(item, index + 1, columns) : buildPositionRow(item);
                        $tbody.append(row);
                    });
                } else {
                    // 显示空数据状态
                    showEmptyState($table);
                }
                
                // 更新分页显示
                const totalItems = response.totalItems || 0;
                const totalPages = Math.ceil(totalItems / pageSize);
                updatePaginationDisplay($paginationBar, page, pageSize, totalItems, totalPages);
                
                // 保存当前状态
                $table.data('current-page', page);
                $table.data('page-size', pageSize);
            },
            error: function(xhr, status, error) {
                console.error('加载数据失败:', error);
                showErrorState($table, '加载数据失败，请稍后重试');
            }
        });
    }
    
    // 构建岗位行HTML
    function buildPositionRow(position) {
        return `
            <tr>
                <td>${position.id}</td>
                <td>${position.name || ''}</td>
                <td>${position.description || ''}</td>
                <td>${position.department ? position.department.name : ''}</td>
                <td>${position.level || ''}</td>
                <td>${position.status === 1 ? '启用' : '禁用'}</td>
                <td>
                    <button type="button" class="btn secondary small" onclick="openPositionViewDrawer('${position.id}')">
                        <i class="fa-solid fa-eye"></i> 查看
                    </button>
                    <button type="button" class="btn secondary small" onclick="openPositionEditDrawer('${position.id}')">
                        <i class="fa-solid fa-edit"></i> 编辑
                    </button>
                </td>
            </tr>
        `;
    }
    
    // 构建动态行HTML
    function buildDynamicRow(rowData, rowNumber, columns) {
        let cellsHtml = '';

        // 添加行号列
        cellsHtml += `<td class="ef-table-td">
            <span class="ef-table-cell ef-table-cell-align-center">
                <span class="ef-table-td-content">${rowNumber}</span>
            </span>
        </td>`;
        
        // 添加选择框列
        cellsHtml += `<td class="ef-table-td">
            <div class="ef-checkbox">
                <input type="checkbox" id="checkbox${rowData.id}" value="${rowData.id}">
                <label for="checkbox${rowData.id}"></label>
            </div>
        </td>`;

        // 根据配置动态生成列
        columns.forEach(column => {
            if (!column.visible || column.field === 'actions') return;

            let cellContent = '';
            const value = rowData[column.field];

            switch (column.type) {
                case 'boolean':
                    const isTrue = value === true || value === 1 || value === '1';
                    const text = isTrue ? (column.trueText || '是') : (column.falseText || '否');
                    const badgeClass = isTrue ? 'ef-badge ef-badge-success' : 'ef-badge ef-badge-secondary';
                    cellContent = `<span class="${badgeClass}">${text}</span>`;
                    break;
                case 'datetime':
                    cellContent = value ? new Date(value).toLocaleString('zh-CN') : '';
                    break;
                case 'relation':
                    // 处理关联对象，如department.name
                    if (value && typeof value === 'object' && value.name) {
                        cellContent = value.name;
                    } else {
                        cellContent = value || '';
                    }
                    break;
                default:
                    cellContent = value || '';
            }

            const alignment = column.type === 'boolean' || column.field === 'id' ? 'ef-table-cell-align-center' : 'ef-table-cell-align-left';
            cellsHtml += `<td class="ef-table-td">
                <span class="ef-table-cell ${alignment}">
                    <span class="ef-table-td-content">${cellContent}</span>
                </span>
            </td>`;
        });

        // 添加操作列
        const actionsColumn = columns.find(col => col.field === 'actions');
        if (actionsColumn && actionsColumn.visible) {
            cellsHtml += `<td class="ef-table-td">
                <span class="ef-table-cell ef-table-cell-align-center">
                    <span class="ef-table-td-content">
                        <button type="button" class="btn secondary small" onclick="openPositionViewDrawer('${rowData.id}')">
                            <i class="fa-solid fa-eye"></i> 查看
                        </button>
                        <button type="button" class="btn secondary small" onclick="openPositionEditDrawer('${rowData.id}')">
                            <i class="fa-solid fa-edit"></i> 编辑
                        </button>
                    </span>
                </span>
            </td>`;
        }

        return `<tr class="ef-table-tr">${cellsHtml}</tr>`;
    }
    
    // 更新表头
    function updateTableHeader($table, columns) {
        const $thead = $table.find('thead');
        if ($thead.length === 0) return;

        const $headerRow = $thead.find('tr');
        if ($headerRow.length === 0) return;

        let headersHtml = '';
        
        // 添加行号列头
        headersHtml += `<th class="ef-table-th">
            <span class="ef-table-cell ef-table-cell-align-center">#</span>
        </th>`;
        
        // 添加选择框列头
        headersHtml += `<th class="ef-table-th">
            <div class="ef-checkbox check-all">
                <input type="checkbox" id="selectAll" value="0">
                <label for="selectAll"></label>
            </div>
        </th>`;

        // 根据配置动态生成列头
        columns.forEach(column => {
            if (!column.visible) return;

            const alignment = column.type === 'boolean' || column.field === 'id' || column.field === 'actions' ? 'ef-table-cell-align-center' : 'ef-table-cell-align-center';
            const sortable = column.sortable;
            
            let headerContent = '';
            if (sortable) {
                headerContent = `
                    <span class="ef-table-cell ${alignment} ef-table-cell-with-sorter">
                        <span class="ef-table-th-title">${column.label}</span>
                        <span class="ef-table-th-sort">
                            <div class="ef-table-th-sort-icon">
                                <i class="fa-solid fa-sort"></i>
                            </div>
                        </span>
                    </span>`;
            } else {
                headerContent = `
                    <span class="ef-table-cell ${alignment}">
                        <span class="ef-table-th-title">${column.label}</span>
                    </span>`;
            }
            
            headersHtml += `<th class="ef-table-th" data-field="${column.field}">${headerContent}</th>`;
        });

        $headerRow.html(headersHtml);
    }
    
    // 显示加载状态
    function showLoadingState($table) {
        const $tbody = $table.find('tbody');
        const colCount = $table.find('thead th').length;
        $tbody.html(`
            <tr class="ef-table-tr">
                <td class="ef-table-td" colspan="${colCount}">
                    <div class="ef-table-loading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 16px; color: #666;"></i>
                        <span style="color: #666;">正在加载数据...</span>
                    </div>
                </td>
            </tr>
        `);
    }
    
    // 显示空数据状态
    function showEmptyState($table) {
        const $tbody = $table.find('tbody');
        const colCount = $table.find('thead th').length;
        $tbody.html(`
            <tr class="ef-table-tr">
                <td class="ef-table-td" colspan="${colCount}">
                    <div class="ef-table-empty" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px;">
                        <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 16px; color: #999;"></i>
                        <span style="color: #666;">暂无数据</span>
                    </div>
                </td>
            </tr>
        `);
    }
    
    // 显示错误状态
    function showErrorState($table, message) {
        const $tbody = $table.find('tbody');
        const colCount = $table.find('thead th').length;
        $tbody.html(`
            <tr class="ef-table-tr">
                <td class="ef-table-td" colspan="${colCount}">
                    <div class="ef-table-error" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px;">
                        <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px; color: #f56565;"></i>
                        <span style="color: #f56565;">${message}</span>
                    </div>
                </td>
            </tr>
        `);
    }
    
    // 客户端分页（用于静态数据）
    function performClientSidePagination($table, currentPage, pageSize) {
        const $tbody = $table.find('tbody');
        const $allRows = $tbody.find('tr').not('.ef-table-empty-row');
        const totalItems = $allRows.length;
        const totalPages = Math.ceil(totalItems / pageSize);
        
        // 隐藏所有行
        $allRows.hide();
        
        // 显示当前页的行
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        $allRows.slice(startIndex, endIndex).show();
        
        // 更新分页显示
        const $paginationBar = $table.find('.ef-pagination-bar');
        updatePaginationDisplay($paginationBar, currentPage, pageSize, totalItems, totalPages);
        
        // 处理空状态
        if (totalItems === 0) {
            if ($tbody.find('.ef-table-empty-row').length === 0) {
                const colCount = $table.find('thead th').length;
                $tbody.append(`
                    <tr class="ef-table-empty-row">
                        <td colspan="${colCount}" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                            暂无数据
                        </td>
                    </tr>
                `);
            }
            $tbody.find('.ef-table-empty-row').show();
        } else {
            $tbody.find('.ef-table-empty-row').hide();
        }
    }
    
    // 为所有带有分页栏的表格初始化分页功能
    $(document).ready(function() {
        $('.ef-table').each(function() {
            const $table = $(this);
            if ($table.find('.ef-pagination-bar').length > 0) {
                // 设置默认数据
                const $paginationBar = $table.find('.ef-pagination-bar');
                const currentPage = parseInt($paginationBar.find('.current-page').val()) || 1;
                const pageSize = parseInt($paginationBar.find('.ef-page-size').val()) || paginationConfig.defaultPageSize;
                
                $table.data('current-page', currentPage);
                $table.data('page-size', pageSize);
                
                initPagination($table);
                
                // 检查是否为服务端分页且表格为空（没有预渲染数据）
                const isServerSide = $table.data('server-side') === true || $table.data('server-side') === 'true';
                const $tbody = $table.find('tbody');
                const hasStaticData = $tbody.find('tr').length > 0 && !$tbody.find('.ef-table-empty').length;
                const hasEmptyState = $tbody.find('.ef-table-empty').length > 0;
                
                // 只有在服务端分页且表格完全为空或只有空状态时才执行初始加载
                if (isServerSide && (!hasStaticData || hasEmptyState)) {
                    goToPage($table, currentPage);
                }
            }
        });
    });
    
    // 公共API：设置表格数据并刷新分页
    window.efDataGrid = {
        setData: function(tableId, data, totalItems) {
            const $table = $('#' + tableId);
            const $tbody = $table.find('tbody');
            const currentPage = $table.data('current-page') || 1;
            const pageSize = $table.data('page-size') || paginationConfig.defaultPageSize;
            
            // 清空现有数据
            $tbody.empty();
            
            // 添加新数据（这里需要根据实际数据结构来渲染）
            // 示例：假设data是行数据数组
            if (data && data.length > 0) {
                data.forEach(function(rowData) {
                    // 这里需要根据实际需求来构建行HTML
                    // $tbody.append(buildRowHtml(rowData));
                });
            }
            
            // 更新总数据量
            const $paginationBar = $table.find('.ef-pagination-bar');
            $paginationBar.data('total-items', totalItems || data.length);
            
            // 重新初始化分页
            initPagination($table);
        },
        
        refresh: function(tableId) {
            const $table = $('#' + tableId);
            const currentPage = $table.data('current-page') || 1;
            goToPage($table, currentPage, true);
        }
    };
    
});
