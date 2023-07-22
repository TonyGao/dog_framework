$(document).ready(function () {
  $(".ef-department-view-search").on("click", function () {
    $(this).find("input:first").focus();
  });

  var departmentVar = 'v' + Math.random();
  window[departmentVar] = '';

  /**
   * flag 用来标记是否为中文输入完毕
   */
  let flag = true;
  $(".ef-department-view-input").on('compositionstart', function () {
    flag = false;
  })

  $(".ef-department-view-input").on('compositionend', function () {
    flag = true;
  })

  $(".ef-department-view-input").on("input change", _.debounce(function () {
    if (flag) {
      let v = $(this).val();
      let departmentInput;
      let contentId;
      // 避免重复或空值执行
      if (window[departmentVar] != v && v != '') {
        let mode = $(this).attr('mode'); // 模式分为单选 single，和多选 mutiple
        departmentInput = $(this).parent();
        contentId = departmentInput.attr("contentid");

        window[departmentVar] = v;
        let payload = {
          key: $(this).val()
        }

        $.ajax({
          url: "/api/admin/org/department/searchByKey",
          method: "POST",
          dataType: "json",
          data: JSON.stringify(payload),
          success: function (response) {
            let data = response.data;
            if (Array.isArray(data) && data.length !== 0) {
              let height = departmentInput.outerHeight();
              let top = departmentInput.position().top + height + 6;
              let left = departmentInput.position().left;
              let selectionUl = departmentInput.parent().children(".ef-department-selection").find("ul");
              $("#" + contentId).css({
                "left": left,
                "top": top
              });
              $(".ef-trigger-popup.ef-trigger-position-bl").hide();
              let liHtml = '';

              selectionIds = [];
              if (mode === 'multiple') {
                let selDoms = selectionUl.find("a");
                selDoms.each(function(idx, dom) {
                  let selId = $(dom).attr("id");
                  selectionIds.push(selId);
                })
              }

              let num = 0;
              $.each(data, function (idx, item) {
                let randomId = item.id;

                // 如果已经选中了，就忽略，如果没选中过添加
                if (!selectionIds.includes(randomId.toString())) {
                  let liTemplate = `<li id = "${randomId}" class="ef-department-option"><span class="ef-department-option-content">${item.name}</span></li>`;
                  liHtml += liTemplate;
                  num += 1;
                }
              })

              if (liHtml !== "") {
                $("#" + contentId + " .ef-department-dropdown-list").html(liHtml); // 添加下拉菜单内容
                $("#" + contentId).show(); // 显示菜单
              }

              /**
               * 增加菜单选择事件
               * 基本逻辑：
               * (1) 单选部门，单击选中部门，在上方显示选中的部门（如果已有值则替换），清空input的搜索内容，清空菜单，隐藏菜单
               * (2) 多选部门，单击选中部门，在菜单中显示选中的状态（不可再单击），并不隐藏菜单，可以继续选其他待选项，在上方添加选中的部门。
               *     如果待选择的部门只有一个，那选中之后自动隐藏菜单。
               */
              $(".ef-department-option").not(".selected").on("click", function (event) {
                departmentInput.attr("chosen", "true");
                let id = $(this).attr("id");
                let content = $(this).children(".ef-department-option-content").text();
                let template = `<li class="ef-department-selection-li">
                                  <div class="ef-department-selection-li-content">
                                    <a href="link" class="ef-link" id="${id}">${content}</a>
                                    <span class="ef-department-view-suffix" style="display: none;">
                                      <span class="ef-department-view-icon">
                                        <i class="fa-regular fa-circle-xmark"></i>
                                      </span>
                                    </span>
                                  </div>
                                </li>`;

                if (mode === 'single') {
                  // 清空上边选中的部门
                  selectionUl.html(template);
                  $("#" + contentId).hide();
                }

                if (mode === 'multiple') {
                  // 在上边添加选中的部门
                  selectionUl.append(template);
                  let liHeight = selectionUl.find('li').last().outerHeight() + 6;
                  let currentTop = parseFloat($("#" + contentId).css("top"), 10);
                  let targetTop = currentTop + liHeight;
                  $("#" + contentId).css("top", targetTop);
                  $(".ef-trigger-popup-close-button").on("click", function () {
                    $("#" + contentId).hide();
                  })

                  $(this).addClass("selected"); // 选中的蓝色底色
                  $(this).off();

                  if (num === 1) {
                    $("#" + contentId).hide();
                  }
                }

                refreshSelectionHover();
                departmentInput.find("input").val("");

                // 删除选项
                $(".ef-department-view-suffix").on("click", function () {
                  $(this).parents(".ef-department-selection-li").remove();
                })
              })
            }
          }
        })
      }

      if (v === '') {
        $("#" + contentId).hide();
      }

      if (v === '') {
        window[departmentVar] = '';
      }
    }
  }, 500))

  // 隐藏选项弹窗，这是公共行为
  $(document).on("click", function (event) {
    if (!$(event.target).closest(".ef-trigger-popup-wrapper, .ef-select-view-single").length) {
      $(".ef-department-view-input").val("");
    }
  })

  function refreshSelectionHover() {
    $(".ef-department-selection-li").hover(
      function () {
        $(this).children().find(".ef-department-view-suffix").show();
      },
      function () {
        $(this).children().find(".ef-department-view-suffix").hide();
      }
    )
  }

  $(".left-tree-wrapper .arrow-icon").on("click", function (event) {
    $(this).parent().nextAll(".tree-indent, .sub-tree-content").toggle(0);

      var icon = $(this).find('i');
      icon.toggleClass(function() {
      if (icon.hasClass("fa-caret-down")) {
        icon.removeClass("fa-caret-down");
        return "fa-caret-right";
      } else {
        icon.removeClass("fa-caret-right");
        return "fa-caret-down";
      }
    })
  })

  refreshSelectionHover();

  // 退出部门弹窗
  $(".cancelDepartment").on("click", function () {

  })
})
