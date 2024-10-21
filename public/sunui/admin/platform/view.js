$(document).ready(function () {
  let createPayload = {
    parent: '',
    type: ''
  };
  $(".tree-text-content").on("click", function (event) {
    let thisChosen = false;
    let type = $(this).attr("type");
    if ($(this).hasClass("chosen")) {
      thisChosen = true;
    }
    $(".tree-text-content.chosen").removeClass("chosen");
    if (!thisChosen) {
      $(this).addClass("chosen");
      createPayload.parent = $(this).attr("id");
      createPayload.type = type;
    }
  })
  // 监听创建文件夹按钮的点击事件
  $("#createFolder").on("click", async function (event) {
    event.preventDefault();

    let route = new Route();
    let uri = await route.generate("platform_view_add_folder");
    ajax({
      url: uri.path,
      method: "GET",
      data: createPayload,
      async: false,
      dataType: "html",
      success: function (data) {
        $(".right-content").html(data);
      },
      error: function (xhr, status, error) {
        // 错误处理，显示错误信息
        console.error("创建目录失败:"+ xhr.responseJSON.message);
        alert.error("表单提交失败: " + xhr.responseJSON.message, { percent: '40%', title: "请求错误", closable: true });
      }
    });
  });

  $("#createView").on("click", async function (event) {
    event.preventDefault();

    let route = new Route();
    let uri = await route.generate("platform_view_add_view");
    ajax({
      url: uri.path,
      method: "GET",
      data: createPayload,
      async: false,
      dataType: "html",
      success: function (data) {
        $(".right-content").html(data);
      },
      error: function (xhr, status, error) {
        // 错误处理，显示错误信息
        console.error("创建视图失败:"+ xhr.responseJSON.message);
        alert.error("表单视图失败: " + xhr.responseJSON.message, { percent: '40%', title: "请求错误", closable: true });
      }
    });
  })
})