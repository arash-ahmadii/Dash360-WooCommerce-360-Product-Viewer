(function () {
  "use strict";

  function parseConfig(node) {
    try {
      return JSON.parse(node.getAttribute("data-dash360") || "{}");
    } catch (err) {
      return {};
    }
  }

  function requestFs(el) {
    if (!document.fullscreenEnabled || !el.requestFullscreen) {
      return;
    }
    el.requestFullscreen().catch(function () {});
  }

  function setupModal() {
    var openButtons = document.querySelectorAll(".js-dash360-open");
    if (!openButtons.length) {
      return;
    }

    openButtons.forEach(function (openButton) {
      if (openButton.dataset.dash360Bound === "1") {
        return;
      }
      openButton.dataset.dash360Bound = "1";

      var target = openButton.getAttribute("data-dash360-target");
      var modal = target ? document.getElementById(target) : null;
      var closeBtn = modal ? modal.querySelector(".js-dash360-close") : null;
      var stage = modal ? modal.querySelector(".js-dash360-modal-stage") : null;
      var loader = modal ? modal.querySelector(".js-dash360-modal-loader") : null;
      var message = modal ? modal.querySelector(".js-dash360-modal-message") : null;

      if (!modal || !closeBtn || !stage) {
        return;
      }

      var viewer = null;
      var config = parseConfig(openButton);

      function setLoading(state) {
        if (loader) {
          loader.hidden = !state;
        }
      }

      function showError(show) {
        if (message) {
          message.hidden = !show;
        }
      }

      function ensureViewer() {
        if (viewer || typeof pannellum === "undefined" || !pannellum.viewer || !config.image) {
          return;
        }

        setLoading(true);
        showError(false);

        try {
          viewer = pannellum.viewer(stage, {
            type: "equirectangular",
            panorama: config.image,
            autoLoad: true,
            showControls: true,
            draggable: true,
            mouseZoom: true,
            doubleClickZoom: true,
            yaw: Number(config.yaw || 0),
            pitch: Number(config.pitch || 0),
            hfov: Number(config.hfov || 105),
            minHfov: 55,
            maxHfov: 120,
          });
        } catch (err) {
          setLoading(false);
          showError(true);
          return;
        }

        viewer.on("load", function () {
          setLoading(false);
          showError(false);
        });

        viewer.on("error", function () {
          setLoading(false);
          showError(true);
        });
      }

      function openModal() {
        modal.hidden = false;
        document.body.classList.add("dash360-modal-open");
        ensureViewer();
        requestFs(modal);
      }

      function closeModal() {
        modal.hidden = true;
        document.body.classList.remove("dash360-modal-open");
        if (document.fullscreenElement && document.exitFullscreen) {
          document.exitFullscreen().catch(function () {});
        }
      }

      openButton.addEventListener("click", openModal);
      closeBtn.addEventListener("click", closeModal);

      modal.addEventListener("click", function (event) {
        if (event.target === modal) {
          closeModal();
        }
      });

      document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) {
          closeModal();
        }
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupModal);
  } else {
    setupModal();
  }

  if (window.jQuery) {
    window.jQuery(window).on("elementor/frontend/init", function () {
      setupModal();
    });
  }
})();
