class Common {
  static forceReloadJS(srcUrlContains) {
    $.each($('script:empty[src*="' + srcUrlContains + '"]'), function(index, el) {
      let oldSrc = $(el).attr('src');
      const cleanUrl = oldSrc.replace(/\?.*/, "");
      let t = +new Date();
      let newSrc = cleanUrl + '?' + t;
      $(el).remove();
      $('<script/>').attr('src', newSrc).appendTo('head');
    });
  }

  /**
   *
   * @param {*} item
   */
  static async getCache(item) {
    $.session = $("meta[session]").attr("session");
    try {
      const storage = await localforage.getItem("Ef-" + $.session);
      let str;
      if (storage === null) {
        await localforage.setItem("Ef-"+ $.session, {"session":$.session})
        return null;
      }

      if (storage !== null) {
        str = storage;
        return str[item]===undefined?null:str[item];
      }
    } catch (err) {
      console.error(err);
    }
  }

  static async setCache(item, val) {
    let str;
    str = await localforage.getItem("Ef-" + $.session);
    str[item] = val;
    await localforage.setItem("Ef-"+ $.session, str)
  }
}
