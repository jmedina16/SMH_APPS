// Parameters
// ----------------------------------------------
var WORLD_FACTOR = 1.0;
var USE_RIFT = false;
var OculusRift = {
    // Parameters from the Oculus Rift DK1
    //    hResolution: 1280,
    //    vResolution: 800,
    //    hScreenSize: 0.14976,
    //    vScreenSize: 0.0936,
    //    interpupillaryDistance: 0.064,
    //    lensSeparationDistance: 0.064,
    //    eyeToScreenDistance: 0.041,
    //    distortionK : [1.0, 0.22, 0.24, 0.0],
    //    chromaAbParameter: [ 0.996, -0.004, 1.014, 0.0]

    // Parameters from the Oculus Rift DK2
    hResolution: 1920, // <--
    vResolution: 1080, // <--
    hScreenSize: 0.12576, // <--
    vScreenSize: 0.07074, // <--
    interpupillaryDistance: 0.0635, // <--
    lensSeparationDistance: 0.0635, // <--
    eyeToScreenDistance: 0.041,
    distortionK: [1.0, 0.22, 0.24, 0.0],
    chromaAbParameter: [0.996, -0.004, 1.014, 0.0]
};

//VIDEO RENDERING

var videoImage, videoImageContext, videoTexture;
var videoMaterial = {};
var renderer;
var effect;

// Globals
// ----------------------------------------------
var WIDTH, HEIGHT;
var cameraLeft, cameraRight;
var scene1, scene2;
var deviceO = false;
var mesh1, mesh2;
var controlsL, controlsR;
var geometry;
var VIEW_INCREMENT = 2;
var lon = 0, lat = 0, phi = 0, theta = 0, distance = 500;
var onPointerDownPointerX, onPointerDownPointerY, onPointerDownLon, onPointerDownLat;
var isUserInteracting = false;
var KDP_ELEMENT;
var fallbackID;
var vr_init_loaded = false;

var clock = new THREE.Clock();
var keyboard = new THREEx.KeyboardState();

function createRenderer() {
    renderer = new THREE.WebGLRenderer({
        'canvas': canvasElement,
        alpha: true,
        antialias: false,
        clearColor: 0xffffff,
        clearAlpha: 1
    });
}

function initWebGL() {
    scene1 = new THREE.Scene();
    scene2 = new THREE.Scene();
    createRenderer();
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(window.innerWidth, window.innerHeight);

    //    OculusRift.hResolution = WIDTH, OculusRift.vResolution = HEIGHT,
    //
    //    effect = new THREE.OculusRiftEffect( renderer, {
    //        HMD:OculusRift, 
    //        worldFactor:WORLD_FACTOR
    //    } );
    //    effect.setSize(WIDTH, HEIGHT );

    // Left Eye
    cameraLeft = new THREE.PerspectiveCamera(90, window.innerWidth / window.innerHeight, 1, 4000);
    cameraLeft.rotation.order = "YXZ";
    cameraLeft.target = new THREE.Vector3(0, 0, 0);
    cameraLeft.position.z = 0.1;
    scene1.add(cameraLeft);

    //Right Eye
    cameraRight = new THREE.PerspectiveCamera(90, window.innerWidth / window.innerHeight, 1, 4000);
    cameraRight.rotation.order = "YXZ";
    cameraRight.target = new THREE.Vector3(0, 0, 0);
    cameraRight.position.z = 0.1;
    scene2.add(cameraRight);

    //Orbit controls for left eye
    controlsL = new THREE.OrbitControls(cameraLeft, canvasElement);
    controlsL.enableDamping = true;
    controlsL.dampingFactor = 1;
    controlsL.enableZoom = true;
    controlsL.zoomSpeed = 1.0;

    //Orbit controls for right eye
    controlsR = new THREE.OrbitControls(cameraRight, canvasElement);
    controlsR.enableDamping = true;
    controlsR.dampingFactor = 1;
    controlsR.enableZoom = true;
    controlsR.zoomSpeed = 1.0;

    if (videoElement) {
        videoTexture = new THREE.VideoTexture(videoElement);
        videoTexture.minFilter = THREE.LinearFilter;
        videoTexture.magFilter = THREE.LinearFilter;
        videoTexture.format = THREE.RGBFormat;
        videoTexture.generateMipmaps = false;

        videoMaterial['video'] = new THREE.MeshBasicMaterial({
            map: videoTexture
        });
        videoMaterial['blank'] = new THREE.MeshBasicMaterial({
            color: 0x000000
        });

        // left sphere
        geometry = new THREE.SphereGeometry(500, 60, 40);
        geometry.scale(-1, 1, 1);
        geometry.dynamic = true;

        mesh1 = new THREE.Mesh(geometry, videoMaterial['blank']);
        scene1.add(mesh1);

        lat = Math.max(-85, Math.min(85, lat));
        phi = THREE.Math.degToRad(90 - lat);
        theta = THREE.Math.degToRad(lon);

        // right sphere
        geometry = new THREE.SphereGeometry(500, 60, 40);
        geometry.scale(-1, 1, 1);
        geometry.dynamic = true;

        mesh2 = new THREE.Mesh(geometry, videoMaterial['blank']);
        scene2.add(mesh2);

        function setOrientationControls(e) {
            if (!e.alpha) {
                return;
            }

            deviceO = true;
            controlsL.enabled = false;
            controlsL = new THREE.DeviceOrientationControls(cameraLeft);
            controlsL.connect();
            controlsL.update();

            controlsR.enabled = false;
            controlsR = new THREE.DeviceOrientationControls(cameraRight);
            controlsR.connect();
            controlsR.update();

            //element.addEventListener('click', fullscreen, false);
            window.removeEventListener('deviceorientation', setOrientationControls, true);
        }

        window.addEventListener('deviceorientation', setOrientationControls, true);
        window.addEventListener('mousedown', onDocumentMouseDown, false);
        window.addEventListener('mousemove', onDocumentMouseMove, false);
        window.addEventListener('mouseup', onDocumentMouseUp, false);
        window.addEventListener('mousewheel', onDocumentMouseWheel, false);
        window.addEventListener('MozMousePixelScroll', onDocumentMouseWheel, false);
        videoElement.addEventListener('ended', removeMaterial, false);
        videoElement.addEventListener('canplaythrough', addMaterial, false);
    }
}

