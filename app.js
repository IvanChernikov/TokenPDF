/* Variable Definition */
// Global file storage
window.Storage.files = [];

/* Responses */
function parseResponse(rawResponse) {
	var response = JSON.parse(rawResponse);
	if (response.Status == 'ok') {
		switch (response.Request) {
			case 'auth':
				// Store full token
				window.Storage.jwt = response.Data.jwt;
				var jwt = response.Data.jwt.split('.');
				var meta = atob(jwt[0]);
				var payload = atob(jwt[1]);
				var key = jwt[2];
				$('#TokenValue').html( [meta,payload,key].join('<br>') );
				setExpire(payload);
				showSuccess('Credentials accepted. Token acquired!');
				break;
			case 'list':
				// update PDF list
				console.log(response);
			case 'view':
				//
				console.log(response);
		}
	} else {
		// Error handling
		showError("Unkown error occured");
	}
}

/* Register app events */

// Token request
$('#LoginForm').submit( function(e) {
	e.preventDefault();
	$.post('api/auth.php', $('#LoginForm').serialize(), function (data) {
		// $('#TokenValue').text(Base64.decode(JSON.parse(data).jwt));
		parseResponse(data);
		//document.cookie = data;
	}).fail( function(response) {
		showError(JSON.parse(response.responseText).Data);
	});
});
// Upload files
$('#UploadForm').submit( function(e) {
	e.preventDefault();
	if (typeof window.Storage.jwt == 'undefined') {
		showError('Not authenticated. Please login before uploading.');
		return;
	}
	if (window.Storage.files.length == 0) {
		showError('No files selected');
		return;
	}
	files = new FormData();
	for (var i = 0; i < window.Storage.files.length; i++) {
		files.append('Files[]', window.Storage.files[i], window.Storage.files[i].name);
	}
	$.ajax({
		xhr: function () {
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function (e) {
				if (e.lengthComputable) {
					updateUploadProgress(e.loaded, e.total);
				}
			}, false);
			xhr.addEventListener("progress", function (e) {
				if (e.lengthComputable) {
					updateRequestProgress(e.loaded, e.total);
				}
			}, false);
			return xhr;
		},
		url: 'api/upload.php',
		headers: {'Authorization' : 'Bearer ' + window.Storage.jwt },
		type: 'POST',
		enctype: 'multipart/form-data',
		processData: false,
		contentType: false,
		cache: false,
		data: files,
		beforeSend: startProgress,
		success: function (data) {
			showSuccess('Files uploaded');
			// Reset form to free file input tag value
			$('#UploadForm')[0].reset();
			resetStorage();
		},
		error: function (response) {
			showError(JSON.parse(response.responseText).Data);
		}
	})
});

// Refresh PDF list
$('#ListForm').submit( function(e) {
	e.preventDefault();
	getList();
});

// [MISC] Token value toggle
$('#TokenShow').click( function(e) {
	$('#TokenShow').next().slideToggle(400);
});

// Warn about two file limit
$('#Files').change( function (e) {
	if (this.files.length > 2) {
		showError("You can only upload two PDFs at a time");
	}
	// Reset storage
	resetStorage();
	for (var i = 0; i < Math.min(this.files.length, 2); i++) {
		addToStorage(this.files[i]);
	}
});

// Drag and Drop
$('#FileDrag').on({
	'dragover dragenter': function (e) {
		e.preventDefault();
		e.stopPropagation();
	},
	'drop': function (e) {
		 var dataTransfer =  e.originalEvent.dataTransfer;
		if( dataTransfer && dataTransfer.files.length) {
			e.preventDefault();
			e.stopPropagation();
			resetStorage();
			$.each( dataTransfer.files, function (i, file) {
				if (file.type !== 'application/pdf') {
					showError('Only PDF files allowed');
				} else {
					console.log(file);
					addToStorage(file);
				}
			});
		}
	}
});

