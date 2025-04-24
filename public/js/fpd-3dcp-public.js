(function( $ ) {
	'use strict';

	function activate3dPreview(){
		const scene_width = fpd_3dcp.scene_width;
		const scene_height = fpd_3dcp.scene_height;
		const cube_height = fpd_3dcp.cube_height;
		const plane_order_array = fpd_3dcp.plane_order;
		const plane_order = plane_order_array.split(',').map(str => parseInt(str.trim()));
		const cubeSides = 6;
		const button_name = fpd_3dcp.button_name;
		const button_class = fpd_3dcp.button_class;
		const previewBtn = document.querySelector('.' + button_class);
		const canvas = document.getElementById('fpd-3dcp-cube-canvas');
		const overlay = document.getElementById('fpd-3dcp-cube-preview-overlay');
		const closeBtn = document.getElementById('fpd-3dcp-close-preview');
		const box = document.getElementById('fpd-3dcp-info-message');
		const closeMessageBtn = document.getElementById('fpd-3dcp-close-message');
		const storageKey = 'fpd_3dcp_quality_notice_closed';
		let renderer, scene, camera, cube, controls, animationId;  // Declare globals

		if ( !canvas || !overlay || !closeBtn ) return;

		function showPreview(){
			const designer = window.fancyProductDesigner;

			if (!designer || typeof designer.getProductDataURL !== 'function') {
				console.log('Fancy Product Designer not ready!');
				return;
			}

			//Show the overlay
			overlay.style.display = 'flex';

			designer.getProductDataURL(async function(dataURL){
				if (dataURL){
					// Slice the single image into 6 vertical sections
					const imageUrls = await sliceCenteredRegions(dataURL, scene_width, scene_height, cube_height, cubeSides);

					// Now use these individual images on each cube side

					const customOrder = plane_order; // your desired order
					const reordered = customOrder.map(index => imageUrls[index]);
					buildCube(reordered);

					//buildCube(imageUrls);
				}else{
					console.error("Failed to capture the design.");
				}
			});
		}

		if( previewBtn ){
			previewBtn.addEventListener('click', showPreview);
		}

		function cubeRendererCleanup() {
			console.log( 'Must remove rendered cube and clen it up to speed up website');
		}

		function closeOverlay() {
			overlay.style.display = 'none';

		//Stop the animation loop
		if (animationId) {
			cancelAnimationFrame(animationId);
			animationId = null;
		}

		// Optional: Dispose THREE.js scene/render stuff
		if (typeof cubeRendererCleanup === 'function') {
			cubeRendererCleanup();
		}
		}


		// Close when ESC is pressed
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closeOverlay();
			}
		});

		// Close when close button is clicked
		closeBtn.addEventListener('click', closeOverlay);


		function sliceCenteredRegions(base64Image, scene_width, scene_height, cube_height, cubeSides){
			return new Promise((resolve) => {
				const image = new Image();
				image.onload = () => {
					const results = [];

					// Calculate cropping start (centered)
					const cropX = (scene_width - cube_height) / 2;
					const cropY = (scene_height - cube_height) / 2;

					for (let i = 0; i < cubeSides; i++){
						const canvas = document.createElement('canvas');
						canvas.width = cube_height;
						canvas.height = cube_height;
						const ctx = canvas.getContext('2d');

						ctx.drawImage(
							image,
							cropX, cropY + i * scene_height,     // Source X, Y
							cube_height, cube_height,     // Source W, H
							0, 0,                               // Destination X, Y
							cube_height, cube_height      // Destination W, H
						);

						const sliceDataUrl = canvas.toDataURL('image/png');
						results.push(sliceDataUrl);
					}

					resolve(results);
				};

				image.src = base64Image;
			});
		}


		function buildCube(imageUrls) {
			renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
			renderer.setSize(window.innerWidth, window.innerHeight);
			renderer.setPixelRatio(window.devicePixelRatio); // Ensures crisp rendering on high-DPI screens

			scene = new THREE.Scene();
			scene.background = new THREE.Color(0xf0f0f0); // light gray

			camera = new THREE.PerspectiveCamera(
				75,
				window.innerWidth / window.innerHeight,
				0.1,
				1000
			);
			camera.position.z = 3;

			// Make sure cube camera is farther back on mobile screens
			if (window.innerWidth < 600) {
				camera.position.z = 5;
			}

			controls = new THREE.OrbitControls(camera, renderer.domElement);
			controls.minDistance = 3; // Minimum zoom (closer to the object)
			controls.maxDistance = 10; // Maximum zoom (farther from the object)

			// Animation controls - stop and resume on action
			let isUserInteracting = false;
			let lastInteractionTime = Date.now();
			let autoRotate = true;

			// Detect interaction events
			controls.addEventListener('start', () => {
				isUserInteracting = true;
				autoRotate = false;
			});

			controls.addEventListener('end', () => {
				lastInteractionTime = Date.now();
				isUserInteracting = false;
			});

			// Pad the imageUrls array to ensure it has 6 items
			while (imageUrls.length < 6) {
				imageUrls.push(null);
			}

			const [right, left, top, bottom, front, back] = imageUrls;

			const loadTextureOrColor = (url) => {
				if (url) {
					return new THREE.MeshStandardMaterial({
						map: new THREE.TextureLoader().load(url),
						roughness: 0.9,          // High roughness = matte
						metalness: 0.0,          // Plastic is non-metal
						side: THREE.DoubleSide
					});
				} else {
					return new THREE.MeshStandardMaterial({
						color: 0xffffff,         // Matte white
						roughness: 0.9,
						metalness: 0.0,
						side: THREE.DoubleSide
					});
				}
			};

			const materials = [
				loadTextureOrColor(right),   // +X (Right)
				loadTextureOrColor(left),    // -X (Left)
				loadTextureOrColor(top),     // +Y (Top)
				loadTextureOrColor(bottom),  // -Y (Bottom)
				loadTextureOrColor(front),   // +Z (Front)
				loadTextureOrColor(back)     // -Z (Back)
			];

			const geometry = new THREE.BoxGeometry(2, 2, 2);
			cube = new THREE.Mesh(geometry, materials);
			scene.add(cube);

			// 🧵 Use a curve to simulate a natural dangling wire
			const curvePath = new THREE.CatmullRomCurve3([
				new THREE.Vector3(0, 0, -1),         // Point where wire exits the lamp
				new THREE.Vector3(0, 0, -1.3),       // Straight out for a short distance
				new THREE.Vector3(0, -0.2, -1.6),    // Begin bending downward
				new THREE.Vector3(0.1, -0.6, -1.8),  
				new THREE.Vector3(0.2, -1.2, -2.0),  // More downward sag
			]);

			const tubeGeometry = new THREE.TubeGeometry(curvePath, 40, 0.015, 8, false);
			const wireMaterial = new THREE.MeshStandardMaterial({
				color: 0x222222,       // Dark gray-black rubbery color
				roughness: 0.8,        // High roughness = matte finish
				metalness: 0.1,         // Very low metalness
				depthTest: true
			});

			const curvedWire = new THREE.Mesh(tubeGeometry, wireMaterial);

			// Attach to cube so it moves together
			cube.add(curvedWire);

			const endPoint = curvePath.getPoint(1); // t = 1 is the end of the curve
			const capGeometry = new THREE.CylinderGeometry(0.015, 0.015, 0.005, 16);
			const wireCap = new THREE.Mesh(capGeometry, wireMaterial);

			wireCap.position.copy(endPoint);

			// Optional: Rotate so it's aligned to the direction of the curve
			const tangent = curvePath.getTangent(1);
			const axis = new THREE.Vector3(0, 1, 0);
			wireCap.quaternion.setFromUnitVectors(axis, tangent.normalize());

			cube.add(wireCap);

			// 🔺 Truncated cone at wire base (a short tapered cylinder)
			const truncatedConeGeometry = new THREE.CylinderGeometry(0.042, 0.03, 0.11, 40); // top radius, bottom radius, height, segments
			const truncatedConeMaterial = new THREE.MeshStandardMaterial({
				color: 0x222222,       // Dark gray-black rubbery color
				roughness: 0.8,        // High roughness = matte finish
				metalness: 0.1,         // Very low metalness
				depthTest: true
			});
			const truncatedCone = new THREE.Mesh(truncatedConeGeometry, truncatedConeMaterial);

			// Align with wire direction
			truncatedCone.rotation.x = Math.PI / 2;

			// Position at wire entrance
			truncatedCone.position.z = -1.056;
			truncatedCone.position.y = 0.004;
			truncatedCone.position.x = 0;

			cube.add(truncatedCone);

			// Ambient light for overall brightness
			const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
			scene.add(ambientLight);

			// Directional light for shadows and highlights
			const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
			directionalLight.position.set(5, 5, 5);
			scene.add(directionalLight);

			// Directional light for shadows and highlights
			const fillLight = new THREE.DirectionalLight(0xffffff, 0.8);
			fillLight.position.set(-5, -5, -5);
			scene.add(fillLight);

			//🎬 Animate scene
			function animate() {
				animationId = requestAnimationFrame(animate);

				// If user hasn't interacted for 7 seconds, resume rotation
				if (!isUserInteracting && Date.now() - lastInteractionTime > 5000) {
					autoRotate = true;
				}

				//Apply auto-rotation only if allowed
				if (autoRotate) {
					cube.rotation.y += 0.002;
				}

				renderer.render(scene, camera);
			}

			animate();
		}

		//Awaiting for 1 second for the toolbar to become available
		function waitForToolbar(callback, retries = 20){
			const toolbar = document.querySelector('fpd-actions-menu');
			if(toolbar){
				callback(toolbar);
			}else if (retries > 0){
				setTimeout(() => waitForToolbar(callback, retries - 1), 600);
			}else{
				console.warn("Unable to add 3D Preview inside FPD toolbar. Toolbar not found.");
			}
		}

		//Add custom button once the Fancy Product Designer toolbar has loaded
		waitForToolbar(function (toolbar) {
			const customBtn = document.createElement('div');
			//customBtn.innerText = button_name;
			customBtn.classList.add('fpd-btn', 'fpd-tooltip', button_class);
			customBtn.setAttribute('aria-label', button_name);

			customBtn.innerHTML = '<i class="fpd-icon-3d-preview"></i><span>' + button_name + '</span><input type="checkbox" class="fpd-switch">';

			//Find the target container
			const targetContainer = document.querySelector('div[data-pos="center"] fpd-actions-menu [data-action="zoom"]');

			// Append the button *after* <fpd-actions-menu> inside the same div
			if (targetContainer) {
				targetContainer.insertAdjacentElement('afterend', customBtn);
			} else {
				console.warn('Unable to add 3D Preview. Target container not found!');
			}

			customBtn.onclick = () => {
				showPreview();
			};
		});

		//Show quality notice every 4 hours after it has been closed
		const now = new Date().getTime();
		const storedUntil = localStorage.getItem(storageKey);

		if (!storedUntil || now > parseInt(storedUntil)){
			box.classList.add('show');
		}

		closeMessageBtn.addEventListener('click', function (){
			const fourHoursLater = now + (4 * 60 * 60 * 1000); // 4 hours in ms
			localStorage.setItem(storageKey, fourHoursLater);
			box.classList.add('fadeout');
			setTimeout(() => {
				box.classList.remove('show');
			}, 3000); // show after 3 seconds
		});


	};

	$(document).on('DOMContentLoaded', activate3dPreview);

})( jQuery );