$(document).ready(function () {
  /**
   * 点击左侧树状的实体名称，如果右侧存在标签页就按原逻辑增加标签页
   * 如果右侧没有任何标签页，就将empty-content隐藏，并将platform-entity显出出来
   */
  let tabsIsEmpty = {};
  $(".node-name").on("click", ".tree-text-content.branch[type=entity]", function () {
    let id = $(this).attr('id');
    let liId = 'tab-'+id;
    let tabName = $(this).text();
    let entityData;

    console.log(tabsIsEmpty['tabsIsEmpty'+id]);

    // 如果右侧没有没有任何标签页
    if (tabsIsEmpty['tabsIsEmpty'+id] === undefined) {
      // 根据点击的模型的token查询模型数据
      $.ajax({
        url: "/admin/platform/entity/itemtable",
        method: "GET",
        async: false,
        dataType: "html",
        data: {token: id},
        success: function(data) {
          entityData = data;
        }
      })
      $(".empty-content").hide();
      $("#platform-entity").show();
      tabsIsEmpty['tabsIsEmpty'+id] = false;
    }

    EfTabs.addTab('platform-entity', liId, tabName, "hello", entityData);

    // $(".create.btn").off('click')
    // $(".create.btn").on('click', function() {
    //   let token = $(this).attr('token');
    // })
  })

  // $("#platform-entity").off("click", "button .create");
  $("#platform-entity").on("click", "button.create", function() {
    let token = $(this).attr("token");
    let id = 'drawer' + token;

    // 检查是否已在DOM中存在相同ID的元素
    if ($("#" + id).length > 0) {
      // 如果已存在相同ID的元素，则提前返回，不执行后续操作
      $().showDrawer(id);
      return;
    }

    let payload = {
      token: token
    }
    $.ajax({
      url: "/admin/platform/entity/addFieldDrawer",
      method: "POST",
      dataType: "html",
      contentType: 'application/json',
      data: JSON.stringify(payload),
      success: function (response) {
        $("#app").append(response);
        $().showDrawer(id);
      }
    })
  })

  $("body").on('click', '#addField', function () {
    // 获取 ef-drawer-container 元素的 entitytoken 属性值
    let entityToken = $(".ef-drawer-container").attr("entitytoken");

    $.ajax({
			url: "/admin/platform/entity/addField",
			method: "GET",
			async: false,
			dataType: "html",
      data: { token: entityToken },
			success: function (data) {
				$("#ef-drawer-body-form").append(data);
			}
		})
  })

  let flag = true;
  $("body").on('compositionstart', 'input[name="form[fieldComment]"]', function () {
    flag = false;
  })

  $("body").on('compositionend', 'input[name="form[fieldComment]"]',function () {
    flag = true;
  })

	$("body").on("input change", 'input[name="form[fieldComment]"]', _.debounce(function() {
		if (flag) {
      let pinyin = Pinyin.convertToPinyin($(this).val(), '~', true);
      camelCasePinyin = Str.firstLetterToLowerCase(Str.toCamelCase(pinyin));
			let engName = $(this).closest('.ef-row.ef-row-align-start.ef-row-justify-start').find('input[name="form[fieldName]"]');
			$(this).closest('.ef-row.ef-row-align-start.ef-row-justify-start').find('input[name="form[fieldName]"]').val(camelCasePinyin);
		}
	}, 500))
})