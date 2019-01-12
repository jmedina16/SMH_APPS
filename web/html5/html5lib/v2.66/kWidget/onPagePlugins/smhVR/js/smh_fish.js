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
var camera;
var scene;
var deviceO = false;
var mesh;
var controls;
var geometry;
var VIEW_INCREMENT = 2;
var lon = 0, lat = 0, phi = 0, theta = 0, distance = 500;
var onPointerDownPointerX, onPointerDownPointerY, onPointerDownLon, onPointerDownLat;
var isUserInteracting = false;

var clock = new THREE.Clock();
var keyboard = new THREEx.KeyboardState();

function initWebGL() {
    if (!Detector.webgl)
        Detector.addGetWebGLMessage();

    scene = new THREE.Scene();
    renderer = new THREE.WebGLRenderer({
        canvas: canvasElement,
        alpha: true,
        antialias: true,
        clearColor: 0xffffff,
        clearAlpha: 1
    });
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.setSize(window.innerWidth, window.innerHeight);

    //    OculusRift.hResolution = WIDTH, OculusRift.vResolution = HEIGHT,
    //
    //    effect = new THREE.OculusRiftEffect( renderer, {
    //        HMD:OculusRift, 
    //        worldFactor:WORLD_FACTOR
    //    } );
    //    effect.setSize(WIDTH, HEIGHT );

    effect = new THREE.StereoEffect(renderer);
    effect.setSize(WIDTH, HEIGHT);

    camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 1, 1000);
    camera.rotation.order = "YXZ";
    camera.target = new THREE.Vector3(0, 0, 0);
    camera.position.z = 0.1;
    camera.position.y = 5;
    camera.position.x = -15;
    scene.add(camera);

    //Orbit controls for left eye
    controls = new THREE.OrbitControls(camera, canvasElement);
    controls.enableDamping = true;
    controls.dampingFactor = 1;
    controls.enableZoom = true;
    controls.zoomSpeed = 1.0;

    if (videoElement) {
        videoTexture = new THREE.VideoTexture(videoElement);
        videoTexture.minFilter = THREE.LinearFilter;
        videoTexture.magFilter = THREE.LinearFilter;
        videoTexture.format = THREE.RGBFormat;
        videoTexture.generateMipmaps = false;
        videoTexture.flipY = false;

        videoMaterial['video'] = new THREE.MeshBasicMaterial({
//            map: videoTexture,
//            overdraw: true,
//            side:THREE.DoubleSide
            color: 0xffff00,
            wireframe: true
        });
        videoMaterial['blank'] = new THREE.MeshBasicMaterial({
            color: 0x000000
        });

        //sphere
        geometry = new THREE.SphereGeometry(15, 32, 32, 0, Math.PI, 0, Math.PI);
        //geometry = new THREE.SphereBufferGeometry( 500, 60, 40 ).toNonIndexed();
        //geometry = new THREE.SphereGeometry( 20, 32, 32 );
        //geometry = new THREE.SphereGeometry(50, 60, 60, 0, Math.PI, 3*Math.PI/2);
        geometry.scale.x = -1;
        geometry.dynamic = true;

        var faceVertexUvs = geometry.faceVertexUvs[ 0 ];
        for (i = 0; i < faceVertexUvs.length; i++) {

            var uvs = faceVertexUvs[ i ];
            var face = geometry.faces[ i ];

            for (var j = 0; j < 3; j++) {

                uvs[ j ].x = face.vertexNormals[ j ].x * 0.5 + 0.5;
                uvs[ j ].y = face.vertexNormals[ j ].y * 0.5 + 0.5;

            }

        }


        mesh = new THREE.Mesh(geometry, videoMaterial['blank']);
        mesh.material.side = THREE.DoubleSide;
        scene.add(mesh);

        lat = Math.max(-85, Math.min(85, lat));
        phi = THREE.Math.degToRad(90 - lat);
        theta = THREE.Math.degToRad(lon);

        function setOrientationControls(e) {
            if (!e.alpha) {
                return;
            }

            deviceO = true;
            controls.enabled = false;
            controls = new THREE.DeviceOrientationControls(camera);
            controls.connect();
            controls.update();

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
    mesh.material = videoMaterial['blank'];
    mesh.material.needsUpdate = true;
}

function addMaterial() {
    mesh.material = videoMaterial['video'];
    mesh.material.needsUpdate = true;
}

function animate() {
    render();
    requestAnimationFrame(animate);
    update(clock.getDelta());
}
function render() {
    if (renderer.domElement != canvasElement) // || !videoImage || videoImage.width==0 || videoImage.height==0)
        initWebGL();
    else
    {
        if (USE_RIFT) {
            effect.render(scene, camera);
        } else {
            renderer.render(scene, camera);
        }
    }
}
function update(dt) {

    if (keyboard.pressed("d")) {
        camera.setRotateY(camera.getRotateY() - VIEW_INCREMENT);
    }
    if (keyboard.pressed("a")) {
        camera.setRotateY(camera.getRotateY() + VIEW_INCREMENT);
    }
    if (keyboard.pressed("w")) {
        if (camera.getRotateX() < 90) { // restrict so they cannot look overhead
            camera.setRotateX(camera.getRotateX() + VIEW_INCREMENT);
        }
    }
    if (keyboard.pressed("s")) {
        if (camera.getRotateX() > -90) { // restrict so they cannot look under feet
            camera.setRotateX(camera.getRotateX() - VIEW_INCREMENT);
        }
    }
    if (deviceO) {
        controls.update(dt);
    }
}

THREE.PerspectiveCamera.prototype.setRotateX = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        camera.rotation.x = deg * (Math.PI / 180);
    }
};
THREE.PerspectiveCamera.prototype.setRotateY = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        camera.rotation.y = deg * (Math.PI / 180);
    }
};
THREE.PerspectiveCamera.prototype.setRotateZ = function (deg) {
    if (typeof (deg) == 'number' && parseInt(deg) == deg) {
        camera.rotation.z = deg * (Math.PI / 180);
    }
};
THREE.PerspectiveCamera.prototype.getRotateX = function () {
    return Math.round(camera.rotation.x * (180 / Math.PI));
};
THREE.PerspectiveCamera.prototype.getRotateY = function () {
    return Math.round(camera.rotation.y * (180 / Math.PI));
};
THREE.PerspectiveCamera.prototype.getRotateZ = function () {
    return Math.round(camera.rotation.z * (180 / Math.PI));
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

    camera.aspect = WIDTH / HEIGHT;
    camera.updateProjectionMatrix();
    renderer.setSize(WIDTH, HEIGHT);
    render();
}

function initSMHVR() {
    WIDTH = window.innerWidth;
    HEIGHT = window.innerHeight;

    initWebGL();
    animate();
    initGui();
}
;
