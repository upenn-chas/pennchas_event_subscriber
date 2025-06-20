$(document).ready(function () {

  if (navigator.userAgent.indexOf('Mac') > -1) {
    document.body.classList.add('mac-os');
  }

  $(".mobile-menu-toggle").click(function () {
    $(this).toggleClass('active');
  })

  var $slider = $('.two-column-slider');

  if ($slider.length) {
    var currentSlide;
    var slidesCount;
    var sliderCounter = document.createElement('div');
    sliderCounter.classList.add('slider__counter');

    var updateSliderCounter = function (slick, currentIndex) {
      currentSlide = slick.slickCurrentSlide() + 1;
      slidesCount = slick.slideCount;

      // Add leading zero if the number is less than 10
      var formattedCurrentSlide = currentSlide < 10 ? '0' + currentSlide : currentSlide;
      var formattedSlidesCount = slidesCount < 10 ? '0' + slidesCount : slidesCount;

      $(sliderCounter).text(formattedCurrentSlide + '/' + formattedSlidesCount);
    };

    $slider.on('init', function (event, slick) {
      $slider.append(sliderCounter);
      updateSliderCounter(slick);
    });

    $slider.on('afterChange', function (event, slick, currentSlide) {
      updateSliderCounter(slick, currentSlide);
    });

    $slider.slick();
  }




  $('.internal-links .dropdown').hover(function () {
    $(this).addClass("selected", 3000);
  }, function () {
    $(this).removeClass("selected", 3000);
  });



  const $menu = $(".select-menu");
  const $selectBtn = $menu.find(".select-btn");
  const $sBtnText = $menu.find(".sbtn-text");

  $selectBtn.on("click", function (event) {
    event.stopPropagation();
    $menu.toggleClass("active");
  });

  $menu.find(".option").on("click", function () {
    $sBtnText.text($(this).find(".option-text").text());
    $menu.removeClass("active");
  });

  // Close menu if clicked outside
  $(document).on("click", function (event) {
    if (!$menu.is(event.target) && $menu.has(event.target).length === 0 && !$selectBtn.is(event.target)) {
      $menu.removeClass("active");
    }
  });


  $(document).ready(function () {
    var $section1 = $('#section1');
    var $section2 = $('#section2');

    function adjustSection2Top() {
      // Get the height of section1
      var section1Height = $section1.outerHeight();

      // Set the top value of section2 dynamically
      $section2.css('top', section1Height + 'px');
    }

    // Run the function when the page loads
    adjustSection2Top();

    // Adjust dynamically when window is resized or content changes
    $(window).on('resize', adjustSection2Top);
  });


});

$('.offer-slider').slick({
  dots: false,
  infinite: true,
  speed: 300,
  slidesToShow: 3,
  arrows: true,
  slidesToScroll: 3,  
  centerPadding: '0', // Adjusts how much of the next slide is visible 
  responsive: [
    {
      breakpoint: 991,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        centerPadding: '20px', // Adjust for smaller screens
      }
    },
    {
      breakpoint: 575,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
        centerPadding: '30px', // Adjust for smallest screens
      }
    },
  ]
});



