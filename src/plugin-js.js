//_____________________________________ JS SCRIPTS ON ELEMENTOR PAGE EDIT _____________________________________//

jQuery(document).ready(function ($) {
  const checklistButton = $("#seo-checklist-button");
  const checklistPopup = $("#seo-checklist-popup");
  const postID = seoChecklistData.post_id;

  // Toggle checklist popup
  checklistButton.on("click", function () {
    checklistPopup.toggle();
  });

  // Retrieve checklist from the database
  function loadChecklist() {
    $.post(seoChecklistData.ajax_url, {
      action: "get_seo_checklist",
      post_id: postID,
    }).done(function (response) {
      if (response.success && response.data.checklist) {
        const checklist = JSON.parse(response.data.checklist);

        // Restore checkbox state
        $('#seo-checklist-popup input[type="checkbox"]').each(function () {
          const id = $(this).attr("id");
          if (checklist[id]) {
            $(this).prop("checked", checklist[id].checked || false);

            // For 'content-updates', show the last change date
            if (id === "content-updates" && checklist[id].date) {
              $("#content-updates-date").text(`✅ Last change: ${checklist[id].date}`);
            }
          }
        });
      }
    });
  }

  // Save checklist to the database
  function saveChecklist() {
    const checklist = {};

    // Collect checkbox states
    $('#seo-checklist-popup input[type="checkbox"]').each(function () {
      const id = $(this).attr("id");
      checklist[id] = {
        checked: $(this).is(":checked"),
      };

      // If 'content-updates' is checked, add the current date
      if (id === "content-updates" && $(this).is(":checked")) {
        const currentDate = new Date().toISOString().split("T")[0];
        checklist[id].date = currentDate;

        // Update the displayed date dynamically
        $("#content-updates-date").text(`Last change: ${currentDate}`);
      }
    });

    // Save to the database via AJAX
    $.post(seoChecklistData.ajax_url, {
      action: "save_seo_checklist",
      post_id: postID,
      checklist: JSON.stringify(checklist),
    }).done(function (response) {
      if (response.success) {
        console.log(response.data.message);
      }
    });
  }

  // Save checklist on checkbox change
  $('#seo-checklist-popup input[type="checkbox"]').on("change", saveChecklist);

  // Load the checklist when the editor loads
  loadChecklist();
});

//_____________________________________ META BOX JS SCRIPTS ON POST & PAGE EDIT _____________________________________//

jQuery(document).ready(function ($) {
  const postID = seoChecklistAdminData.post_id;

  // Save checklist state on checkbox change
  $('#seo-checklist-meta-box input[type="checkbox"]').on("change", function () {
    const checklist = {};

    // Collect checklist states
    $('#seo-checklist-meta-box input[type="checkbox"]').each(function () {
      const id = $(this).attr("id");
      checklist[id] = $(this).is(":checked");
    });

    // Send checklist state to the server
    $.post(seoChecklistAdminData.ajax_url, {
      action: "save_seo_checklist_meta",
      post_id: postID,
      checklist: JSON.stringify(checklist),
    }).done(function (response) {
      if (response.success) {
        console.log(response.data.message);
      } else {
        console.error("Error saving checklist:", response.data.message);
      }
    });
  });
});
