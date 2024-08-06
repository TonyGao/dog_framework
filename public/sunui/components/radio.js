$(document).ready(function () {
  $("body").on("click", ".ef-radio", function (event) {
    let id = $(this).attr("radioid");
    let chosenRadio = $(`label.ef-radio-checked[radioid=${id}]`);
    if (!$(this).hasClass("ef-radio-checked")) {
      chosenRadio.removeClass("ef-radio-checked");
      chosenRadio.children("span.ef-icon-hover-disabled").first().removeClass("ef-icon-hover-disabled");
      $(this).addClass("ef-radio-checked");

      let hover = $(this).children("span.ef-icon-hover").first();
      hover.addClass("ef-icon-hover-disabled");
    }

    event.stopPropagation();
  })
})
