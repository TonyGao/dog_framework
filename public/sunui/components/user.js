$(document).ready(function () {
  $("body").on("click", ".ef-user-view-search", function () {
    $(this).find("input:first").focus();
  });

  var userVar = 'v' + Math.random();
  window[userVar] = '';

  /**
   * flag 用来标记是否为中文输入完毕
   */
  let flag = true;
  $("body").on('compositionstart', ".ef-user-view-input", function () {
    flag = false;
  })

  $("body").on('compositionend', ".ef-user-view-input", function () {
    flag = true;
  })

  $("body").on("input change", ".ef-user-view-input", _.debounce(function () {
    if (flag) {
      let v = $(this).val();
      let userInput;
      let contentId;
      // 避免重复或空值执行
      if (window[userVar] != v && v != '') {
        let mode = $(this).attr('mode'); // 模式分为单选 single，和多选 mutiple
        userInput = $(this).parents('.ef-user-view-single.ef-user');
        contentId = userInput.attr("contentid");

        window[userVar] = v;
        let payload = {
          key: $(this).val()
        }

        $.ajax({
          url: "/api/admin/org/user/searchByKey",
          method: "POST",
          dataType: "json",
          data: JSON.stringify(payload),
          success: function (response) {
            let data = response.data;
            if (Array.isArray(data) && data.length !== 0) {
              let height = userInput.outerHeight();
              let top = userInput.position().top + height + 6;
              let left = userInput.position().left;
              let selectionUl = userInput.children().find(".ef-user-selection-span ul");
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
                  let liTemplate = `<li id = "${randomId}" class="ef-user-option"><span class="ef-user-option-content">${item.displayName}</span></li>`;
                  liHtml += liTemplate;
                  num += 1;
                }
              })

              if (liHtml !== "") {
                $("#" + contentId + " .ef-user-dropdown-list").html(liHtml); // 添加下拉菜单内容
                $("#" + contentId).show(); // 显示菜单
              }

              /**
               * 增加菜单选择事件
               * 基本逻辑：
               * (1) 单选部门，单击选中部门，在上方显示选中的部门（如果已有值则替换），清空input的搜索内容，清空菜单，隐藏菜单
               * (2) 多选部门，单击选中部门，在菜单中显示选中的状态（不可再单击），并不隐藏菜单，可以继续选其他待选项，在上方添加选中的部门。
               *     如果待选择的部门只有一个，那选中之后自动隐藏菜单。
               */
              $(".ef-user-option").not(".selected").on("click", function (event) {
                userInput.attr("chosen", "true");
                let id = $(this).attr("id");
                let content = $(this).children(".ef-user-option-content").text();
                let template = `<li class="ef-user-selection-li">
                                  <div class="ef-user-selection-li-content">
                                    <a href="link" class="ef-link" id="${id}">${content}</a>
                                    <span class="ef-user-view-suffix close-chose-user" style="display: none;">
                                      <span class="ef-user-view-icon">
                                        <i class="fa-regular fa-circle-xmark"></i>
                                      </span>
                                    </span>
                                  </div>
                                </li>`;

                if (mode === 'single') {
                  // 清空上边选中的部门
                  selectionUl.html(template);
                  $("#" + contentId).hide();
                  let input = $('[contentId="'+ contentId +'"]').find('.ef-user-selection-container input');
                  input.attr("chose", "true");
                  input.val("");
                  input.hide();
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
                //userInput.find("input").val("");

                // 删除选项
                $(".close-chose-user").on("click", function () {
                  if (mode === 'single') {
                    let input = $(this).parents('.ef-user-selection-container').find('input');
                    input.attr("chose", "false");
                    input.show();
                    input.focus();
                  }
                  $(this).parents(".ef-user-selection-li").remove();
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
        window[userVar] = '';
      }
    }
  }, 200))

  //隐藏选项弹窗，隐藏上方有选定部门的输入框，这是公共行为
  $(document).on("click", function (event) {
    if ($(event.target).closest(".ef-trigger-popup-wrapper, .ef-select-view-single").length > 0 || $(event.target).children().find(".ef-user-selection-li-content").length > 0) {
      let input = $(".ef-user-view-input");
      if (input.length > 0) {
        $.each(input, function(idx, ele) {
          if ($(ele).attr("chose") == 'true') {
            $(ele).hide();
          }
        })
      }
      input.val("");
    }
  })

  // function refreshSelectionHover() {
  //   $(".ef-user-selection-li").hover(
  //     function () {
  //       $(this).children().find(".ef-user-view-suffix").show();
  //     },
  //     function () {
  //       $(this).children().find(".ef-user-view-suffix").hide();
  //     }
  //   )
  // }

  function refreshSelectionHover() {
    $("body").on("mouseenter", ".ef-user-selection-li", function() {
        $(this).find(".close-chose-user").show();
    });

    $("body").on("mouseleave", ".ef-user-selection-li", function() {
        $(this).find(".close-chose-user").hide();
    });
}


  $("body").on("click", ".user-tree-wrapper .sub-tree-content .arrow-icon", function (event) {
    // 阻止事件冒泡
    event.stopPropagation();
    // 找到最近的 item-content 容器，然后找到里面的 .tree-indent 和 .sub-tree-content
    const parentItem = $(this).closest('.item-content');
    parentItem.nextAll(".tree-indent, .sub-tree-content").toggle(0);

    let icon = $(this).find('i');
    if (icon.hasClass("fa-caret-down")) {
      icon.removeClass("fa-caret-down").addClass("fa-caret-right");
    } else {
      icon.removeClass("fa-caret-right").addClass("fa-caret-down");
    }
  })

  refreshSelectionHover();

  $("body").on("click", ".user-tree-wrapper .user-select-line", function (event) {
    $(this).children(".ef-radio").trigger("click");
  })

  $("body").on("click", ".user-tree-wrapper .ef-radio", function (event) {
    // 获取部门id值
    let line = $(this).parent();
    let content = line.children().find('.org-text-content.user[type="user"]');
    let id = content.attr("id");
    let path = content.attr("path");
    // 给确定按钮赋值选中的部门id
    let button = line.parents(".ef-modal").children().find('.confirmuser[type="button"]');
    button.attr("choseId", id);
    button.attr("path", path);
    event.stopPropagation();
  });

  // 退出部门弹窗
  $("body").on("click", ".canceluser", function () {
    let inputid = $(this).attr("inputid");
    $(".ef-modal-container[inputid='" + inputid + "']").hide();
  })

  // 显示部门弹窗
  $("body").on("click", ".show-user-modal",async function () {
    let inputid = $(this).parent().attr("id");
    let mode = $(this).parent().children().find(".ef-user-view-input").attr("mode");

    // 检查是否已经存在该 inputid 的模态弹窗容器
    let modal = $(".ef-modal-container[inputid='" + inputid + "']");
    if (modal.length > 0) {
      modal.show();
    } else {
      let modalWindow = `
    <div class="ef-modal-container" style="z-index: 1001;" inputid="${inputid}">
    <div class="ef-modal-mask"></div>
    <div class="ef-modal-wrapper ef-modal-wrapper-align-center">
      <div class="ef-modal" style="width: 784px">
        <div class="ef-modal-header">
          <div class="ef-modal-title ef-modal-title-align-left">
            ${mode == 'single'?'单部门选择':'多部门选择'}
          </div>
          <div tabindex="-1" role="button" aria-label="Close" class="ef-modal-close-btn" inputid="${inputid}">
            <span class="ef-icon-hover">
              <svg viewbox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
                <path d="M9.857 9.858 24 24m0 0 14.142 14.142M24 24 38.142 9.858M24 24 9.857 38.142"></path>
              </svg>
            </span>
          </div>
        </div>
        <div class="ef-modal-body">
          <div class="user-modal-body">
            <div class="left-tree-wrapper">
              <div class="search-user-wrapper">
                <span class="ef-input-wrapper">
                  <input class="ef-input ef-input-size-mini text" type="text" clearable="true" placeholder="请输入部门名称">
                  <span class="ef-input-suffix">
                    <i class="fa-solid fa-magnifying-glass"></i>
                  </span>
                </span>
              </div>
              <div class="user-tree-wrapper">
                
              </div>
            </div>
            <div class="right-user-wrapper">
              <div id="user">
                <div class="ef-row ef-row-align-start ef-row-justify-start ef-form-item ef-form-item-layout-horizontal">
                  <div class="ef-col ef-col-6 ef-form-item-label-col">
                    <label class="ef-form-item-label" for="user_name">部门全称</label>
                  </div>
                  <div class="ef-col ef-col-18 ef-form-item-wrapper-col">
                    <div class="ef-form-item-content-wrapper">
                      <div class="ef-form-item-content ef-form-item-content-flex">行业解决方案部三</div>
                    </div>
                  </div>
                </div>
                <div class="ef-row ef-row-align-start ef-row-justify-start ef-form-item ef-form-item-layout-horizontal">
                  <div class="ef-col ef-col-6 ef-form-item-label-col">
                    <label class="ef-form-item-label" for="user_alias">部门简称</label>
                  </div>
                  <div class="ef-col ef-col-18 ef-form-item-wrapper-col">
                    <div class="ef-form-item-content-wrapper">
                      <div class="ef-form-item-content ef-form-item-content-flex">行业解决方案部三</div>
                    </div>
                  </div>
                </div>
                <div class="ef-row ef-row-align-start ef-row-justify-start ef-form-item ef-form-item-layout-horizontal">
                  <div class="ef-col ef-col-6 ef-form-item-label-col">
                    <label class="ef-form-item-label" for="user_company">所属公司</label>
                  </div>
                  <div class="ef-col ef-col-18 ef-form-item-wrapper-col">
                    <div class="ef-form-item-content-wrapper">
                      <div class="ef-form-item-content ef-form-item-content-flex">天津港公司2</div>
                    </div>
                  </div>
                </div>
                <div class="ef-row ef-row-align-start ef-row-justify-start ef-form-item ef-form-item-layout-horizontal">
                  <div class="ef-col ef-col-6 ef-form-item-label-col">
                    <label class="ef-form-item-label" for="user_owner">部门负责人</label>
                  </div>
                  <div class="ef-col ef-col-18 ef-form-item-wrapper-col">
                    <div class="ef-form-item-content-wrapper">
                      <div class="ef-form-item-content ef-form-item-content-flex">高强</div>
                    </div>
                  </div>
                </div>
                <div class="ef-row ef-row-align-start ef-row-justify-start ef-form-item ef-form-item-layout-horizontal">
                  <div class="ef-col ef-col-6 ef-form-item-label-col">
                    <label class="ef-form-item-label" for="user_parent">上级部门</label>
                  </div>
                  <div class="ef-col ef-col-18 ef-form-item-wrapper-col">
                    <div class="ef-form-item-content-wrapper">
                      <div class="ef-form-item-content ef-form-item-content-flex"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="ef-modal-footer">
          <button class="btn secondary small canceluser" type="button" inputid="${inputid}">取消</button>
          <button class="btn primary small confirmuser" type="button" mode="${mode}" inputid="${inputid}">确定</button>
        </div>
      </div>
    </div>
    </div>`;

      // 将模态弹窗插入到页面中
      $('body').append(modalWindow);

      let modal = $(".ef-modal-container[inputid='" + inputid + "']");
      modal.show();
      // 调用部门接口，如果已经调用过就不再调用了，而是直接使用缓存
      let singleDepCache = await Common.getCache("org.singleuser");
      if (singleDepCache === null) {
        $.ajax({
          url: "/admin/org/departemnt/singleSelect",
          method: "GET",
          async: false,
          dataType: "html",
          success: async function (data) {
            await Common.setCache("org.singleuser", data);
            $(".user-tree-wrapper").html(data);
            // Common.forceReloadJS('components');
            // Common.forceReloadJS('admin');
          }
        })
      } else {
        modal.children().find(".user-tree-wrapper").html(singleDepCache);
      }
    }
  })

  $("body").on("click", ".ef-modal-close-btn", function() {
    let inputid = $(this).attr("inputid");
    $(".ef-modal-container[inputid='" + inputid + "']").hide();
  })

  $("body").on("click", ".ef-user-selection-li-content", function(event) {
    event.stopPropagation();
    let input = $(this).parents('.ef-user-selection-container').find('input');
    input.show().focus();
  })

  $("body").on("click", "[type='button'].confirmuser", function() {
    let inputid = $(this).attr("inputid");
    let choseId = $(this).attr("choseId");
    let mode = $(this).attr("mode");
    let path = $(this).attr("path");
    let userInput = $('#'+inputid);
    let selectionUl = userInput.children().find(".ef-user-selection-span ul");
    let template = `<li class="ef-user-selection-li">
    <div class="ef-user-selection-li-content">
      <a href="link" class="ef-link" id="${choseId}">${path}</a>
      <span class="ef-user-view-suffix close-chose-user" style="display: none;">
        <span class="ef-user-view-icon">
          <i class="fa-regular fa-circle-xmark"></i>
        </span>
      </span>
    </div>
    </li>`;

    let modal = $(this).parents(".ef-modal-container[inputid='"+inputid+"']");
    if (mode === 'single') {
      selectionUl.html(template);
      modal.hide();
      let input = userInput.find('.ef-user-selection-container input');
      input.attr("chose", "true");
      input.val("");
      input.hide();
    }

    refreshSelectionHover();

    // 删除选项
    $(".close-chose-user").on("click", function () {
      if (mode === 'single') {
        let input = $(this).parents('.ef-user-selection-container').find('input');
        input.attr("chose", "false");
        input.show();
        input.focus();
      }
      $(this).parents(".ef-user-selection-li").remove();
    })
  })
})
