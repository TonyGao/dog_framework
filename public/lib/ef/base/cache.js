$(document).ready(function() {
  // 获取所有带有 name="cache-status" 的 meta 标签
  $('meta[name="cache-status"]').each(async function() {
      console.log("cache clear");
      const cacheMeta = $(this);
      // 获取 data-cache-type 中的缓存类型，以及 content 中的状态信息
      const cacheType = cacheMeta.data('cache-type');
      const status = cacheMeta.attr('content');

      // 根据不同的缓存类型和状态执行相应的清除逻辑
      if (status === 'clear') {
          switch (cacheType) {
              case 'org.singleDepartment':
                  await clearSingleDepartmentCache();
                  break;
              default:
                  console.warn(`Unknown cache type: ${cacheType}`);
          }
      }
  });
});

//定义各类缓存的清除方法，根据实际情况修改
async function clearSingleDepartmentCache() {
  let route = new Route();
  let url = await route.generate("org_deparment_single_select");
  $.ajax({
    url: url.path,
    method: "GET",
    async: false,
    dataType: "html",
    success: async function (data) {
      await Common.setCache("org.singleDepartment", data);
      $(".department-tree-wrapper.single-department").html(data);
    }
  })
}