function removeMaterial() {
    mesh1.material = videoMaterial['blank'];
    mesh1.material.needsUpdate = true;
    mesh2.material = videoMaterial['blank'];
    mesh2.material.needsUpdate = true;
}

function addMaterial() {
    mesh1.material = videoMaterial['video'];
    mesh1.material.needsUpdate = true;
    mesh2.material = videoMaterial['video'];
    mesh2.material.needsUpdate = true;
}

function animate() {
    render();
    requestAnimationFrame(animate);
    update(clock.getDelta());
}
function render() {
    if (renderer.domElement != canvasElement && videoElement) // || !videoImage || videoImage.width==0 || videoImage.height==0)
        initWebGL();
    else
    {
        if (USE_RIFT) {
            //effect.render(scene, camera);
            var width = Math.round(window.innerWidth / 2),
                    height = window.innerHeight;
            // Render left eye
            renderer.setViewport(0, 0, width, height);
            renderer.setScissor(0, 0, width, height);
            renderer.setScissorTest(true);
            cameraLeft.updateProjectionMatrix();
            renderer.render(scene1, cameraLeft);

            //Render right eye
            renderer.setViewport(width, 0, width, height);
            renderer.setScissor(width, 0, width, height);
            renderer.setScissorTest(true);
            cameraRight.updateProjectionMatrix();
            renderer.render(scene2, cameraRight);
        } else {
            renderer.setScissorTest(false);
            cameraLeft.updateProjectionMatrix();
            renderer.render(scene1, cameraLeft);
        }
    }
}
function update(dt) {
    if (keyboard.pressed("d")) {
        cameraLeft.setRotateYL(cameraLeft.getRotateYL() - VIEW_INCREMENT);
        cameraRight.setRotateYR(cameraLeft.getRotateYR() - VIEW_INCREMENT);
    }
    if (keyboard.pressed("a")) {
        cameraLeft.setRotateYL(cameraLeft.getRotateYL() + VIEW_INCREMENT);
        cameraRight.setRotateYR(cameraRight.getRotateYR() + VIEW_INCREMENT);
    }
    if (keyboard.pressed("w")) {
        if (cameraLeft.getRotateXL() < 90) { // restrict so they cannot look overhead
            cameraLeft.setRotateXL(cameraLeft.getRotateXL() + VIEW_INCREMENT);
        }
        if (cameraRight.getRotateXR() < 90) { // restrict so they cannot look overhead
            cameraRight.setRotateXR(cameraRight.getRotateXR() + VIEW_INCREMENT);
        }
    }
    if (keyboard.pressed("s")) {
        if (cameraLeft.getRotateXL() > -90) { // restrict so they cannot look under feet
            cameraLeft.setRotateXL(cameraLeft.getRotateXL() - VIEW_INCREMENT);
        }
        if (cameraRight.getRotateXR() > -90) { // restrict so they cannot look under feet
            cameraRight.setRotateXR(cameraRight.getRotateXR() - VIEW_INCREMENT);
        }
    }
    if (deviceO) {
        controlsL.update(dt);
        controlsR.update(dt);
    }
}

