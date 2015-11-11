//
//  converter.js
//  Mr-Data-Converter
//
//  Created by Shan Carter on 2010-09-01.
//


function DataConverter(nodeId) {

  //---------------------------------------
  // PUBLIC PROPERTIES
  //---------------------------------------

  this.nodeId = nodeId;
  this.node = $("#" + nodeId);

  this.outputDataTypes = [
    {"text": "Actionscript", "id": "as", "notes": ""},
    {"text": "ASP/VBScript", "id": "asp", "notes": ""},
    {"text": "HTML", "id": "html", "notes": ""},
    {"text": "JSON - Properties", "id": "json", "notes": ""},
    {"text": "JSON - Column Arrays", "id": "jsonArrayCols", "notes": ""},
    {"text": "JSON - Row Arrays", "id": "jsonArrayRows", "notes": ""},
    {"text": "JSON - Dictionary", "id": "jsonDict", "notes": ""},
    {"text": "MySQL", "id": "mysql", "notes": ""},
    {"text": "PHP", "id": "php", "notes": ""},
    {"text": "Python - Dict", "id": "python", "notes": ""},
    {"text": "Ruby", "id": "ruby", "notes": ""},
    {"text": "XML - Properties", "id": "xmlProperties", "notes": ""},
    {"text": "XML - Nodes", "id": "xml", "notes": ""},
    {"text": "XML - Illustrator", "id": "xmlIllustrator", "notes": ""}];
  this.outputDataType = "json";

  this.columnDelimiter = "\t";
  this.rowDelimiter = "\n";

  this.inputTextArea = {};
  this.outputTextArea = {};

  this.inputHeader = {};
  this.outputHeader = {};
  this.dataSelect = {};

  this.inputText = "";
  this.outputText = "";

  this.newLine = "\n";
  this.indent = "  ";

  this.commentLine = "//";
  this.commentLineEnd = "";
  this.tableName = "MrDataConverter"

  this.useUnderscores = true;
  this.headersProvided = true;
  this.downcaseHeaders = true;
  this.upcaseHeaders = false;
  this.includeWhiteSpace = true;
  this.useTabsForIndent = false;

}

//---------------------------------------
// PUBLIC METHODS
//---------------------------------------

DataConverter.prototype.create = function () {
  var self = this;

  //build HTML for converter
  this.inputTextArea = $('#order-data-input');
  this.tempTextPlace = $('#RawJson');
  this.outputTextArea = $('#order-data-output');


  //add event listeners

  $("#convertButton").bind('click',function(evt){
     evt.preventDefault();
     self.convert();
  });

  this.outputTextArea.click(function (evt) {
    this.select();
  });

  $("#order-data-input").keyup(function () {
    self.convert()
  });
  $("#order-data-input").change(function () {
    self.convert();
  });
};


DataConverter.prototype.convert = function () {
  this.inputText = this.inputTextArea.val();
  this.outputText = "";
  //make sure there is input data before converting...
  if (this.inputText.length > 0) {

    if (this.includeWhiteSpace) {
      this.newLine = "\n";
      // console.log("yes")
    } else {
      this.indent = "";
      this.newLine = "";
      // console.log("no")
    }

    CSVParser.resetLog();
    var parseOutput = CSVParser.parse(this.inputText, this.headersProvided, this.delimiter, this.downcaseHeaders, this.upcaseHeaders);

    var dataGrid = parseOutput.dataGrid;
    var headerNames = parseOutput.headerNames;
    var headerTypes = parseOutput.headerTypes;
    var errors = parseOutput.errors;

    this.outputText = DataGridRenderer[this.outputDataType](dataGrid, headerNames, headerTypes, this.indent, this.newLine);
    if(!errors){
      var jsonArrayData = JSON.parse(this.outputText);
      var resultData = [];
      var postData = [];
      $.each(jsonArrayData, function(index,item){
        resultData.push({"订单号":item['订单号'],"快递单号":item['快递单号'], "物流方式":item['快递方式'], "快递公司" : item['快递公司']});
        postData.push({"order_id":item['订单号'],"ship_code":item['快递单号'], "ship_type_name":item['快递公司']});
      });
      Pys.sharerManage.orderManage.batch_set_ship_order_data = postData;
      this.tempTextPlace.val(JSON.stringify(resultData));
      Process();
    }else{
      alert('格式有误');
    }
  }; //end test for existence of input text
}




