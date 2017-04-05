<!DOCTYPE html>
<html>

    <head>

        <title> Kaltura Portal Upload Scripts </title>

        <style>
            body { padding: 30px }
            .progress { position:relative; width:400px; border: 1px solid #ddd; padding: 1px; border-radius: 3px; margin-left: auto; margin-right: auto; text-align: center;}
            #uploadProgress {margin-left: auto; margin-right: auto; text-align: center;}
            .bar { background-color: #B4F5B4; width:0%; height:20px; border-radius: 3px; }
            .percent { position:absolute; display:inline-block; top:3px; left:48%; }
            #status{margin-left: auto; margin-right: auto; text-align: center;}
            #uploadData{margin-left: auto; margin-right: auto; text-align: center;}
            #upload {
                -moz-box-shadow:inset 0px 0px 0px 0px #ffffff;
                -webkit-box-shadow:inset 0px 0px 0px 0px #ffffff;
                box-shadow:inset 0px 0px 0px 0px #ffffff;
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #fffcff), color-stop(1, #edeaed) );
                background:-moz-linear-gradient( center top, #fffcff 5%, #edeaed 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fffcff', endColorstr='#edeaed');
                background-color:#fffcff;
                -moz-border-radius:6px;
                -webkit-border-radius:6px;
                border-radius:6px;
                border:1px solid #949494;
                display:inline-block;
                color:#404040;
                font-family:Arial;
                font-size:13px;
                font-weight:bold;
                padding:3px 12px;
                text-decoration:none;
                text-shadow:1px 1px 0px #ffffff;
                margin-right: 5px;
                width: 76px;
            }
            #upload:hover {
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #edeaed), color-stop(1, #fffcff) );
                background:-moz-linear-gradient( center top, #edeaed 5%, #fffcff 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#edeaed', endColorstr='#fffcff');
                background-color:#edeaed;
            }
            #upload:active {
                position:relative;
                top:1px;
            }
            .validate-error {
                border-color: #e9322d !important;
                box-shadow: 0 0 6px #f8b9b7 !important;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="/css/tooltipster.css" />
        <script src="/js/jquery.min.js" type="text/javascript"></script>
        <script src="/js/jquery.form.js" type="text/javascript"></script>
        <script src="/js/jquery.validate.min.js" type="text/javascript"></script>
        <script src="/js/jquery.tooltipster.min.js" type="text/javascript"></script>
        <script type="text/javascript">	

            var flavorAssetId;

            var uploadToken;
            
            var validator;
            
            var ks = "<?php echo $_GET['ks']; ?>";
            
            function getFileName() {

                var fileName = document.getElementById("inputFile").value;

                if (fileName.indexOf("\\") != -1) {

                    var subStr = fileName.split("\\");

                    return subStr[subStr.length -1];

                }

                else if (fileName.indexOf("/") != -1) {

                    var subStr = fileName.split("/");

                    return subStr[subStr.length -1];

                }

                else return fileName;

            }

	

            function addFlavorAsset(){

		

                var sessData = {

                    ks: ks,

                    entryId:"<?php echo $_GET['eid']; ?>",

                    "flavorAsset:flavorParamsId":"<?php echo $_GET['fid']; ?>",
                    
                    "flavorAsset:objectType":"KalturaFlavorAsset"

                };

		

                $.ajax({

                    cache: 			false,

                    url:"/api_v3/index.php?service=flavorAsset&action=add&format=1",

                    type:			'GET',

                    data:			sessData,

                    dataType:		'json',

                    async: false,

                    success:function(data) {

                        flavorAssetId = data["id"];

                        //alert("Media Entry id: " + mediaEntryId);

                    },

                    error: function(xhr, textStatus, errorThrown) {

                        alert(errorThrown + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + textStatus);

                    }

                });    

            }

	

            function addUploadToken(){

                var sessData = {

                    ks: ks

                };

		

                $.ajax({

                    cache: 			false,

                    url:"/api_v3/index.php?service=uploadtoken&action=add&format=1",

                    type:			'GET',

                    data:			sessData,

                    dataType:		'json',

                    async: false,

                    success:function(data) {

                        uploadToken = data["id"];

                        //alert("UploadToken: " + uploadToken);

                    },

                    error: function(xhr, textStatus, errorThrown) {

                        alert(errorThrown + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + textStatus);

                    }

                });    

            }

	

            function addFlavorContent(){

                var sessData = {

                    ks: ks,

                    "contentResource:token":uploadToken,
                    
                    "contentResource:objectType":"KalturaUploadedFileTokenResource",

                    id:flavorAssetId

                };

		

                $.ajax({

                    cache: 			false,

                    url:"/api_v3/index.php?service=flavorAsset&action=setContent&format=1",

                    type:			'GET',

                    data:			sessData,

                    dataType:		'json',

                    async: false,

                    success:function(data) {

                        var id = data["id"];

                        //alert("AddContent:Media " + id);

                        document.getElementById("smhUploadToken").value=uploadToken;

                        document.getElementById("smhKs").value=ks;

                    },

                    error: function(xhr, textStatus, errorThrown) {

                        alert(errorThrown + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText + textStatus);

                    }

                });    

            }
            
            var BrowserDetect = {

                init: function () {

                    this.browser = this.searchString(this.dataBrowser) || "An unknown browser";

                    this.version = this.searchVersion(navigator.userAgent)

                        || this.searchVersion(navigator.appVersion)

                        || "an unknown version";

                    this.OS = this.searchString(this.dataOS) || "an unknown OS";

                },

                searchString: function (data) {

                    for (var i=0;i<data.length;i++)	{

                        var dataString = data[i].string;

                        var dataProp = data[i].prop;

                        this.versionSearchString = data[i].versionSearch || data[i].identity;

                        if (dataString) {

                            if (dataString.indexOf(data[i].subString) != -1)

                            return data[i].identity;

                    }

                    else if (dataProp)

                    return data[i].identity;

            }

        },

        searchVersion: function (dataString) {

            var index = dataString.indexOf(this.versionSearchString);

            if (index == -1) return;

            return parseFloat(dataString.substring(index+this.versionSearchString.length+1));

        },

        dataBrowser: [

            {

                string: navigator.userAgent,

                subString: "Chrome",

                identity: "Chrome"

            },

            { 	string: navigator.userAgent,

                subString: "OmniWeb",

                versionSearch: "OmniWeb/",

                identity: "OmniWeb"

            },

            {

                string: navigator.vendor,

                subString: "Apple",

                identity: "Safari",

                versionSearch: "Version"

            },

            {

                prop: window.opera,

                identity: "Opera",

                versionSearch: "Version"

            },

            {

                string: navigator.vendor,

                subString: "iCab",

                identity: "iCab"

            },

            {

                string: navigator.vendor,

                subString: "KDE",

                identity: "Konqueror"

            },

            {

                string: navigator.userAgent,

                subString: "Firefox",

                identity: "Firefox"

            },

            {

                string: navigator.vendor,

                subString: "Camino",

                identity: "Camino"

            },

            {		// for newer Netscapes (6+)

                string: navigator.userAgent,

                subString: "Netscape",

                identity: "Netscape"

            },

            {

                string: navigator.userAgent,

                subString: "MSIE",

                identity: "Explorer",

                versionSearch: "MSIE"

            },

            {

                string: navigator.userAgent,

                subString: "Gecko",

                identity: "Mozilla",

                versionSearch: "rv"

            },

            { 		// for older Netscapes (4-)

                string: navigator.userAgent,

                subString: "Mozilla",

                identity: "Netscape",

                versionSearch: "Mozilla"

            }

        ],

        dataOS : [

            {

                string: navigator.platform,

                subString: "Win",

                identity: "Windows"

            },

            {

                string: navigator.platform,

                subString: "Mac",

                identity: "Mac"

            },

            {

                string: navigator.userAgent,

                subString: "iPhone",

                identity: "iPhone/iPod"

            },

            {

                string: navigator.platform,

                subString: "Linux",

                identity: "Linux"

            }

        ]



    };

    BrowserDetect.init();

        </script>



    </head>



    <body>



        <div id="uploadData">
            <form id="smhUploadForm" action="/api_v3/index.php?service=uploadtoken&action=upload" enctype="multipart/form-data" method="POST" onsubmit="return doUpload()" >

                <input type="hidden" id="smhKs" name="ks">

                <input type="hidden" id="smhUploadToken" name="uploadTokenId" >

                <label for="fileData">File to upload: &nbsp </label>

                <input type="file" style="width: 300px; border: 1px solid #CDCDCD; margin-right: 10px; margin-bottom:10px" name="fileData" id="inputFile"/>

                <input type="submit"  id="upload" value="Upload" />

            </form>

        </div>

        <script>
    $('#smhUploadForm input[type="file"]').tooltipster({
        trigger: 'custom',
        onlyOne: false,
        position: 'top'
    });
    
    validator = $("#smhUploadForm").validate({
        highlight: function(element, errorClass) {
            $(element).removeClass("valid").removeClass("error").addClass("validate-error");
        },        
        unhighlight: function(element, errorClass) {
            $(element).removeClass("valid").removeClass("validate-error");
        },
        errorPlacement: function (error, element) {
            $(element).tooltipster('update', $(error).text());
            $(element).tooltipster('show');
        },
        success: function (label, element) {
            $(element).tooltipster('hide');
        },
        rules:{
            fileData:{
                required: true
            }
        },
        messages: {
            fileData:{
                required: "Error: No file selected"
            }
        }
    });    
    
    function doUpload() {

        var valid = validator.form();
        if(valid){
            addFlavorAsset();

            addUploadToken();

            addFlavorContent();

            if (BrowserDetect.browser == "Explorer" && BrowserDetect.version < 10) {

                //alert("IE version less than 10 detected

                document.getElementById("uploadProgress").innerHTML="<img src='/img/busy.gif' /> <br/> File: \"" + getFileName() + "\" is being uploaded, please wait...";

            }

            return true;                  
        }
        // End of testing code

    }         
        </script>



        <span id="uploadProgress">
            <div class="progress">

                <div class="bar"></div >

                <div class="percent">0%</div >

            </div>

            <div id="status"></div>
        </span>



        <script>            
    (function() {

        var bar = $('.bar');

        var percent = $('.percent');

        var status = $('#status');

        var percentVal=0;

  

        $('#smhUploadForm').ajaxForm({

            beforeSend: function() {

                status.empty();

                var percentVal = '0%';

                bar.width(percentVal)

                percent.html(percentVal);

            },

            uploadProgress: function(event, position, total, percentComplete) {

                percentVal = percentComplete + '%';

                bar.width(percentVal)

                percent.html(percentVal);

            },

            complete: function(xhr) {

                if (BrowserDetect.browser == "Explorer" && BrowserDetect.version < 10) {

                    document.getElementById("uploadProgress").innerHTML="<p> Uploading of file: \"" + getFileName() + "\" has completed! </p>";

                    return;

                }

                percentVal = 100 + '%';

                bar.width(percentVal)

                percent.html(percentVal);

                status.html("Uploading of file: \"" + getFileName() + "\" is completed.");

                //status.html(xhr.responseText);

            }

        });  



    })();       

        </script>
    </body>
</html>

