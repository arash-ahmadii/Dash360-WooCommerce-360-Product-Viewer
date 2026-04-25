(function ($) {
  "use strict";

  var frame = null;
  var $imageId = $("#dash360_image_id");
  var $preview = $("#dash360-image-preview");
  var $select = $("#dash360-select-image");
  var $remove = $("#dash360-remove-image");

  function setPreview(url, id) {
    $imageId.val(id || "");

    if (url) {
      $preview.html(
        '<img src="' +
          url +
          '" alt="" style="max-width:100%;height:auto;display:block;border:1px solid #dcdcde;border-radius:6px;" />'
      );
      $preview.show();
      $remove.show();
    } else {
      $preview.hide().empty();
      $remove.hide();
    }
  }

  $select.on("click", function (event) {
    event.preventDefault();

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: "Select 360 image",
      button: { text: "Use this image" },
      multiple: false,
      library: { type: "image" },
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      setPreview(url, attachment.id);
    });

    frame.open();
  });

  $remove.on("click", function (event) {
    event.preventDefault();
    setPreview("", "");
  });
})(jQuery);
