$(document).ready(function() {
  $.fn.extend({
    showDrawer: function(drawerId) {
      let $drawer = $('#'+drawerId+' .ef-drawer');;
      // let drawerWidth = $drawer.outerWidth();
      $('#'+drawerId).show();

      $drawer.stop().animate({
        right: 0
      }, 300);
    },
    hideDrawer: function(drawerId) {
      let id = drawerId;
      let drawerWidth = $('#'+id+' .ef-drawer').outerWidth();
  
      $('#'+id+' .ef-drawer').stop().animate({
        right: -drawerWidth // 向右侧隐藏
      }, 300, function() {
          $('#'+id).hide();
      });
    }
  })

  $('body').on("click", '.ef-drawer-mask', function(e) {
    let id = $(this).attr("maskid");
    $().hideDrawer(id);
  })

  $('body').on("click", '.ef-drawer-close-btn .ef-icon-hover', function() {
    let id = $(this).closest('.ef-drawer-container').attr('id');
    $().hideDrawer(id);
  })

  $('body').on("click", '.cancelDrawer', function() {
    let id = $(this).closest('.ef-drawer-container').attr('id');
    $().hideDrawer(id);
  })
})