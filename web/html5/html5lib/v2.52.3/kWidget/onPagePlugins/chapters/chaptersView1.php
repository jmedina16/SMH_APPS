<!DOCTYPE HTML>
<html>
<head>
	<title>Chapters View page</title>
        <script type="text/javascript" src="http://mediaplatform.streamingmediahosting.com/html5/html5lib/v1.8.7/resources/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../../mwEmbedLoader.php"></script>
	<script type="text/javascript" src="../../../docs/js/doc-bootstrap.js"></script>
	<script type="text/javascript">	
	//Enable uiconf js which includes external resources ( not needed if resources are defined in uiConf )
	mw.setConfig('Kaltura.EnableEmbedUiConfJs', true);
	</script>
	<!-- qunit-kaltura must come after qunit-bootstrap.js and after mwEmbedLoader.php and after any jsCallbackReady stuff-->
	<script type="text/javascript" src="../../../modules/KalturaSupport/tests/resources/qunit-kaltura-bootstrap.js"></script>
</head>
<body>
<h2> Chapters Views </h2>
<div id="smh_player" style="width:460px;height:258px;"></div>
<script>
kWidget.featureConfig({
	'targetId' : 'smh_player',
	'wid' : '_10012',
	'uiconf_id' : '6709796',
	'entry_id' : '0_6iiicta2',
	'flashvars': {
		'chaptersView' :{
			'plugin': true,
			'path' : "/content/uiconf/ps/kaltura/kdp/v3.6.9/plugins/facadePlugin.swf",
			'relativeTo': 'video',
			'position': 'before',
			'includeInLayout':false,

			'tags': 'chaptering',
			// all custom data is stored in "partnerData"
			'layout' : 'horizontal',
			'containerPosition': 'after',
			'overflow': false,
			'pauseAfterChapter': false,
			'includeThumbnail': true,
			'includeChapterStartTime': true,
			'includeChapterDuration': true,
			'thumbnailRotator' : true,
			'includeChapterNumberPattern' : null,
			'thumbnailWidth' : 100,
			'horizontalChapterBoxWidth': 320,
			'titleLimit' : 24,
			'chapterRenderer': 'onChapterRenderer',
			'chaptersRenderDone': 'onChaptersRenderDone',

			'requiresJQuery' : true,
			'onPageJs1': "{onPagePluginPath}/chapters/chaptersView.js",
			'onPageCss1': "{onPagePluginPath}/chapters/chaptersView.css",

			// for overflow=false jcarousellite.js:
			'onPageJs2': "{onPagePluginPath}/libs/jcarousellite.js",
			'onPageJs3': "{onPagePluginPath}/libs/jquery.sortElements.js"
		}
	}
});
</script>


