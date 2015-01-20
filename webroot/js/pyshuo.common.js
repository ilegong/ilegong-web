Pyshuo={};
Pyshuo.ui={};
Pyshuo.ui.utils={

    $tipInfoPanel:null,

    saveToLocalStorage:function(key,value){
        if(this.storage){
            this.storage[key]=value;
        }
    },
    readFromLocalStorage:function(key){
        if(this.storage){
            return this.storage.getItem(key);
        }
    },
    initLocalStorage:function(){
        if(window.localStorage){
            if(!this.storage){
                this.storage=window.localStorage;
            }
        }
    },
    mobileShowTip:function(msg,fadeInTime,fadeOutTime,callBack){
        if(!this.$tipInfoPanel){
            this.$tipInfoPanel = $('<div class="comment_tip_layer radius10" style="width:60%; left:50%; top:30%; margin-left:-30%; display: none;"></div>');
            $('body').append(this.$tipInfoPanel);
        }
        fadeInTime = fadeInTime?fadeInTime:1000;
        fadeOutTime = fadeOutTime?fadeOutTime:2000;
        callBack = callBack?callBack:function(){
        };
        this.$tipInfoPanel.text(msg).fadeIn(fadeInTime).fadeOut(fadeOutTime,callBack);
    }
};