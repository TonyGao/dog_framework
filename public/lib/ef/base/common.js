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
   * @param {boolean} useSession - 是否通过 session 读取缓存
   */
  static async getCache(item, useSession = true) {
    const sessionKey = useSession ? $("meta[session]").attr("session") : "global";
    try {
      const storage = await localforage.getItem("Ef-" + sessionKey);
      let str;
      if (storage === null) {
        await localforage.setItem("Ef-"+ sessionKey, {"session": sessionKey})
        return null;
      }

      if (storage !== null) {
        str = storage;
        return str[item] === undefined ? null : str[item];
      }
    } catch (err) {
      console.error(err);
    }
  }

  /**
   *
   * @param {*} item
   * @param {*} val
   * @param {boolean} useSession - 是否通过 session 写入缓存
   */
  static async setCache(item, val, useSession = true) {
    const sessionKey = useSession ? $("meta[session]").attr("session") : "global";
    let str;
    str = await localforage.getItem("Ef-" + sessionKey);
    str = str || {};  // 确保 str 存在
    str[item] = val;
    await localforage.setItem("Ef-" + sessionKey, str);
  }
}
