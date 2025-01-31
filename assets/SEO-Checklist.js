jQuery(document).ready(function ($) {
  console.log("Checking seoChecklistData:", typeof seoChecklistData !== "undefined" ? seoChecklistData : "NOT LOADED");
  console.log("Checking seoChecklistAdminData:", typeof seoChecklistAdminData !== "undefined" ? seoChecklistAdminData : "NOT LOADED");

  // Ensure Elementor-specific logic runs only if `seoChecklistData` exists
  if (typeof seoChecklistData !== "undefined") {
    const checklistButton = $("#seo-checklist-button");
    const checklistPopup = $("#seo-checklist-popup");

    if (checklistButton.length > 0 && checklistPopup.length > 0) {
      checklistButton.on("click", function () {
        checklistPopup.toggle();
      });
    } else {
      console.warn("SEO Checklist elements not found. Waiting for them...");
      waitForElement("#seo-checklist-button", function () {
        console.log("SEO Checklist Button found. Initializing event listeners.");
        $("#seo-checklist-button").on("click", function () {
          $("#seo-checklist-popup").toggle();
        });
      });
    }

    function loadChecklist() {
      if (typeof seoChecklistData.ajax_url !== "undefined") {
        $.post(seoChecklistData.ajax_url, {
          action: "get_seo_checklist",
          post_id: seoChecklistData.post_id,
        }).done(function (response) {
          if (response.success && response.data.checklist) {
            const checklist = JSON.parse(response.data.checklist);
            $('#seo-checklist-popup input[type="checkbox"]').each(function () {
              const id = $(this).attr("id");
              if (checklist[id]) {
                $(this).prop("checked", checklist[id].checked || false);
              }
            });
          }
        });
      }
    }

    loadChecklist();

    $(document).on("change", '#seo-checklist-popup input[type="checkbox"]', function () {
      saveChecklist(seoChecklistData.post_id);
    });

    function saveChecklist(postID) {
      const checklist = {};

      $('#seo-checklist-popup input[type="checkbox"]').each(function () {
        const id = $(this).attr("id");
        checklist[id] = { checked: $(this).is(":checked") };
      });

      if (typeof seoChecklistData.ajax_url !== "undefined") {
        $.post(seoChecklistData.ajax_url, {
          action: "save_seo_checklist",
          post_id: postID,
          checklist: JSON.stringify(checklist),
        }).done(function (response) {
          if (response.success) {
            console.log("Checklist saved successfully:", response.data.message);
          } else {
            console.error("Error saving checklist:", response.data.message);
          }
        });
      }
    }
  }

  // Ensure Post Editor logic runs only if `seoChecklistAdminData` exists
  if (typeof seoChecklistAdminData !== "undefined") {
    const postID = seoChecklistAdminData.post_id;
    const metaBox = $("#seo-checklist-meta-box");

    if (metaBox.length > 0) {
      metaBox.find('input[type="checkbox"]').on("change", function () {
        saveAdminChecklist(postID);
      });
    } else {
      console.warn("SEO Checklist Meta Box not found. Waiting for it...");
      waitForElement("#seo-checklist-meta-box", function () {
        console.log("SEO Checklist Meta Box found.");
        $("#seo-checklist-meta-box input[type='checkbox']").on("change", function () {
          saveAdminChecklist(postID);
        });
      });
    }

    function saveAdminChecklist(postID) {
      const checklist = {};

      metaBox.find('input[type="checkbox"]').each(function () {
        const id = $(this).attr("id");
        checklist[id] = $(this).is(":checked");
      });

      if (typeof seoChecklistAdminData.ajax_url !== "undefined") {
        $.post(seoChecklistAdminData.ajax_url, {
          action: "save_seo_checklist_meta",
          post_id: postID,
          checklist: JSON.stringify(checklist),
        }).done(function (response) {
          if (response.success) {
            console.log("Admin Checklist saved successfully:", response.data.message);
          } else {
            console.error("Error saving admin checklist:", response.data.message);
          }
        });
      }
    }
  }

  // Utility function to wait for elements to appear before executing code
  function waitForElement(selector, callback) {
    const interval = setInterval(function () {
      if ($(selector).length) {
        clearInterval(interval);
        callback();
      }
    }, 500);
  }
});
