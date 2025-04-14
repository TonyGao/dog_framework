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
});
