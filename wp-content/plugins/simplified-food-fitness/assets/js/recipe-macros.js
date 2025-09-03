jQuery(function($){
    function updateMacros(){
        var ids = $('select[name="sff_recipe_ingredients[]"]').val() || [];
        var servings = $('input[name="sff_recipe_servings"]').val() || 1;
        $.post(sff_ajax_obj.ajax_url, {
            action: 'sff_calc_recipe_macros',
            security: sff_ajax_obj.nonce,
            ingredients: ids,
            servings: servings
        }, function(resp){
            if(!resp || !resp.success){return;}
            var data = resp.data;
            var $display = $('#sff-recipe-macro-display');
            var html = '';
            if(data.per_serving){
                html += '<h4>Macros per serving</h4><ul class="per-serving">';
                $.each(data.per_serving, function(key,val){
                    var label = key.replace(/_/g,' ');
                    html += '<li><strong>'+label+':</strong> '+val+'</li>';
                });
                html += '</ul>';
            }
            if(data.total){
                html += '<h4>Total for recipe</h4><ul class="total">';
                $.each(data.total, function(key,val){
                    var label = key.replace(/_/g,' ');
                    html += '<li><strong>'+label+':</strong> '+val+'</li>';
                });
                html += '</ul>';
            }
            $display.html(html);
        });
    }
    $('select[name="sff_recipe_ingredients[]"], input[name="sff_recipe_servings"]').on('change', updateMacros);
    // trigger on load to populate
    updateMacros();
});
