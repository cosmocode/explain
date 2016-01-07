/**
 * Handle Explain plugin popups
 */
function plugin_explain(obj,id,text){
    var $obj = jQuery(obj);
    var ident = 'plg_explain__'+id;
    var div = document.getElementById(ident);
    if(!div){
        div = document.createElement('div');
        div.className = 'dokuwiki insitu-footnote JSpopup';
        div.textContent = text;
        div.innerText = text;
        div.style.position = 'absolute';
        div.style.left = ($obj.offset().left + 15)+'px';
        div.style.top = ($obj.offset().top + 15)+'px';
        div.style.zIndex = 100;
        div.id = 'plg_explain__'+id;
        document.body.appendChild(div);

        $obj.mouseout(closePopups);
    }
    div.style.display = 'block';
}
