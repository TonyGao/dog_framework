$(document).ready(function () {
  $("body").on("click", "label.ef-checkbox", function (event) {
    // let chosenCheckbox = $(this).parent().children("label.ef-checkbox-checked");
    let checkboxHover = $(this).children("span.ef-checkbox-icon-hover").first();
    let notChecked = !$(this).hasClass("ef-checkbox-checked");
    if (notChecked) {
      $(this).toggleClass("ef-checkbox-checked");
      checkboxHover.toggleClass("ef-icon-hover-disabled");

      let checkboxIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 1024 1024" width="200" height="200" fill="currentColor" class="ef-checkbox-icon-check"><path d="M877.44815445 206.10060629a64.72691371 64.72691371 0 0 0-95.14856334 4.01306852L380.73381888 685.46812814 235.22771741 533.48933518a64.72691371 64.72691371 0 0 0-92.43003222-1.03563036l-45.82665557 45.82665443a64.72691371 64.72691371 0 0 0-0.90617629 90.61767965l239.61903446 250.10479331a64.72691371 64.72691371 0 0 0 71.19960405 15.14609778 64.33855261 64.33855261 0 0 0 35.08198741-21.23042702l36.24707186-42.71976334 40.5190474-40.77795556-3.36579926-3.49525333 411.40426297-486.74638962a64.72691371 64.72691371 0 0 0-3.88361443-87.64024149l-45.3088404-45.43829334z" p-id="840"></path></svg>';
      checkboxHover.children(".ef-checkbox-icon").html(checkboxIcon);
    } else {
      $(this).removeClass("ef-checkbox-checked");
      checkboxHover.removeClass("ef-icon-hover-disabled");
      checkboxHover.children(".ef-checkbox-icon").html("");
    }

    event.stopPropagation();
    event.preventDefault();
  })
})
