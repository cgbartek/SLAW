$(function() {

  $('.console-input').focus();

  $('.console-input').on("enterKey",function(e){
     e.preventDefault;
     var input = $(this).val();
     var inputArr = input.split(' ');
     $(this).val('');

     // Register
     if (inputArr[0] == 'register') {
       $.post('api/', {action: "userAdd", username: inputArr[1], password: inputArr[2], passwordVerify: inputArr[3]})
       .done(function(data) {
           data = $.parseJSON(data);
           if(data.success) {
             output(data.success);
           } else if (data.error) {
             output(data.error);
           } else {
             output(data);
           }
       });
     }

     // Login
     if (inputArr[0] == 'login') {
       $.post('api/', {action: "userLogin", username: inputArr[1], password: inputArr[2]})
       .done(function(data) {
           data = $.parseJSON(data);
           if(data.success) {
             output(data.success);
           } else if (data.error) {
             output(data.error);
           } else {
             output(data);
           }
       });
     }

     // Get Game List
     if (inputArr[0] == 'list') {
       $.post('api/', {action: "gameList"})
       .done(function(data) {
         data = $.parseJSON(data);
         if(data.success) {
            output('CURRENT GAMES:');
           var out = data.success.split(',');
            out.forEach(function(item){
              output(item);
            });
           //output(data.success);
         } else if (data.error) {
           output(data.error);
         } else {
           output(data);
         }
       });
     }

     // Create Game
     if (inputArr[0] == 'create') {
       $.post('api/', {action: "gameAdd", game: inputArr[1]})
       .done(function(data) {
         data = $.parseJSON(data);
         if(data.success) {
           output(data.success);
         } else if (data.error) {
           output(data.error);
         } else {
           output(data);
         }
       });
     }

     // Join Game
     if (inputArr[0] == 'join') {
       $.post('api/', {action: "gameJoin", game: inputArr[1]})
       .done(function(data) {
         data = $.parseJSON(data);
         if(data.success) {
           output(data.success);
         } else if (data.error) {
           output(data.error);
         } else {
           output(data);
         }
       });
     }
  });

  $('.console-input').keyup(function(e){
      if(e.keyCode == 13)
      {
          $(this).trigger("enterKey");
      }
  });

  function output(str) {
    $('.console').html($('.console').html() + '<br>' + str);
    $('.console').prop({ scrollTop: $(".console").prop("scrollHeight") });
  }

});

/*$('.leftSide').on('click','a', function(e) {
  e.preventDefault();
  $('#article-form section').hide();
  $($(this).attr('href')).fadeIn(200);
});*/