THREE.PerspectiveCamera.prototype.setRotateXL = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        cameraLeft.rotation.x = deg * (Math.PI / 180);
    }
};
THREE.PerspectiveCamera.prototype.setRotateXR = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        cameraRight.rotation.x = deg * (Math.PI / 180);
    }
};

THREE.PerspectiveCamera.prototype.setRotateYL = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        cameraLeft.rotation.y = deg * (Math.PI / 180);
    }
};
THREE.PerspectiveCamera.prototype.setRotateYR = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        cameraRight.rotation.y = deg * (Math.PI / 180);
    }
};

THREE.PerspectiveCamera.prototype.setRotateZL = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        cameraLeft.rotation.z = deg * (Math.PI / 180);
    }
};
THREE.PerspectiveCamera.prototype.setRotateZR = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        cameraRight.rotation.z = deg * (Math.PI / 180);
    }
};

THREE.PerspectiveCamera.prototype.getRotateXL = function () {
    return Math.round(cameraLeft.rotation.x * (180 / Math.PI));
};
THREE.PerspectiveCamera.prototype.getRotateXR = function () {
    return Math.round(cameraRight.rotation.x * (180 / Math.PI));
};

THREE.PerspectiveCamera.prototype.getRotateYL = function () {
    return Math.round(cameraLeft.rotation.y * (180 / Math.PI));
};
THREE.PerspectiveCamera.prototype.getRotateYR = function () {
    return Math.round(cameraRight.rotation.y * (180 / Math.PI));
};

THREE.PerspectiveCamera.prototype.getRotateZL = function () {
    return Math.round(cameraLeft.rotation.z * (180 / Math.PI));
};
THREE.PerspectiveCamera.prototype.getRotateZR = function () {
    return Math.round(cameraRight.rotation.z * (180 / Math.PI));
};

function initGui() {
    window.addEventListener('resize', resize, false);
}

function onDocumentMouseDown(event) {
    var iframe = parent.document.getElementsByTagName('iframe')[0];
    iframe.focus();
    event.preventDefault();

    isUserInteracting = true;

    onPointerDownPointerX = event.clientX;
    onPointerDownPointerY = event.clientY;

    onPointerDownLon = lon;
    onPointerDownLat = lat;

}

function onDocumentMouseMove(event) {

    if (isUserInteracting === true) {

        lon = (onPointerDownPointerX - event.clientX) * 0.1 + onPointerDownLon;
        lat = (event.clientY - onPointerDownPointerY) * 0.1 + onPointerDownLat;

    }

}

function onDocumentMouseUp(event) {

    isUserInteracting = false;

}

function onDocumentMouseWheel(event) {

    // WebKit

    if (event.wheelDeltaY) {

        distance -= event.wheelDeltaY * 0.05;

        // Opera / Explorer 9

    } else if (event.wheelDelta) {

        distance -= event.wheelDelta * 0.05;

        // Firefox

    } else if (event.detail) {

        distance += event.detail * 1.0;

    }

}

function resize() {
    WIDTH = window.innerWidth;
    HEIGHT = window.innerHeight;

    //    OculusRift.hResolution = WIDTH,
    //    OculusRift.vResolution = HEIGHT,
    //    effect.setHMD(OculusRift);

    cameraLeft.aspect = WIDTH / HEIGHT;
    cameraLeft.updateProjectionMatrix();
    cameraRight.aspect = WIDTH / HEIGHT;
    cameraRight.updateProjectionMatrix();
    renderer.setSize(WIDTH, HEIGHT);
    render();
}

