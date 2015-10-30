$(document).ready(function(){
  var d = new DataConverter();
  d.create();
  function updateSettings () {
    d.indent = "  ";
    d.downcaseHeaders = false;
    d.upcaseHeaders = false;
    d.delimiter = 'auto';
    d.decimal = 'dot';
    d.useUnderscores = true;
    d.convert();
  };
  updateSettings();
})

