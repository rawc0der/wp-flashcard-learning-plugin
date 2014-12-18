function addWord(wd){
    var words = $('#WordCollection').text().split(' ');
    var contains = $.inArray( wd, words );
    if ( contains == -1 ) {
        var word = $('<span class="word"> '+wd.toLowerCase()+' <b> x </b> </span>')
        word.find('b').on('click',function(evt){
            var $wd = $(this).parent();
            $.when(
                removeWordFromLibrary(wd)
            ).then(function(){
                $wd.remove();
                console.log(' remove word ', wd)
            });
         })
        $.when(
             addWordToLibrary(wd)
        ).then(function(rsv){
            if (rsv === true) {
                console.log( 'adding new word ', wd )
                
            } else if (rsv === "1" || rsv === "2" || rsv === "new") {
                word.css({'text-decoration': 'line-through'});
            } 
                $('#WordCollection').append(word)
                console.log( 'added word ', wd )
        });
       
    }
}


function removeWordFromLibrary(wd){
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
            console.log('XHResponseRemove::', response );
            if (response.meta) {
                console.log('Word removed');
                dfd.resolve();
            }
        }
    );
     return dfd.promise()
}


function addWordToLibrary(wd){
     var dfd =  $.Deferred();
     $.post(
        window.FC_Ajax.ajaxurl,
        {
            // wp ajax action
            action : 'ajax-addWord',
            userID : window.FC_Ajax.userID,
            nextNonce : window.FC_Ajax.nextNonce,
            word:  wd
        },
        function( response,err,data ) {
            console.log('XHResponseAdd::', response );
            if (response.meta) {
                console.log('Word added');
                dfd.resolve(response.meta);
            }
        }
    );
     return dfd.promise()
}

function updateWord(wd, flag){
      console.log('setting ', wd, 'to ', flag);
     var dfd =  $.Deferred();
     $.post(
        window.FC_Ajax.ajaxurl,
        {
            // wp ajax action
            action : 'ajax-updateWord',
            userID : window.FC_Ajax.userID,
            nextNonce : window.FC_Ajax.nextNonce,
            word: wd,
            flag: flag
        },
        function( response,err,data ) {
            console.log('XHResponseUpdate::', response );
            if (response.meta) {
                console.log('Word updated');
            }
                dfd.resolve();
            
        }
    );
     return dfd.promise()
}

function deleteLibrary(){
     var dfd =  $.Deferred();
     $.post(
        window.FC_Ajax.ajaxurl,
        {
            // wp ajax action
            action : 'ajax-deleteLibrary',
            userID : window.FC_Ajax.userID,
            nextNonce : window.FC_Ajax.nextNonce,
        },
        function( response,err,data ) {
            console.log('XHResponseRemove::', response );
            if (response.meta === "") {
                console.log('Library empty');
                dfd.resolve();
            }
        }
    );
     return dfd.promise()
}

$ = jQuery;

$(document).ready(function(){
//  deleteLibrary()
    $('article:eq(0)').css({display:'block'});

     $('.flashcard-container li div.back').hide().css('left', 0);
    
    function mySideChange(front) {
        if (front) {
            $(this).parent().find('div.front').show();
            $(this).parent().find('div.back').hide();
            
        } else {
            $(this).parent().find('div.front').hide();
            $(this).parent().find('div.back').show();
        }
    }
    
    /* Replaced hover action with click action as Daniel Clarke suggested */ 
    $('.flashcard-container li').click(
        function () {
            $(this).find('div.fcontainer').stop().rotate3Di('toggle', 150, {direction: 'clockwise', sideChange: mySideChange});
        }
    );

    $('.save').click(function(evt){
        evt.stopPropagation();
            // console.log( 'come on',$('#wordTitle').val() )

        if( $('input[type=radio]:checked').length > 0 ) {
            var val = $('input[type=radio]:checked').val();
            var word = $(this).parent().parent().parent().parent().find('.wordTitle').val();
            var that = this;
            $.when ( updateWord(word, val) ).then(function(){
                    $(that).parent().parent().parent().parent().css({display:'none'})
                    $(that).parent().parent().parent().parent().next().css({display:'block'}) 
            });
        }

    })

    // $('.nextCard').click(function(evt){
    //     evt.stopPropagation();
    //         // console.log( 'come on',$('#wordTitle').val() )
    //     $(this).parent().parent().parent().parent().css({display:'none'})
    //     $(this).parent().parent().parent().parent().next().css({display:'block'}) 
    // })



    $('input[type=radio]').click(function(evt){
        evt.stopPropagation()
        
    })
    $('label').click(function(evt){
        evt.stopPropagation()
        
    })
     if ( $('.flashcard').length > 0 ) {
        var WordCollectionLibrary = $('<div id="FlashcardCollection" style="display:block;"><span class="arrow-w arrow"></span><div class="ftitle">Flashcard Collection </div>  <div> <p id="WordCollection"> </p></div></div>' );
        $('body').prepend( WordCollectionLibrary );


        $('span.arrow').click(function(){
            var cls = $(this).hasClass('arrow-w')
            if (cls) {
                $(this).addClass('arrow-e')
                $(this).removeClass('arrow-w')
                $('#FlashcardCollection').stop(true, false).animate({
                    'margin-left': '-270px'
                }, 900);
            } else {
                $(this).addClass('arrow-w')
                $(this).removeClass('arrow-e')
                $('#FlashcardCollection').stop(true, false).animate({
                    'margin-left': '0px'
                }, 900);
            }
        })

         $('.flashcard').click(function(evt){
            evt.stopPropagation();
            var cardName = $(this).text();
            addWord( cardName );
         });
     }
    

    

// end Ready
});

