function tamingselect()
{
    if(!document.getElementById && !document.createTextNode){return;}

// Classes for the link and the visible dropdown
    var ts_selectclass='product-count'; 	// class to identify selects
//	var ts_selectclass='turnintodropdown_demo2'; 	// class to identify selects
    var ts_listclass='turnintoselect_demo2';		// class to identify ULs
    var ts_boxclass='dropcontainer_demo2'; 		// parent element
    var ts_triggeron='activetrigger_demo2'; 		// class for the active trigger link
    var ts_triggeroff='trigger_demo2';			// class for the inactive trigger link
    var ts_dropdownclosed='dropdownhidden_demo2'; // closed dropdown
    var ts_dropdownopen='dropdownvisible_demo2';	// open dropdown
    /*
     Turn all selects into DOM dropdowns
     */
    var count=0;
    var toreplace=new Array();
    var sels=document.getElementsByTagName('select');
    // console.log(sels);
    for(var i=0;i<sels.length;i++){
        if (ts_check(sels[i],ts_selectclass))
        {
            var hiddenfield=document.createElement('input');
            hiddenfield.name=sels[i].name;
            hiddenfield.type='hidden';
            hiddenfield.id=sels[i].id;
            hiddenfield.setAttribute('data-type',sels[i].getAttribute('data-type'));
            hiddenfield.setAttribute('data-id',sels[i].getAttribute('data-id'));

            if(sels[i].getAttribute('data-measure') != null){
                hiddenfield.setAttribute('data-measure',sels[i].getAttribute('data-measure'));
            }

            if(sels[i].getAttribute('readonly') != null) {
                hiddenfield.setAttribute('readonly', sels[i].getAttribute('readonly'));
            }

            if(sels[i].getAttribute('data-count') == null){
                hiddenfield.value=sels[i].options[1].value;
            }else{
                hiddenfield.value=sels[i].getAttribute('data-count');
            }

            sels[i].parentNode.insertBefore(hiddenfield,sels[i])
            var trigger=document.createElement('a');
            ts_addclass(trigger,ts_triggeroff);
            ts_addclass(trigger,'select-header');
            trigger.href='#';
            trigger.onclick=function(){
                ts_swapclass(this,ts_triggeroff,ts_triggeron)
                ts_swapclass(this.parentNode.getElementsByTagName('ul')[0],ts_dropdownclosed,ts_dropdownopen);
                return false;
            }

            // trigger.appendChild(document.createTextNode(sels[i].options[1].text));
            trigger.appendChild(document.createTextNode(hiddenfield.value));
            sels[i].parentNode.insertBefore(trigger,sels[i]);
            var replaceUL=document.createElement('ul');
            for(var j=0;j<sels[i].getElementsByTagName('option').length;j++)
            {
                var newli=document.createElement('li');
                var newa=document.createElement('a');
                newli.v=sels[i].getElementsByTagName('option')[j].value;
                newli.elm=hiddenfield;
                newli.istrigger=trigger;
                newa.href='#';
                newa.appendChild(document.createTextNode(
                    sels[i].getElementsByTagName('option')[j].text));
                newli.onclick=function(){
                    this.elm.value=this.v;
                    ts_swapclass(this.istrigger,ts_triggeron,ts_triggeroff);
                    ts_swapclass(this.parentNode,ts_dropdownopen,ts_dropdownclosed)
                    this.istrigger.firstChild.nodeValue=this.firstChild.firstChild.nodeValue;
                    return false;
                }
                newli.appendChild(newa);
                replaceUL.appendChild(newli);
            }
            ts_addclass(replaceUL,ts_dropdownclosed);
            ts_addclass(replaceUL,'ul-dropdownselect');
            var div=document.createElement('div');
            div.appendChild(replaceUL);
            ts_addclass(div,ts_boxclass);
            sels[i].parentNode.insertBefore(div,sels[i])
            toreplace[count]=sels[i];
            count++;
        }
    }

    /*
     Turn all ULs with the class defined above into dropdown navigations
     */

    var uls=document.getElementsByTagName('ul');
    for(var i=0;i<uls.length;i++)
    {
        if(ts_check(uls[i],ts_listclass))
        {
            var newform=document.createElement('form');
            var newselect=document.createElement('select');
            for(j=0;j<uls[i].getElementsByTagName('a').length;j++)
            {
                var newopt=document.createElement('option');
                newopt.value=uls[i].getElementsByTagName('a')[j].href;
                newopt.appendChild(document.createTextNode(uls[i].getElementsByTagName('a')[j].innerHTML));
                newselect.appendChild(newopt);
            }
            newselect.onchange=function()
            {
                window.location=this.options[this.selectedIndex].value;
            }
            newform.appendChild(newselect);
            uls[i].parentNode.insertBefore(newform,uls[i]);
            toreplace[count]=uls[i];
            count++;
        }
    }
    for(i=0;i<count;i++){
        toreplace[i].parentNode.removeChild(toreplace[i]);
    }
    function ts_check(o,c)
    {
        return new RegExp('\\b'+c+'\\b').test(o.className);
    }
    function ts_swapclass(o,c1,c2)
    {
        var cn=o.className
        o.className=!ts_check(o,c1)?cn.replace(c2,c1):cn.replace(c1,c2);
    }
    function ts_addclass(o,c)
    {
        if(!ts_check(o,c)){o.className+=o.className==''?c:' '+c;}
    }
    $(document).click(function (e) {

        if ($(e.target).closest(".select-header").length) return;
        if ($(e.target).closest(".ul-dropdownselect").length) return;

        var s_h = $('.select-header');
        var u_d = $('.ul-dropdownselect');
        s_h.attr('class','trigger_demo2 select-header');
        u_d.attr('class','dropdownhidden_demo2 ul-dropdownselect');
        e.stopPropagation();

    });
}

window.onload=function()
{
    tamingselect();
    // add more functions if necessary
}

// $(function($){
//
// });
