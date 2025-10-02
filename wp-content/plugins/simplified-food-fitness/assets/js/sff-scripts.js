jQuery(document).ready(function($) {
    function sffFormatMacroLabel(key) {
        if (!key) {
            return '';
        }
        return key.replace(/_/g, ' ').replace(/\b\w/g, function(letter) {
            return letter.toUpperCase();
        });
    }

    function sffCollectMacroValues() {
        var values = {};
        $('[name^="sff_macros"]').each(function() {
            var match = $(this).attr('name').match(/\[(.+)\]/);
            if (!match || !match[1]) {
                return;
            }
            var numeric = parseFloat($(this).val());
            if (isNaN(numeric)) {
                return;
            }
            values[match[1]] = numeric;
        });
        return values;
    }

    function sffShowMacroSummary(map, source) {
        map = map || {};
        var $summary = $('#sff-macro-summary');
        if (!$summary.length) {
            return;
        }

        var fields = (window.sff_ajax_obj && Array.isArray(sff_ajax_obj.macro_fields) && sff_ajax_obj.macro_fields.length)
            ? sff_ajax_obj.macro_fields
            : Object.keys(map);

        var gridItems = '';
        var hasEntries = false;

        fields.forEach(function(field) {
            if (!Object.prototype.hasOwnProperty.call(map, field)) {
                return;
            }
            var numeric = parseFloat(map[field]);
            if (isNaN(numeric)) {
                return;
            }
            if (Math.abs(numeric) < 0.0001) {
                return;
            }
            hasEntries = true;
            gridItems += '<div class="sff-macro-summary-item">' +
                '<span class="sff-macro-summary-label">' + sffFormatMacroLabel(field) + '</span>' +
                '<span class="sff-macro-summary-value">' + numeric.toFixed(2) + '</span>' +
            '</div>';
        });

        if (!hasEntries) {
            $summary.hide().empty();
            return;
        }

        var title = 'Nutrition Summary';
        if (source === 'scan') {
            title = 'Values from Scanned Label';
        } else if (source === 'usda') {
            title = 'Values from USDA';
        } else if (source === 'manual') {
            title = 'Values from Manual Entry';
        }

        var html = '<div class="sff-macro-summary-title">' + title + '</div>' +
            '<div class="sff-macro-summary-grid">' + gridItems + '</div>';

        $summary.html(html).fadeIn(150);
    }

    function sffPopulateMacros(map, source) {
        map = map || {};
        var hasField = false;
        $.each(map, function(k, v) {
            var $field = $('[name="sff_macros[' + k + ']"]');
            if ($field.length) {
                $field.val(v);
                hasField = true;
            }
        });

        if (hasField) {
            sffShowMacroSummary(map, source);
        } else if (source) {
            $('#sff-macro-summary').hide().empty();
        }
    }

    function sffEscapeHtml(value) {
        if (value === undefined || value === null) {
            return '';
        }
        return String(value).replace(/[&<>"']/g, function(match) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[match];
        });
    }

    function sffHasValue(value) {
        return value !== undefined && value !== null && value !== '';
    }

    function sffMetaRow(label, value) {
        if (!sffHasValue(value)) {
            return '';
        }
        return '<div><strong>' + sffEscapeHtml(label) + ':</strong> <span>' + sffEscapeHtml(value) + '</span></div>';
    }

    function sffFindAttribute(attributes, attributeName) {
        if (!Array.isArray(attributes) || !attributeName) {
            return '';
        }

        var match = '';
        $.each(attributes, function(_, attribute) {
            if (match) {
                return false;
            }
            if (attribute && attribute.name === attributeName && sffHasValue(attribute.value)) {
                match = attribute.value;
            }
        });
        return match;
    }

    function sffBuildNutrientSection(items) {
        if (!Array.isArray(items) || !items.length) {
            return '';
        }

        var rows = '';
        var displayed = 0;
        var total = 0;

        $.each(items, function(_, nutrientItem) {
            if (!nutrientItem || !nutrientItem.nutrient) {
                return;
            }

            if (!sffHasValue(nutrientItem.amount)) {
                return;
            }

            total++;
            if (displayed >= 20) {
                return;
            }

            var nutrient = nutrientItem.nutrient;
            var name = nutrient.name || nutrient.number || 'Nutrient';
            var amount = nutrientItem.amount;
            var unit = nutrient.unitName || '';

            rows += '<tr>' +
                '<td>' + sffEscapeHtml(name) + '</td>' +
                '<td>' + sffEscapeHtml(amount) + '</td>' +
                '<td>' + sffEscapeHtml(unit) + '</td>' +
            '</tr>';
            displayed++;
        });

        if (!rows) {
            return '';
        }

        var title = 'Food Nutrients';
        if (total > displayed) {
            title += ' (showing ' + displayed + ' of ' + total + ')';
        }

        var html = '<details class="usda-response-section" open>' +
            '<summary>' + sffEscapeHtml(title) + '</summary>' +
            '<table class="usda-response-table">' +
                '<thead><tr><th>Nutrient</th><th>Amount</th><th>Unit</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>';

        if (total > displayed) {
            html += '<p class="usda-response-note">Additional nutrients available in the full USDA record.</p>';
        }

        html += '</details>';
        return html;
    }

    function sffBuildPortionSection(portions) {
        if (!Array.isArray(portions) || !portions.length) {
            return '';
        }

        var rows = '';
        $.each(portions, function(_, portion) {
            if (!portion) {
                return;
            }
            var amountDisplay = sffHasValue(portion.amount) ? portion.amount : '—';
            var modifier = portion.modifier || (portion.measureUnit && portion.measureUnit.name) || '—';
            var gramDisplay = sffHasValue(portion.gramWeight) ? portion.gramWeight : '—';

            rows += '<tr>' +
                '<td>' + sffEscapeHtml(amountDisplay) + '</td>' +
                '<td>' + sffEscapeHtml(modifier) + '</td>' +
                '<td>' + sffEscapeHtml(gramDisplay) + '</td>' +
            '</tr>';
        });

        if (!rows) {
            return '';
        }

        return '<details class="usda-response-section" open>' +
            '<summary>Portion Examples</summary>' +
            '<table class="usda-response-table">' +
                '<thead><tr><th>Amount</th><th>Measure</th><th>Grams</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>' +
        '</details>';
    }

    function sffBuildAttributeSection(attributes) {
        if (!Array.isArray(attributes) || !attributes.length) {
            return '';
        }

        var items = '';
        $.each(attributes, function(_, attribute) {
            if (!attribute || !attribute.name) {
                return;
            }

            var value = sffHasValue(attribute.value) ? attribute.value : '—';
            items += '<li><strong>' + sffEscapeHtml(attribute.name) + ':</strong> ' + sffEscapeHtml(value) + '</li>';
        });

        if (!items) {
            return '';
        }

        return '<details class="usda-response-section">' +
            '<summary>Additional Attributes</summary>' +
            '<ul class="usda-response-list">' + items + '</ul>' +
        '</details>';
    }

    function sffRenderUsdaRaw(raw, message) {
        var $rawBox = $('#usda-full-response');
        if (!$rawBox.length) {
            return;
        }

        if (raw && typeof raw === 'object') {
            var headerTitle = raw.description || 'USDA Food Item';
            var headerHtml = '<div class="usda-response-header">' +
                '<h4 class="usda-response-title">' + sffEscapeHtml(headerTitle) + '</h4>';

            if (sffHasValue(raw.fdcId)) {
                headerHtml += '<span class="usda-response-badge">FDC ' + sffEscapeHtml(raw.fdcId) + '</span>';
            }

            if (sffHasValue(raw.dataType)) {
                headerHtml += '<span class="usda-response-badge">' + sffEscapeHtml(raw.dataType) + '</span>';
            }

            headerHtml += '</div>';

            var metaHtml = '';
            metaHtml += sffMetaRow('Publication Date', raw.publicationDate);
            metaHtml += sffMetaRow('Category', raw.foodCategory && raw.foodCategory.description);
            metaHtml += sffMetaRow('Food Class', raw.foodClass);
            metaHtml += sffMetaRow('Scientific Name', sffFindAttribute(raw.foodAttributes, 'Scientific Name'));
            if (Array.isArray(raw.foodPortions) && raw.foodPortions.length) {
                metaHtml += sffMetaRow('Portion Options', raw.foodPortions.length);
            }

            var sections = '';
            sections += sffBuildNutrientSection(raw.foodNutrients);
            sections += sffBuildPortionSection(raw.foodPortions);
            sections += sffBuildAttributeSection(raw.foodAttributes);

            var jsonHtml = '';
            try {
                jsonHtml = '<details class="usda-response-section">' +
                    '<summary>Full USDA JSON</summary>' +
                    '<pre class="usda-response-json">' + sffEscapeHtml(JSON.stringify(raw, null, 2)) + '</pre>' +
                '</details>';
            } catch (err) {
                jsonHtml = '<p class="usda-response-message">Unable to format USDA response: ' + sffEscapeHtml(err.message) + '</p>';
            }

            var html = headerHtml;
            if (metaHtml) {
                html += '<div class="usda-response-meta">' + metaHtml + '</div>';
            }
            if (message) {
                html += '<p class="usda-response-note">' + sffEscapeHtml(message) + '</p>';
            }
            html += sections;
            html += jsonHtml;

            $rawBox.html(html).show();
        } else if (message) {
            $rawBox.html('<p class="usda-response-message">' + sffEscapeHtml(message) + '</p>').show();
        } else {
            $rawBox.html('<p class="usda-response-message">No USDA response data available.</p>').show();
        }
    }


        // Product Name Scan Handler
  

    // Ensure wizard moves to Step 2 and retains scanned data
    $('#next_step_button').on('click', function() {
        $('#sff-wizard-step-1').hide();
        $('#sff-wizard-step-2').show();

        var hasImage = $('#front_image_attachment_id').val() ||
            ($('#sff_front_image_upload')[0] && $('#sff_front_image_upload')[0].files.length > 0);

        if (!hasImage) {
            $('#sff_scan_fields').hide();
        } else {
            $('#sff_scan_fields').show();
        }

        // Set the Step 2 category from the USDA filter
        var categorySelect = $('select[name="sff_ingredient_category"]');
        var usdaCategory = $('#usda-category-filter').val();
        if (categorySelect.length && usdaCategory) {
            categorySelect.val(usdaCategory);
        }

        var fdc = $('#sff_fdc_id').val();
        if (fdc && categorySelect.length) {
            // Hide/disable category selection when a USDA match is used
            categorySelect.prop('disabled', true).hide();
            categorySelect.prev('label').hide();
        }

        if (fdc) {
            sffRenderUsdaRaw(null, 'Loading USDA data...');
            $.post(
                sff_ajax_obj.ajax_url,
                { action: 'sff_usda_macros', security: sff_ajax_obj.nonce, fdc_id: fdc },
                function(res) {
                    if (res.success) {
                        var macros = res.data && res.data.macros ? res.data.macros : {};
                        sffPopulateMacros(macros, 'usda');
                        $('#sff_macro_source').val('usda');
                        $('#macro_source_text').text('USDA');
                        var notice = res.data && res.data.notice ? res.data.notice : '';
                        var message = notice ? notice : '';
                        sffRenderUsdaRaw(res.data ? res.data.raw : null, message);
                    } else {
                        var errorMessage = res.data && res.data.message ? res.data.message : 'Unable to fetch USDA data.';
                        sffPopulateMacros({}, 'clear');
                        sffRenderUsdaRaw(null, errorMessage);
                    }
                }
            );
        } else if (categorySelect.length) {
            // Ensure category dropdown is visible if no USDA match
            categorySelect.prop('disabled', false).show();
            categorySelect.prev('label').show();
        }
    });

    $('#scan_front_image_button').on('click', function() {
    let file = $('#sff_front_image_upload')[0].files[0];

    if (!file) {
        $('#scan_front_results').html('⚠️ Please select an image first.');
        console.error("🚨 No image selected for scanning.");
        return;
    }

    let formData = new FormData();
    formData.append('action', 'sff_scan_product_name');
    formData.append('security', sff_ajax_obj.nonce);
    formData.append('front_image', file);

    console.log("🚀 Sending Image File for Product Name:", file.name);

    $.ajax({
        url: sff_ajax_obj.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#scan_front_image_button').html('⏳ Scanning...');
        },
        success: function(response) {
            $('#scan_front_image_button').html('📷 Scan Product Name');

            if (response.success) {
                const data = response.data;
                console.log("✅ Scan Successful:", data);

                if (data.exists) {
                    $('#scan_front_results').html(`⚠️ Product "${data.product_name}" already exists.`);
                    window.location.href = `/wp-admin/post.php?post=${data.existing_id}&action=edit`;
                } else {
                    $('#scan_front_results').html(`✅ Scan successful! Product name: ${data.product_name}`);

                    // Store attachment ID in hidden field
                    if ($('#front_image_attachment_id').length) {
                        $('#front_image_attachment_id').val(data.attachment_id);
                    } else {
                        $('<input>', {
                            type: 'hidden',
                            id: 'front_image_attachment_id',
                            name: 'front_image_attachment_id',
                            value: data.attachment_id
                        }).appendTo('form');
                    }

                    $('#next_step_button').show();
                    $('[name="sff_brand_name"]').val(data.product_name).trigger('change');
                }
            } else {
                console.error("🚨 Scan Error:", response);
                $('#scan_front_results').html(`⚠️ Error: ${response.data}`);
            }
        },
        error: function(xhr, status, error) {
            console.error("🚨 AJAX Error:", error);
            $('#scan_front_results').html('⚠️ Failed to process the request.');
        }
    });
});



    // ✅ Fix: Prevent Default Form Submission on Save
    $('#save_nutrition_data_button').on('click', function(e) {
        e.preventDefault(); // Prevent traditional form submission
    });

    // ✅ Step 2: Scan Nutrition Label
    $('#scan_nutrition_label_button').on('click', function() {
        var fileInput = $('#sff_nutrition_label_upload')[0]; 
        var file = fileInput.files.length > 0 ? fileInput.files[0] : null;

        if (!file) {
            $('#scan_results').html('<p style="color:red;">⚠️ Please upload an image before scanning.</p>');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'sff_scan_nutrition_label');
        formData.append('security', sff_ajax_obj.nonce);
        formData.append('nutrition_label', file);

        console.log("🚀 Sending Image File:", file.name); // Debugging

        $.ajax({
            url: sff_ajax_obj.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#scan_nutrition_label_button').html('⏳ Scanning...');
            },
            success: function(response) {
                $('#scan_nutrition_label_button').html('2️⃣ Scan Nutrition Label 🥗');

                if (response.success) {
                    let data = response.data;
                    console.log("✅ Image Uploaded Successfully:", data);

                    // ✅ Fill form fields with scanned data
                    $('[name="sff_serving_size"]').val(data.serving_size || '');
                    $('[name="sff_servings"]').val(data.servings || 0);

                    var labelToMacro = {
                        calories: 'calories',
                        carbohydrates: 'carbs',
                        protein: 'protein',
                        fat: 'fat',
                        saturated_fat: 'saturated_fat',
                        trans_fat: 'trans_fat',
                        cholesterol: 'cholesterol',
                        sodium: 'sodium',
                        fiber: 'fiber',
                        sugars: 'sugars',
                        added_sugars: 'added_sugars',
                        vitamin_d: 'vitamin_d',
                        calcium: 'calcium',
                        iron: 'iron',
                        potassium: 'potassium',
                        magnesium: 'magnesium',
                        vitamin_a: 'vitamin_a',
                        vitamin_c: 'vitamin_c',
                        vitamin_e: 'vitamin_e',
                        zinc: 'zinc',
                        folate: 'folate',
                        riboflavin: 'riboflavin',
                        niacin: 'niacin',
                        vitamin_b6: 'vitamin_b6',
                        vitamin_b12: 'vitamin_b12',
                        thiamin: 'thiamin'
                    };
                    var scanMap = {};
                    Object.keys(labelToMacro).forEach(function(rawKey) {
                        if (!Object.prototype.hasOwnProperty.call(data, rawKey)) {
                            return;
                        }
                        var macroKey = labelToMacro[rawKey];
                        scanMap[macroKey] = data[rawKey] || 0;
                    });
                    sffPopulateMacros(scanMap, 'scan');

                    // ✅ Store hidden input for attachment ID
                    if ($('#nutrition_label_image_id').length) {
                        $('#nutrition_label_image_id').val(data.attachment_id);
                    } else {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'nutrition_label_image_id',
                            name: 'nutrition_label_image_id',
                            value: data.attachment_id
                        }).appendTo('form');
                    }

                    $('#scan_results').html('<p style="color:green;">✅ Scan successful!</p>');

                    $('#sff_macro_source').val('scan');
                    $('#macro_source_text').text('Scan');

                    // ✅ Highlight next step: Save button
                    $('#save_nutrition_data_button').html('3️⃣ Save & Continue ✅').fadeIn();
                } else {
                    console.error("🚨 Error Response:", response);
                    $('#scan_results').html('<p style="color:red;">⚠️ Error: ' + (response.data || 'Failed to scan label') + '</p>');
                }
            },
            error: function(xhr, status, error) {
                console.log("🚨 AJAX Error:", error);
                $('#scan_results').html('<p style="color:red;">⚠️ Failed to process the request.</p>');
                $('#scan_nutrition_label_button').html('2️⃣ Scan Nutrition Label 🥗');
            }
        });
    });

    // ✅ Step 3: Save Ingredient
    $('#save_nutrition_data_button').on('click', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $('#sff-ingredient-form').serialize();
        formData += '&action=sff_save_ingredient';
        formData += '&security=' + sff_ajax_obj.nonce;

        console.log("🚀 Saving Ingredient...");

        $.ajax({
            url: sff_ajax_obj.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json', // Ensure JSON response is parsed correctly
            beforeSend: function () {
                $('#save_nutrition_data_button').html('⏳ Saving...');
            },
            success: function(response) {
                if (response.success) {
                    console.log("✅ Ingredient saved:", response.data);

                    // ✅ Hide Step 2 and Show Step 3
                    $('#sff-wizard-step-2').hide();
                    $('#sff-wizard-step-3').fadeIn().html(`
                        <h2>✅ Ingredient Added!</h2>
                        <p>${response.data.message}</p>
                        <button id="add_new_ingredient_button" 
                            style="background:#023441; color:#E9FAB0; padding:12px 20px; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
                            ➕ Add a New Ingredient
                        </button>
                    `);
                } else {
                    alert('⚠️ Error: ' + (response.data.message || 'Something went wrong.'));
                    $('#save_nutrition_data_button').html('3️⃣ Save & Continue ✅');
                }
            },
            error: function(xhr, status, error) {
                console.log("🚨 AJAX Error:", error);
                alert('⚠️ Failed to save ingredient.');
                $('#save_nutrition_data_button').html('3️⃣ Save & Continue ✅');
            }
        });
    });

    // ✅ Reset Wizard for Adding a New Ingredient
    $(document).on('click', '#add_new_ingredient_button', function() {
        $('#sff-wizard-step-3').hide();
        $('#sff-wizard-step-1').fadeIn();

        // ✅ Reset all form fields
        $('#sff-ingredient-form')[0].reset();
        $('#scan_results').html('');
    });

   
    $('#intake-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        console.log("Submitting form...");  // ✅ Debugging Step 1: Check if form is being submitted
        console.log("Form Data:", formData);  // ✅ Debugging Step 2: Show form data before sending

        $.ajax({
            url: sff_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'sff_save_client_intake',
                form_data: formData
            },
            beforeSend: function() {
                console.log("Sending AJAX request...");  // ✅ Debugging Step 3: Confirm AJAX request is firing
            },
            success: function(response) {
                console.log("AJAX Response:", response);  // ✅ Debugging Step 4: Log the response

                if (response.success) {
                    alert("Form submitted successfully!");
                    $('#intake-form')[0].reset();
                } else {
                    alert("Error: " + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);  // ✅ Debugging Step 5: Log AJAX error messages
                alert("Submission failed. Check console for errors.");
            }
        });
    });


 // ✅ Convert to Client Button
  $('#convert-to-client-btn').on('click', function () {
    const leadId = $(this).data('lead-id');
    console.log("🔥 Convert button clicked. Lead ID:", leadId);

    if (!leadId) {
      alert("❌ Lead ID is missing.");
      return;
    }

    if (!confirm('Are you sure you want to convert this lead to a client?')) return;

    $.post(sff_ajax_obj.ajax_url, {
      action: 'sff_convert_to_client',
      security: sff_ajax_obj.nonce,
      lead_id: leadId
    }, function (response) {
      console.log("🧠 AJAX Response:", response);
      if (response.success) {
        alert('✅ Lead converted successfully!');
        location.reload();
      } else {
        alert('❌ Error: ' + (response.data?.message || response.data));
      }
    });
  });

  // Hamburger menu toggle
  $(document).on('click', '#sff-menu-toggle', function (e) {
    e.preventDefault();
    $('body').toggleClass('sff-menu-open');
  });

  // Close menu when a link is clicked
  $(document).on('click', '#sff-menu a', function () {
    $('body').removeClass('sff-menu-open');
  });

  // Recipe modal logic
  $(document).on('click', '#sff-open-recipe-modal', function() {
    $('#sff-recipe-modal').show();
  });
  $(document).on('click', '#sff-recipe-modal-close', function() {
    $('#sff-recipe-modal').hide();
  });

  var sffSelectedIngredients = [];
  function sffUpdateTotals() {
    var macroFields = (window.sff_ajax_obj && Array.isArray(sff_ajax_obj.macro_fields) && sff_ajax_obj.macro_fields.length)
      ? sff_ajax_obj.macro_fields
      : ['calories', 'carbs', 'protein', 'fat'];

    var macroTotals = {};
    macroFields.forEach(function(field){
      macroTotals[field] = 0;
    });
    var costTotal = 0;

    sffSelectedIngredients.forEach(function(item){
      macroFields.forEach(function(field){
        var value = item.macros && item.macros[field] !== undefined ? parseFloat(item.macros[field]) : 0;
        if (!isNaN(value)) {
          macroTotals[field] += value;
        }
      });
      var costValue = parseFloat(item.unit_cost || 0);
      if (!isNaN(costValue)) {
        costTotal += costValue;
      }
    });

    $('#sff-total-calories').text((macroTotals.calories || 0).toFixed(2));
    $('#sff-total-carbs').text((macroTotals.carbs || 0).toFixed(2));
    $('#sff-total-protein').text((macroTotals.protein || 0).toFixed(2));
    $('#sff-total-fat').text((macroTotals.fat || 0).toFixed(2));
    $('#sff-total-cost').text(costTotal.toFixed(2));

    var $summary = $('#sff-recipe-macro-summary');
    if ($summary.length) {
      var $grid = $summary.find('.sff-recipe-macro-grid');
      var gridHtml = '';
      var hasEntries = false;
      macroFields.forEach(function(field){
        var value = macroTotals[field] || 0;
        if (Math.abs(value) < 0.0001) {
          return;
        }
        hasEntries = true;
        gridHtml += '<div class="sff-recipe-macro-item">' +
          '<span class="sff-recipe-macro-label">' + sffFormatMacroLabel(field) + '</span>' +
          '<span class="sff-recipe-macro-value">' + value.toFixed(2) + '</span>' +
        '</div>';
      });

      if (hasEntries) {
        $grid.html(gridHtml);
        $summary.show();
      } else {
        $grid.empty();
        $summary.hide();
      }
    }
  }

  $('#sff-ingredient-search').on('keyup', function(){
    var q = $(this).val();
    var cat = $('#sff-ingredient-category-filter').val();
    if (q.length < 2) { $('#sff-ingredient-results').empty(); return; }
    $.get(sff_ajax_obj.ajax_url, {action:'sff_search_ingredients', security:sff_ajax_obj.nonce, q:q, category:cat}, function(res){
      if(res.success){
        var list = $('#sff-ingredient-results').empty();
        res.data.forEach(function(item){
          var li = $('<li>').text(item.name + ' ($'+item.unit_cost+')').data('item', item);
          list.append(li);
        });
      }
    });
  });

  $('#sff-ingredient-category-filter').on('change', function(){
    $('#sff-ingredient-search').trigger('keyup');
  });

  $(document).on('click', '#sff-ingredient-results li', function(){
    var item = $(this).data('item');
    sffSelectedIngredients.push(item);
    $('#sff-selected-ingredients').append($('<li>').text(item.name));
    sffUpdateTotals();
  });

  $('#sff-save-recipe').on('click', function(){
    var name = $('#sff-recipe-name').val();
    if(!name || !sffSelectedIngredients.length){
      alert('Please enter a name and select ingredients');
      return;
    }
    var ids = sffSelectedIngredients.map(function(i){return i.id;});
    $.post(sff_ajax_obj.ajax_url, {action:'sff_create_recipe', security:sff_ajax_obj.nonce, name:name, ingredients:ids}, function(res){
      if(res.success){
        var select = $('select[name="sff_meal_data[recipe_id]"]');
        select.append($('<option>').val(res.data.recipe_id).text(res.data.title));
        select.val(res.data.recipe_id).trigger('change');
        $('#sff-recipe-modal').hide();
        sffSelectedIngredients = [];
        $('#sff-selected-ingredients').empty();
        $('#sff-ingredient-results').empty();
        $('#sff-recipe-name').val('');
        sffUpdateTotals();
      } else {
        alert('Error creating recipe');
      }
    });
  });

  // Ensure busy/activity day fields are only required when visible
  function toggleClientDayRequired(selector) {
    var fields = $(selector);
    if (!fields.length) return;
    function toggle() {
      fields.each(function () {
        $(this).prop('required', $(this).is(':visible'));
      });
    }
    toggle();
    const observer = new MutationObserver(toggle);
    fields.each(function () {
      observer.observe(this, { attributes: true, attributeFilter: ['style', 'class'] });
    });
  }

  toggleClientDayRequired('[name="client[]busy_days"]');
  toggleClientDayRequired('[name="client[]activity_days"]');

  var usdaIndex = -1;

  $('[name="sff_brand_name"]').on('keydown', function(e){
    var items = $('#usda-suggestions li');
    if(!items.length) return;
    if(e.key === 'ArrowDown'){
      e.preventDefault();
      usdaIndex = (usdaIndex + 1) % items.length;
      items.removeClass('active').eq(usdaIndex).addClass('active');
    } else if(e.key === 'ArrowUp'){
      e.preventDefault();
      usdaIndex = (usdaIndex - 1 + items.length) % items.length;
      items.removeClass('active').eq(usdaIndex).addClass('active');
    } else if(e.key === 'Enter' && usdaIndex >= 0){
      e.preventDefault();
      items.eq(usdaIndex).trigger('click');
    }
  });

  function usdaSearch(){
    var query = $('[name="sff_brand_name"]').val();
    var category = $('#usda-category-filter').val();
    if(query.length < 2){
      $('#usda-suggestions').hide().empty();
      usdaIndex = -1;
      return;
    }
    $.post(sff_ajax_obj.ajax_url,{action:'sff_usda_search',security:sff_ajax_obj.nonce,query:query,category:category},function(res){
      if(res.success){
        var list = $('<ul/>');
        $.each(res.data,function(i,item){
          if(!category || (item.foodCategory && item.foodCategory.toLowerCase().includes(category.toLowerCase())) || (item.dataType && item.dataType.toLowerCase().includes(category.toLowerCase()))){
            list.append($('<li>').text(item.description).attr('data-fdc',item.fdc_id));
          }
        });
        if(list.children().length){
          $('#usda-suggestions').html(list).show();
        } else {
          $('#usda-suggestions').hide().empty();
        }
        usdaIndex = -1;
      } else {
        $('#usda-suggestions').hide().empty();
      }
    });
  }

  $('#usda-search-button').on('click', function(){
    usdaSearch();
  });
  $('[name="sff_brand_name"]').on('keypress', function(e){
    if(e.key === 'Enter'){
      e.preventDefault();
      usdaSearch();
    }
  });
  $('#usda-category-filter').off('change').on('change', function(){
    $('[name="sff_fdc_id"]').val('');
    usdaSearch();
  });

    $('#usda-suggestions').on('click','li',function(){
    var fdc = $(this).data('fdc');
    $('[name="sff_brand_name"]').val($(this).text());
    $('[name="sff_fdc_id"]').val(fdc);
    $('#usda-suggestions').hide().empty();
    sffRenderUsdaRaw(null, 'Loading USDA data...');
    $.post(sff_ajax_obj.ajax_url,{action:'sff_usda_macros',security:sff_ajax_obj.nonce,fdc_id:fdc},function(res){
      if(res.success){
        var macros = res.data && res.data.macros ? res.data.macros : {};
        sffPopulateMacros(macros, 'usda');
        $('#sff_macro_source').val('usda');
        $('#macro_source_text').text('USDA');
        var notice = res.data && res.data.notice ? res.data.notice : '';
        sffRenderUsdaRaw(res.data ? res.data.raw : null, notice);
      } else {
        var errorMessage = res.data && res.data.message ? res.data.message : 'Unable to fetch USDA data.';
        sffPopulateMacros({}, 'clear');
        sffRenderUsdaRaw(null, errorMessage);
      }
    });
  });

  $('#sff-wizard-step-2').on('input','[name^="sff_macros"]',function(){
    $('#sff_macro_source').val('manual');
    $('#macro_source_text').text('Manual');
    sffShowMacroSummary(sffCollectMacroValues(), 'manual');
  });

  sffShowMacroSummary(sffCollectMacroValues(), $('#sff_macro_source').val() || 'manual');
  });
