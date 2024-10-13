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

  const $adminAside = $('.admin-aside-outer'); // 获取 admin-aside-outer 容器
  const $toggleBtn = $('.system-menu-toggle-btn');   // 获取折叠/展开按钮

  // 点击事件：折叠侧边栏
  $toggleBtn.on('click', function () {
    $(".admin-aside-outer").hide();
    $(".app-side-menu").addClass('collapsed');
    $(".app-side-menu-scroll-btn").css('display', 'flex');
    document.cookie = "sideMenuState=shrunk" + "; path=/";
  });

  // 点击事件：展开侧边栏
  $(".app-side-menu-scroll-btn").on("click", function () {
    $(".admin-aside-outer").show();
    $(".app-side-menu").removeClass('collapsed');
    $(".app-side-menu-scroll-btn").hide();
    document.cookie = "sideMenuState=expanded" + "; path=/";
  })
})
