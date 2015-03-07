$(function(){
    var $tags = $('input[name^="tags"]');
    var $specs = $('select[name^="spec"]');
    var $spec_table = $('#spec_table');
    var hasSelectAttr = [];
    var specData = {};
    var tableData = [];
    var tableHeaders=[];
    var columns=[];
    var tempTableData = [];
    function initData(){
        $.each(product_specs,function(index,item){
            var attr_id = item['attr_id'];
            var spec_name = item['name'];
            if(!specData[attr_id]){
                specData[attr_id]=[];
            }
            specData[attr_id].push(spec_name);
        });
    }
    function initView(){
        var i=0;
        $.each(specData,function(index,item){
            //console.log(index);
            //console.log(item);
            hasSelectAttr.push(index);
            $($specs[i]).val(index);
            $($tags[i]).val(item.join(','));
            i++;
        });
        //TODO init table
    }
    function initTable(){
        $.each($specs,function(index,item){
            var me = $(item);
            var val = me.val();
            var tagElement= me.parent('div').siblings('input');
            var selectedTags = getTagsByElement(tagElement);
            if(val!=0&&selectedTags.length>0){
                var attrName = $('option:selected',me).text();
                tableHeaders.push(attrName);
                columns.push({data:val});
                tempTableData.push({'key':val,'tags':selectedTags});
            }
        });
        tableHeaders.concat(['价格','库存']);
        columns.concat([{data:'price',type:'numeric'},{data:'stock',type:'numeric'}]);
        var tempDataLen = tempTableData.length;
        if(tempDataLen>0){
            if(tempDataLen==1){
                var attr1Id = tempTableData[0]['key'];
                var attr1Tags = tempTableData[0]['tags'];
                $.each(attr1Tags,function(index,item){
                    tableData.push({
                        attr1Id:item,
                        'price':0,
                        'stock':0
                    });
                });
            }else if(tempDataLen==2){

            }else{

            }
        }
    }
    function genTable(){
        initTable();
        //var container = document.getElementById('hot');
        $spec_table.handsontable({
            data: tableData,
            colHeaders: true,
            contextMenu: true,
            mergeCells:true,
            colHeaders:tableHeaders,
            columns:columns
        });
    }
    var $options = '<option value="0">请选择规格名称</option>';
    $.each(all_product_attrs,function(index,item){
        $options+='<option value="'+item['id']+'">'+item['name']+'</option>';
    });
    $specs.html($options);
    $specs.on('change',function(){
        var me = $(this);
        var before = me.attr('before-value');
        var currVal = me.val();
        if($.inArray(currVal,hasSelectAttr)<0){
            hasSelectAttr = removeA(hasSelectAttr,before);
            if(currVal!=0){
                me.attr('before-value',currVal);
                hasSelectAttr.push(currVal);
                //clean tags
                me.parent('div').siblings('input').importTags('');
            }
        }else{
            var before = me.attr('before-value');
            me.val(before);
            alert("请选择不同的名称");
        }
    }).on('focus',function(){
        var me = $(this);
        me.attr('before-value',me.val());
    });

    function removeA(arr) {
        var what, a = arguments, L = a.length, ax;
        while (L > 1 && arr.length) {
            what = a[--L];
            while ((ax= arr.indexOf(what)) !== -1) {
                arr.splice(ax, 1);
            }
        }
        return arr;
    }

    function getTagsByElement($element){
        var $keywords = $element.siblings(".tagsinput").children(".tag");
        var tags = [];
        for (var i = $keywords.length; i--;) {
            tags.push($($keywords[i]).text().substring(0, $($keywords[i]).text().length -  1).trim());
        }
        /*Then if you only want the unique tags entered:*/
        var uniqueTags = $.unique(tags);
        $element.val(uniqueTags.join(','));
        return uniqueTags;
    }

    initData();
    initView();
    $tags.tagsInput({
        'height':'100px',
        'width':'230px',
        'interactive':true,
        'defaultText':'添加规格',
        'onChange':onChangeTag
    });
    function onChangeTag(input,tag){
        var me = $(this);
        //TODO reset table
    }
});