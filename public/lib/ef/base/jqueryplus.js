(function ($) {
  // 当input focus时，将光标挪动到最后的字符后
  $.fn.textFocus = function (v) {
    var range,
      len,
      v = v === undefined ? 0 : parseInt(v);
    this.each(function () {
      len = this.value.length;
      v === 0 ? this.setSelectionRange(len, len) : this.setSelectionRange(v, v);
      this.focus();
    });
    return this;
  };
})(jQuery);
