(function ($) {
  // 定义插件函数
  $.fn.capitalize = function () {
    // 定义处理输入的函数
    const handleInput = _.debounce(function () {
      let $input = $(this); // 当前触发事件的 input 元素
      if ($input.attr('format') === "data-pascal-style") {
        let value = $input.val();
        if (value.length > 0) {
          // 将首字母转换为大写，并更新到输入框中
          let capitalizedValue = Str.formatToPascalStyle(value);
          $input.val(capitalizedValue);
        }
      }

      if ($input.attr('data-sync')) {
        // 处理 data-sync 属性的逻辑
        let targetIds = $input.attr('data-sync'); // 假设目标 ID 用逗号分隔
        if (targetIds) {
          let target = JSON.parse(targetIds)
        }
        let value = $input.val();
        if ($input.attr('callback')) {
          value = callback($input);
        }
        target.forEach(function(item, idx) {
          let $targetInput = $("#".idx); // 获取目标元素
          if ($targetInput.length) {
            if (item.length > 0) {
              item.forEach(function(stuff, func) {
                if (func === 'callback') {
                  value = callback(value, item);
                }
              })
            }
            $targetInput.val(value); // 将值同步到目标元素
          }
        });
      }
    }, 200); // 设置防抖时间为 100 毫秒

    function callback(value, item) {
      let callbackProperty = stuff.attr('callback').split(',');
      if (callbackProperty[0] === 'addExtension') {
        let extension = callbackProperty[1];
        let value = stuff.val() + '.' + extension;
        return value;
      }
    }

    // 使用事件委托监听整个文档的 input 事件
    $(document).on('input', 'input.ef-input', handleInput);
  };

  // 插件自动执行，无需手动调用
  $(document).ready(function () {
    $.fn.capitalize(); // 自动应用插件逻辑到符合条件的元素
  });
})(jQuery);
