function confirmRedirect(q, url){
    if (confirm(q)){
        window.location=url;
    };
};


function f_display(OBJ, Derection)
{
    objID=OBJ;
    if(Derection=="")
    {
            if(document.getElementById(objID).style.display=="none")document.getElementById(objID).style.display="block";
            else document.getElementById(objID).style.display="none";
    }
    else
    {
            if(document.getElementById(objID).style.display=="none")document.getElementById(objID).style.display=Derection;
            else document.getElementById(objID).style.display="none";
    }
};

function JQDisplay(obj, Derection) {

    if (Derection == '') {
        $('#'+obj).slideToggle('fast', function() {
        // Animation complete.
        });
    } else {
        $('#'+obj).css('display', Derection);
    }
}

function CSSdisplay(obj, derection) {
    display = $('#'+obj).css('display');
    if (derection == '') derection = 'block';


    if (display == 'none') $('#'+obj).css('display', derection);
    else $('#'+obj).css('display', 'none');
}

function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
} 