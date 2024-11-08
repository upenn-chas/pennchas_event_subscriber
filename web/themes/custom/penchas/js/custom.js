/**
 * @file
 * Global utilities.
 *
 */
(function($, Drupal) {

  'use strict';

  Drupal.behaviors.penchas = {
    attach: function(context, settings) {

        $(document).ready(function() {
          // Toggle sub-menu on click
          $('.mega-menu .menu-item > a').click(function(e) {
            console.log('asjdgajdhs');
            var $subMenu = $(this).siblings('.sub-menu');

            // Prevent default link behavior (if you don't want the link to be followed)
            e.preventDefault();

            // Toggle the sub-menu visibility
            $subMenu.toggleClass('open');
          });
        });

      // Custom code here
      $(document).ready(function () {
        if (window.outerWidth < 991) {

          $('.events').slick({
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 1,
            arrows: false,
            slidesToScroll: 1,
            responsive: [
              {
                breakpoint: 991,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 1
                }
              },
              {
                breakpoint: 575,
                settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1
                }
              },
            ]
          });
        }


        $('.internal-links .dropdown').hover(function () {
          $(this).addClass("selected", 3000);
        }, function () {
          $(this).removeClass("selected", 3000);
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

      const menu = document.querySelector(".select-menu");
      const [selectBtn, sBtnText] = [menu.querySelector(".select-btn"), menu.querySelector(".sBtn-text")];

      selectBtn.onclick = () => menu.classList.toggle("active");

      menu.querySelectorAll(".option").forEach(option => {
        option.onclick = () => {
          sBtnText.innerText = option.querySelector(".option-text").innerText;
          menu.classList.remove("active");
        };
      });

      // Function to close menu if clicked outside
      document.addEventListener("click", function (event) {
        if (!menu.contains(event.target) && !selectBtn.contains(event.target)) {
          menu.classList.remove("active");
        }
      });



      window.addEventListener('DOMContentLoaded', function () {
        var section1 = document.getElementById('section1');
        var section2 = document.getElementById('section2');

        function adjustSection2Top() {
          // Get the height of section1
          var section1Height = section1.offsetHeight;

          // Set the top value of section2 dynamically
          section2.style.top = section1Height + 'px';
        }

        // Run the function when the page loads
        adjustSection2Top();

        // Adjust dynamically when window is resized or content changes
        window.addEventListener('resize', adjustSection2Top);
      });





    }
  };

})(jQuery, Drupal);
