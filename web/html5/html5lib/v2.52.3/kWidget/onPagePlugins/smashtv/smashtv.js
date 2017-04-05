(function(){
    kWidget.addReadyCallback( function( playerId ){
        window.kdp = $('#'+playerId).get(0);
        genClipListId = 'k-clipList-' + playerId;
        $('#' + genClipListId ).remove();
        
        new smashtv( playerId );
    });
    
    var smashtv = function(kdp){
        return this.init(kdp);
    }
    
    smashtv.prototype = {
        pluginName: 'smashtv',
        init: function(kdp){
            this.kdp = kdp;
            this.pid = pid;
            this.addOnce = false;
            this.loadVideos();                      
        },
        getClipListTarget: function(){
            // check for generated id:
            if( $('#' + genClipListId ).length ){
                return  $('#' + genClipListId );
            }
            var clipListId = null;
            // check for clip target:
            if( clipListId && $('#' + clipListId ).length ){
                return  $('#' + clipListId)
            }  
            return $('<div />').attr('id', genClipListId ).insertBefore(  $( '#' + this.kdp ) );

        },
        activateEntry: function(activeEntryId){
            var _this = this;
            var $carousel = this.getClipListTarget().find( '.k-carousel' );
            // highlight the active clip ( make sure only one clip is highlighted )
            var $clipList = this.getClipListTarget().find( 'ul li' );
            ;
            if( $clipList.length && activeEntryId ){
                $clipList.each( function( inx, clipLi ){
                    // kdp moves entryId to .entryId in playlist data provider ( not a db mapping )
                    var entryMeta =  $( clipLi ).data( 'entryMeta' );
                    var clipEntryId = entryMeta;
                    if( clipEntryId == activeEntryId ){
                        $( clipLi ).addClass( 'k-active' ).data( 'activeEntry', true );

                        // scroll to the target entry ( if not already shown ):
                        if( inx == 0 || _this.getClipListTarget().find('ul').width() > _this.getClipListTarget().width() ){
                            $carousel[0].jCarouselLiteGo( inx );
                        }
                    } else {
                        $( clipLi ).removeClass( 'k-active' ).data('activeEntry', false)
                    }
                });
            }
        },
        changeVideo: function(entryId,title,description,createdAt,views){
            kdp.sendNotification( 'doPause' );
            kdp.sendNotification('changeMedia', {
                'entryId': entryId                             
            }); 
            $('#videoname').html("<h3>"+title+"</h3>");
            $('#desc').html(description);
            $('#date').html(createdAt);
            $('.views').html(views);
        },
        loadVideos: function(){ 
            var _this = this;
            kdp.kBind( "changeMedia.onPagePlaylist", function( clip ){  
                _this.activateEntry( clip['entryId'] );
            });
            
            kdp.kBind( "mediaReady", function(){
                if( _this.addOnce ){
                    return ;
                }
                var clipListId = null;                
                _this.addOnce = true; 
                
                // check for a target
                $clipListTarget = _this.getClipListTarget();
                $clipListTarget.addClass( 'kWidget-clip-list' );
                
                // add layout mode:
                var layoutMode = 'right';
                $clipListTarget.addClass( 'k-' + layoutMode );

                // get the thumbWidth:
                var thumbWidth =  '110';
                // standard 3x4 box ratio:
                var thumbHeight = thumbWidth*.75;

                // calculate how many clips should be visible per size and cliplist Width
                var clipsVisible = null;
                var liSize = {};

                // Give player height if dynamically added:
                if( !clipListId ){
                    // if adding in after the player make sure the player is float left so
                    // the playlist shows up after:
                    $(kdp).css('float', 'left');
                    $clipListTarget
                    .css({
                        'float' : 'right',
                        'padding-left' : '5px',
                        'height' : $( kdp ).height() + 'px',
                        'width' : '340px',
                        'position' : 'absolute',
                        'right' : '0'
                    });
                }

                clipsVisible = Math.floor( $clipListTarget.height() / ( parseInt( thumbHeight ) + 4 ) );
                liSize ={
                    'width' : '100%',
                    'height': thumbHeight
                };
		
                var $clipsUl = $('<ul>').css({
                    "height": '100%'
                })
                .appendTo( $clipListTarget )
                .wrap(
                    $( '<div />' ).addClass('k-carousel')
                    )
		
                // append all the clips
                init_clip = false;
                var first_clip = '';
                $.each( entries, function( inx, clip ){
                    
                    if(clip['entry_id']){                                               
                        if(!init_clip){
                            first_clip = clip['entry_id'];                            
                            init_clip = true;   
                        }
                        
                        $clipsUl.append(
                            $('<li />')
                            .css( liSize )
                            .data( {
                                'entryMeta': clip['entry_id'],
                                'index' : inx
                            })
                            .append(
                                $('<img />')
                                .attr({
                                    'src' : 'http://imgs.mediaplatform.streamingmediahosting.com/p/'+pid+'/thumbnail/entry_id/'+clip['entry_id']+ '/width/' + thumbWidth + '/height/62/type/2/bgcolor/000000',
                                    'onerror' : 'ImgError(this)',
                                    'width' : '110px',
                                    'height' : '62px'
                                }),
                                $('<div />')
                                .addClass( 'k-clip-desc' )
                                .append(
                                    $('<h3 />')
                                    .addClass( 'k-title' )
                                    .text( clip['title'] ),

                                    $('<div />')
                                    .addClass( 'k-date' )
                                    .addClass( 'user-icon' )
                                    .addClass( 'icon-calendar5' )
                                    .text( ( clip['created_at'] == null ) ? '': clip['created_at'] ),
                                
                                    $('<div />')
                                    .addClass( 'k-dur-views' )
                                    .append( 
                                        $('<span />')
                                        .addClass( 'k-duration' )
                                        .addClass( 'viewers' )
                                        .addClass( 'user-icon' )
                                        .addClass( 'icon-clock5' )
                                        .text( ( clip['duration'] == null ) ? '': clip['duration'] )
                                        ).append( 
                                        $('<span />')
                                        .addClass( 'k-views' ) 
                                        .addClass( 'viewers' )
                                        .addClass( 'user-icon' )
                                        .addClass( 'icon-eye4' )
                                        .text( ( clip['views'] == null ) ? '': clip['views'])
                                        )
                                
                                    )
                                    
                                )
                            .click(function(){                 
                                _this.changeVideo(clip['entry_id'],clip['full_title'],clip['description'],clip['created_at'],clip['views']);
                            }).hover(function(){
                                $( this ).addClass( 'k-active' );
                            },
                            function(){
                                // only remove if not the active entry:
                                if( !$( this ).data( 'activeEntry' ) ){
                                    $( this ).removeClass( 'k-active' );
                                }
                            })
                            )
                    } else {
                        $clipsUl.append(
                            $('<li />')
                            .css( liSize )
                            )
                    }

                });

                // Add scroll buttons
                $clipListTarget.prepend(
                    $( '<a />' )
                    .addClass( "k-scroll k-prev" )
                    )
                $clipListTarget.append(
                    $( '<a />' )
                    .addClass( "k-scroll k-next" )
                    )
                $clipListTarget.append(
                    $( '<div />' )
                    .addClass( "k-scroll more-videos" )
                    .html( '<span style="position: relative; top: 8px;"><a href="/userpage.php?pid='+pid+'">More Videos</a></span>' )
                    )
                // don't show more clips then we have available 
                if( clipsVisible > entries.length ){
                    clipsVisible = entries.length;
                }
		
                // Add scrolling carousel to clip list ( once dom sizes are up-to-date )
                var verical = true;
                
                $clipListTarget.find( '.k-carousel' ).jCarouselLite({
                    btnNext: ".k-next",
                    btnPrev: ".k-prev",
                    visible: clipsVisible,
                    mouseWheel: true,
                    circular: false,
                    vertical: verical
                });
                // test if k-carousel is too large for scroll buttons:
                if( !verical && $clipListTarget.find( '.k-carousel' ).width() > $clipListTarget.width() - 40 ){
                    $clipListTarget.find( '.k-carousel' ).css('width',
                        $clipListTarget.width() - 40
                        )
                }

                // sort ul elements:
                $clipsUl.find('li').sortElements(function(a, b){
                    return $(a).data('index') > $(b).data('index') ? 1 : -1;
                });
                    
                _this.activateEntry(first_clip);
            });
        }
    }
})();