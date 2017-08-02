// Probka 4: JS z wykorzystaniem Three.js - animacja lotu przez gwiazdy, reagowanie
// na ruch kursorem, część projektu do tła landing-page.

var scene, camera, renderer;
var geometry, material, mesh, meshes;

var particleSystem, starsGeometry;

var framesPassed = 0;

var maxLookDistance = 2000;

var camera_view_width = 125;

var windowWidth = window.innerWidth;
var windowHeight = window.innerHeight;

init();
animate();


function init() {

    scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2( 0x000000, 0.00100 );    
    meshes = [];

    camera = new THREE.PerspectiveCamera( camera_view_width, window.innerWidth / window.innerHeight, 1, 8000 );
    camera.position.z = 0;


    starsGeometry = new THREE.Geometry();

    for ( var i = 0; i < 2000; i ++ ) {

        var star = new THREE.Vector3();

        var calc_x = THREE.Math.randFloatSpread(3000);
        var calc_y = THREE.Math.randFloatSpread(3000);

        if (calc_x < camera_view_width && calc_x >= 0 ) {
            calc_x = (camera_view_width + 5) - Math.random() * 20;            
        }
        if (calc_x < 0 && calc_x >= - camera_view_width ) {
            calc_x = ( - camera_view_width - 5) + Math.random() * 20;
        }    

        star.x = calc_x;
        star.y = calc_y;
        star.z = THREE.Math.randFloat(-2000, 0);
        meshes.push(Math.abs(star.z));
        starsGeometry.vertices.push( star );

    }


    var particleMaterial = new THREE.PointsMaterial({
        color: 0xffffff, 
        size: 6,
        map: THREE.ImageUtils.loadTexture("http://82.177.235.234/wp-content/themes/myahki-inmaking/images/myah-kulka_blur.png"),
        blending: THREE.AdditiveBlending,
        depthWrite: false,
        transparent: true,
        // alphaTest: 0.9
    });

    particleSystem = new THREE.Points(
        starsGeometry,     
        particleMaterial
    );

    particleSystem.sortParticles = true;
    scene.add( particleSystem );    

    renderer = new THREE.WebGLRenderer();
    renderer.setSize( window.innerWidth, window.innerHeight );
    

    document.body.appendChild( renderer.domElement );

}

function move_stars() {

    var is_dirty = false;
    var curr_camera_z = camera.position.z;

    for (var i = 0; i < starsGeometry.vertices.length; i++) {
        var particle = starsGeometry.vertices[i];

        var curr_x = particle.x;
        var curr_y = particle.y;              
        var curr_z = particle.z;
        
        if (curr_z < - 1500) { curr_z = 50 + THREE.Math.randFloat(0, 100); }
        curr_z -= 0.25;

        particle.set(curr_x, curr_y, curr_z);
        is_dirty = true;
    }

    if (is_dirty == true) {
        starsGeometry.verticesNeedUpdate = true;
        starsGeometry.computeVertexNormals();
        is_dirty = false;
    }
}

function animate() {
    requestAnimationFrame( animate );
    move_stars();
    camera.rotation.z +=0.0013;
    renderer.render( scene, camera );
}


window.onmousemove = function(wsth) {    
    var mouse_x = wsth.clientX;
    var mouse_y = wsth.clientY;
    updateCameraPosition(mouse_x, mouse_y);
}

function updateCameraPosition(mouse_x, mouse_y) {
    camera.position.x = 80 *  parseFloat( (mouse_x - (windowWidth / 2))  / windowWidth );
    camera.position.y = 80 *  parseFloat( (mouse_y - (windowHeight / 2)) / windowHeight );    
}
