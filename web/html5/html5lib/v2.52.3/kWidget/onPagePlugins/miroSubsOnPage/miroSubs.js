var subtitles;

$(document).on('click','.mirosubs-modal-widget-title-close',function(){
    $('#smh-modal').css('z-index','2000');
    $('#smh-modal').css('display','block');
});

function showEditor(){                        
    $('#smh-modal').css('z-index','2');
    $('#smh-modal').css('display','none');
    $('#mirosubsAPI').remove();
    $('head').append('<script src="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/miroSubsOnPage/mirosubs/mirosubs-api.min.js" id="mirosubsAPI" type="text/javascript"><\/script>');
    subtitles = [];
    loadAndDisplayEditor();
}

function loadAndDisplayEditor(){
    var _this = this;
    _this.mirosubs = mirosubs.api.openDialog( getMiroConfig() );
}

function getMiroConfig(){
    var _this = this;
    return {
        'username' : 'userName',
        'subtitles': getSubsInMiroFormat(),
        'status' : 'ok',
        'closeListener': function(){
        },
        'videoURL' : 'https://mediaplatform.streamingmediahosting.com/p/'+sessInfo.pid+'/sp/'+sessInfo.pid+'00/playManifest/entryId/'+cap_e+'/flavorId/'+cap_flavor+'/format/url/protocol/http/a.'+cap_ex,
        'save': function( miroSubs, doneSaveCallback, cancelCallback) {
            var srtText = _this.miroSubs2Srt( miroSubs );
            _this.saveSrtText(srtText);
            doneSaveCallback();
            _this.mirosubs.close();
        },
        'mediaURL': _this.getBasePath() + '/media/',
        'permalink': 'http://commons.wikimedia.org',
        // not sure if this is needed
        'login': function( ){
            mirosubs.api.loggedIn( 'user name' );
        },
        'embedCode' : 'some code to embed'
    };
}

function getBasePath(){
    return 'https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/miroSubsOnPage/mirosubs/';
}
        
function invokeEditor(){
    $('#smh-modal').css('z-index','2');
    $('#smh-modal').css('display','none');
    $('#mirosubsAPI').remove();
    $('head').append('<script src="/html5/html5lib/v2.52.3/kWidget/onPagePlugins/miroSubsOnPage/mirosubs/mirosubs-api.min.js" id="mirosubsAPI" type="text/javascript"><\/script>');
    if(cap_url == null || cap_url == ''){
        subtitles = [];
    } else{
        var cap_arr = new Array();
        var sub = new Object();
        ;
        $.ajax({
            cache:  false,
            url:    cap_url,
            async:  false,
            type:   'GET',
            dataType:   'text',
            success:function(data) {
                var cap = $.trim(data).replace(/\s{4,}/g, "##").split('##');
                $.each(cap, function(key, value) {
                    var capline = value.split('\n');
                    var captime = capline[1].split(' --> ');
                    var captext = '';
                    for(var i = 2; i < capline.length; i++){
                        captext += capline[i]+' ';
                    }
                    sub = {
                        'subtitle_id':'sub_'+capline[0],
                        'text':captext,
                        'sub_order': capline[0],
                        'start_time': npt2seconds(captime[0]),
                        'end_time': npt2seconds(captime[1])
                    };
                    cap_arr.push(sub);
                });
            }
        });
        subtitles = cap_arr;
        loadAndDisplayEditor(); 
    }                
}

function getSubsInMiroFormat(){
    return subtitles;
}

function miroSubs2Srt( miroSubs ){
    var srtString = '';
    for(var i =0; i < miroSubs.length ; i ++ ){
        var miroSub = miroSubs[i];
        var startStr = String( this.seconds2npt( miroSub.start_time, true ) ).replace('.',',');
        var endStr = String( this.seconds2npt( miroSub.end_time, true ) ).replace( '.', ',' );
        srtString += miroSub.sub_order + "\n" +
        startStr + ' --> ' + endStr + "\n" +
        miroSub.text + "\n\n";
    }
    return srtString;
}

function npt2seconds(npt_str){
    if(!npt_str){
        return false;
    }
    npt_str=npt_str.replace(/npt:|s/g,'');
    var hour=0;
    var min=0;
    var sec=0;
    times=npt_str.split(':');
    if(times.length==3){
        sec=times[2];
        min=times[1];
        hour=times[0];
    }else if(times.length==2){
        sec=times[1];
        min=times[0];
    }else{
        sec=times[0];
    }
    sec=sec.replace(/,\s?/,'.');
    return parseInt(hour*3600)+parseInt(min*60)+parseFloat(sec);
}

function seconds2npt(sec,show_ms){
    if(isNaN(sec)){
        return'0:00:00';
    }
    var tm=this.seconds2Measurements(sec)
    if(show_ms){
        tm.seconds=Math.round(tm.seconds*1000)/1000;
    }else{
        tm.seconds=Math.round(tm.seconds);
    }
    if(tm.seconds<10)
        tm.seconds='0'+tm.seconds;
    if(tm.minutes<10)
        tm.minutes='0'+tm.minutes;
    return tm.hours+":"+tm.minutes+":"+tm.seconds;
}
        
function seconds2Measurements(sec){
    var tm={};
    
    tm.days=Math.floor(sec/(3600*24))
    tm.hours=Math.floor(sec/3600);
    tm.minutes=Math.floor((sec/60)%60);
    tm.seconds=sec%60;
    return tm;
}

function saveSrtText(srtText){
    var srt_id = Math.floor(new Date().getTime() / 1000);
    var srt_name = 'smh_srt_'+srt_id;
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    document.body.appendChild(iframe);
            
    var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            
    var form = document.createElement('form');
    form.action = '/apps/scripts/srt.php';
    form.method = 'POST';
            
    var srtName = document.createElement('input');
    srtName.type = 'hidden';
    srtName.name = 'name';
    srtName.value = srt_name;
            
    form.appendChild(srtName);
            
    var srtData = document.createElement('input');
    srtData.type = 'hidden';
    srtData.name = 'data';
    srtData.value = srtText;
            
    form.appendChild(srtData); 
            
    (iframeDoc.body || iframeDoc).appendChild(form);
            
    form.submit();
    
    $('#smh-modal').css('z-index','2000');
    $('#smh-modal').css('display','block');
}