</body>
<!--  UiConf file used in this example: 
<layout id="full" name="DescriptionBox" skinPath="/content/uiconf/kaltura/kmc/appstudio/kdp3/falcon/skin/v3.6.5/KDP_Blue.swf">
  <HBox id="topLevel" width="100%" height="100%">
    <VBox id="player" width="100%" height="100%" styleName="black">
      <Plugin id="kalturaMix" width="0%" height="0%" includeInLayout="false" loadingPolicy="onDemand"/>
      <Plugin id="statistics" width="0%" height="0%" includeInLayout="false"/>
      <Plugin id="relatedEntries" width="0%" height="0%" sourceType="automatic" autoPlay="false" automaticPlaylistId="_KDP_RE_PL" entryId="{mediaProxy.entry.id}" referenceIdsSourceData="{mediaProxy.entryMetadata.ReferenceIds}" playlistSourceData="" entryIdsSourceData="{mediaProxy.entryMetadata.EntryIds}" autoPlayDelay="10" selectRandomNext="false" itemClickAction="loadInPlayer" urlAddress="" jsFunc=""/>
      <Canvas id="PlayerHolder" height="100%" width="100%" styleName="black">
        <Video id="video" width="100%" height="100%"/>
        <VBox id="titleMessageVbox" width="100%" height="80" paddingLeft="10"/>
        <VBox id="offlineMessageHolder" verticalAlign="middle" horizontalAlign="center" includeInLayout="false" width="100%" height="100%">
          <Spacer height="100%"/>
          <Spacer height="100%"/>
          <Label id="offlineMessage" styleName="offlineMessage" text="{mediaProxy.entry.offlineMessage}" visible="{mediaProxy.isOffline}" width="100%" height="30"/>
          <Spacer height="100%"/>
        </VBox>
        <VBox id="generalPluginContainer" width="100%" height="100%">
          <Spacer id="contentPusher" height="100%"/>
        </VBox>
        <Screens id="screensLayer" width="100%" height="100%" mouseOverTarget="{PlayerHolder}" styleName="clickThrough" startScreenId="startScreen" startScreenOverId="startScreen" pauseScreenOverId="pauseScreen" pauseScreenId="pauseScreen" playScreenOverId="playScreen" endScreenId="endScreen" endScreenOverId="endScreen"/>
        <Watermark id="watermark" width="100%" height="100%" watermarkPath="http://www.kaltura.com/content/uiconf/kaltura/kmc/appstudio/kdp3/exampleWatermark.png" watermarkClickPath="http://www.kaltura.com/" watermarkPosition="bottomLeft" padding="5"/>
        <VBox id="skipBtnHolder" width="100%" height="100%">
          <Spacer height="100%"/>
          <HBox width="100%" height="30">
            <Spacer width="100%"/>
          </HBox>
        </VBox>
        <VBox id="relatedViewVBox" horizontalAlign="center" verticalAlign="middle" width="100%" height="100%" visible="{relatedView.visible}" includeInLayout="{relatedView.visible}" verticalGap="10" styleName="black" viewType="tile" showAfterPlayEnd="true">
          <VBox id="relatedVBox" width="100%" height="100%" maxWidth="800" maxHeight="600" paddingLeft="13" paddingRight="13" styleName="black" verticalAlign="bottom">
            <HBox id="upNextHbox" width="100%" height="35" paddingTop="10" horizontalGap="0" visible="{not(layoutProxy.isInFullScreen)}" includeInLayout="{not(layoutProxy.isInFullScreen)}">
              <Label id="upNextLabel" visible="{relatedEntries.autoPlay}" text="Up next in {relatedEntries.timeRemaining} secs" width="115" height="20" styleName="Related_Text_UpNext"/>
              <HBox id="pauseConteinueHBox" width="63" height="20" visible="{relatedEntries.autoPlay}" paddingLeft="0" verticalAlign="middle">
                <Button id="pauseRelatedBtn" width="30" height="20" label="Pause" visible="{relatedEntries.isTimerRunning}" includeInLayout="{relatedEntries.isTimerRunning}" kClick="sendNotification('pauseResumeRelatedTimer')" buttonType="labelButton" color1="0xCECECE" color2="0xFFFFFF"/>
                <Button id="playRelatedBtn" width="63" height="20" visible="{not(relatedEntries.isTimerRunning)}" includeInLayout="{not(relatedEntries.isTimerRunning)}" label="Continue" kClick="sendNotification('pauseResumeRelatedTimer')" buttonType="labelButton" color1="0xCECECE" color2="0xFFFFFF"/>
              </HBox>
              <HBox id="actionsHBox" width="100%" height="35" horizontalAlign="right" horizontalGap="5">
                <Button id="replayOnRelatedScreen" kClick="sendNotification('doSeek','0');sendNotification('doPlay')" label="Replay" labelPlacement="right" textPadding="5" minWidth="80" visible="{relatedView.showReplayBtn}" icon="replayIcon_Up" upIcon="replayIcon_Up" overIcon="replayIcon_Hover" downIcon="replayIcon_Down" disabeledIcon="replayIcon_Disabled"/>
                <Button id="kalturaShareBtnOnRelatedAuto" visible="{and(relatedEntries.autoPlay,relatedEntries.isTimerRunning)}" includeInLayout="{kalturaShareBtnOnRelatedAuto.visible}" kClick="sendNotification ('pauseResumeRelatedTimer');sendNotification('showAdvancedShare')" textPadding="5" label="Share" labelPlacement="right" minWidth="80" icon="shareIcon_Up" upIcon="shareIcon_Up" overIcon="shareIcon_Hover" downIcon="shareIcon_Down" disabeledIcon="shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea"/>
                <Button id="kalturaShareBtnOnRelated" visible="{not(kalturaShareBtnOnRelatedAuto.visible)}" includeInLayout="{kalturaShareBtnOnRelated.visible}" kClick="sendNotification('showAdvancedShare')" label="Share" textPadding="5" labelPlacement="right" minWidth="80" icon="shareIcon_Up" upIcon="shareIcon_Up" overIcon="shareIcon_Hover" downIcon="shareIcon_Down" disabeledIcon="shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea"/>
              </HBox>
            </HBox>
            <HBox id="upNextHboxFS" width="100%" height="50" paddingTop="10" horizontalGap="0" visible="{layoutProxy.isInFullScreen}" includeInLayout="{layoutProxy.isInFullScreen}">
              <Label id="upNextLabelFS" visible="{relatedEntries.autoPlay}" text="Up next in {relatedEntries.timeRemaining} secs" width="135" height="20" styleName="RelatedFS_Text_UpNext"/>
              <HBox width="85" height="20" visible="{relatedEntries.autoPlay}" paddingLeft="0" verticalAlign="middle">
                <Button id="pauseRelatedBtnFS" width="40" height="30" label="Pause" visible="{relatedEntries.isTimerRunning}" includeInLayout="{relatedEntries.isTimerRunning}" kClick="sendNotification('pauseResumeRelatedTimer')" buttonType="labelButton" color1="0xCECECE" color2="0xFFFFFF" styleName="FS"/>
                <Button id="playRelatedBtnFS" width="85" height="30" visible="{not(relatedEntries.isTimerRunning)}" includeInLayout="{not(relatedEntries.isTimerRunning)}" label="Continue" kClick="sendNotification('pauseResumeRelatedTimer')" buttonType="labelButton" color1="0xCECECE" color2="0xFFFFFF" styleName="FS"/>
              </HBox>
              <HBox width="100%" height="50" horizontalAlign="right" horizontalGap="5">
                <Button id="replayOnRelatedScreenFS" kClick="sendNotification('doSeek','0');sendNotification('doPlay')" label="Replay" labelPlacement="right" textPadding="5" minWidth="80" visible="{relatedView.showReplayBtn}" icon="replayIcon_Up" upIcon="replayIcon_Up" overIcon="replayIcon_Hover" downIcon="replayIcon_Down" disabeledIcon="replayIcon_Disabled"/>
                <Button id="kalturaShareBtnOnRelatedAutoFS" visible="{and(relatedEntries.autoPlay,relatedEntries.isTimerRunning)}" includeInLayout="{kalturaShareBtnOnRelatedAutoFS.visible}" kClick="sendNotification ('pauseResumeRelatedTimer');sendNotification('showAdvancedShare')" textPadding="5" label="Share" labelPlacement="right" minWidth="80" icon="shareIcon_Up" upIcon="shareIcon_Up" overIcon="shareIcon_Hover" downIcon="shareIcon_Down" disabeledIcon="shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea"/>
                <Button id="kalturaShareBtnOnRelatedFS" visible="{not(kalturaShareBtnOnRelatedAutoFS.visible)}" includeInLayout="{kalturaShareBtnOnRelatedFS.visible}" kClick="sendNotification('showAdvancedShare')" label="Share" textPadding="5" labelPlacement="right" minWidth="80" icon="shareIcon_Up" upIcon="shareIcon_Up" overIcon="shareIcon_Hover" downIcon="shareIcon_Down" disabeledIcon="shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea"/>
              </HBox>
            </HBox>
            <Spacer height="20"/>
            <Plugin id="relatedView" visible="false" width="100%" height="100%" dataProvider="{relatedEntries.dataProvider}" itemRenderer="relatedViewItemRenderer" viewType="tile" showAfterPlayEnd="true"/>
          </VBox>
        </VBox>
        <Plugin id="kalturaShare" uiconfId="8700151" width="100%" height="100%" via="" pubid=""/>
      </Canvas>
      <Canvas id="controlsHolder" width="100%" height="80" styleName="PlayerBg">
        <VBox id="ControllerScreenHolder" width="100%" height="80" verticalAlign="middle">
          <HBox id="scrubberBox" width="100%" height="28" verticalAlign="middle" paddingRight="10" paddingLeft="10">
            <HBox id="scrubberContainer" visible="{not(mediaProxy.isLive)}" width="100%" height="28" horizontalGap="0" paddingRight="9" verticalAlign="middle">
              <Timer id="timerControllerScreen1" width="45" height="25" styleName="timerProgressLeft" format="mm:ss" timerType="forwards"/>
              <Label id="timerControllerScreen1Label" text="/" width="10" height="25" color1="0xFFFFFF" dynamicColor="true" styleName="timerProgressRight" timerType="forwards"/>
              <Timer id="timerControllerScreen2" width="45" height="25" styleName="timerProgressRight" format="mm:ss" timerType="total"/>
              <VBox width="100%" height="28" paddingLeft="8" paddingRight="8" supportEnableGui="false">
                <Scrubber id="scrubber" width="100%" height="100%"/>
              </VBox>
            </HBox>
            <Button id="kalturaLogo" minWidth="50" kClick="navigate('http://www.kaltura.com')" styleName="controllerScreen" icon="kalturaLogo"/>
          </HBox>
          <HBox id="ControllerScreen" width="100%" height="100%" horizontalGap="15" paddingLeft="25" paddingBottom="8" paddingRight="25" verticalAlign="middle">
            <Button id="playBtnControllerScreen" width="20" height="30" command="play" icon="playIcon_up" overIcon="playIcon_Hover" downIcon="playIcon_Down" disabeledIcon="playIcon_Disabled" selectedUpIcon="pauseIcon_Up" selectedOverIcon="pauseIcon_Hover" selectedDownIcon="pauseIcon_Down" selectedDisabledIcon="pauseIcon_Disabled" k_buttonType="falconButtonIconControllerArea" buttonType="normal" font="Arial"/>
            <Button id="liveToggleStatus" toggle="true" color1="0xFF0000" color2="0xFF0000" upIcon="onAirIcon_Up" overIcon="onAirIcon_Hover" downIcon="onAirIcon_Down" disabeledIcon="onAirIcon_Disabled" selectedUpIcon="offlineIcon_up" selectedOverIcon="offlineIcon_Hover" selectedDownIcon="offlineIcon_Down" selectedDisabledIcon="offlineIcon_Disabled" isSelected="{mediaProxy.isOffline}" visible="{mediaProxy.isLive}" includeInLayout="{mediaProxy.isLive}" mouseEnable="false" useHandCursor=""/>
            <Spacer id="buttonsPusher" width="100%"/>
            <Button id="kalturaShareBtnControllerScreen" kClick="sendNotification('showAdvancedShare')" height="30" styleName="controllerScreen" icon="shareIcon_Up" overIcon="shareIcon_Hover" downIcon="shareIcon_Down" disabeledIcon="shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea" uiconfId="" buttonType="normal" font="Arial"/>
            <VolumeBar id="volumeBar" width="30" height="30" icon="VolumeIcon_Up" buttonUpSkin="VolumeIcon_Up" styleName="controllerScreen" buttonOverSkin="VolumeIcon_Hover" buttonDownSkin="VolumeIcon_Down" buttonDisabledSkin="VolumeIcon_Disabled" buttonSelectedUpSkin="MuteIcon_up" buttonSelectedOverSkin="MuteIcon_Hover" buttonSelectedDownSkin="MuteIcon_Down" buttonSelectedDisabledSkin="MuteIcon_Disabled" initialValue="1" forceInitialValue="false" font="Arial"/>
            <Button id="fullScreenBtnControllerScreen" command="fullScreen" height="30" styleName="controllerScreen" allowDisable="false" icon="closeFullScreenIcon_Up" overIcon="closeFullScreenIcon_Hover" downIcon="closeFullScreenIcon_Down" disabeledIcon="closeFullScreenIcon_Disabled" selectedUpIcon="openFullScreenIcon_Up" selectedOverIcon="openFullScreenIcon_Hover" selectedDownIcon="openFullScreenIcon_Down" selectedDisabledIcon="openFullScreenIcon_Disabled" k_buttonType="falconButtonIconControllerArea" buttonType="normal" font="Arial"/>
          </HBox>
        </VBox>
      </Canvas>
    </VBox>
  </HBox>
  <screens>
    <screen id="startScreen">
      <VBox id="startContainer" width="100%" height="100%" verticalAlign="middle" horizontalAlign="center">
        <Tile id="startTile" width="100%" verticalGap="10" verticalAlign="middle" horizontalAlign="center">
          <Button id="onVideoPlayBtnStartScreen" command="play" minWidth="80" minHeight="80" labelPlacement="bottom" textPadding="5" label="Play" styleName="onScreenBtn" upIcon="OnVideo_playIcon_Up" overIcon="OnVideo_playIcon_Hover" downIcon="OnVideo_playIcon_Down" disabeledIcon="OnVideo_playIcon_Disabled" k_buttonType="falconButtonIconControllerArea" buttonType="normal" font="Arial"/>
          <Button id="kalturaShareBtnStartScreen" kClick="sendNotification('showAdvancedShare')" minWidth="80" minHeight="80" labelPlacement="bottom" textPadding="5" label="Share" styleName="onScreenBtn" icon="OnVideo_shareIcon_Up" upIcon="OnVideo_shareIcon_Up" overIcon="OnVideo_shareIcon_Hover" downIcon="OnVideo_shareIcon_Down" disabeledIcon="OnVideo_shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea" uiconfId="" buttonType="normal" font="Arial"/>
        </Tile>
      </VBox>
    </screen>
    <screen id="pauseScreen">
      <VBox id="pauseContainer" width="100%" height="100%" verticalAlign="middle" horizontalAlign="center">
        <Tile id="pauseTile" width="100%" verticalGap="10" verticalAlign="middle" horizontalAlign="center">
          <Button id="onVideoPlayBtnPauseScreen" command="play" minWidth="80" minHeight="80" labelPlacement="bottom" textPadding="5" label="Play" styleName="onScreenBtn" upIcon="OnVideo_playIcon_Up" overIcon="OnVideo_playIcon_Hover" downIcon="OnVideo_playIcon_Down" disabeledIcon="OnVideo_playIcon_Disabled" k_buttonType="falconButtonIconControllerArea" buttonType="normal" font="Arial"/>
          <Button id="kalturaShareBtnPauseScreen" kClick="sendNotification('showAdvancedShare')" minWidth="80" minHeight="80" labelPlacement="bottom" textPadding="5" label="Share" styleName="onScreenBtn" icon="OnVideo_shareIcon_Up" upIcon="OnVideo_shareIcon_Up" overIcon="OnVideo_shareIcon_Hover" downIcon="OnVideo_shareIcon_Down" disabeledIcon="OnVideo_shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea" uiconfId="" buttonType="normal" font="Arial"/>
        </Tile>
      </VBox>
    </screen>
    <screen id="playScreen">
      <VBox id="playContainer" width="100%" height="100%" verticalAlign="middle" horizontalAlign="center">
        <Tile id="playTile" width="100%" verticalGap="10" verticalAlign="middle" horizontalAlign="center"/>
      </VBox>
    </screen>
    <screen id="endScreen">
      <VBox id="endContainer" width="100%" height="100%" verticalAlign="middle" horizontalAlign="center">
        <Tile id="endTile" width="100%" verticalGap="10" verticalAlign="middle" horizontalAlign="center">
          <Button id="replayBtnEndScreen" kClick="sendNotification('doPlay')" label="Replay" minWidth="80" minHeight="80" labelPlacement="bottom" textPadding="5" styleName="onScreenBtn" icon="OnVideo_replayIcon_Up" upIcon="OnVideo_replayIcon_Up" overIcon="OnVideo_replayIcon_Hover" downIcon="OnVideo_replayIcon_Down" disabeledIcon="OnVideo_replayIcon_Disabled" buttonType="normal" font="Arial"/>
          <Button id="kalturaShareBtnEndScreen" kClick="sendNotification('showAdvancedShare')" minWidth="80" minHeight="80" labelPlacement="bottom" textPadding="5" label="Share" styleName="onScreenBtn" icon="OnVideo_shareIcon_Up" upIcon="OnVideo_shareIcon_Up" overIcon="OnVideo_shareIcon_Hover" downIcon="OnVideo_shareIcon_Down" disabeledIcon="OnVideo_shareIcon_Disabled" k_buttonType="falconButtonIconControllerArea" uiconfId="" buttonType="normal" font="Arial"/>
        </Tile>
      </VBox>
    </screen>
  </screens>
  <renderers>
    <renderer id="relatedViewItemRenderer" viewType="tile" showAfterPlayEnd="true">
      <VBox id="relatedIR" width="100%" height="100%" verticalAlign="middle" horizontalAlign="center">
        <Canvas id="relatedCanvas" styleName="black" width="100%" height="100%">
          <Image id="relatedImage" width="100%" height="100%" url="{this.entry.thumbnailUrl}/width/180/height/145/type/5"/>
          <VBox id="relatedVBox" width="100%" height="100%" visible="{not(this.isOver)}" styleName="TileUp"/>
          <VBox id="upNextVBox" width="100%" height="100%" visible="{this.isUpNext}" styleName="TileSelected"/>
          <VBox id="labelsHolder" width="100%" height="100%" visible="{this.isOver}" styleName="TileSelected" paddingLeft="7" paddingRight="8" paddingTop="5" paddingBottom="5">
            <Text id="relatedHoverNameAndDesc" height="35" width="100%" text="{this.entry.name}" styleName="Tile_itemOver_Title"/>
            <HBox id="durationHBox" width="100%" height="100%" verticalAlign="bottom">
              <Label id="relatedDurationIrScreen" height="15" width="40" text="{formatDate(this.entry.duration, 'NN:SS')}" styleName="Tile_itemOver_duration"/>
              <Spacer width="100%"/>
              <Button id="playIconBtn" icon="Tile_itemOver_icon"/>
            </HBox>
          </VBox>
        </Canvas>
      </VBox>
    </renderer>
  </renderers>
  <strings>
    <string key="ENTRY_CONVERTING" value="Entry is processing, please try again in a few minutes."/>
  </strings>
  <extraData>null</extraData>
  <plugins/>
  <uiVars>
    <var key="video.keepAspectRatio" value="true"/>
    <var key="playlistAPI.autoContinue" value="false"/>
    <var key="imageDefaultDuration" value="2"/>
    <var key="autoPlay" value="false"/>
    <var key="autoMute" value="false"/>
  </uiVars>
</layout>
-->


</html>
