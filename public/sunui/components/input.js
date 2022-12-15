$(document).ready(function () {
  let xmark = `
    <span class="ef-icon-hover ef-input-icon-hover ef-input-clear-btn">
      <i class="fa-regular fa-circle-xmark"></i>
    </span>
  `;
  /**
   * flag 用来标记是否为中文输入完毕
   */
  let flag = true;
  $("input.ef-input.text").on('compositionstart', function () {
    flag = false;
  })

  $("input.ef-input.text").on('compositionend', function () {
    flag = true;
  })
  let clearable = true;
  $("input.ef-input.text").on('input', _.debounce(function () {
    if (flag) {
      // 判断是否需要显示限制字数长度
      let isLimited = $(this).attr('show-word-limit') !== undefined && $(this).attr('show-word-limit') !== false;
      let limitString;
      let maxLength;
      let limitNum;
      let textLength = $(this).val().length;
      if (isLimited) {
        limitString = $(this).parent().find('.ef-input-word-limit').html().split('/');
        $(this).parent().find('.ef-input-word-limit').html(textLength + '/' + limitString[1]);
      }

      // 如果 input 包含 max-length="{length:10,errorOnly:true}"
      // 将 length 更新到 .ef-input-word-limit 上
      if ($(this).attr('max-length')) {
        eval('maxLength=' + $(this).attr('max-length'));
        maxLength.length ? limitNum = maxLength.length : limitNum = null;
        if (maxLength.errorOnly && limitNum) {
          $(this).parent().find('.ef-input-word-limit').html(textLength + '/' + limitNum);
          // 如果输入的字符长度超过了限制长度，向父级span添加ef-input-error类
          let hasError = $(this).parent().hasClass('ef-input-error');
          if (textLength > limitNum) {
            if (!hasError) {
              $(this).parent().addClass('ef-input-error');
            }
          } else if (hasError) {
            $(this).parent().removeClass('ef-input-error');
          }
        }
      }

      clearable = $(this).attr('clearable') == 'true' ? true : false;
      if ($(this).val().length > 0 && clearable) {
        clearable = false;
        $(this).attr('clearable', 'false');
        $(this).after(xmark);
        $(".ef-input-clear-btn").on("click", function () {
          if (isLimited) {
            $(this).parent().find('.ef-input-word-limit').html('0/' + limitString[1]);
            $(this).parent().removeClass('ef-input-error');
          }
          $(this).prev().val("");
          $(this).prev().attr('clearable', 'true');
          clearable = true;
          $(this).remove();
        })
      };

      if ($(this).val().length == 0 && !clearable) {
        clearable = true;
        $(this).attr('clearable', 'true');
        $(this).next().remove();
      }
    }
  }, 100))
})
