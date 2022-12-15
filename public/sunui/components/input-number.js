$(document).ready(function () {
  $("input.ef-input.number").on("input", function () {
    if (numOject.isInteger($(this))) {
      $(this).val($(this).val().replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1'));
    }

    if (!numOject.isInteger($(this))) {
      $(this).val($(this).val().replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'));
    }
  })

  $("input.ef-input.number").on("focusout", function () {
    numOject.modify($(this));
  })

  $(".ef-input-number-step-button.add").on("click", function () {
    let input = $(this).closest(".ef-input-number").find("input.ef-input.number");
    numOject.modify(input, 'add');
  })

  $(".ef-input-number-step-button.subtract").on("click", function () {
    let input = $(this).closest(".ef-input-number").find("input.ef-input.number");
    numOject.modify(input, 'subtract');
  })

  let numOject = {
    getMaxNum: function (input) {
      let maxNum = input.attr('num-valuemax');
      return Number(maxNum);
    },
    getMinNum: function (input) {
      let minNum = input.attr('num-valuemin');
      return Number(minNum);
    },
    getStepNum: function (input) {
      let step = input.attr('step');
      return Number(step);
    },
    isInteger: function (input) {
      let isInteger = input.attr('isInteger');
      if (isInteger == 'true') {
        return true;
      }

      if (isInteger == 'false') {
        return false;
      }
    },
    getPrecision: function (input) {
      let precision = input.attr('precision');
      return Number(precision);
    },
    modify: function (input, type) {
      if (input.val() != '' || type != undefined) {
        let step = numOject.getStepNum(input);
        let min = numOject.getMinNum(input);
        let max = numOject.getMaxNum(input);
        let precision = numOject.getPrecision(input);

        step = step ? step : 1;
        if (numOject.isInteger(input)) {
          step = Number(step.toFixed(0));
        }

        let num = Number(input.val());
        if (type == "add") {
          num = num + step;
        }

        if (type == 'subtract') {
          num = num - step;
        }

        if (!isNaN(max)) {
          if (num > max) {
            num = max;
          }
        }

        if (!isNaN(min)) {
          if (num < min) {
            num = min;
          }
        }

        if (!isNaN(precision)) {
          num = num.toFixed(precision);
        }

        input.val(num);
      }
    },
  }
})
