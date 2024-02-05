$(document).ready(function () {
  /**
   * 点击左侧树状的实体名称，如果右侧存在标签页就按原逻辑增加标签页
   * 如果右侧没有任何标签页，就将empty-content隐藏，并将platform-entity显出出来
   */
  let tabsIsEmpty = true;
  $(".node-name").on("click", ".tree-text-content.branch[type=entity]", function () {
    let id = $(this).attr('id');
    let liId = 'tab-'+id;
    let tabName = $(this).text();

    // 如果右侧没有没有任何标签页
    if (tabsIsEmpty) {
      $(".empty-content").hide();
      $("#platform-entity").show();
      EfTabs.addTab('platform-entity', liId, tabName, "hello");
    }
  })
})