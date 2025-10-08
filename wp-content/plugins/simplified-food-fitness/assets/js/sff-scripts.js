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

    function sffNormalizeMacroSource(source) {
        if (!source || typeof source !== 'string') {
            return source || '';
        }
        var normalized = source.toLowerCase();
        if (normalized.indexOf('personal') !== -1) {
            return 'personal';
        }
        if (normalized.indexOf('general') !== -1) {
            return 'general';
        }
        if (normalized.indexOf('database') !== -1) {
            return 'database';
        }
        return source;
    }

    function sffShowMacroSummary(map, source) {
        map = map || {};
        source = sffNormalizeMacroSource(source);
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
        } else if (source === 'personal') {
            title = 'Values from My Ingredients';
        } else if (source === 'general') {
            title = 'Values from General Database';
        } else if (source === 'database') {
            title = 'Values from Ingredient Database';
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

        if (sffForceLabelScan) {
            $('#sff_scan_fields').show();
        } else if (!hasImage) {
            $('#sff_scan_fields').hide();
        } else {
            $('#sff_scan_fields').show();
        }
        sffForceLabelScan = false;

        var categorySelect = $('select[name="sff_ingredient_category"]');
        var fdc = $('#sff_fdc_id').val();
        var macroState = sffNormalizeMacroSource($('#sff_macro_source').val());
        var shouldFetchUsda = fdc && (!macroState || macroState === 'usda');

        if (shouldFetchUsda && categorySelect.length) {
            categorySelect.prop('disabled', true).hide();
            categorySelect.prev('label').hide();
        } else if (categorySelect.length) {
            categorySelect.prop('disabled', false).show();
            categorySelect.prev('label').show();
        }

        if (shouldFetchUsda) {
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
        } else {
            $('#usda-full-response').hide();
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

  var $ingredientNameField = $('#sff_product_name');
  if (!$ingredientNameField.length) {
    $ingredientNameField = $('[name="sff_brand_name"]');
  }
  var sffSuggestionIndex = -1;
  var sffDatabaseTimer = null;
  var sffLastDatabaseResults = [];
  var sffSettingProductName = false;
  var sffForceLabelScan = false;

  function sffClearIngredientSelectionMeta() {
    $('#sff_source_ingredient').val('');
    $('#sff_selected_owner').val('');
    $('#sff-ingredient-selection-note').hide().empty();
  }

  function sffRenderIngredientSuggestions(items, query) {
    var $container = $('#sff-ingredient-suggestions');
    sffSuggestionIndex = -1;
    if (!$container.length) {
      return;
    }
    if (!items || !items.length) {
      var emptyHtml = '<div class="sff-suggestions-empty">No ingredients found in the selected database.</div>';
      emptyHtml += '<button type="button" class="sff-open-label-scan" data-query="' + sffEscapeHtml(query || '') + '">➕ Scan a label to add to My Ingredients</button>';
      $container.html(emptyHtml).show();
      return;
    }
    var $list = $('<ul/>');
    items.forEach(function(item, index) {
      var label = item.brand_name || item.title || item.description || '';
      var isUsda = item.source && item.source.toLowerCase() === 'usda';
      var badge = item.owner_badge || (isUsda ? 'USDA' : (item.is_personal ? 'My Ingredient' : 'General Database'));
      var badgeClass = item.owner_badge_class || (isUsda ? 'usda' : (item.is_personal ? 'personal' : 'general'));
      var metaText = item.meta_text || '';
      if (!metaText && item.serving_size) {
        metaText = item.serving_size;
      }
      if (!metaText && isUsda) {
        var parts = [];
        if (item.dataType) {
          parts.push(item.dataType);
        }
        if (item.foodCategory) {
          parts.push(item.foodCategory);
        }
        metaText = parts.join(' • ');
      }
      var $li = $('<li/>')
        .attr('data-index', index)
        .addClass('sff-ingredient-suggestion')
        .append('<span class="sff-suggestion-name">' + sffEscapeHtml(label) + '</span>' +
                '<span class="sff-suggestion-badge sff-badge-' + sffEscapeHtml(badgeClass) + '">' + sffEscapeHtml(badge) + '</span>');
      if (metaText) {
        $li.append('<span class="sff-suggestion-meta">' + sffEscapeHtml(metaText) + '</span>');
      }
      $li.data('item', item);
      $list.append($li);
    });
    $container.html($list).show();
  }

  function sffSearchIngredientDatabase() {
    if (!$ingredientNameField.length) {
      return;
    }
    var query = $ingredientNameField.val();
    if (!query || query.trim().length < 2) {
      $('#sff-ingredient-suggestions').hide().empty();
      return;
    }
    var scope = $('#sff-ingredient-scope').val() || 'all';
    $.post(
      sff_ajax_obj.ajax_url,
      {
        action: 'sff_search_user_ingredients',
        security: sff_ajax_obj.nonce,
        query: query,
        scope: scope
      },
      function(res) {
        if (res && res.success && res.data) {
          var items = Array.isArray(res.data.items) ? res.data.items : [];
          sffLastDatabaseResults = items;
          sffRenderIngredientSuggestions(items, res.data.query || query);
        } else {
          sffLastDatabaseResults = [];
          $('#sff-ingredient-suggestions').hide().empty();
        }
      }
    );
  }

  $ingredientNameField.on('input', function() {
    if (sffSettingProductName) {
      return;
    }
    sffClearIngredientSelectionMeta();
    $('#sff_macro_source').val('manual');
    $('#macro_source_text').text('Manual');
    $('#usda-full-response').hide();
    clearTimeout(sffDatabaseTimer);
    var value = $ingredientNameField.val();
    if (!value || value.trim().length < 2) {
      $('#sff-ingredient-suggestions').hide().empty();
      return;
    }
    sffDatabaseTimer = setTimeout(function() {
      sffSearchIngredientDatabase();
    }, 250);
  });

  $ingredientNameField.on('keydown', function(e) {
    var $items = $('#sff-ingredient-suggestions li');
    if (!$items.length) {
      return;
    }
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      sffSuggestionIndex = (sffSuggestionIndex + 1) % $items.length;
      $items.removeClass('active').eq(sffSuggestionIndex).addClass('active');
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      sffSuggestionIndex = (sffSuggestionIndex - 1 + $items.length) % $items.length;
      $items.removeClass('active').eq(sffSuggestionIndex).addClass('active');
    } else if (e.key === 'Enter' && sffSuggestionIndex >= 0) {
      e.preventDefault();
      $items.eq(sffSuggestionIndex).trigger('click');
    } else if (e.key === 'Escape') {
      $('#sff-ingredient-suggestions').hide().empty();
      sffSuggestionIndex = -1;
    }
  });

  $('#sff-database-search-button').on('click', function() {
    sffSearchIngredientDatabase();
  });

  $('#sff-ingredient-scope').on('change', function() {
    sffSearchIngredientDatabase();
  });

  $(document).on('click', '.sff-ingredient-suggestion', function() {
    var $item = $(this);
    var item = $item.data('item');
    if (!item) {
      var idx = parseInt($item.attr('data-index'), 10);
      if (!isNaN(idx) && sffLastDatabaseResults[idx]) {
        item = sffLastDatabaseResults[idx];
      }
    }
    if (!item) {
      return;
    }

    var suggestionLabel = item.brand_name || item.title || item.description || '';
    sffSettingProductName = true;
    $ingredientNameField.val(suggestionLabel);
    sffSettingProductName = false;

    $('#sff-ingredient-suggestions').hide().empty();
    sffSuggestionIndex = -1;

    var isUsda = item.source && item.source.toLowerCase() === 'usda';

    if (isUsda) {
      $('#sff_source_ingredient').val('');
      $('#sff_selected_owner').val('');
      if (item.fdc_id) {
        $('#sff_fdc_id').val(item.fdc_id);
      } else {
        $('#sff_fdc_id').val('');
      }
      if (item.serving_size) {
        $('#sff_serving_size').val(item.serving_size);
      }
      $('#sff_macro_source').val('usda');
      $('#macro_source_text').text('USDA');
      sffPopulateMacros({}, 'usda');
      $('#sff-ingredient-selection-note').text('Loaded from USDA search results. Continue to Step 2 to pull nutrition details.').show();
      $('#usda-full-response').hide().empty();
      return;
    }

    $('#sff_source_ingredient').val(item.id || '');
    $('#sff_selected_owner').val(item.owner_type || '');
    var macroText = item.is_personal ? 'My Ingredients' : 'General Database';
    $('#macro_source_text').text(macroText);
    $('#sff_macro_source').val(item.is_personal ? 'database_personal' : 'database_general');

    if (item.fdc_id) {
      $('#sff_fdc_id').val(item.fdc_id);
    } else {
      $('#sff_fdc_id').val('');
    }

    if (item.serving_size) {
      $('#sff_serving_size').val(item.serving_size);
    }
    if (item.servings !== undefined && item.servings !== null && item.servings !== '') {
      $('#sff_servings').val(item.servings);
    }

    if (item.category_id) {
      var $categorySelect = $('select[name="sff_ingredient_category"]');
      if ($categorySelect.length) {
        $categorySelect.prop('disabled', false).show();
        $categorySelect.prev('label').show();
        $categorySelect.val(item.category_id);
      }
    }

    if (item.price !== undefined && item.price !== null && !isNaN(parseFloat(item.price))) {
      var $priceField = $('[name="sff_price"]');
      if ($priceField.length) {
        $priceField.val(parseFloat(item.price));
      }
    }

    sffPopulateMacros(item.macros || {}, item.is_personal ? 'personal' : 'general');

    var noteMessage = item.is_personal ? 'Loaded from your personal ingredient library.' : 'Loaded from the shared ingredient database.';
    $('#sff-ingredient-selection-note').text(noteMessage).show();

    $('#usda-full-response').hide().empty();
  });

  $(document).on('click', '.sff-open-label-scan', function(e) {
    e.preventDefault();
    var query = $(this).data('query');
    if (query) {
      sffSettingProductName = true;
      $ingredientNameField.val(query);
      sffSettingProductName = false;
    }
    sffForceLabelScan = true;
    $('#sff-ingredient-suggestions').hide().empty();
    sffSuggestionIndex = -1;
    $('#sff_source_ingredient').val('');
    $('#sff_selected_owner').val('');
    $('#next_step_button').trigger('click');
    setTimeout(function() {
      $('#sff_nutrition_label_upload').trigger('focus');
    }, 250);
  });

  $('#sff-wizard-step-2').on('input','[name^="sff_macros"]',function(){
    $('#sff_macro_source').val('manual');
    $('#macro_source_text').text('Manual');
    sffShowMacroSummary(sffCollectMacroValues(), 'manual');
  });

  var initialMacroSource = $('#sff_macro_source').val() || 'manual';
  sffShowMacroSummary(sffCollectMacroValues(), sffNormalizeMacroSource(initialMacroSource));
});
