/**
 * Created by shichaopeng on 5/21/15.
 */

var table = document.getElementsByName("order-data-table");

if(table.length>1){
    var i;
    for (i = 0; i < table.length; i++) {
        new Tablesort(table[i]);
    }
}else{
    var sort = new Tablesort(table[0]);
}
