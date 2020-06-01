(function() {
	'use strict';

	let uploads = () => {
		var fileInput = document.getElementById('validatedCustomFile');
		var fileList = [];

		fileInput.addEventListener("change", function(event) {
			fileList = [];

			for(let i = 0; i < fileInput.files.length; i++) {
				fileList.push(fileInput.files[i]);
			}

		    fileList.forEach(function(file) {
		    	console.warn(file);
		    	send(file);
		    });
			
		});
	};

	let send = (files) => {
		var formData = new FormData();
		var request = new XMLHttpRequest();
		var hash = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);

		formData.set('file', files);
		formData.set('hash', hash);

		request.onreadystatechange = () => {
			if (request.readyState === 4) {
				$('input[type="submit"]').after('<input type="hidden" value="'+ request.responseText +'" name="images_'+ hash +'" />');
			}
		};

		request.open("POST", "https://shop-piesetv.ro/api/shop/upload");
		request.send(formData);
	};

	uploads();
})(jQuery);