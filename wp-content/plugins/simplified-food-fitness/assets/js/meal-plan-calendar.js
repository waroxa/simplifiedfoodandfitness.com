(function(){
    document.addEventListener('DOMContentLoaded', function(){
        if (typeof Sortable === 'undefined' || typeof sffMealPlan === 'undefined') {
            return;
        }

        var recipeList = document.getElementById('sff-recipe-list');
        var calendar = document.getElementById('sff-meal-calendar');
        var hiddenInput = document.getElementById('sff_meal_data');
        var schedule = sffMealPlan.schedule || {};
        var recipes = sffMealPlan.recipes || [];
        var macros = sffMealPlan.macros || {};

        function createRecipeElement(recipe){
            var el = document.createElement('div');
            el.className = 'sff-recipe-item';
            el.textContent = recipe.title;
            el.dataset.id = recipe.id;
            return el;
        }

        function renderRecipeList(){
            recipes.forEach(function(r){
                recipeList.appendChild(createRecipeElement(r));
            });
            Sortable.create(recipeList, {
                group: { name: 'recipes', pull: 'clone', put: false },
                sort: false
            });
        }

        function renderCalendar(){
            var days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            days.forEach(function(day){
                var column = document.createElement('div');
                column.className = 'sff-day';
                column.dataset.day = day;
                var title = day.charAt(0).toUpperCase()+day.slice(1);
                column.innerHTML = '<h4>'+title+'</h4>';

                if(schedule[day]){
                    schedule[day].forEach(function(id){
                        var recipe = recipes.find(function(r){ return parseInt(r.id) === parseInt(id); });
                        if(recipe){
                            column.appendChild(createRecipeElement(recipe));
                        }
                    });
                }

                Sortable.create(column, {
                    group: 'recipes',
                    onAdd: updateSchedule,
                    onUpdate: updateSchedule
                });
                calendar.appendChild(column);
            });
        }

        function updateSchedule(){
            schedule = {};
            var dayEls = calendar.querySelectorAll('.sff-day');
            dayEls.forEach(function(dayEl){
                var day = dayEl.dataset.day;
                schedule[day] = [];
                var items = dayEl.querySelectorAll('.sff-recipe-item');
                items.forEach(function(item){
                    schedule[day].push(parseInt(item.dataset.id));
                });
            });
            hiddenInput.value = JSON.stringify(schedule);
            updateTotals();
        }

        function updateTotals(){
            var totalsContainer = document.getElementById('sff-macro-totals');
            totalsContainer.innerHTML = '';
            Object.keys(schedule).forEach(function(day){
                var totals = {calories:0, carbs:0, protein:0, fat:0};
                schedule[day].forEach(function(id){
                    var m = macros[id];
                    if(m){
                        totals.calories += parseFloat(m.calories) || 0;
                        totals.carbs += parseFloat(m.carbs) || 0;
                        totals.protein += parseFloat(m.protein) || 0;
                        totals.fat += parseFloat(m.fat) || 0;
                    }
                });
                var line = document.createElement('div');
                var title = day.charAt(0).toUpperCase()+day.slice(1);
                line.innerHTML = '<strong>'+title+':</strong> Calories '+totals.calories+' | Carbs '+totals.carbs+'g | Protein '+totals.protein+'g | Fat '+totals.fat+'g';
                totalsContainer.appendChild(line);
            });
        }

        renderRecipeList();
        renderCalendar();
        updateSchedule();
    });
})();

