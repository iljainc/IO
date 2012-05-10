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
