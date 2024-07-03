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
        // 初始化时监听现有的字段类型选择控件
        $("input[data-field-type]").each(function () {
          observeFieldTypeChange(this);
        });
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
        let newField = $(response);
        container.find("#ef-drawer-body-form").append(newField);
        newField.find("input[data-field-type]").each(function () {
          observeFieldTypeChange(this);
        });
      },
    });
  });

  let flag = true;
  $("body").on("compositionstart", "input.fieldComment", function () {
    flag = false;
  });

  $("body").on("compositionend", "input.fieldComment", function () {
    flag = true;
  });

  $("body").on(
    "input change",
    "input.fieldComment",
    _.debounce(function () {
      if (flag) {
        let pinyin = Pinyin.convertToPinyin($(this).val(), "~", true);
        camelCasePinyin = Str.firstLetterToLowerCase(Str.toCamelCase(pinyin));
        let engName = $(this)
          .closest(".ef-row.ef-row-align-start.ef-row-justify-start")
          .find("input.fieldName");
        let fieldName = $(this)
          .closest(".ef-row.ef-row-align-start.ef-row-justify-start")
          .find("input.fieldName")
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
    // $(this).closest(".ef-drawer");
    let token = $(this).closest(".ef-drawer-container").attr("entitytoken");

    // 校验表单必填和规则校验
    let form = $(this).parents(".ef-drawer").find("#submitFields");
    form.formValid();

    if (!form.valid()) {
      somethingWrong = true;
      return;
    }

    let groupedData = collectFormData(form);


    let payload = {
      entity: {
        entityId: $(this).closest(".ef-drawer-container").attr("entitytoken"),
        fields: groupedData
      }
    };

    console.log(JSON.stringify(payload, null, 2));

    // 发送 AJAX 请求
    let field = route.generate("api_platform_entity_submitFields");
    $.ajax({
      type: field.methods[0],
      url: field.path, // 提交表单的路由路径
      contentType: "application/json",
      data: JSON.stringify(payload),
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
    const row = $(this).closest(".ef-logic-row");
    // 检查是否只剩下一行
    if (
      $(this).closest("#ef-drawer-body-form").find(".ef-row").length > 1
    ) {
      if (row) {
        row.remove();
      }
    } else {
      // 如果只剩下一行，给出相应的提示或者不执行任何操作
      let alert = new Alert($(this).closest(".ef-drawer"));
      alert.warning("至少需要保留一行字段", "40%", "不能再删除了");
    }
  });

  // text addition attributes
  // 获取文本类型字段的补充属性模板
  function getTextFieldAttributes () {
    // 生成每个组件的唯一随机字符串
    const fieldLengthId = Str.generateRandomString(40);
    const defaultValueId = Str.generateRandomString(40);
    const nullableId = Str.generateRandomString(40);
    const uniqueId = Str.generateRandomString(40);
    return `
          <div class="ef-field-attr-action-bar">
              <div class="ef-col-24">
                  <div class="ef-row ef-row-align-start ef-row-justify-start ef-row-vertical-center">
                      <div class="ef-col-6" style="padding-left: 12px; padding-right: 12px;">
                          <div class="ud__col ud__form__item__label">
                              <label class="ud__form__item-required" title="字段长度">字段长度<div class="ud__form__item__required-mark">*</div></label>
                          </div>
                          <div class="ef-field-control-wrapper">
                              <span class="ef-input-wrapper" style="max-width: 320px;">
                                  <input component="input" class="ef-input ef-input-size-medium fieldComment" clearable="true" type="text" id="${fieldLengthId}" name="fieldLength${fieldLengthId}" required="" fieldname="length" value="255">
                              </span>
                          </div>
                      </div>
                      <div class="ef-col-6" style="padding-left: 12px; padding-right: 12px;">
                          <div class="ud__col ud__form__item__label">
                              <label class="ud__form__item-required" title="默认值">默认值</label>
                          </div>
                          <div class="ef-field-control-wrapper">
                              <span class="ef-input-wrapper" style="max-width: 320px;">
                                  <input component="input" class="ef-input ef-input-size-medium fieldComment" clearable="true" type="text" id="${defaultValueId}" name="defaultValue${defaultValueId}" fieldname="defaultValue">
                              </span>
                          </div>
                      </div>
                      <div class="ef-col-3" style="padding-left: 12px; padding-right: 12px;">
                          <div class="ud__col ud__form__item__label">
                              <label class="ud__form__item-required" title="允许为空">允许为空</label>
                          </div>
                          <div class="ef-field-control-wrapper">
                              <label aria-disabled="false" class="ef-checkbox ef-checkbox-checked">
                                  <input type="checkbox" component="checkbox" class="ef-checkbox-target" id="${nullableId}" name="nullable${nullableId}" value="1" checked=true fieldname="nullable">
                                  <span class="ef-icon-hover ef-checkbox-icon-hover ef-icon-hover-disabled">
                                      <div class="ef-checkbox-icon">
                                          <svg aria-hidden="true" focusable="false" viewBox="0 0 1024 1024" width="200" height="200" fill="currentColor" class="ef-checkbox-icon-check"><path d="M877.44815445 206.10060629a64.72691371 64.72691371 0 0 0-95.14856334 4.01306852L380.73381888 685.46812814 235.22771741 533.48933518a64.72691371 64.72691371 0 0 0-92.43003222-1.03563036l-45.82665557 45.82665443a64.72691371 64.72691371 0 0 0-0.90617629 90.61767965l239.61903446 250.10479331a64.72691371 64.72691371 0 0 0 71.19960405 15.14609778 64.33855261 64.33855261 0 0 0 35.08198741-21.23042702l36.24707186-42.71976334 40.5190474-40.77795556-3.36579926-3.49525333 411.40426297-486.74638962a64.72691371 64.72691371 0 0 0-3.88361443-87.64024149l-45.3088404-45.43829334z" p-id="840"></path></svg>
                                      </div>
                                  </span>
                              </label>
                          </div>
                      </div>
                      <div class="ef-col-3" style="padding-left: 12px; padding-right: 12px;">
                          <div class="ud__col ud__form__item__label">
                              <label class="ud__form__item-required" title="唯一的">唯一的</label>
                          </div>
                          <div class="ef-field-control-wrapper">
                              <label aria-disabled="false" class="ef-checkbox">
                                  <input type="checkbox" component="checkbox" class="ef-checkbox-target" id="${uniqueId}" name="unique${uniqueId}" fieldname="unique" value="1" checked=false>
                                  <span class="ef-icon-hover ef-checkbox-icon-hover">
                                      <div class="ef-checkbox-icon"></div>
                                  </span>
                              </label>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      `;
  }

  // 使用MutationObserver监听属性变化
  function observeFieldTypeChange (element) {
    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
          let selectedType = $(mutation.target).val();
          let efRow = $(mutation.target).closest(".ef-row");

          // 如果选择的是文本类型，添加补充属性部分
          if (selectedType === 'string') {
            if (efRow.next(".ef-field-attr-action-bar").length === 0) {
              efRow.after(getTextFieldAttributes());
            }
          } else {
            // 如果不是文本类型，移除补充属性部分
            efRow.next(".ef-field-attr-action-bar").remove();
          }
        }
      });
    });

    observer.observe(element, {
      attributes: true,
      attributeFilter: ['value']
    });
  }

  // 手动收集表单数据并分组
  function collectFormData (form) {
    const formData = [];
    const logicRows = $(form).find('.ef-logic-row');

    logicRows.each(function () {
      const fields = {};
      const elements = $(this).find('input, select, textarea');

      elements.each(function () {
        const field = $(this);
        const fieldName = field.attr('fieldname');
        if (fieldName) {
          const id = field.attr('id');
          const name = field.attr('name');
          const component = field.attr('component');
          const value = field.val();

          fields[fieldName] = {
            id,
            name,
            component,
            value
          };
        }
      });

      formData.push(fields);
    });

    return formData;
  }

});
