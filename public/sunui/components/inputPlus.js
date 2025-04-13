(function ($) {
  // 定义插件函数
  $.fn.capitalize = function () {
    // 定义标志变量，用于中文输入法检测
    let isComposing = false; 

    // 定义处理输入的函数，并加上防抖
    const handleInput = _.debounce(function () {
      if (isComposing) return; // 如果正在中文输入，则不处理

      let $input = $(this); // 当前输入框

      // 处理 format 属性
      if ($input.attr('format') === "data-pascal-style") {
        let value = $input.val();
        if (value.length > 0) {
          let capitalizedValue = Str.formatToPascalStyle(value);
          $input.val(capitalizedValue); // 格式化为 Pascal 风格
        }
      }

      // 处理 data-sync 属性
      if ($input.attr('data-sync')) {
        let syncConfig = {};
        try {
          syncConfig = JSON.parse($input.attr('data-sync')); // 解析 JSON
        } catch (error) {
          console.error("data-sync 属性的 JSON 格式错误:", error);
          alert.error("data-sync 属性的 JSON 格式错误:");
          return; // JSON 解析失败时退出
        }

        let value = $input.val(); // 获取当前输入框的值

        // 如果值为空，清空目标元素并退出
        if (value === '') {
          $.each(syncConfig, function (targetId) {
            let $targetInput = $("#" + targetId); // 获取目标元素
            if ($targetInput.length) {
              $targetInput.val(''); // 清空目标元素
            }
          });
          return; // 不执行回调
        }

        $.each(syncConfig, function (targetId, config) {
          let $targetInput = $("#" + targetId); // 获取目标元素
          if ($targetInput.length) {
            let currentValue = $input.val(); // 初始值为当前输入框的值

            if (config.callback) {
              $.each(config.callback, function (callbackName, callbackParams) {
                if (typeof window[callbackName] === 'function') {
                  currentValue = window[callbackName]($targetInput, currentValue, callbackParams);
                } else {
                  console.warn(`回调函数 ${callbackName} 未定义`);
                }
              });
            }

            $targetInput.val(currentValue); // 将值同步到目标元素
          }
        });
      }
    }, 500); // 设置防抖时间为 500ms

    // 监听中文输入法的开始和结束
    $(document).on('compositionstart', 'input.ef-input', function () {
      isComposing = true; // 中文输入开始
    });

    $(document).on('compositionend', 'input.ef-input', function () {
      isComposing = false; // 中文输入结束
      $(this).trigger('input'); // 手动触发 input 事件以处理内容
    });

    // 使用事件委托监听 input 事件
    $(document).on('input', 'input.ef-input', handleInput);
  };

  // 插件自动执行
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

/**
 * 调用接口进行翻译
 * @param {*} $target 
 * @param {*} value 
 * @returns 
 */
function translate($target, value) {
  let translatedValue = value; // 默认返回原值

  ajax({
    method: 'POST',
    url: '/api/admin/platform/utils/translate',
    contentType: 'application/json',
    async: false,  // 同步请求确保能及时返回结果
    data: {
      sourceText: value,
      sourceLanguage: 'zh',
      targetLanguage: 'en'
    },
    success: res => {
      translatedValue = res.data.translatedText || value;
    },
    error: (xhr, _, msg) => {
      $.alert.error(`翻译失败: ${msg}`);
    }
  });

  return translatedValue;
}

/**
 * 删除value里的所有标点符号
 * @param {} value 
 * @returns 
 */
function removePunctuation($target, value) {
  console.log(value);
  // 使用正则表达式匹配所有标点符号，并替换为空字符串
  return value.replace(/[.,\/#!$%\^&\*;:{}=\-_`~()?"'[\]\\|<>@+]/g, "");
}


