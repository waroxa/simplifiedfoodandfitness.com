jQuery(document).ready(function($) {

        // Product Name Scan Handler
  

    // Move to Step 2 and determine macro workflow
    $('#next_step_button').on('click', function() {
        $('#sff-wizard-step-1').hide();
        $('#sff-wizard-step-2').show();

        var fdc = $('#sff_fdc_id').val();
        var fromPicture = $('#front_image_attachment_id').length > 0;

        if (fdc) {
            // Hide scan option and fetch macros from USDA
            $('#sff_nutrition_label_upload, #scan_nutrition_label_button, #scan_results').hide();
            $.post(sff_ajax_obj.ajax_url, {action:'sff_usda_macros', security:sff_ajax_obj.nonce, fdc_id:fdc}, function(res){
                if(res.success){
                    $.each(res.data, function(k,v){
                        $('[name="sff_macros['+k+']"]').val(v);
                    });
                    $('#sff_macro_source').val('usda');
                    $('#macro_source_text').text('USDA');
                }
            });
        } else {
            // Show scan option only if product image provided
            if(fromPicture){
                $('#sff_nutrition_label_upload, #scan_nutrition_label_button, #scan_results').show();
            } else {
                $('#sff_nutrition_label_upload, #scan_nutrition_label_button, #scan_results').hide();
            }
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
                    $('[name="sff_macros[calories]"]').val(data.calories || 0);
                    $('[name="sff_macros[carbs]"]').val(data.carbohydrates || 0);
                    $('[name="sff_macros[protein]"]').val(data.protein || 0);
                    $('[name="sff_macros[fat]"]').val(data.fat || 0);
                    $('[name="sff_macros[saturated_fat]"]').val(data.saturated_fat || 0);
                    $('[name="sff_macros[trans_fat]"]').val(data.trans_fat || 0);
                    $('[name="sff_macros[cholesterol]"]').val(data.cholesterol || 0);
                    $('[name="sff_macros[sodium]"]').val(data.sodium || 0);
                    $('[name="sff_macros[fiber]"]').val(data.fiber || 0);
                    $('[name="sff_macros[sugars]"]').val(data.sugars || 0);
                    $('[name="sff_macros[added_sugars]"]').val(data.added_sugars || 0);
                    $('[name="sff_macros[vitamin_d]"]').val(data.vitamin_d || 0);
                    $('[name="sff_macros[calcium]"]').val(data.calcium || 0);
                    $('[name="sff_macros[iron]"]').val(data.iron || 0);
                    $('[name="sff_macros[potassium]"]').val(data.potassium || 0);
                    $('[name="sff_macros[magnesium]"]').val(data.magnesium || 0);

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
    var totals = {calories:0, carbs:0, protein:0, fat:0, cost:0};
    sffSelectedIngredients.forEach(function(item){
      totals.calories += item.macros.calories;
      totals.carbs += item.macros.carbs;
      totals.protein += item.macros.protein;
      totals.fat += item.macros.fat;
      totals.cost += item.unit_cost;
    });
    $('#sff-total-calories').text(totals.calories.toFixed(2));
    $('#sff-total-carbs').text(totals.carbs.toFixed(2));
    $('#sff-total-protein').text(totals.protein.toFixed(2));
    $('#sff-total-fat').text(totals.fat.toFixed(2));
    $('#sff-total-cost').text(totals.cost.toFixed(2));
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
    if(query.length < 1){
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
    $('[name="sff_fdc_id"]').val('');
    usdaSearch();
  });

  $('#usda-category-filter').on('change', function(){
    $('#usda-search-button').trigger('click');
  });

  $('#usda-suggestions').on('click','li',function(){
    var fdc = $(this).data('fdc');
    $('[name="sff_brand_name"]').val($(this).text());
    $('[name="sff_fdc_id"]').val(fdc);
    $('#usda-suggestions').hide().empty();
  });

  $('#sff-wizard-step-2').on('input','[name^="sff_macros"]',function(){
    $('#sff_macro_source').val('manual');
    $('#macro_source_text').text('Manual');
  });
});


