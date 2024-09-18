$(document).ready(function () {
  $("body").on("click", ".ef-switch.ef-switch-type-circle", function () {
    if ($(this).attr("aria-checked") === "true") {
      $(this).attr("aria-checked", "false");
      $(this).removeClass("ef-switch-checked");
      $(this).children("input[type='hidden']").attr('value', '0');
    } else {
      $(this).attr("aria-checked", "true")
      $(this).addClass("ef-switch-checked");
      $(this).children("input[type='hidden']").attr('value', '1');
    }
  })
})
