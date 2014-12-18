$ = jQuery;

// color class
var CClass;
var CClasses = ['nlearned', 'plearned', 'learned'];
var siteurl = $('#siteurl').val();
function addWord(wd, idx){

    var words = $('#WordCollection').text().split(' ');
    var contains = $.inArray( wd, words );
    if ( contains == -1 ) {
        var word = $('<span class="word '+CClasses[idx-1]+'"> <a class="noc" target="_blank" href="'+siteurl+'?flashcard='+wd+'">'+wd+'</a> <b> x </b> </span>')
        word.css({
         	'background-color': CClass[idx-1]
         }).find('b').on('click',function(evt){
           var $wd = $(this).parent();
           $.when(
            	removeWord(wd)
           	).then(function(){
	            $wd.remove();
	            console.log(' remove word ', wd)
           	});
         })
        $('#WordCollection').append(word)
        console.log( 'add word ', wd )
    }
}

function createWordLibrary() {
	 $.post(
		window.FC_Ajax.ajaxurl,
		{
			// wp ajax action
			action : 'ajax-getWords',
			userID : window.FC_Ajax.userID,
			nextNonce : window.FC_Ajax.nextNonce,
		},
		function( response,err,data ) {
			console.log('XHResponse::', response );
			if (response.meta) {
				for (var key in response.meta) {
					var wordObj = response.meta[key];
            		console.log('%c Word Object ==>', 'color:red', wordObj);
            		var wordStr = Object.keys(wordObj)[0] ;
					addWord( wordStr ,wordObj[wordStr] );
				};
			}
		}
	);
}

function removeWord(wd){
	 var dfd =  $.Deferred();
	 $.post(
		window.FC_Ajax.ajaxurl,
		{
			// wp ajax action
			action : 'ajax-removeWord',
			userID : window.FC_Ajax.userID,
			nextNonce : window.FC_Ajax.nextNonce,
			word: wd
		},
		function( response,err,data ) {
			console.log('XHResponse::', response );
			if (response.meta) {
				console.log('Word removed');
				dfd.resolve();
			}
		}
	);
	 return dfd.promise()
}

function saveNoCards(no){
	var dfd =  $.Deferred();
	 $.post(
		window.FC_Ajax.ajaxurl,
		{
			// wp ajax action
			action : 'ajax-changeNo',
			userID : window.FC_Ajax.userID,
			nextNonce : window.FC_Ajax.nextNonce,
			no:no
		},
		function( response,err,data ) {
			console.log('XHResponse::', response );
			if (response.meta) {
				console.log('changed no');
				dfd.resolve();
			}
		}
	);
	 return dfd.promise()
}


$(document).ready(function(){

    CClass = [ $('#color_n').val(), $('#color_p').val(), $('#color_l').val() ];

	console.log('FlashcardCollection Admin::init');
    createWordLibrary();

    $('#changeNo').click(function(){
    	var no = $('#no').val();
    	if(no !== '') saveNoCards(no);
    })
});