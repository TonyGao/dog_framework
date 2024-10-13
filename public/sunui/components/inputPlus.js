(function ($) {
  // 定义插件函数
  $.fn.capitalize = function () {
    // 定义处理输入的函数
    const handleInput = _.debounce(function () {
      let $input = $(this); // 当前触发事件的 input 元素

      // 处理 format 属性的逻辑
      if ($input.attr('format') === "data-pascal-style") {
        let value = $input.val();
        if (value.length > 0) {
          // 将首字母转换为大写，并更新到输入框中
          let capitalizedValue = Str.formatToPascalStyle(value);
          $input.val(capitalizedValue);
        }
      }

      // 处理 data-sync 属性的逻辑
      if ($input.attr('data-sync')) {
        // 尝试将 data-sync 属性解析为 JSON 对象
        let syncConfig = {};
        try {
          syncConfig = JSON.parse($input.attr('data-sync'));
        } catch (error) {
          console.error("data-sync 属性的 JSON 格式错误:", error);
          alert.error("data-sync 属性的 JSON 格式错误:". error)
          return; // JSON 解析失败，退出函数
        }

        // 遍历 data-sync 对象中的每个目标元素和对应的回调配置
        $.each(syncConfig, function (targetId, config) {
          let $targetInput = $("#" + targetId); // 获取目标元素
          if ($targetInput.length) {
            let value = $input.val(); // 初始值为当前输入框的值

            // 检查是否存在回调函数配置，并按顺序执行回调函数
            if (config.callback) {
              $.each(config.callback, function (callbackName, callbackParams) {
                // 检查全局是否存在对应的回调函数
                if (typeof window[callbackName] === 'function') {
                  // 执行回调函数，传递目标元素、当前输入框的值和额外参数
                  value = window[callbackName]($targetInput, value, callbackParams);
                } else {
                  console.warn(`回调函数 ${callbackName} 未定义`);
                }
              });
            }

            // 将处理后的值同步到目标元素
            $targetInput.val(value);
          }
        });
      }
    }, 200); // 设置防抖时间为 200 毫秒

    // 使用事件委托监听整个文档的 input 事件
    $(document).on('input', 'input.ef-input', handleInput);
  };

  // 插件自动执行，无需手动调用
  $(document).ready(function () {
    let alert = new Alert("body");
    $.fn.capitalize(); // 自动应用插件逻辑到符合条件的元素
  });
})(jQuery);

function addExtension($target, value, extension) {
  // 将当前值添加指定扩展名，并返回新的值
  return value + '.' + extension;
}

function addToFqn($target, value) {
  // 简单示例：将输入框的值添加到目标元素的现有值中，形成完整的 FQN（Fully Qualified Name）
  return $target.attr('value') + '\\' + value;
}

function tableize($target, value) {
  return Str.tableize(value);
}
