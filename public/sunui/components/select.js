$(document).ready(function () {
  let elements = document.getElementsByClassName("ef-select");
  let config = {
    prevent_repeat: true
  };

  /**
   * 遍历class是ef-select的dom，获取id，存储selectList对象数组中
   *
   * selectList对象格式
   * {
   *  selectEleId: '557919876',
   *  activeEle: '1999887089',
   *  list: [
   *    { id: "1999887089", idx: 1, value: 'Beijing' },
   *    { id: "717768562", idx: 1, value: 'Shanghai' },
   *    { id: "446617704", idx: 1, value: 'Guangzhou' },
   *    { id: "475261113", idx: 1, value: 'Shenzhen' },
   *    { id: "407502384", idx: 1, value: 'Chengdu' },
   *    { id: "622693981", idx: 1, value: 'Wuhan' },
   *  ]
   * }
   */

  $.each($(".ef-select"), function () {
    let selectList = {};
    let selectEleId = $(this).attr('id');
    selectList['selectEleId'] = selectEleId;
  })

  let selectList = {
    selectEleId: '557919876',
    activeEle: '1999887089',
    list: [{
        id: "1999887089",
        idx: 1,
        value: 'Beijing'
      },
      {
        id: "1999887089",
        idx: 1,
        value: 'Shanghai'
      },
      {
        id: "1999887089",
        idx: 1,
        value: 'Guangzhou'
      },
      {
        id: "1999887089",
        idx: 1,
        value: 'Shenzhen'
      },
      {
        id: "1999887089",
        idx: 1,
        value: 'Chengdu'
      },
      {
        id: "1999887089",
        idx: 1,
        value: 'Wuhan'
      },
    ]
  }

  Array.prototype.forEach.call(elements, function (element) {
    let listener = new window.keypress.Listener(element, config);
    listener.register_combo({
      "keys": "down",
      "on_keydown": function (e) {
        let id = $(e.target).parent().attr("id");
        // console.log($(".ef-trigger-popup[parentid='"+ id +"']"));
      }
    });
    listener.register_combo({
      "keys": "up",
      "on_keydown": function (e) {
        console.log("up");
        //console.log($(e.target).parent().attr("id"));
      }
    })
  });

  $(".ef-select-view-search").on("click", function () {
    let isSelected = $(this).attr("chosen");
    if (isSelected !== "true") {
      $(this).toggleClass("ef-select-view-opened");
      $(this).find("input:first").focus();
      $(this).find("svg").remove();
      let searchIcon = `
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-search" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
        <path d="M33.072 33.071c6.248-6.248 6.248-16.379 0-22.627-6.249-6.249-16.38-6.249-22.628 0-6.248 6.248-6.248 16.379 0 22.627 6.248 6.248 16.38 6.248 22.628 0Zm0 0 8.485 8.485"></path>
      </svg>
    `;
      $(this).find(".ef-select-view-icon").html(searchIcon);
    }
    let contentId = $(this).attr("contentid");
    let height = $(this).outerHeight();
    let top = $(this).position().top + height + 6;
    let left = $(this).position().left;
    $("#" + contentId).css({
      "left": left,
      "top": top
    });
    $(".ef-trigger-popup.ef-trigger-position-bl").hide();
    // $("#" + contentId).children().find(".ef-select-dropdown-list li:first-child").addClass("arco-select-option-active");
    $("#" + contentId).show();
  });

  let selectIcon = `
  <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-expand" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter" style="transform: rotate(-45deg);">
    <path d="M7 26v14c0 .552.444 1 .996 1H22m19-19V8c0-.552-.444-1-.996-1H26"></path>
  </svg>
`;

  $(".ef-select-view-input").on("focusout", function (event) {
    $(this).parent().find("svg").remove();
    $(this).parent().find('.ef-select-view-icon').html(selectIcon);
  })

  $(document).on("click", function (event) {
    if (!$(event.target).closest(".ef-trigger-popup-wrapper, .ef-select-view-single").length) {
      $(".ef-trigger-popup.ef-trigger-position-bl").hide();
    }
  })

  $(".ef-select-option").on("click", function (event) {
    $(event.target).siblings('.arco-select-option-active').removeClass('arco-select-option-active');
    $(event.target).addClass("arco-select-option-active");
  })

  // 选取选项
  $(".ef-select-option").not(".ef-select-option-disabled").on("click", function () {
    let val = $(this).children(".ef-select-option-content").html();
    let value = $(this).attr("value");
    let selectContent = $(this).closest(".ef-trigger-popup.ef-trigger-position-bl");
    let id = selectContent.attr("parentId");
    let selectInput = $("#" + id);
    selectInput.prev('input').attr("value", value);
    selectInput.children(".ef-select-view-input").addClass("ef-select-view-input-hidden");
    selectInput.children(".ef-select-view-value").html(val).removeClass("ef-select-view-value-hidden");
    let closeIon = '<i class="fa-regular fa-circle-xmark"></i>';
    selectInput.children().find(".ef-select-view-icon").html(closeIon);
    selectInput.attr("chosen", "true");

    // 删掉选中的选项
    $(".fa-regular.fa-circle-xmark").on("click", function (event) {
      event.stopPropagation();
      let selectInput = $(this).closest(".ef-select-view-single");
      selectInput.attr("chosen", "false");
      selectInput.children(".ef-select-view-input").removeClass("ef-select-view-input-hidden");
      selectInput.children(".ef-select-view-value").addClass("ef-select-view-value-hidden");
      selectInput.children(".ef-select-view-value").html("");
      selectInput.children().find(".ef-select-view-icon").html(selectIcon);
    })
    selectContent.hide();
  })

  // 当在输入框输入时，将li元素遍历出来值并保存到原始数组中，并通过输入的内容模糊查询
  let dynamic = [];

  /**
   * flag 用来标记是否为中文输入完毕
   */
  let flag = true;
  $(".ef-select-view-input").on('compositionstart', function () {
    flag = false;
  })

  $(".ef-select-view-input").on('compositionend', function () {
    flag = true;
  })

  $(".ef-select-view-input").on("input change", _.debounce(function () {
    if (flag) {
      let contentId = $(this).parent().attr("contentid");
      let optionList = $("#" + contentId).children().find(".ef-select-option-content");
      let ul = $("#" + contentId).children().find(".ef-select-dropdown-list");
      if (dynamic['selectOrigin' + contentId] === undefined) {
        dynamic['selectOrigin' + contentId] = [];
        optionList.each(function (idx, element) {
          let eleObj = {};
          eleObj['lowerText'] = $(this).text().toLowerCase()
          eleObj['orginText'] = $(this).text();
          eleObj['id'] = $(this).parent().attr('id');
          dynamic['selectOrigin' + contentId].push(eleObj);
        })
      }
      // 除了搜索的li，其他全部隐藏
      let searchResult = Str.searchArr(dynamic['selectOrigin' + contentId], $(this).val().toLowerCase());
      let hideList = _.difference(dynamic['selectOrigin' + contentId], searchResult);
      if (hideList.length >= 0) {
        $.each(searchResult, function (idx, ele) {
          if (idx === 0) {
            $("#" + ele.id).addClass("arco-select-option-active");
          }
          $("#" + ele.id).show();
        })
        $.each(hideList, function (idx, ele) {
          $("#" + ele.id).hide();
        })
      }
    }
  }, 500))

  $(".ef-select-view-input").on("focusout", function () {
    $(this).val("");
  })
})
