(function ($) {
  // 当input focus时，将光标挪动到最后的字符后
  $.fn.textFocus = function (v) {
    var range,
      len,
      v = v === undefined ? 0 : parseInt(v);
    this.each(function () {
      len = this.value.length;
      v === 0 ? this.setSelectionRange(len, len) : this.setSelectionRange(v, v);
      this.focus();
    });
    return this;
  };

  // 添加自定义验证方法
  $.validator.addMethod(
    "selectRequired",
    function (value, element) {
      return value !== "";
    },
    "This field is required."
  );

  // 将自定义验证方法添加为默认规则
  $.validator.setDefaults({
    ignore: [],
    rules: {
      // 将自定义验证方法作为默认规则添加到所有 input 和 select 元素上
      "input[component='select']": {
        selectRequired: true,
      },
    },
  });
  // Form validation plugin
  $.fn.formValid = function (options) {
    // Default configuration
    const defaultConfig = {
      errorPlacement: function (error, element) {
        // Default: No error label
      },
      highlight: function (element) {
        console.log($(element));
        if ($(element).is("[component='input']")) {
          // Add aria-invalid="true" attribute and error class when input is invalid
          $(element).attr("aria-invalid", "true").addClass("error");
          // Add error class to parent span
          $(element).closest(".ef-input-wrapper").addClass("ef-input-error");
        }

        if ($(element).is("[component='select']")) {
          $(element).next(".ef-select").addClass("ef-select-error");
        }
      },
      unhighlight: function (element) {
        if ($(element).is("[component='input']")) {
          // Remove aria-invalid="true" attribute and error class when input is valid
          $(element).removeAttr("aria-invalid").removeClass("error");
          // Remove error class from parent span
          $(element)
            .closest(".ef-input-wrapper")
            .removeClass("ef-input-error")
            .removeAttr("style");
        }

        if ($(element).is("[component='select']")) {
          $(element).next(".ef-select").removeClass("ef-select-error");
        }
      },
      invalidHandler: function (event, validator) {
        // Handle invalid form
        let errors = validator.numberOfInvalids();
        if (errors) {
          validator.errorList.forEach(function (error) {
            if ($(error.element).is("[component='input']")) {
              $(error.element).attr("aria-invalid", "true").addClass("error");
              $(error.element)
                .closest(".ef-input-wrapper")
                .addClass("ef-input-error");
            }
          });
        }
      },
    };

    // Merge default configuration with user-provided options
    const settings = $.extend(true, {}, defaultConfig, options);

    // Initialize validation on the selected form(s)
    this.each(function () {
      $(this).validate(settings);
    });

    // Allow chaining
    return this;
  };

  $.fn.serializeWithId = function() {
    var formArray = this.serializeArray();
    var serializedData = {};

    $.each(formArray, function() {
        // 使用 id 作为键
        var id = $('[name="' + this.name + '"]').attr('id');
        if (id) {
            serializedData[id] = this.value;
        }
    });

    return serializedData;
};
})(jQuery);
