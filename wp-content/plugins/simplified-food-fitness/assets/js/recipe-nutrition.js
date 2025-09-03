(function($){
    function recalcRecipe(){
        var ids = $('select[name="sff_recipe_ingredients[]"]').val() || [];
        var servings = parseInt($('input[name="sff_recipe_servings"]').val(),10) || 1;
        $.post(sff_ajax_obj.ajax_url, {
            action: 'sff_recalc_recipe_nutrition',
            security: sff_ajax_obj.nonce,
            ingredient_ids: ids,
            servings: servings
        }, function(resp){
            if(resp.success){
                var per = resp.data.per_serving;
                var total = resp.data.total;
                var perHtml = '';
                var totalHtml = '';
                $.each(per, function(k,v){
                    perHtml += '<p><strong>'+k.replace(/_/g,' ') + ':</strong> '+v+'</p>';
                });
                $.each(total, function(k,v){
                    totalHtml += '<p><strong>'+k.replace(/_/g,' ') + ':</strong> '+v+'</p>';
                });
                $('#sff-recipe-nutrients-per-serving').html(perHtml);
                $('#sff-recipe-nutrients-total').html(totalHtml);
            }
        });
    }
    $(document).on('change', 'select[name="sff_recipe_ingredients[]"], input[name="sff_recipe_servings"]', recalcRecipe);
})(jQuery);
