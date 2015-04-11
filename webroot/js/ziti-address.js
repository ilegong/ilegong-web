/**
 * Created by ldy on 15/3/25.
 */
var zitiAddress = function(){
    var beijingArea = [
        {"id":1,"name":"东城区"},
        {"id":2,"name":"海淀区"},
        {"id":3,"name":"西城区"},
        {"id":4,"name":"朝阳区"},
        {"id":5,"name":"丰台区"},
        {"id":6,"name":"崇文区"},
        {"id":7,"name":"宣武区"},
        {"id":8,"name":"昌平区"}
    ];
    var ship_address = {
        1: [{"shop_code":146,"shop_name":"好邻居兴化路店","address":"东城区兴华西里2号楼南侧"},
            {"shop_code":251,"shop_name":"好邻居金宝街金宝汇店","address":"东城区金宝街道路北侧一线临时建筑物"},
            {"shop_code":265,"shop_name":"好邻居美术馆后街店","address":"北京市东城区大佛寺东街1号"},
            {"shop_code":287,"shop_name":"好邻居安定门内店","address":"北京市东城区安定门内大街16号"},
            {"shop_code":290,"shop_name":"好邻居东四北店","address":"北京市东城区东四北大街146号"},
            {"shop_code":300,"shop_name":"好邻居东直南小店","address":"北京市东直门南小街20-1"},
            {"shop_code":304,"shop_name":"好邻居张自忠路店","address":"东城区张自忠路2号"},
            {"shop_code":311,"shop_name":"好邻居沙滩北街店","address":"东城区五四大街沙滩北街求是杂志社对面"},
            {"shop_code":312,"shop_name":"好邻居美术馆东街店","address":"北京市东城区美术馆后街9号"},
            {"shop_code":328,"shop_name":"五四大街二分店","address":"北京市东城区沙滩北街甲2号五四大街甲31号"},
            {"shop_code":331,"shop_name":"东四大街三分店","address":"东城区东四北大街43号首层"},
            {"shop_code":333,"shop_name":"沙滩后街分店","address":"东城区沙滩后街53号"},
            {"shop_code":347,"shop_name":"好邻居东营房店","address":"东城区吉士口东路聚龙花园对面东城区东营房八条3号楼"},
            {"shop_code":349,"shop_name":"好邻居安定门二","address":"东城区安定门桥南300米路西东城区安定门内大街107号"},
            {"shop_code":840,"shop_name":"好邻居民旺园分店","address":"东城区民旺园32号楼一层"}],
        2:[{"shop_code":125,"shop_name":"北三环店","address":"海淀区北三环西路60号"},
            {"shop_code":135,"shop_name":"好邻居阜成路店","address":"海淀区白堆子立新9号楼前"},
            {"shop_code":136,"shop_name":"好邻居大慧寺店","address":"海淀区魏公村大慧寺路5号"},
            {"shop_code":137,"shop_name":"好邻居增光路店","address":"海淀区增光路乙48号"},
            {"shop_code":147,"shop_name":"好邻居羊坊路店","address":"海淀区羊坊店路3号"},
            {"shop_code":155,"shop_name":"好邻居索家坟店","address":"海淀区积水潭桥往西400米,远洋风景往北300米路东"},
            {"shop_code":163,"shop_name":"好邻居北洼路东店","address":"海淀区北洼路42号院大门北侧，首都师大附中东门对面。"},
            {"shop_code":164,"shop_name":"好邻居交大东路店","address":"海淀区北下关广通苑小区四号楼一层，嘉世堂药店旁"},
            {"shop_code":169,"shop_name":"好邻居科学院南路店","address":"海淀区科学院南路55号，中关村中学正对面"},
            {"shop_code":173,"shop_name":"好邻居文慧园店","address":"海淀区文慧园路10号，双汇超市对面"},
            {"shop_code":183,"shop_name":"好邻居厂洼路店","address":"海淀区厂洼小区24号楼，北京电视台西门"},
            {"shop_code":191,"shop_name":"好邻居车道沟店","address":"海淀区车道沟西南角嘉豪国际大厦B座大堂内（周一至周六营业）"},
            {"shop_code":199,"shop_name":"好邻居永定路店","address":"海淀区永定路63号(武警总医院北200米)"},
            {"shop_code":201,"shop_name":"好邻居白石桥店","address":"海淀区中关村南大街4号线国图地铁站东北口"},
            {"shop_code":219,"shop_name":"好邻居中关村南大街店","address":"北京市海淀区中关村大街甲A18号北京国际B1（周一至周六营业）"},
            {"shop_code":225,"shop_name":"好邻居北三环东路店","address":"海淀区中关村南大街四通桥西南角"},
            {"shop_code":234,"shop_name":"好邻居海淀南路店","address":"海淀区通惠寺3号七一棉织厂东一楼东侧"},
            {"shop_code":244,"shop_name":"好邻居蓟门里店","address":"海淀区蓟门里小区北商业楼1幢号平房"},
            {"shop_code":263,"shop_name":"好邻居畅春园店","address":"北京市海淀区西苑草场5号"},
            {"shop_code":272,"shop_name":"好邻居马甸桥店","address":"北京市海淀区北太平庄邮信宿舍9门"},
            {"shop_code":278,"shop_name":"好邻居人大店","address":"北京市海淀区中关村南大街四通桥西北角(人民大学地铁站A1口)"},
            {"shop_code":279,"shop_name":"好邻居理工大学店","address":"北京市海淀区中关村南大街5号102"},
            {"shop_code":292,"shop_name":"好邻居花园路店","address":"北京市海淀区花园路C2号南1号"},
            {"shop_code":299,"shop_name":"好邻居学清农大店","address":"北京市海淀区农大东校区B105"},
            {"shop_code":313,"shop_name":"好邻居北太平庄西店","address":"北太平庄路25号"},
            {"shop_code":315,"shop_name":"好邻居学院南路店","address":"海淀区大柳树路2号"},
            {"shop_code":316,"shop_name":"好邻居紫竹院路店","address":"海淀区紫竹院路车道沟粮店"},
            {"shop_code":320,"shop_name":"好邻居学知轩店","address":"海淀区学清路16号学知轩一层西侧106号"},
            {"shop_code":334,"shop_name":"苏州街五分店","address":"海淀区苏州街工商局一层苏州街工商店对面偏南"},
            {"shop_code":341,"shop_name":"四道口路二分店","address":"北京市海淀区四道口路净土寺32号东区41幢一层北部"},
            {"shop_code":344,"shop_name":"好邻居皂君庙店","address":"海淀区皂君庙大钟寺派出所正对面海淀区皂君庙14号院一号楼1层101室"},
            {"shop_code":348,"shop_name":"好邻居清华东三","address":"海淀区农业大学南门东200米路北海淀区清华东路11号2号幢一层西侧"},
            {"shop_code":350,"shop_name":"好邻居大钟寺东路","address":"海淀区大钟寺东路京仪大厦底商海淀区大钟寺东路9号1幢1层101-1"},
            {"shop_code":351,"shop_name":"好邻居板井店","address":"海淀区车道沟桥进入板井路单行路直行300米路北"},
            {"shop_code":379,"shop_name":"好邻居科南二分店","address":"海淀区中关村新科祥园甲2号楼1层03室"},
            {"shop_code":561,"shop_name":"好邻居万泉河店","address":"海淀区万泉河路68号紫金大厦1层"},
            {"shop_code":830,"shop_name":"好邻居苏州街6店","address":"海淀区苏州街19号一层"},
            {"shop_code":562,"shop_name":"电科院超市发店","address":"清河小营电科院旁边超市发","area_code":502,"lat":39.972615,"lon":116.314524,'not_shop':true},
            {"shop_code":562,"shop_name":"西三旗上奥世纪B座","address":"西三旗上奥世纪B座430","area_code":502,"lat":39.972615,"lon":116.314524,'not_shop':true}],
        3:[{"shop_code":102,"shop_name":"好邻居车公庄店","address":"西城区车公庄西口29号楼"},
            {"shop_code":103,"shop_name":"好邻居西四店","address":"西城区西四北大街158号"},
            {"shop_code":109,"shop_name":"好邻居二龙路店","address":"西城区二龙路41号"},
            {"shop_code":110,"shop_name":"好邻居月坛北街店","address":"西城区月坛北街11号楼7号"},
            {"shop_code":114,"shop_name":"好邻居半壁街店","address":"西城区前半壁街35号"},
            {"shop_code":116,"shop_name":"好邻居佟麟阁路店","address":"西城区佟麟阁路91号"},
            {"shop_code":119,"shop_name":"好邻居德内大街店","address":"西城区德内大街232号"},
            {"shop_code":121,"shop_name":"好邻居甘家口店","address":"西城区阜外大街44号"},
            {"shop_code":138,"shop_name":"北滨河店","address":"西城区北滨河路2号院"},
            {"shop_code":154,"shop_name":"新南店","address":"西城区新街口南大街48号(新街口饭店往南200米路东)"},
            {"shop_code":170,"shop_name":"好邻居百万庄店","address":"西城区百万庄大街31号院1号楼1门1层2号"},
            {"shop_code":176,"shop_name":"好邻居阜外大街店","address":"西城区阜外大街37号"},
            {"shop_code":211,"shop_name":"好邻居西四东店","address":"西城区西安门大街152号"},
            {"shop_code":222,"shop_name":"好邻居丰盛店","address":"西城区西四南大街111号"},
            {"shop_code":223,"shop_name":"英蓝国际店","address":"北京市西城区金融大街7号英蓝国际金融中心B120（周一至周六营业）"},
            {"shop_code":260,"shop_name":"好邻居棉花胡同北口店","address":"西城区新街口东街53号4幢号楼房一层"},
            {"shop_code":261,"shop_name":"好邻居西四北大街店","address":"西城区西四北大街5号平房"},
            {"shop_code":270,"shop_name":"好邻居月坛北桥店","address":"北京市西城区阜成门南大街9号楼"},
            {"shop_code":280,"shop_name":"好邻居什刹海店","address":"北京市西城区地安门西大街47号5号房"},
            {"shop_code":296,"shop_name":"好邻居西直门北店","address":"北京市海淀区西直门北大街47号院2号楼北侧一层"},
            {"shop_code":305,"shop_name":"好邻居前门西大街店西","address":"西城区前门西大街57号"},
            {"shop_code":306,"shop_name":"好邻居西四东街店","address":"西城区西四东大街62号"},
            {"shop_code":308,"shop_name":"好邻居德胜东口店","address":"西城区德胜门内大街6号"},
            {"shop_code":314,"shop_name":"好邻居积水潭北店","address":"西城区新街口外大街28-13"},
            {"shop_code":338,"shop_name":"好邻居中行店","address":"西城区复兴门内大街1号 （非中行员工无法进入，若非中行员工请选择邻近的民丰店） "},
            {"shop_code":401,"shop_name":"好邻居榆树馆店","address":"西城区榆树馆15号"},
            {"shop_code":403,"shop_name":"好邻居灵境胡同店","address":"西城区灵镜胡同1号楼"},
            {"shop_code":404,"shop_name":"好邻居月坛南街店","address":"西城区月坛南街甲1号"},
            {"shop_code":405,"shop_name":"好邻居西什库店","address":"西城区西什库大街24号"},
            {"shop_code":406,"shop_name":"好邻居鼓楼西店","address":"西城区鼓楼大街93号"},
            {"shop_code":409,"shop_name":"好邻居北新华街店","address":"西城区北新华街88号"},
            {"shop_code":410,"shop_name":"好邻居白云里店","address":"西城区白云里丙1号"},
            {"shop_code":411,"shop_name":"好邻居月坛西街店","address":"西城区月坛西街21号"},
            {"shop_code":412,"shop_name":"好邻居德内大街北店","address":"西城区德内大街169号"},
            {"shop_code":413,"shop_name":"好邻居赵登禹路店","address":"西城区赵登禹路148号"},
            {"shop_code":415,"shop_name":"好邻居德胜门店","address":"西城区鼓楼西大街207号"},
            {"shop_code":416,"shop_name":"好邻居复兴门中路店","address":"西城区木樨地25号"},
            {"shop_code":417,"shop_name":"好邻居官园店","address":"西城区官园南里三区1-1"},
            {"shop_code":418,"shop_name":"好邻居辟才胡同店","address":"西城区辟才胡同56号"},
            {"shop_code":419,"shop_name":"好邻居新外荣茂","address":"西城区新外大街12号"},
            {"shop_code":420,"shop_name":"好邻居地安门店","address":"西城区地外大街178号"},
            {"shop_code":421,"shop_name":"好邻居万方园店","address":"西城区葱店胡同2号院1号楼"},
            {"shop_code":422,"shop_name":"好邻居西便门店","address":"西城区复兴门南大街1号"},
            {"shop_code":423,"shop_name":"好邻居三里河店","address":"西城区月坛南街37号"},
            {"shop_code":508,"shop_name":"木樨地店J","address":"西城区复兴门外大街甲22号"},
            {"shop_code":558,"shop_name":"好邻居总政店","address":"西城区黄寺大街总政大院东门"}],
        4:[{"shop_code":148,"shop_name":"好邻居北沙滩店","address":"朝阳区大屯路风林绿洲小区6号楼底商D单元S-F06-01D"},
            {"shop_code":150,"shop_name":"好邻居雅宝路店","address":"朝阳区外交部南街8号，京华豪园南座213-C。"},
            {"shop_code":184,"shop_name":"好邻居红领巾桥店","address":"朝阳区延静东里甲3号商务学院综合楼一层"},
            {"shop_code":185,"shop_name":"好邻居花家地店","address":"朝阳区望京中环南路5号"},
            {"shop_code":218,"shop_name":"好邻居广渠路店","address":"朝阳区广渠东路33号沿海赛洛城底商"},
            {"shop_code":237,"shop_name":"好邻居定福庄店","address":"朝阳区定福庄北街7号楼西侧平房，北京文教用品厂内"},
            {"shop_code":239,"shop_name":"好邻居百子湾三店","address":"朝阳区百子湾路16号百子园14号楼B门101号"},
            {"shop_code":243,"shop_name":"团结湖店","address":"朝阳区朝阳北路219号"},
            {"shop_code":249,"shop_name":"好邻居广顺桥南店","address":"朝阳区利泽中一路1号望京科技大厦商铺"},
            {"shop_code":262,"shop_name":"百子湾5店","address":"朝阳区百子湾路16号百子园4号楼一层C单元101室"},
            {"shop_code":275,"shop_name":"好邻居丽都店","address":"北京市朝阳区高家园小区311号"},
            {"shop_code":277,"shop_name":"好邻居东土城2店","address":"北京市朝阳区东土城路13号院1号楼"},
            {"shop_code":284,"shop_name":"好邻居麦子西街店","address":"朝阳区枣营北里38号楼一层104"},
            {"shop_code":286,"shop_name":"好邻居财满街店","address":"朝阳区朝阳路69号楼1-1-1（5）号"},
            {"shop_code":291,"shop_name":"好邻居酒仙桥路店","address":"北京市朝阳区酒仙桥路26号院1号楼A05号"},
            {"shop_code":293,"shop_name":"好邻居广泽果岭店","address":"北京市朝阳区广泽路6号院13号楼"},
            {"shop_code":294,"shop_name":"好邻居广顺北店","address":"北京市朝阳区利泽西园102号楼"},
            {"shop_code":297,"shop_name":"好邻居鼓外黄寺","address":"朝阳区安外黄寺大街3号院"},
            {"shop_code":298,"shop_name":"好邻居慧忠北路店","address":"北京市朝阳区慧忠北路慧忠里231楼鼓浪屿会所一层底商"},
            {"shop_code":303,"shop_name":"好邻居外经贸店","address":"朝阳区太阳宫乡芍药居村甲3号"},
            {"shop_code":309,"shop_name":"好邻居安苑小关店","address":"朝阳区小关北街43号平房"},
            {"shop_code":321,"shop_name":"好邻居工体南路店","address":"北京市朝阳区工体南路朝阳医院西侧"},
            {"shop_code":322,"shop_name":"好邻居七圣路分店","address":"北京市朝阳区光熙门北里甲31号"},
            {"shop_code":326,"shop_name":"好邻居永安里中街店","address":"朝阳区建国门外大街永安里中街25号"},
            {"shop_code":332,"shop_name":"左家庄店","address":"北京市朝阳区左家庄东里14号楼院"},
            {"shop_code":343,"shop_name":"管庄分店","address":"朝阳区朝阳路管庄西里65号"},
            {"shop_code":502,"shop_name":"光华桥店","address":"朝阳区光华路7号（周一至周五营业）"},
            {"shop_code":542,"shop_name":"好邻居甜怡霖超市","address":"朝阳区安华西里一区21号楼东侧底商社区服务中心旁甜怡霖超市"}],
        5:[{"shop_code":161,"shop_name":"西站南路店","address":"丰台区西客站南路8号南广场往南过红绿灯直行300米路东","area_code":506,"lat":39.893183,"lon":116.327939},
            {"shop_code":346,"shop_name":"刘家窑南里分店","address":"丰台区刘家窑路丰台区刘家窑南里甲一号","area_code":506,"lat":39.868292,"lon":116.423535}],
        6:[{"shop_code":229,"shop_name":"都市馨园店","address":"崇文区兴隆都市馨园地上一层A101","area_code":504,"lat":39.901802,"lon":116.42174}],
        7:[{"shop_code":208,"shop_name":"永安路店","address":"宣武区永安路104号G间","area_code":505,"lat":39.89244,"lon":116.394263},
            {"shop_code":258,"shop_name":"广义街店","address":"宣武区广安门内大街311号院2号楼祥龙大厦首层","area_code":505,"lat":39.896673,"lon":116.363458},
            {"shop_code":221,"shop_name":"友谊医院店","address":"北京市宣武区永安路32号","area_code":505,"lat":39.892503,"lon":116.401073},
            {"shop_code":302,"shop_name":"鸭子桥北店","address":"宣武区南滨河31号","area_code":505,"lat":39.88646,"lon":116.355091}],
        8:[
            {"shop_code":999,"shop_name":"万科小区","address":"昌平县城万科城东门世纪联华超市蔬菜水果铺","area_code":505,"lat":40.218246,"lon":116.243044,"not_shop":true},
            {"shop_code":999,"shop_name":"佳莲小区","address":"昌平区中山口路21号底商万姐商店	","area_code":505,"lat":40.23553,"lon":116.247147,"not_shop":true},
            {"shop_code":999,"shop_name":"国泰清秀园阳光","address":"昌平区国泰商场西侧胡同走100m","area_code":505,"lat":40.226406,"lon":116.25076,"not_shop":true},
            {"shop_code":999,"shop_name":"宁馨苑畅春阁","address":"昌平区宁馨苑小区北门往里50m路东","area_code":505,"lat":40.226,"lon":116.266018,"not_shop":true},
            {"shop_code":999,"shop_name":"西关三角地","address":"昌平区西关三角地政府街西路南侧","area_code":505,"lat":40.226227,"lon":116.229773,"not_shop":true},

            {"shop_code":999,"shop_name":"望都新地","address":"昌平区望都新地小区","area_code":505,"lat":40.118888,"lon":116.421711,"not_shop":true},
            {"shop_code":999,"shop_name":"领秀小区","address":"昌平区领秀超市","area_code":505,"lat":40.103176,"lon":116.309779,"not_shop":true},
            {"shop_code":999,"shop_name":"拓然佳苑小区","address":"昌平区拓然家苑小区","area_code":505,"lat":40.205174,"lon":116.243894,"not_shop":true}
        ]
    };
    var getShipAddress = function(areaId){
        return ship_address[areaId];
    };
    return {
        getBeijingAreas: beijingArea,
        getShipAddress: getShipAddress
    }
}();