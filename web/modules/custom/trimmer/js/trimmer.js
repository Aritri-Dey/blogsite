// js to trim body field to 50 words.
(function ($) {
    $(document).ready(function () {
      $('.field--name-body').each(function(){
        var words = $(this).text();
        console.log(words);
        var maxWords = 50;
        var wordLength = WordCount(words);
     
        if(wordLength > maxWords){
          // Splits the string into an array of words
          const arrWord = words.trim().split(/\s+/);
          // Getting first 50 words from the array and joining with space.
          const fiftyWord = arrWord.slice(0, 50).join(' ');
          // Storng the remaining text to show on toggle.
          const remaining = arrWord.slice(maxWords, wordLength).join(' ');
  
          html = fiftyWord +'<span class="show-more" style="display:none;"> ' + remaining +'</span>' + '<a href="#" class="more">...Read More</a>'
          $(this).html(html)
          $(this).find('a.more').click(function(event){
            $(this).toggleClass("less");
            event.preventDefault();
            if($(this).hasClass("less")){
              $(this).html("Show Less")
              $(this).parent().find(".show-more").show();
            }
            else {
              $(this).html("...Read More")
              $(this).parent().find(".show-more").hide();
            }
          })
        }
      })
    });
  })(jQuery);
  
  // Function to count number of words. 
  function WordCount(text) {
    var total = 0;
    // console.log(text[7]);
    for (var i = 0; i < text.length; i++){
      if (text[i] == ' ') { 
        total++;
      }
    }
    total++; 
    // console.log(total);
    return total;
  }
  