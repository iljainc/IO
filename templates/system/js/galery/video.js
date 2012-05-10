imgNum = 0;

function showVideo() {
    jQuery('#darkBG').css('display', 'block');
    jQuery('#darkBG').css('z-index', '1000');
    
    //fName = imgArrName[imgNum];
    var video = jQuery('#videoCode').html();
    
    var title = jQuery('title').html();
    var width = Math.ceil((jQuery('iframe').attr('width')/2)+30);
    var height = Math.ceil((jQuery('iframe').attr('height')/2)+52);
    
    jQuery('#video').html('<div class="videoImgDiv"> \
                              <div class="videoNavigation"> \
                                  <div class="numFielf">'+title+'</div> \
                                  <a href="javascript:closeVideo();" class="closeImg"> \
                                      <img src="/templates/system/js/galery/img/close.png" /> \
                                  </a>\
                              </div> \
                              <div class="videoCenter">'+video+'</div> \
                           </div>');

    jQuery('#video .videoImgDiv').css('margin-left', '-'+width+'px');
    jQuery('#video .videoImgDiv').css('margin-top', '-'+height+'px');
    jQuery('#video .videoRight').css('z-index', '1002');
    jQuery('#video .videoLeft').css('z-index', '1003');
    jQuery('#video .videoCenter').css('z-index', '1003');
    jQuery('#video .videoImgDiv').css('z-index', '1010');
    jQuery('#video .videoNavigation').css('z-index', '1020');
};

function closeVideo() {
    jQuery('#darkBG').css('display', 'none');
        jQuery('#video').html('');
};

jQuery(document).ready(function(){
    jQuery("#darkBG").click(function () {closeVideo(); });
});