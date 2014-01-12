jQuery(function ($) {

	function VideoThumbnailsBulkScanner( posts ) {
		this.currentItem        = 0;
		this.posts              = posts;
		this.paused             = false;
		this.newThumbnails      = 0;
		this.existingThumbnails = 0;
		this.delay              = 1000;
		this.delayTimer         = false;
		this.logList            = $('#vt-bulk-scan-results .log');
		this.progressBar        = $('#vt-bulk-scan-results .progress-bar');
	}

	VideoThumbnailsBulkScanner.prototype.log = function(text) {
		$('<li>'+text+'</li>').prependTo(this.logList).hide().slideDown(200);
		console.log(text);
	};

	VideoThumbnailsBulkScanner.prototype.startScan = function() {
		this.paused = false;
		if ( this.currentItem == 0 ) {
			this.log( 'Started Scanning' );
			this.progressBar.show();
			this.resetProgressBar();
			$('#video-thumbnails-scan-all-posts').parent().slideUp();
		} else {
			this.log( 'Resumed Scanning' );
		}
		this.scanCurrentItem();
	};

	VideoThumbnailsBulkScanner.prototype.pauseScan = function() {
		this.clearSchedule();
		this.paused = true;
		this.log( 'Paused Scanning' );
	};

	VideoThumbnailsBulkScanner.prototype.toggleScan = function() {
		if ( this.paused ) {
			this.startScan();
		} else {
			this.pauseScan();
		}
	};

	VideoThumbnailsBulkScanner.prototype.scanCompleted = function() {
		this.log( 'Done! Scanned ' + this.posts.length + ' posts' );
	};

	VideoThumbnailsBulkScanner.prototype.resetProgressBar = function() {
		$('#vt-bulk-scan-results .percentage').html('0%');
		this.progressBar
			.addClass('disable-animation')
			.css('width','0')
		this.progressBar.height();
		this.progressBar.removeClass('disable-animation');
	};

	VideoThumbnailsBulkScanner.prototype.updateProgressBar = function() {
		console.log( percentage = ( this.currentItem + 1 ) / this.posts.length );
		if ( percentage == 1 ) {
			progressText = 'Done!';
			this.scanCompleted();
		} else {
			progressText = Math.round(percentage*100)+'%';
		}
		$('#vt-bulk-scan-results .percentage').html(progressText);
		this.progressBar.css('width',(percentage*100)+'%');
	};

	VideoThumbnailsBulkScanner.prototype.updateCounter = function() {
		$('#vt-bulk-scan-results .stats .scanned').html( 'Scanned ' + (this.currentItem+1) + ' of ' + this.posts.length );
		$('#vt-bulk-scan-results .stats .found').html( 'Found ' + this.newThumbnails + ' new thumbnails and ' + this.existingThumbnails + ' existing thumbnails' );
	}

	VideoThumbnailsBulkScanner.prototype.updateStats = function() {
		this.updateProgressBar();
		this.updateCounter();
	}

	VideoThumbnailsBulkScanner.prototype.scheduleNextItem = function() {
		if ( ( this.currentItem + 1 ) < this.posts.length ) {
			var self = this;
			self.currentItem++;
			this.delayTimer = setTimeout(function() {
				self.scanCurrentItem();
			}, this.delay);
		}
	}

	VideoThumbnailsBulkScanner.prototype.clearSchedule = function() {
		clearTimeout( this.delayTimer );
	}

	VideoThumbnailsBulkScanner.prototype.scanCurrentItem = function() {

		if ( this.paused ) return false;

		if ( this.currentItem < this.posts.length ) {

			this.log( '[' + this.posts[this.currentItem] + '] Scanning ' + (this.currentItem+1) + ' of ' + this.posts.length );

			var data = {
				action: 'video_thumbnails_get_thumbnail_for_post',
				post_id: this.posts[this.currentItem]
			};
			var self = this;
			$.post(ajaxurl, data, function(response) {
				var result = $.parseJSON( response );
				if ( result.length == 0 ) {
					self.log( '[' + self.posts[self.currentItem] + '] No thumbnail' );
				} else {
					self.log( '[' + self.posts[self.currentItem] + '] ' + result.url + ' (' + result.type + ')' );
					if ( result.type == 'new' ) {
						self.newThumbnails++;
					} else {
						self.existingThumbnails++;
					}
				}
				self.updateStats();
				self.scheduleNextItem();
			});

		} else {
			this.updateStats();
			this.currentItem = 0;
		}

	};

	$('#video-thumbnails-scan-all-posts').on('click',function(e){
		e.preventDefault();
		var data = {
			action: 'video_thumbnails_bulk_posts_query'
		};
		$.post(ajaxurl, data, function(response) {
			var posts = $.parseJSON( response );
			var r = confirm("This will scan " + posts.length + " posts, continue?");
			if ( r == true ) {
				x = new VideoThumbnailsBulkScanner( posts );
				x.startScan();
			} else {
				x = "You pressed Cancel!";
			}
		});
	});

});