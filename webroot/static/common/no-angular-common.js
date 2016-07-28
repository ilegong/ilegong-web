Pyshuo = {};
Pyshuo.share = {};
Pyshuo.share.utils = {
  init: function () {
    this.markUnRead = $('#mark-has-unread');
  },
  checkHasUnreadInfo: function () {
    var me = this;
    //$.getJSON('/share_opt/check_opt_has_new', function (data) {
    //  //reset bottom nav
    //  if (data['has_new']) {
    //    me.markUnRead.show();
    //  } else {
    //    me.markUnRead.hide();
    //  }
    //});
  },
  saveToLocalStorage: function (key, value) {
    if (this.storage) {
      this.storage[key] = value;
    }
  },
  readFromLocalStorage: function (key) {
    if (this.storage) {
      return this.storage.getItem(key);
    }
  },
  initLocalStorage: function () {
    if (window.localStorage) {
      if (!this.storage) {
        this.storage = window.localStorage;
      }
    }
  }
};
