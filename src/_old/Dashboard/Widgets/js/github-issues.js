(function($) {
  $(function() {
    // Init Tabs
    $("ul.issues__tabs li").click(function() {
      var tab_id = $(this).attr("data-tab");

      $("ul.issues__tabs li").removeClass("--current");
      $(".issues__tab").removeClass("--current");

      $(this).addClass("--current");

      $("#" + tab_id).addClass("--current");
    });

    // Init Accordion
    var acc = document.getElementsByClassName("issue__header");
    var i;

    for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
          panel.style.maxHeight = null;
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      });
    }

    // New Issue Toggler
    var issue_form = $(".issues__form");
    $(".new-issue-toggle").on("click", function() {
      $(this).toggleClass("active");

      if (issue_form.css("max-height") != "0px") {
        issue_form.css("max-height", "0px");
      } else {
        let scrollHeight = issue_form.prop("scrollHeight") + "px";
        issue_form.css("max-height", scrollHeight);
      }
    });

    // Submit Form
    $("#issue-send").on("click", function(e) {
      e.preventDefault();

      // Get the Form
      var $form = $("#issue-form");

      // Response HTML Object
      var responseHtml = $(this).next(".issue-comment-response");
      responseHtml.html("");

      // Calls the save method on all editor instances in the collection. This can be useful when a form is to be submitted
      tinyMCE.triggerSave();

      $.ajax({
        dataType: "json",
        type: "POST",
        url: $form.attr("action"),
        data: $form.serialize(),
        cache: false
      })
        .done(function(response) {
          console.log(response);
          if (true === response.error) {
            console.warn("Something went wrong!");
            responseHtml.html("Da ist etwas schief gelaufen!");
          } else {
            console.info("Success");
            responseHtml.html("Erfolgreich!");
          }
          // Reset Form
          $form.trigger("reset");
        })
        .fail(function() {
          console.warn("error");
        })
        .always(function() {
          console.log("complete");
        });
    });

    // Submit Comment Form
    $(".issue-comment-submit").on("click", function(e) {
      e.preventDefault();

      // Get the Form
      var $form = $(this).parent(".issue__comment-form");

      // Response HTML Object
      var responseHtml = $(this).next(".issue-comment-response");
      responseHtml.html("");

      // Send Ajax
      $.ajax({
        dataType: "json",
        type: "POST",
        url: $form.attr("action"),
        data: $form.serialize(),
        cache: false
      })
        .done(function(response) {
          console.log(response);
          if (true === response.error) {
            console.warn("Something went wrong!");
            responseHtml.html("Da ist etwas schief gelaufen!");
          } else {
            console.info("Success");
            responseHtml.html("Erfolgreich!");
          }
          // Reset Form
          $form.trigger("reset");
        })
        .fail(function() {
          console.warn("error");
        })
        .always(function() {
          console.log("complete");
        });

      // Create an FormData object
      // $.ajax({
      //   type: "POST",
      //   url: $form.attr("action"),
      //   data: $form.serialize(),
      //   cache: false,
      //   success: function(data, textStatus, XMLHttpRequest) {
      //     console.log(data);
      //     $form.trigger("reset");
      //   },
      //   error: function(XMLHttpRequest, textStatus, errorThrown) {
      //     alert(errorThrown);
      //   }
      // });
    });
  });
})(jQuery);
