(document => {
  const placeholders = document.querySelectorAll('.sff-ingredients-placeholder');
  if (!('IntersectionObserver' in window) || placeholders.length === 0) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const recipeId = el.getAttribute('data-recipe-id');
        fetch(sff_ingredient_loader.ajax_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({
            action: 'sff_fetch_ingredients',
            recipe_id: recipeId,
            nonce: sff_ingredient_loader.nonce
          })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success && Array.isArray(data.data)) {
              const container = document.createElement('div');
              container.className = 'sff-ingredients';
              const title = document.createElement('h4');
              title.textContent = 'Ingredients';
              const ul = document.createElement('ul');
              data.data.forEach(name => {
                const li = document.createElement('li');
                li.textContent = name;
                ul.appendChild(li);
              });
              container.appendChild(title);
              container.appendChild(ul);
              el.replaceWith(container);
            } else {
              el.textContent = 'Failed to load ingredients';
            }
          })
          .catch(() => {
            el.textContent = 'Failed to load ingredients';
          });
        observer.unobserve(el);
      }
    });
  });

  placeholders.forEach(el => observer.observe(el));
})(document);
