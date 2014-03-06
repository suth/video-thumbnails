var video_thumbnails_bulk_scanner;

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
		this.language           = video_thumbnails_bulk_language;
	}

	VideoThumbnailsBulkScanner.prototype.log = function(text) {
		$('<li>'+text+'</li>').prependTo(this.logList).hide().slideDown(200);
		console.log(text);
	};

	VideoThumbnailsBulkScanner.prototype.disableSubmit = function(text) {
		$('#video-thumbnails-bulk-scan-options input[type="submit"]').attr('disabled','disabled');
	};

	VideoThumbnailsBulkScanner.prototype.enableSubmit = function(text) {
		$('#video-thumbnails-bulk-scan-options input[type="submit"]').removeAttr('disabled');
	};

	VideoThumbnailsBulkScanner.prototype.findPosts = function(text) {
		var data = {
			action: 'video_thumbnails_bulk_posts_query',
			params: $('#video-thumbnails-bulk-scan-options').serialize()
		};
		var self = this;
		this.disableSubmit();
		$('#queue-count').text(this.language.working);
		$.post(ajaxurl, data, function(response) {
			self.posts = $.parseJSON( response );
			if ( self.posts.length == 1 ) {
				queueText = self.language.queue_singular;
			} else {
				queueText = self.language.queue_plural.replace('%d',self.posts.length);
			}
			$('#queue-count').text(queueText);
			if ( self.posts.length > 0 ) {
				self.enableSubmit();
			}
		});
	};

	VideoThumbnailsBulkScanner.prototype.startScan = function() {
		this.disableSubmit();
		this.paused = false;
		if ( this.currentItem == 0 ) {
			this.log( this.language.started );
			this.progressBar.show();
			this.resetProgressBar();
			$('#video-thumbnails-bulk-scan-options').slideUp();
		} else {
			this.log( this.language.resumed );
		}
		this.scanCurrentItem();
	};

	VideoThumbnailsBulkScanner.prototype.pauseScan = function() {
		this.clearSchedule();
		this.paused = true;
		this.log( this.language.paused );
	};

	VideoThumbnailsBulkScanner.prototype.toggleScan = function() {
		if ( this.paused ) {
			this.startScan();
		} else {
			this.pauseScan();
		}
	};

	VideoThumbnailsBulkScanner.prototype.scanCompleted = function() {
		if ( this.posts.length == 1 ) {
			message = this.language.done + ' ' + this.language.final_count_singular;
		} else {
			message = this.language.done + ' ' + this.language.final_count_plural.replace('%d',this.posts.length);
		}
		this.log( message );
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
			progressText = this.language.done;
			this.scanCompleted();
		} else {
			progressText = Math.round(percentage*100)+'%';
		}
		$('#vt-bulk-scan-results .percentage').html(progressText);
		this.progressBar.css('width',(percentage*100)+'%');
	};

	VideoThumbnailsBulkScanner.prototype.updateCounter = function() {
		$('#vt-bulk-scan-results .stats .scanned').html( (this.currentItem+1) + '/' + this.posts.length );
		$('#vt-bulk-scan-results .stats .found-new').html( this.newThumbnails );
		$('#vt-bulk-scan-results .stats .found-existing').html( this.existingThumbnails );
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

			this.log( '[ID: ' + this.posts[this.currentItem] + '] ' + this.language.scanning_of.replace('%1$s',this.currentItem+1).replace('%2$s',this.posts.length) );

			var data = {
				action: 'video_thumbnails_get_thumbnail_for_post',
				post_id: this.posts[this.currentItem]
			};
			var self = this;
			$.ajax({
				url: ajaxurl,
				type: "POST",
				data: data,
				success: function(response) {
					var result = $.parseJSON( response );
					if ( result.length == 0 ) {
						self.log( '[ID: ' + self.posts[self.currentItem] + '] ' + self.language.no_thumbnail );
					} else {
						if ( result.type == 'new' ) {
							resultText = self.language.new_thumbnail;
						} else {
							resultText = self.language.existing_thumbnail;
						}
						self.log( '[ID: ' + self.posts[self.currentItem] + '] ' + resultText + ' ' + result.url );
						if ( result.type == 'new' ) {
							self.newThumbnails++;
						} else {
							self.existingThumbnails++;
						}
					}
					self.updateStats();
					self.scheduleNextItem();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					self.log( '[ID: ' + self.posts[self.currentItem] + '] ' + self.language.error + ' ' + errorThrown );
					self.updateStats();
					self.scheduleNextItem();
				}
			});

		} else {
			this.updateStats();
			this.currentItem = 0;
		}

	};

	video_thumbnails_bulk_scanner = new VideoThumbnailsBulkScanner();
	video_thumbnails_bulk_scanner.findPosts();

	$('#video-thumbnails-bulk-scan-options').on('change',function(e){
		video_thumbnails_bulk_scanner.findPosts();
	});

	$('#video-thumbnails-bulk-scan-options').on('submit',function(e){
		e.preventDefault();
		video_thumbnails_bulk_scanner.startScan();
	});

});