function vr_fallback() {
    window.kWidget.addReadyCallback(function (playerId) {
        var kdp = document.getElementById(playerId);
        KDP_ELEMENT = kdp;
        if (!vr_init_loaded) {
            if (isSafari() || isEdge()) {
                if (!isIOS()) {
                    overlayHandler();
                    videoHeightHandler();
                }
            } else {
                if (!isIOS()) {
                    kdp.addJsListener("entryReady", "overlayHandler");
                    kdp.addJsListener("mediaReady", "videoHeightHandler");
                }
            }
            fallbackID = getConfig('fallbackEntryID');
            if (fallbackID) {
                if (isSafari() || isEdge()) {
                    mediaReadyHandler();
                } else {
                    kdp.addJsListener("mediaReady", "mediaReadyHandler");
                }
            }
        }
        vr_init_loaded = true;
    });
}

function normalizeAttrValue(attrValue) {
    // normalize flash kdp string values
    switch (attrValue) {
        case "null":
        case "":
        case undefined:
            return null;
            break;
        case "true":
            return true;
            break;
        case "false":
            return false;
            break;
    }
    return attrValue;
}

function getAttr(attr) {
    return normalizeAttrValue(
            KDP_ELEMENT.evaluate('{' + attr + '}')
            );
}

function getConfig(attr) {
    return getAttr('smhVR.' + attr);
}

function overlayHandler() {
    var displayOverlay = getConfig('showCompMessage');
    if (displayOverlay || displayOverlay == undefined || displayOverlay == null) {
        $('.smh-vr-overlay').remove();
        var error_pop = '<div class="smh-vr-overlay" style="height: 60px; width: 80%; z-index: 1000; position: absolute; bottom: 0; left: 10%; margin-bottom: 10px;">' +
                '<div class="overlay-win ui-state-default ui-widget-header ui-corner-all" style="height: 100%; width: 100%; position: absolute; margin: 0px 10px 10px 0px; overflow: hidden; z-index: 5;">' +
                '<div class="overlay-content" style="width: 97%; margin-left: 0;">' +
                '<div class="btn icon-close" style="right: 0; top: 0; position: absolute; margin: 5px; padding: 2px;" onclick="smh_vr_close_overlay();"></div>' +
                '<div class="smh-vr-alert-container">' +
                '<div class="smh-vr-alert-title smh-vr-alert-text">For a full VR expierence, please use a compatible browser such as Chrome</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
        $('.mwPlayerContainer').after(error_pop);
    }
    $('.videoDisplay').addClass('nonVrHover');
}

function videoHeightHandler() {
    $('.videoHolder').css('height', '100%');
}

function smh_vr_close_overlay() {
    $('.smh-vr-overlay').remove();
}

function mediaReadyHandler() {
    KDP_ELEMENT.sendNotification('changeMedia', {
        'entryId': fallbackID
    });
    KDP_ELEMENT.addJsListener("mediaLoaded", "pausePlayerHandler");
}

function pausePlayerHandler() {
    setTimeout(function () {
        KDP_ELEMENT.sendNotification('doPause');
        $('.largePlayBtn').css('display', 'block');
    }, 300);
}

function isIOS() {
    return ((navigator.userAgent.indexOf('iPhone') != -1) ||
            (navigator.userAgent.indexOf('iPod') != -1) ||
            (navigator.userAgent.indexOf('iPad') != -1));
}

function isChrome() {
    return ((navigator.userAgent.indexOf('Chrome') != -1));
}

function isIE() {
    return ((navigator.userAgent.indexOf('MSIE') != -1) ||
            (navigator.userAgent.indexOf('Trident') != -1));
}

function isEdge() {
    return ((navigator.userAgent.indexOf('Edge') != -1));
}

function isSafari() {
    return ((navigator.userAgent.indexOf('Safari') != -1) &&
            (navigator.userAgent.indexOf('Chrome') == -1));
}

function initSMHVR() {
    WIDTH = window.innerWidth;
    HEIGHT = window.innerHeight;

    if (!Detector.webgl || isIOS() || isIE() || isSafari() || isEdge()) {
        vr_fallback();
    } else {
        initWebGL();
        animate();
        initGui();
        window.kWidget.addReadyCallback(function (playerId) {
            $('.videoDisplay').addClass('vrHover');
            $('video').addClass('hideVideo');
        });
    }
//vr_fallback();
}
