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
      // For textarea, line-height might be 'normal', so we fallback to a calculation or a specific value
      let cssLineHeight = obj.css('line-height');
      if (cssLineHeight && cssLineHeight !== 'normal') {
         lineHeight = parseInt(cssLineHeight.replace('px', ''));
      }
      
      // Calculate padding and border
      let paddingTop = parseInt(obj.css('padding-top').replace('px', '')) || 0;
      let paddingBottom = parseInt(obj.css('padding-bottom').replace('px', '')) || 0;
      let borderTop = parseInt(obj.css('border-top-width').replace('px', '')) || 0;
      let borderBottom = parseInt(obj.css('border-bottom-width').replace('px', '')) || 0;
      let outerHeight = paddingTop + paddingBottom + borderTop + borderBottom;

      // Use scrollHeight for content height calculation
      // Important: We must set height to 'auto' on the ACTUAL element temporarily to get the correct scrollHeight
      let currentHeight = obj.css('height');
      obj.css('height', 'auto');
      let scrollHeight = obj[0].scrollHeight;
      obj.css('height', currentHeight);

      let minRows = parseInt(obj.attr('min-rows')) ? parseInt(obj.attr('min-rows')) : 2;
      let maxRows = parseInt(obj.attr('max-rows')) ? parseInt(obj.attr('max-rows')) : null;
      
      // Calculate min height based on minRows
      // Total height = (lineHeight * rows) + padding + border
      let minTotalHeight = (lineHeight * minRows) + outerHeight;

      let newHeight = scrollHeight;
      
      if (obj.css('box-sizing') === 'border-box') {
          newHeight = newHeight + borderTop + borderBottom;
      }

      if (newHeight < minTotalHeight) {
        newHeight = minTotalHeight;
      }
      
      if (maxRows !== null) {
          let maxTotalHeight = (lineHeight * maxRows) + outerHeight;
          if (newHeight > maxTotalHeight) {
              newHeight = maxTotalHeight;
              obj.css('overflow-y', 'auto'); // Enable scroll if max height reached
          } else {
              obj.css('overflow-y', 'hidden'); // Hide scroll otherwise
          }
      } else {
          obj.css('overflow-y', 'hidden');
      }

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
