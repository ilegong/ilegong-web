$(function(){
    var $tags = $('input[name^="tags"]');
    var $specs = $('select[name^="spec"]');
    var $addForm = $('#ProductAddForm');
    var hasSelectAttr = [];
    $tags.tagsInput({
        'height':'100px',
        'width':'230px',
        'interactive':true,
        'defaultText':'添加规格'
    });

    $.getJSON('/productAttributes/getAllProductAttribute',function(data){
        //console.log(data);
        var $options = '<option value="0">请选择规格名称</option>';
        $.each(data,function(index,item){
            $options+='<option value="'+item['id']+'">'+item['name']+'</option>';
        });
        $specs.html($options);
    });

    $specs.on('change',function(){
        var me = $(this);
        var before = me.attr('before-value');
        var currVal = me.val();
        if($.inArray(currVal,hasSelectAttr)<0){
            hasSelectAttr = removeA(hasSelectAttr,before);
            if(currVal!=0){
                me.attr('before-value',currVal);
                hasSelectAttr.push(currVal);
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

    $addForm.on('submit',function(){
        //set tag val
        $.each($tags,function(index,item){
            var $keywords = $(item).siblings(".tagsinput").children(".tag");
            var tags = [];
            for (var i = $keywords.length; i--;) {
                tags.push($($keywords[i]).text().substring(0, $($keywords[i]).text().length -  1).trim());
            }
            /*Then if you only want the unique tags entered:*/
            var uniqueTags = $.unique(tags);
            $(item).val(uniqueTags.join(','));
            return true;
        });
    });
});