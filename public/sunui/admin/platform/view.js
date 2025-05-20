$(document).ready(function () {
  let alert = new Alert($('.app-content-container'));
  let createPayload = {
    parent: '',
    type: ''
  };
  
  // 监听视图编辑器按钮的点击事件
  $("#viewEditor").on("click", async function (event) {
    event.preventDefault();
    
    // 获取当前选中的视图节点
    const selectedNode = $(".tree-text-content.chosen");
    if (!selectedNode.length || selectedNode.attr("type") !== "view") {
      alert.error("请先选择一个视图节点", { percent: '40%', title: "操作提示", closable: true });
      return;
    }
    
    // 获取选中节点的ID
    const viewId = selectedNode.attr("id");
    
    // 生成编辑器URL
    let route = new Route();
    let uri = await route.generate("platform_view_editor", { id: viewId });
    if (uri && uri.path) {
      // 在新标签页中打开编辑器
      window.open(uri.path, '_blank');
    } else {
      alert.error("生成编辑器URL失败", { percent: '40%', title: "请求错误", closable: true });
    }
  });
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
        
        // 监听表单提交
        $(".right-content form").on("submit", function(e) {
          $(this).ajaxSubmit({
            success: function(response) {
              if (response.includes('视图管理')) {
                // 成功提交后刷新页面
                window.location.reload();
              } else {
                $(".right-content").html(response);
              }
            },
            error: function(xhr) {
              if (xhr.responseJSON && xhr.responseJSON.message) {
                alert.error(xhr.responseJSON.message, { percent: '40%', title: "创建失败", closable: true });
              } else {
                alert.error("创建文件夹失败，请检查输入", { percent: '40%', title: "创建失败", closable: true });
              }
            }
          });
          return false;
        });
      },
      error: function (xhr, status, error) {
        // 错误处理，显示错误信息
        let errorMsg = "创建目录失败";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        console.error("创建目录失败: "+ errorMsg);
        alert.error(errorMsg, { percent: '40%', title: "请求错误", closable: true });
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
        
        // 监听表单提交
        $(".right-content form").on("submit", function(e) {
          $(this).ajaxSubmit({
            success: function(response) {
              if (response.includes('视图管理')) {
                // 成功提交后刷新页面
                window.location.reload();
              } else {
                $(".right-content").html(response);
              }
            },
            error: function(xhr) {
              if (xhr.responseJSON && xhr.responseJSON.message) {
                alert.error(xhr.responseJSON.message, { percent: '40%', title: "创建失败", closable: true });
              } else {
                alert.error("创建视图失败，请检查输入", { percent: '40%', title: "创建失败", closable: true });
              }
            }
          });
          return false;
        });
      },
      error: function (xhr, status, error) {
        // 错误处理，显示错误信息
        let errorMsg = "创建视图失败";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        console.error("创建视图失败: "+ errorMsg);
        alert.error(errorMsg, { percent: '40%', title: "请求错误", closable: true });
      }
    });
  })
})