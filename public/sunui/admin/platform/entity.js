$(document).ready(function () {
  /**
   * 点击左侧树状的实体名称，如果右侧存在标签页就按原逻辑增加标签页
   * 如果右侧没有任何标签页，就将empty-content隐藏，并将platform-entity显出出来
   */
  let tabsIsEmpty = {};

  $(".node-name").on(
    "click",
    ".tree-text-content.branch[type=entity]",
    function () {
      let id = $(this).attr("id");
      let liId = "tab-" + id;
      let tabName = $(this).text();
      let entityData;

      // console.log(tabsIsEmpty["tabsIsEmpty" + id]);

      // 如果右侧没有没有任何标签页
      if (tabsIsEmpty["tabsIsEmpty" + id] === undefined) {
        // 根据点击的模型的token查询模型数据
        $.ajax({
          url: "/admin/platform/entity/itemtable",
          method: "GET",
          async: false,
          dataType: "html",
          data: { token: id },
          success: function (data) {
            entityData = data;
          },
        });
        $(".empty-content").hide();
        $("#platform-entity").show();
        tabsIsEmpty["tabsIsEmpty" + id] = false;
      }

      EfTabs.addTab("platform-entity", liId, tabName, "hello", entityData);

      // $(".create.btn").off('click')
      // $(".create.btn").on('click', function() {
      //   let token = $(this).attr('token');
      // })
    }
  );

  // $("#platform-entity").off("click", "button .create");
  $("#platform-entity").on("click", "button.create", function () {
    let token = $(this).attr("token");
    let id = "drawer" + token;

    // 检查是否已在DOM中存在相同ID的元素
    if ($("#" + id).length > 0) {
      // 如果已存在相同ID的元素，则提前返回，不执行后续操作
      $().showDrawer(id);
      return;
    }

    let payload = {
      token: token,
    };
    $.ajax({
      url: "/admin/platform/entity/addFieldDrawer",
      method: "POST",
      dataType: "html",
      contentType: "application/json",
      data: JSON.stringify(payload),
      success: function (response) {
        $("#app").append(response);
        $().showDrawer(id);
      },
    });
  });

  let fieldLineNum = 1;
  $("body").on("click", "#addField", function () {
    // 获取 ef-drawer-container 元素的 entitytoken 属性值
    let container = $(this).closest(".ef-drawer-container");
    let entityToken = container.attr("entitytoken");
    let payload = { token: entityToken };
    let choosedGroup = container.find("#form_entityGroup").attr("value");
    //自增行数
    fieldLineNum++;

    payload.choosedGroup = choosedGroup;

    $.ajax({
      url: "/admin/platform/entity/addField",
      method: "POST",
      async: false,
      dataType: "html",
      contentType: "application/json",
      data: JSON.stringify(payload),
      success: function (response) {
        container.find("#ef-drawer-body-form").append(response);
      },
    });
  });

  let flag = true;
  $("body").on(
    "compositionstart",
    'input.fieldComment',
    function () {
      flag = false;
    }
  );

  $("body").on(
    "compositionend",
    'input.fieldComment',
    function () {
      flag = true;
    }
  );

  $("body").on(
    "input change",
    'input.fieldComment',
    _.debounce(function () {
      if (flag) {
        let pinyin = Pinyin.convertToPinyin($(this).val(), "~", true);
        camelCasePinyin = Str.firstLetterToLowerCase(Str.toCamelCase(pinyin));
        let engName = $(this)
          .closest(".ef-row.ef-row-align-start.ef-row-justify-start")
          .find('input.fieldName');
        let fieldName =  $(this)
          .closest(".ef-row.ef-row-align-start.ef-row-justify-start")
          .find('input.fieldName')
          .val(camelCasePinyin);

        if (somethingWrong) {
          fieldName.valid();
        }
      }
    }, 500)
  );

  const route = new Route();
  let somethingWrong = false;
  $("body").on("click", "#submitFormButton", function (e) {
    e.preventDefault(); // 阻止按钮的默认行为
    $(this).closest(".ef-drawer");

    // 校验表单必填和规则校验
    let form = $(this).parents('.ef-drawer').find("#submitFields");
    form.formValid();

    if (!form.valid()) {
      somethingWrong = true;
      return;
    }

    let formData = Str.serializeToJson(form.serialize());
    let length = Object.entries(formData).length;
    // 每行的字段个数
    const indexPerLine = Math.floor(length / fieldLineNum);
    let groupArr = [];
    let arr = [];
    Object.entries(formData).forEach((entry, index) => {
      const [key, value] = entry;
      let obj = {};
      obj[key] = value;
      arr.push(obj);
      // console.log(`Index: ${index}, Key: ${key}, Value: ${value}`);
      if ((index + 1) % indexPerLine === 0) {
        groupArr.push(arr);
        arr = [];
      }
    });

    // 发送 AJAX 请求
    let field = route.generate("api_platform_entity_submitFields");
    $.ajax({
      type: field.methods[0],
      url: field.path, // 提交表单的路由路径
      contentType: "application/json",
      data: JSON.stringify(groupArr),
      success: function (response) {
        // 在成功响应时执行的操作，可以是重定向、显示消息等
        console.log("表单提交成功！");

        // 如果有必要，在这里可以执行其他操作，比如隐藏模态框
      },
      error: function (xhr, status, error) {
        // 在发生错误时执行的操作
        console.error("表单提交失败: " + error);
      },
    });
  });

  $("body").on("click", "#ef-drawer-body-form .close-field-row", function (e) {
    const row = $(this).closest('.ef-row');
    // 检查是否只剩下一行
    if ($(this).closest("#ef-drawer-body-form").children(".ef-row").length > 1) {
      if (row) {
        row.remove();
      }
    } else {
      // 如果只剩下一行，给出相应的提示或者不执行任何操作
      let alert = new Alert($(this).closest(".ef-drawer"));
      alert.warning("至少需要保留一行字段", "40%", "不能再删除了");
    }
  }); 
});
