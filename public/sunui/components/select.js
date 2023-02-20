$(document).ready(function () {
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
    $("#"+contentId).css({"left": left, "top": top});
    console.log("left:"+left+" top:" + top);
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

  // 选取选项
  $(".ef-select-option").not(".ef-select-option-disabled").on("click", function () {
    let val = $(this).children(".ef-select-option-content").html();
    let selectContent = $(this).parents().find(".ef-trigger-popup.ef-trigger-position-bl");
    let id = selectContent.attr("parentId");
    let selectInput = $("#" + id);
    selectInput.children(".ef-select-view-input").addClass("ef-select-view-input-hidden");
    selectInput.children(".ef-select-view-value").html(val).removeClass("ef-select-view-value-hidden");
    let closeIon = '<i class="fa-regular fa-circle-xmark"></i>';
    selectInput.children().find(".ef-select-view-icon").html(closeIon);
    selectInput.attr("chosen", "true");

    // 删掉选中的选项
    $(".fa-regular.fa-circle-xmark").on("click", function (event) {
      event.stopPropagation();
      let selectInput = $(this).parents().find(".ef-select-view-single");
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
  $(".ef-select-view-input").on("input change", _.debounce(function () {
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
        $("#" + ele.id).show();
      })
      $.each(hideList, function (idx, ele) {
        $("#" + ele.id).hide();
      })
    }
  }, 500))

  $(".ef-select-view-input").on("focusout", function () {
    $(this).val("");
  })
})