/* List and View requests */
// Get PDF list
function getList() {
	$.ajax({
		url: 'api/list.php',
		type: 'POST',
		dataType: 'json',
		headers: {'Authorization' : 'Bearer ' + window.Storage.jwt },
		success: renderList,
		error: function (response) {
			showError(JSON.parse(response.responseText).Data);
		}
	});
}

// Load pdf from server
function getFile(anchor) {
	var id = $(anchor).attr('data');
	$.ajax({
		url: 'api/view.php',
		type: 'POST',
		data: { 'id': id },
		dataType: 'json',
		headers: {'Authorization' : 'Bearer ' + window.Storage.jwt },
		success: function (data) {
			window.open(data.Data,'_blank');
		},
		error: function (response) {
			showError(JSON.parse(response.responseText).Data);
		}
	});
}
/* UI Specifics */

// Display popup message boxes
function showPopup(className, header, text) {
	var popup = $('.message.clone').clone();
	popup.find('h2').text(header);
	popup.find('p').text(text);
	popup.removeClass('clone');
	popup.addClass(className);
	popup.appendTo("#MessageContainer");
	popup.slideDown(400).delay(5000).slideUp(400, function () { $(this).remove(); });
}
function showError(message) {
	showPopup('error', 'Oops, an error occured!', message);
}
function showSuccess(message) {
	showPopup('success', 'Success!', message);
}
// Show UI after authorization
function setExpire(rawJwt) {
	var jwt = JSON.parse(rawJwt);
	window.Storage.expires = jwt.exp * 1000;
	window.Storage.expiresTimer = setInterval(tick, 1000);
	tick();
	$('#LoginBlock').fadeOut(400, function () {
		$('#TokenBlock, #UploadBlock, #ListBlock').fadeIn(400);
	});
}
// Render PDF list, response has an array of file info
function renderList(response) {
	var list = response.Data;
	$('#PDFList').html('');
	for (var i = 0; i < list.length; i++) {
		var item = $('.pdf.clone').clone();
		item.removeClass('clone');
		item.find('.filename').text(list[i]['filename']);
		item.find('.date').text(new Date(list[i]['date']).toLocaleDateString());
		item.find('.uploader').text(list[i]['user']);
		item.find('.pdfLink').attr('data',list[i]['id']).click(function (e) {
			getFile(this);
		});
		item.appendTo("#PDFList").slideDown(100);
	}
}
// Show files in storage
function resetStorage() {
	window.Storage.files = [];
	$('#FileList').html('');
}
// Adds a file to upload queue
function addToStorage(file) {
console.log(file);
	var count = window.Storage.files.length;
	if (count < 2) {
		window.Storage.files[count] = file;
		var item = $('.file.clone').clone();
		item.removeClass('clone');
		item.find('.filename').text(file.name);
		item.find('.filesize').text(sizeToString(file.size));
		item.appendTo('#FileList');
		item.slideDown(400)
	} else {
		showError('Cannot have more than 2 files');
	}
}
// [MISC] File size
function sizeToString(size) {
	var kb = Math.round(size/1024);
	var mb = Math.round(kb/1024); 
	return (mb < 1 ? kb + 'KB' : mb + 'MB');
}
// [MISC] Update expiration timer
function tick() {
	var timeLeft = window.Storage.expires - Date.now();
	if (timeLeft > 0) {
		var s = timeLeft/1000;
		var m = Math.floor(s/60);
		s = Math.floor(s-m*60);
		$('#TokenExpires').text( 'Expires in: ' + m + ':' + (s < 10 ? '0'+s : s));
	} else {
		$('#TokenExpires').text( 'Expired' );
	}
}
// Update progress bar
function startProgress() {
	$('#StatusBlock').slideDown(100);
	$('#StatusFront').css('width','0%');
}
function updateUploadProgress(current, total) {
	var percentComplete = Math.round(current/total*100) + '%'
	console.log(percentComplete);
	$('#StatusText').text(percentComplete);
	$('#StatusFront').animate({
		width: percentComplete
	}, 200);
}
function updateRequestProgress(current, total) {
	if (current == total) {
		console.log('Request complete!');
		$('#StatusBlock').delay(3000).slideUp(400);
	}
}
