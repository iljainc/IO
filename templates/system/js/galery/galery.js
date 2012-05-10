var imgNum = 0;
var imgArrName = new Array();
var imgArrPath = new Array();
var imgArrCount = 0;
function showGalery(setImgNum) {
    imgNum = setImgNum;
    
    jQuery('#darkBG').css('display', 'block');
    jQuery('#darkBG').css('z-index', '1000');
    
    fName = imgArrName[imgNum];
    fPath = imgArrPath[imgNum];
    
    var title = jQuery('title').html();
    //var alt = jQuery("a[rel^='gal']").attr("alt");
    
    jQuery('#galery').html('<div class="galeryImgDiv"> \
                                <div class="galeryNavigation"> \
                                    <div class="galTop"> \
                                        <span>'+title+'</span> \
                                    </div> \
                                    <a href="javascript:closeGalery();" class="closeImg"> \
                                        <img src="/templates/system/js/galery/img/close.png" />\n\
                                    </a> \
                                </div> \
                                <div class="galeryLeft"> \
                                    <a href="javascript:showGaleryPrew();" class="aimL"> \
                                        <img src="/templates/system/js/galery/img/aimL.png" /> \
                                    </a> \
                                </div> \
                                <div class="galeryCenter"> \
                                    <a href="javascript:showGaleryNext()"> \
                                        <img src="'+fPath+fName+'" class="galeryImg" /> \
                                    </a> \
                                </div> \
                                <div class="galeryRight"> \
                                    <a href="javascript:showGaleryNext();" class="aimR"> \
                                        <img src="/templates/system/js/galery/img/aimR.png" /> \
                                    </a> \
                                </div> \
                                <div style="clear: both"></div> \
                                <div class="numFielf"> \
                                        <span class="num">'+(imgNum+1)+'</span>/'+imgArrName.length+' \
                                </div> \
                            </div>');
  
    if (imgArrName.length == 1) {
      jQuery('.aimL').css('display', 'none'); 
      jQuery('.aimR').css('display', 'none');
    };
  
    jQuery('#galery .ggalleryBlock').css('z-index', '1001');
    jQuery('#galery .galeryRight').css('z-index', '1002');
    jQuery('#galery .galeryLeft').css('z-index', '1003');
    jQuery('#galery .galeryCenter').css('z-index', '1003');
    jQuery('#galery .galeryImgDiv').css('z-index', '1010');
    jQuery('#galery .galeryNavigation').css('z-index', '1020');
};

function showGaleryNext() { 
    nextImgNum = imgNum+1;
    if (nextImgNum == imgArrName.length) nextImgNum = 0;

    showGalery(nextImgNum);
};

function showGaleryPrew() {
    prevImgNum = imgNum-1;
    if (prevImgNum == -1) prevImgNum = imgArrName.length - 1;

    showGalery(prevImgNum);
};

function closeGalery() {
    jQuery('#darkBG').css('display', 'none');
        jQuery('#galery').html('');
};

jQuery(document).ready(function(){
    jQuery("#darkBG").click(function () {closeGalery(); });
});