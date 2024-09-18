$(document).ready(async function () {
  $(".hPerson-avatar-box").on("click", function () {
    $(".dropdown-menu.hidden").toggle("slow", "swing");
  });

  $(".parent-menu .scroll-item").on("click", function (event) {
    $(this).next().toggle("fast");
    var icon = $(this).find('.link-text i');
    icon.toggleClass(function() {
      if (icon.hasClass("fa-chevron-down")) {
        icon.removeClass("fa-chevron-down");
        return "fa-chevron-up";
      } else {
        icon.removeClass("fa-chevron-up");
        return "fa-chevron-down";
      }
    })
  })

  $('#toggle-header-button').on('click', async function() {
    let isShrunk = $('.app-header').hasClass('shrunk');
    document.cookie = "headerState=" + (isShrunk ? 'expanded' : 'shrunk') + "; path=/";
    $('.admin-logo').toggleClass('shrunk');
    $('.logo-image').toggleClass('shrunk');
    $('.hPerson-avatar-box').toggleClass('shrunk');
    $('.module-icon-container').toggleClass('shrunk');
    $('.hPerson-avatar').toggle();
    $('.app-header').toggleClass('shrunk');
  });
})
