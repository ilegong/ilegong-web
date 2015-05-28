var tablesToExcel = (function() {
    var uri = 'data:application/vnd.ms-excel;base64,'
        , tmplWorkbookXML = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            + '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"><Author>pys</Author><Created>{created}</Created></DocumentProperties>'
            + '<Styles>'
            + '<Style ss:ID="Currency"><NumberFormat ss:Format="Currency"></NumberFormat></Style>'
            + '<Style ss:ID="Date"><NumberFormat ss:Format="Medium Date"></NumberFormat></Style>'
            + '</Styles>'
            + '{worksheets}</Workbook>'
        , tmplWorksheetXML = '<Worksheet ss:Name="{nameWS}"><Table>{rows}</Table></Worksheet>'
        , tmplCellXML = '<Cell{attributeStyleID}{attributeFormula}><Data ss:Type="{nameType}">{data}</Data></Cell>'
        , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
        , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
    return function(tables, wsnames, wbname, appname,ignoreRows) {
        var ctx = "";
        var workbookXML = "";
        var worksheetsXML = "";
        var rowsXML = "";
        for (var i = 0; i < tables.length; i++) {
            var statisticsData = {};
            if (!tables[i].nodeType) tables[i] = document.getElementById(tables[i]);
            for (var j = 0; j < tables[i].rows.length; j++) {
                if(j!=0){
                    var p_s_id = tables[i].rows[j].getAttribute('data-mark-tag');
                    var p_s_name = tables[i].rows[j].getAttribute('data-mark-tag-name');
                    var p_s_num = tables[i].rows[j].getAttribute('data-mark-tag-num');
                    if (statisticsData[p_s_id]) {
                        statisticsData[p_s_id]['num'] =parseInt(statisticsData[p_s_id]['num'])+parseInt(p_s_num);
                    } else {
                        statisticsData[p_s_id] = {
                            'name': p_s_name,
                            'num': p_s_num
                        };
                    }
                }
                rowsXML += '<Row>';
                for (var k = 0; k < tables[i].rows[j].cells.length; k++) {
                    if(ignoreRows.indexOf(k)>=0){
                        continue;
                    }
                    var dataType = tables[i].rows[j].cells[k].getAttribute("data-type");
                    var dataStyle = tables[i].rows[j].cells[k].getAttribute("data-style");
                    var dataValue = tables[i].rows[j].cells[k].getAttribute("data-value");
                    dataValue = (dataValue)?dataValue:tables[i].rows[j].cells[k].textContent;
                    dataValue = dataValue.replace(/\s+/g,"");
                    var dataFormula = tables[i].rows[j].cells[k].getAttribute("data-formula");
                    dataFormula = (dataFormula)?dataFormula:(appname=='Calc' && dataType=='DateTime')?dataValue:null;
                    ctx = {  attributeStyleID: (dataStyle=='Currency' || dataStyle=='Date')?' ss:StyleID="'+dataStyle+'"':''
                        , nameType: (dataType=='Number' || dataType=='DateTime' || dataType=='Boolean' || dataType=='Error')?dataType:'String'
                        , data: (dataFormula)?'':dataValue
                        , attributeFormula: (dataFormula)?' ss:Formula="'+dataFormula+'"':''
                    };
                    rowsXML += format(tmplCellXML, ctx);
                }
                rowsXML += '</Row>'
            }
            for(row_tag in statisticsData){
                rowsXML +='<Row>';
                var item_data = statisticsData[row_tag];
                ctx = {  attributeStyleID: ''
                    , nameType: 'String'
                    , data: item_data['name']+':'+item_data['num']
                    , attributeFormula: ''
                };
                rowsXML += format(tmplCellXML, ctx);
                rowsXML +='</Row>';
            }
            ctx = {rows: rowsXML, nameWS: wsnames[i] || 'Sheet' + i};
            worksheetsXML += format(tmplWorksheetXML, ctx);
            rowsXML = "";

        }

        ctx = {created: (new Date()).getTime(), worksheets: worksheetsXML};
        workbookXML = format(tmplWorkbookXML, ctx);

        //console.log(workbookXML);

        var link = document.createElement("A");
        link.href = uri + base64(workbookXML);
        link.download = wbname || 'Workbook.xls';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
})();