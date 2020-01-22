
var waitForEl = function(selector, callback) {
  if (jQuery(selector).length) {
    callback();
  } else {
    setTimeout(function() {
      waitForEl(selector, callback);
    }, 100);
  }
};

jQuery(document).ready(function($){
	var selector = 'div:contains("You do not appear to be a lobbyist"):last';
	var trigger_selector = '.ui-front.ui-dialog-content.ui-widget-content';
	var page_selector = '.webform-submission-form'
	
	waitForEl(trigger_selector, function() {
		$(trigger_selector).on('click', 'input', function(event) {
			if(
				event.currentTarget.value == 'No' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6_1' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6_2' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6_3' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6_4' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6_5' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_6_6' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_7' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_9' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_10' &&
				$(page_selector).children().first().attr('data-webform-key') != 'page_11'
			) {
				$('.js-form-wrapper .js-webform-webform-buttons').hide();
			} else {
				$('.webform-button--next').click();
			}
			waitForEl(selector, function() {
				setTimeout(function() {
				    if(($(selector).parent().attr('style') == "display: none;")) {
					  $('.webform-button--next').show();
				  	} else if (
				  		$(selector).parent().parent().attr('data-webform-key') != 'page_1' &&
				  		$(selector).parent().parent().attr('data-webform-key') != 'page_3' &&
				  		$(selector).parent().parent().attr('data-webform-key') != 'page_5' &&
				  		$(selector).parent().parent().attr('data-webform-key') != 'page_8'
				  	) {
					  $('.webform-button--next').hide();
				  	}
				}, 180);
			});
		});
	});
});