$().ready(function () {
  if ($("textarea").length != 0) {
    let xmark = `
      <span class="ef-textarea-clear-btn">
        <i class="fa-regular fa-circle-xmark"></i>
      </span>
    `;
    /**
     * flag 用来标记是否为中文输入完毕
     */
    let flag = true;
    $("textarea").on('compositionstart', function () {
      flag = false;
    })

    $("textarea").on('compositionend', function () {
      flag = true;
    })

    let clearable = true;

    function calcHeight(obj) {
      let fontSize = obj.css('font-size');
      let lineHeight = Math.floor(parseInt(fontSize.replace('px', '')) * 1.5);
      let outerHeight = parseInt(obj.css('padding-top').replace('px', '')) +
        parseInt(obj.css('padding-bottom').replace('px', ''));

      let content = obj.val();
      let minRows = parseInt(obj.attr('min-rows')) ? parseInt(obj.attr('min-rows')) : 2;
      let maxRows = parseInt(obj.attr('max-rows')) ? parseInt(obj.attr('max-rows')) : null;
      let numberOfLineBreaks = (content.match(/\n/g) || []).length;
      // 当没有 minRows, 并且没有 maxRows 时，不用做任何处理。
      // 当实际行数少于 minRows 时
      if (minRows != 2 && (numberOfLineBreaks + 1) < minRows) {
        numberOfLineBreaks = minRows - 1;
      }
      // 当有实际行数多于 maxRows 时
      if (maxRows != null && (numberOfLineBreaks + 1) > maxRows) {
        numberOfLineBreaks = maxRows - 1;
      }

      let newHeight = lineHeight + numberOfLineBreaks * lineHeight + outerHeight + numberOfLineBreaks;
      return newHeight;
    }

    // Initialize height
    $("textarea.resizeable").each(function () {
      $(this).height(calcHeight($(this)));
    });

    $("textarea.resizeable").on("input", function () {
      $(this).height(calcHeight($(this)));
    });

    $("textarea").on('input', _.debounce(function () {
      if ($(this).hasClass('clear-btn-disabled')) {
        return;
      }
      if (flag) {
        let isLimited = $(this).attr('show-word-limit') !== undefined && $(this).attr('show-word-limit') !== false;
        clearable = $(this).attr('clearable') == 'true' ? true : false;
        if ($(this).val().length > 0 && clearable) {
          clearable = false;
          $(this).attr('clearable', 'false');
          $(this).after(xmark);
          $(".ef-textarea-clear-btn").on("click", function () {
            if (isLimited) {
              $(this).parent().find('.ef-textarea-word-limit').html('0/' + limitString[1]);
              $(this).parent().removeClass('ef-textarea-error');
            }
            $(this).prev().val("");
            $(this).prev().attr('clearable', 'true');
            clearable = true;

            if ($(this).prev().attr('min-rows') != undefined) {
              $(this).prev().height(calcHeight($(this).prev()));
            }
            $(this).remove();
          })
        };

        if ($(this).val().length == 0 && !clearable) {
          clearable = true;
          $(this).attr('clearable', 'true');
          if ($(this).next().hasClass('ef-textarea-clear-btn')) {
            $(this).next().remove();
          }
        }
      }
    }, 100))
  }
})
