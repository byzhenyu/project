//无限下拉菜单
_doc=document;
var Doc={};
Doc.Create=function(tagName){
    return _doc.createElement(tagName);
}
Doc.Append=function(dom){
    _doc.body.appendChild(dom);
}
Doc.Get=function(_id){
    return _doc.getElementById(_id);
}

var data = catListStr;
//无限级 select 分类
var typeCount=0;
function Select(arr,chg){
    //获得子类 集合（select）
    var getSel=function(pid){
        if(typeCount>=3){
            return;
        }
        typeCount+=1;
        var _select=Doc.Create("select");
        _select.style.width="25%";
        _select.style.marginTop="0px";
        _select.style.marginRight="0px";
        _select.style.height="31px";
        _select.style.float='left';
        _select.name="jsname";
        _select.options.add(new Option('-请选择商品分类-',""));
        for(var i=0;i<arr.length;i++){
            if(arr[i].parent_id==pid){
                _select.options.add(new Option("|-"+arr[i].cat_name,arr[i].cat_id));
            }
        }
        var delChildfun=function(obj){

            if(obj.child){
                var _child=obj.child;

                if(_child.parentNode){
                    _child.parentNode.removeChild(_child);
                }
                typeCount-=1;
                delChildfun(_child);
            }
        }

        _select.onchange=function(){
            delChildfun(this);
            this.child = getSel(this.options[this.selectedIndex].value);
            if(this.child){
                chg(this.child);
            }
            $("#goods_cat_id").val(this.options[this.selectedIndex].value);
        }
        return _select;

    }

    //===================获得节点
    var r_arr=[];
    var getPidById=function(id){
        for(var i=0;i<arr.length;i++)
            if(arr[i].cat_id==id) return arr[i].parent_id;

        return "";
    }

    var getSelBySid=function(sid){
        var pid = getPidById(sid);

        var sel = getSel(pid);
        for(var i=0;i<sel.options.length;i++) {
            if(sel.options[i].value==sid) {
                sel.selectedIndex=i; break;
            }
        }

        if(pid>0) getSelBySid(pid);
        r_arr.push(sel);
    }

    this.getDom=function(selectid){
        getSelBySid(selectid||arr[0].cat_id);
        for(var i=0;i<r_arr.length;i++)
            if(i+1<r_arr.length)
                r_arr[i].child=r_arr[i+1];

        return r_arr;
    }
}
var demo1=Doc.Get("goods_cat_id_div");
var chg=function(obj){
    if(obj.options.length>1){
        obj.selectedIndex=0;
        demo1.appendChild(obj);
    }
}

var sel1=new Select(data,chg);
var _arr=sel1.getDom();
for(var i=0;i<_arr.length;i++){
    demo1.appendChild(_arr[i]);
    _arr[i].options[0].selected = true;
}

var ids = cat_ids;
function initCatSelect(){

    for(var i=0;i<ids.length;i++){
        var jsname=document.getElementsByName("jsname");
        var options = jsname[i].options;
        var count=0;
        var id=ids[ids.length-(i+1)];
        for(var j=0;j<options.length;j++){
            if(options[j].value==id){
                count=j;
                break;
            }
        }
        jsname[i].options[count].selected = true;
        jsname[i].onchange();
    }
}