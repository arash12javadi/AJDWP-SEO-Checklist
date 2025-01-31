//_____________________________________ Save Keywords _____________________________________//
//_____________________________________ Save Keywords _____________________________________//
//_____________________________________ Save Keywords _____________________________________//

function saveKeywords(customKeywords = null) {
  if (!customKeywords || customKeywords.length === 0) return;

  jQuery.ajax({
    type: "POST",
    url: keywordChecklistData.ajax_url,
    data: {
      action: "save_keyword_checklist",
      post_id: keywordChecklistData.post_id,
      keywords: JSON.stringify(customKeywords),
    },
    success: function (response) {
      console.log("✅ Response:", response);
      if (response.success) {
        loadKeywords(); // Refresh the list
      } else {
        console.error("❌ Error:", response.data.message);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error("❌ AJAX error:", textStatus, errorThrown, jqXHR.responseText);
    },
  });
}

jQuery(document).ready(function ($) {
  // ✅ Bulk Insert Keywords
  $("#save-keywords").on("click", function (event) {
    event.preventDefault();
    event.stopPropagation();

    const bulkKeywords = $("#bulk-keyword-input").val().trim();
    if (!bulkKeywords) return;

    let keywordLines = bulkKeywords.split("\n");
    let keywordData = [];
    let keywordMap = new Map(); // Using a map for efficient lookup

    keywordLines.forEach((line) => {
      // Split using tab, 2+ spaces, or /
      let parts = line.split(/\t|\s{2,}|\/+/); // Match tab, 2+ spaces, or one/more slashes

      if (parts.length < 3) return; // Skip invalid lines

      let keyword = parts[0].trim();
      let searchVolume = parseInt(parts[1]) || 0;
      let difficulty = parseInt(parts[2]) || 0;

      if (keywordMap.has(keyword)) {
        // ✅ If keyword exists, update SV and KD values
        let existingKeyword = keywordMap.get(keyword);
        existingKeyword.search_volume = searchVolume;
        existingKeyword.difficulty = difficulty;
      } else {
        // ✅ Add new keyword
        keywordMap.set(keyword, {
          keyword: keyword,
          search_volume: searchVolume,
          difficulty: difficulty,
          used: false, // Default
        });
      }
    });

    // Convert map values to an array
    keywordData = Array.from(keywordMap.values());

    console.log("Saving Keywords:", keywordData);
    saveKeywords(keywordData);
  });
});

//_____________________________________ Load Keywords _____________________________________//
//_____________________________________ Load Keywords _____________________________________//
//_____________________________________ Load Keywords _____________________________________//

// Define the function globally to reload keywords after changes
function loadKeywords() {
  const keywordTableBody = jQuery("#keyword-table tbody");

  jQuery
    .post(keywordChecklistData.ajax_url, {
      action: "get_keyword_checklist",
    })
    .done(function (response) {
      if (response.success) {
        keywordTableBody.empty();
        let keywords = response.data.keywords || [];

        keywords.forEach((keyword) => {
          let usedClass = keyword.used ? "used-keyword" : "";
          let usedIndicator = keyword.used ? '<span class="used-indicator">✅</span>' : "<span id='use-this-keyword'>✍🏼</span>";
          let pageLink = keyword.page_link ? `<a href="${keyword.page_link}" target="_blank">${keyword.page_title}</a>` : "<em>Not Assigned</em>";

          let row = `
          <tr class="${usedClass}" data-keyword="${keyword.keyword}">
            <td><input type="checkbox" class="delete-checkbox" data-keyword="${keyword.keyword}" ${keyword.used ? "disabled" : ""}></td>
            <td class="toggle-used">${usedIndicator}</td>
            <td>${keyword.keyword}</td>
            <td>${keyword.search_volume}</td>
            <td>${keyword.difficulty}</td>
            <td class="page-info">${pageLink}</td>
          </tr>
        `;
          keywordTableBody.append(row);
        });

        console.log("✅ Loaded Keywords:", keywords);
      }
    });
}

//_____________________________________ Use the keywords in the context - Toggle _____________________________________//
//_____________________________________ Use the keywords in the context - Toggle _____________________________________//
//_____________________________________ Use the keywords in the context - Toggle _____________________________________//

jQuery(document).ready(function ($) {
  // ✅ Hover effect for "Used" toggle (✅ ↔ ❌)
  $(document).on("mouseenter", ".use-btn", function () {
    if ($(this).text() === "✅") {
      $(this).text("❌");
    }
  });

  $(document).on("mouseleave", ".use-btn", function () {
    if ($(this).text() === "❌") {
      $(this).text("✅");
    }
  });

  // Function to clean page title for both WordPress and Elementor
  function getCleanPageTitle() {
    let cleanPageTitle = document.title
      .replace(/Edit Page “|” ‹.*?— WordPress/g, "") // ✅ Clean WordPress page title
      .replace(/Edit\s+"(.*?)"\s+with Elementor/, "$1") // ✅ Clean Elementor title format
      .trim();

    console.log("✅ Clean Page Title:", cleanPageTitle);
    return cleanPageTitle;
  }

  // ✅ Toggle "Used" status and save page info
  $(document).on("click", ".toggle-used", function () {
    let row = $(this).closest("tr");
    let keyword = row.data("keyword");
    let cleanPageTitle = getCleanPageTitle();
    row.find(".delete-checkbox").first().prop("checked", false);

    $.ajax({
      type: "POST",
      url: keywordChecklistData.ajax_url,
      data: {
        action: "toggle_keyword_used",
        post_id: keywordChecklistData.post_id, // ✅ Ensure post_id is included
        keyword: keyword,
        page_title: cleanPageTitle,
        page_link: window.location.href,
      },
      success: function (response) {
        console.log("✅ Toggle Response:", response);

        if (response.success) {
          if (row.hasClass("used-keyword")) {
            row.removeClass("used-keyword");
            row.find(".toggle-used").html("✍🏼"); // Remove checkmark
            row.find(".delete-checkbox").prop("disabled", false);
            row.find(".page-info").html("<em>Not Assigned</em>"); // 🔥 Remove page info
          } else {
            row.addClass("used-keyword");
            row.find(".toggle-used").html('<span class="used-indicator">✅</span>');
            row.find(".delete-checkbox").prop("disabled", true);
            row.find(".page-info").html(`<a href="${window.location.href}" target="_blank">${document.title}</a>`); // 🔥 Assign current page info
          }
        } else {
          console.error("❌ Error:", response.data.message);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("❌ AJAX error:", textStatus, errorThrown, jqXHR.responseText);
      },
    });
  });
});

//_____________________________________ Delete keyword from list _____________________________________//
//_____________________________________ Delete keyword from list _____________________________________//
//_____________________________________ Delete keyword from list _____________________________________//

jQuery(document).ready(function ($) {
  $("#delete-keywords").on("click", function () {
    const selectedKeywords = [];

    $("#keyword-table tbody input.delete-checkbox:checked").each(function () {
      selectedKeywords.push($(this).data("keyword"));
    });

    if (!selectedKeywords.length) {
      alert("❌ Please select at least one keyword to delete.");
      return;
    }

    // ✅ Show confirmation ONLY before deleting
    if (!confirm("⚠️ Are you sure you want to delete the selected keywords?")) {
      return;
    }

    // Disable button & show loading indicator
    $(this).prop("disabled", true).text("Deleting...");

    $.post(keywordChecklistData.ajax_url, {
      action: "delete_keywords",
      keywords_to_delete: JSON.stringify(selectedKeywords),
    })
      .done(function (response) {
        if (response.success) {
          // ✅ Silently remove rows from the table (No Alerts, No Success Message)
          $("#keyword-table tbody tr").each(function () {
            let keyword = $(this).find(".delete-checkbox").data("keyword");
            if (selectedKeywords.includes(keyword)) {
              $(this).fadeOut(300, function () {
                $(this).remove(); // Remove row after fade-out
              });
            }
          });
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("❌ AJAX error:", textStatus, errorThrown, jqXHR.responseText);
      })
      .always(function () {
        // ✅ Re-enable button after request completes
        $("#delete-keywords").prop("disabled", false).text("🗑️ Delete Selected");
      });
  });
});

//_____________________________________ Sorting Keywords _____________________________________//
//_____________________________________ Sorting Keywords _____________________________________//
//_____________________________________ Sorting Keywords _____________________________________//

jQuery(document).ready(function ($) {
  console.log("✅ Keyword Checklist Loaded");

  let sortDirection = {}; // Store sorting direction for each column

  $("#keyword-table th").on("click", function () {
    let sortKey = $(this).data("sort");
    let colIndex = $(this).index() + 1; // Get column index
    let rows = $("#keyword-table tbody tr").get();

    // Toggle sort direction
    sortDirection[sortKey] = sortDirection[sortKey] === "asc" ? "desc" : "asc";

    // Sorting logic based on the clicked column
    rows.sort((a, b) => {
      let aValue, bValue;

      if (sortKey === "select") {
        // ✅ Sort by checked/unchecked state
        aValue = $(a).find(".delete-checkbox").prop("checked") ? 1 : 0;
        bValue = $(b).find(".delete-checkbox").prop("checked") ? 1 : 0;
        return sortDirection[sortKey] === "asc" ? aValue - bValue : bValue - aValue;
      }

      if (sortKey === "used") {
        // ✅ Sort by "✅" (used) or "USE" (unused)
        aValue = $(a).find(".use-btn").text() === "✅" ? 1 : 0;
        bValue = $(b).find(".use-btn").text() === "✅" ? 1 : 0;
        return sortDirection[sortKey] === "asc" ? aValue - bValue : bValue - aValue;
      }

      if (sortKey === "keyword" || sortKey === "page_info") {
        // ✅ Sort A-Z and Z-A for keywords & pages
        aValue = $(a).find(`td:nth-child(${colIndex})`).text().trim().toLowerCase();
        bValue = $(b).find(`td:nth-child(${colIndex})`).text().trim().toLowerCase();
        return sortDirection[sortKey] === "asc" ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
      }

      if (sortKey === "search_volume" || sortKey === "difficulty") {
        // ✅ Sort numerical columns (Min-Max or Max-Min)
        aValue = parseInt($(a).find(`td:nth-child(${colIndex})`).text().trim()) || 0;
        bValue = parseInt($(b).find(`td:nth-child(${colIndex})`).text().trim()) || 0;
        return sortDirection[sortKey] === "asc" ? aValue - bValue : bValue - aValue;
      }

      return 0; // Default return if no condition matches
    });

    // ✅ Re-render sorted rows
    $("#keyword-table tbody").empty().append(rows);
  });

  // ✅ Prevent default behavior & toggle "USE" ↔ "✅"
  jQuery(document).on("click", ".use-btn", function (event) {
    event.preventDefault();
    let button = jQuery(this);
    let row = button.closest("tr");
    let keyword = row.data("keyword");
    let isUsed = button.text() === "✅";

    // Toggle button text
    button.text(isUsed ? "USE" : "✅");

    // ✅ Send AJAX request to update the database
    jQuery.post(keywordChecklistData.ajax_url, {
      action: "toggle_keyword_used",
      keyword: keyword,
      used: !isUsed,
    });
  });

  loadKeywords(); // ✅ Load keywords on page load
});

//_____________________________________ Search Keywords _____________________________________//
//_____________________________________ Search Keywords _____________________________________//
//_____________________________________ Search Keywords _____________________________________//
// ✅ Search Function: Search in ALL columns (Keyword, SV, KD, Page, Used)
jQuery("#keyword-search").on("input", function () {
  let searchVal = jQuery(this).val().toLowerCase();

  jQuery("#keyword-table tbody tr").each(function () {
    let rowText = jQuery(this).text().toLowerCase(); // Get entire row text
    if (rowText.includes(searchVal)) {
      jQuery(this).show();
    } else {
      jQuery(this).hide();
    }
  });
});

//_____________________________________ JS for elementor page edit side _____________________________________//
//_____________________________________ JS for elementor page edit side _____________________________________//
//_____________________________________ JS for elementor page edit side _____________________________________//

jQuery(document).ready(function ($) {
  console.log("✅ Checking keywordChecklistData:", typeof keywordChecklistData !== "undefined" ? keywordChecklistData : "NOT LOADED");

  // ✅ Ensure keywordChecklistData is defined before using it
  if (typeof keywordChecklistData !== "undefined") {
    const keywordChecklistButton = $("#keyword-checklist-button");
    const keywordChecklistPopup = $("#keyword-checklist-popup");

    if (keywordChecklistButton.length > 0 && keywordChecklistPopup.length > 0) {
      keywordChecklistButton.on("click", function () {
        keywordChecklistPopup.toggle();
      });
    } else {
      console.warn("⚠️ Keyword Checklist elements not found. Waiting for them...");
      waitForElement("#keyword-checklist-button", function () {
        console.log("✅ Keyword Checklist Button found. Initializing event listeners.");
        $("#keyword-checklist-button").on("click", function () {
          $("#keyword-checklist-popup").toggle();
        });
      });
    }

    function loadKeywordChecklist() {
      if (typeof keywordChecklistData.ajax_url !== "undefined") {
        $.post(keywordChecklistData.ajax_url, {
          action: "get_keyword_checklist",
          post_id: keywordChecklistData.post_id,
        }).done(function (response) {
          if (response.success && response.data.keywords) {
            console.log("✅ Loaded Keyword Checklist:", response.data.keywords);
          }
        });
      }
    }

    loadKeywordChecklist();

    $(document).on("change", '#keyword-checklist-popup input[type="checkbox"]', function () {
      saveKeywordChecklist(keywordChecklistData.post_id);
    });

    function saveKeywordChecklist(postID) {
      const checklist = {};

      $('#keyword-checklist-popup input[type="checkbox"]').each(function () {
        const id = $(this).attr("id");
        checklist[id] = { checked: $(this).is(":checked") };
      });

      if (typeof keywordChecklistData.ajax_url !== "undefined") {
        $.post(keywordChecklistData.ajax_url, {
          action: "save_keyword_checklist",
          post_id: postID,
          checklist: JSON.stringify(checklist),
        }).done(function (response) {
          if (response.success) {
            console.log("✅ Keyword Checklist saved successfully:", response.data.message);
          } else {
            console.error("❌ Error saving Keyword Checklist:", response.data.message);
          }
        });
      }
    }
  }

  // ✅ Utility function to wait for elements before executing code
  function waitForElement(selector, callback) {
    const interval = setInterval(function () {
      if ($(selector).length) {
        clearInterval(interval);
        callback();
      }
    }, 500);
  }
});
