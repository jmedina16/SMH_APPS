/*
 *
 *	Streaming Media Hosting
 *	
 *	Upload
 *
 *	9-01-2015
 */
//Main constructor
function Upload(pid, ks) {
    this.pid = pid;
    this.ks = ks;
}

//Global variables
var categories = {};
var cats = [];
var ac = {};
var trans_profiles = {};
var template = {};
var data = {};
var fileNumber = 0;

//Upload prototype/class
Upload.prototype = {
    constructor: Upload,
    formatFileSize: function (bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }
        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }
        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }
        return (bytes / 1000).toFixed(2) + ' KB';
    },
    init_fileupload: function () {
        var maxFiles = 10;
        // Initialize the jQuery File Upload widget:
        $('#fileupload').fileupload({
            //maxChunkSize: 1024 * 256,
            //maxChunkSize: 1024 * 128,
            maxChunkSize: 3000000,
            dynamicChunkSizeInitialChunkSize: 1000000,
            dynamicChunkSizeThreshold: 250000000,
            dynamixChunkSizeMaxTime: 30,
            maxRetries: 5,
            retryTimeout: 500,
            multipart: false,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png|bmp|tiff|mp4|flv|f4v|m4v|asf|mov|avi|3gp|ogg|mkv|wmv|wma|webm|mpeg|mpg|m1v|m2v|wav|mp3|aac|flac|ac3)$/i,
            singleFileUploads: false,
            maxNumberOfFiles: 10,
            dropZone: $('#dropzone'),
            add: function (e, data) {
                if (fileNumber > 1) {
                    alert("Please drag one file at a time");
                    return false;
                } else {
                    var fileCount = data.files.length;
                    var numberOfFiles = $(this).fileupload('option').getNumberOfFiles();
                    var filetype = $(this).fileupload('option').acceptFileTypes;
                    if (fileCount > maxFiles || numberOfFiles >= 10) {
                        alert("The max number of files is " + maxFiles);
                        return false;
                    } else if (!filetype.test(data.files[0].name)) {
                        alert('File type not allowed');
                        return false;
                    } else {
                        $('.fileinput-button').css('display', 'inline-block');
                        $('.fileupload-buttonbar .start').css('display', 'inline-block');
                        $('.fileupload-buttonbar .cancel').css('display', 'inline-block');
                        var file, filename, sessionID, sessionName, session;
                        var that = this;
                        file = data.files[0];
                        filename = file.name;
                        sessionName = $.base64.encode(sessInfo.pid).replace(/\+|=|\//g, '');
                        sessionID = $.base64.encode(filename).replace(/\+|=|\//g, '');
                        session = sessionName + sessionID;

                        $.getJSON('/server/php/', {
                            file: session,
                            pid: sessInfo.pid,
                            ks: sessInfo.ks
                        }, function (result) {
                            var file = result.file;
                            data.uploadedBytes = file && file.size;
                            $.blueimp.fileupload.prototype
                                    .options.add.call(that, e, data);
                        });
                    }
                }

            },
            drop: function (e, data) {
                fileNumber = 0;
                $.each(data.files, function (index, file) {
                    fileNumber++;
                });
            },
            fail: function (e, data) {
                if (data.errorThrown == 'abort') {
                    var numberOfFiles = $(this).fileupload('option').getNumberOfFiles();
                    if (numberOfFiles == 1) {
                        $('.fileupload-buttonbar .start').css('display', 'none');
                        $('.fileupload-buttonbar .cancel').css('display', 'none');
                        $('.fileinput-button').css('display', 'inline-block');
                    }
                }

                var file = data.files[0];
                var filename = file.name;
                var sessionName = $.base64.encode(sessInfo.pid).replace(/\+|=|\//g, '');
                var sessionID = $.base64.encode(filename).replace(/\+|=|\//g, '');
                var session = sessionName + sessionID;
                var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload'),
                        retries = data.context.data('retries') || 0,
                        retry = function () {
                            $.getJSON('/server/php/', {
                                file: session,
                                pid: sessInfo.pid,
                                ks: sessInfo.ks
                            })
                                    .done(function (result) {
                                        var file = result.file;
                                        data.uploadedBytes = file && file.size;
                                        // clear the previous data:
                                        data.data = null;
                                        data.submit();
                                    })
                                    .fail(function () {
                                        fu._trigger('fail', e, data);
                                    });
                        };
                if (data.errorThrown !== 'abort' &&
                        data.uploadedBytes < data.files[0].size &&
                        retries < fu.options.maxRetries) {
                    retries += 1;
                    data.context.data('retries', retries);
                    window.setTimeout(retry, retries * fu.options.retryTimeout);
                    return;
                } else if (data.uploadedBytes == data.files[0].size) {
                    $.each(data.files, function (index, file) {
                        data.context.html('<td colspan="4"><div class="upload-finish"><h4>' + file.name + '</h4> size: ' + smhUpload.formatFileSize(parseInt(file.size)) + '<br><br><i style="color: green;">Upload finished</i></div></td>');
                    });
                    return;
                }

                data.context.removeData('retries');
                $.blueimp.fileupload.prototype
                        .options.fail.call(this, e, data);
            },
            beforeSend: function (e, files, index, xhr, handler, callback) {
                var chrome, context, device, file, filename, filesize, ios, sessionID, sessionName, session;

                // Retrieve the file that is about to be sent to nginx
                file = files.files[0];

                // Collect some basic file information
                filename = file.name;

                // Get the generated sessionID for this upload
                sessionID = $.base64.encode(filename).replace(/\+|=|\//g, '');
                sessionName = $.base64.encode(sessInfo.pid).replace(/\+|=|\//g, '');
                session = sessionName + sessionID;

                // Set the required headers for the nginx upload module
                e.setRequestHeader("Session-ID", session);
                e.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                e.setRequestHeader("Accept", "*/*");

                device = navigator.userAgent.toLowerCase();
                ios = device.match(/(iphone|ipod|ipad)/);
                chrome = device.match(/crios/);

                if (ios && !chrome) {
                    e.setRequestHeader("Cache-Control", "no-cache");
                }
            },
            done: function (e, data) {
                var sessData, file, filename, orig_filename, filesize, sessionID, sessionName, session;
                file = data.files[0];
                filename = file.name;
                filesize = data.files[0].size;
                sessionName = $.base64.encode(sessInfo.pid).replace(/\+|=|\//g, '');
                sessionID = $.base64.encode(filename).replace(/\+|=|\//g, '');
                session = sessionName + sessionID;

                sessData = {
                    file_sess: session,
                    file_name: $.trim(encodeURIComponent(filename)),
                    orig_file_name: $.trim(encodeURIComponent(filename)),
                    file_size: filesize,
                    pid: sessInfo.pid,
                    ks: sessInfo.ks,
                    eid: sessInfo.eid,
                    paramid: sessInfo.paramid
                }

                var reqUrl = "/server/php/processFlavor.php";
                $.ajax({
                    cache: false,
                    url: reqUrl,
                    async: false,
                    type: 'POST',
                    data: sessData,
                    beforeSend: function () {
                        data.context.html('<td colspan="4"><div style="text-align: left;"><h4 style="color: green;">Processing..</h4></div></td>');
                    },
                    error: function () {
                        data.context.html('<td colspan="4"><div style="text-align: left;"><h4 style="color: red;">Something went wrong..</h4></div></td>');
                    },
                    success: function (r) {
                        var response = JSON.parse(data.result);
                        $.each(response.files, function (index, file) {
                            data.context.html('<td colspan="4"><div class="upload-finish"><h4>' + decodeURIComponent(file.name) + '</h4> size: ' + smhUpload.formatFileSize(parseInt(file.size)) + '<br><br><i style="color: green;">Upload finished</i></div></td>');
                        });
                    }
                });
            }
        });
    },
    //Reset Modal
    resetModal: function () {
        $('#smh-modal2 .modal-header').empty();
        $('#smh-modal2 .modal-body').empty();
        $('#smh-modal2 .modal-footer').empty();
        $('#smh-modal2 .modal-content').css('min-height', '');
        $('#smh-modal2 .smh-dialog2').css('width', '');
        $('#smh-modal2 .modal-body').css('height', '');
        $('#smh-modal2 .modal-body').css('padding', '15px');
    },
    //Register actions
    registerActions: function () {
        $('button.cancel').click(function (e) {
            $('.fileupload-buttonbar .start').css('display', 'none');
            $('.fileupload-buttonbar .cancel').css('display', 'none');
            $('.fileinput-button').css('display', 'inline-block');
        });
        $('#smh-modal2').on('click', '.smh-close2', function () {
            $('#smh-modal').css('z-index', '');
            $('#smh-modal2').on('hidden.bs.modal', function (e) {
                $('body').addClass('modal-open');
            });
        });
    }
}

// Main on ready
$(document).ready(function () {
    smhUpload = new Upload(sessInfo.pid, sessInfo.ks);
    smhUpload.init_fileupload();
    smhUpload.registerActions();
});