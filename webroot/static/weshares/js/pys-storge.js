/**
 * Created by shichaopeng on 8/11/15.
 */
  //Pys common name space
PYS={};
PYS.storage = {
  save : function(key, jsonData, expirationHour){
    //if (!Modernizr.localstorage){return false;}
    var expirationMS = expirationHour * 60 * 60 * 1000;
    var record = {value: JSON.stringify(jsonData), timestamp: new Date().getTime() + expirationMS}
    localStorage.setItem(key, JSON.stringify(record));
    return jsonData;
  },
  load : function(key){
    //if (!Modernizr.localstorage){return false;}
    var record = JSON.parse(localStorage.getItem(key));
    if (!record){return false;}
    return (new Date().getTime() < record.timestamp && JSON.parse(record.value));
  },
  clear : function(){
    localStorage.clear();
  }
}