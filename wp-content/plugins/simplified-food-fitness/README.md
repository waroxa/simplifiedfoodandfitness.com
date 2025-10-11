# Simplified Food & Fitness

## USDA API Key Configuration

The plugin integrates with the [USDA FoodData Central API](https://fdc.nal.usda.gov/api-guide.html) for nutrition data.

To configure the API key:

1. Obtain an API key from the USDA website.
2. In the WordPress admin dashboard, navigate to **Settings → Nutrition Label Settings**.
3. Enter your key in the **USDA API Key** field and save.
4. Alternatively, you can set an `USDA_API_KEY` environment variable which will override the saved option.

The key is required for features that search nutrition data through the USDA service.

## Front-end shortcodes

### Personal ingredient library

Add the `[sff_personal_ingredients]` shortcode to the **My Ingredients** page (or any page of your choice) to render the curated ingredient bank. The shortcode automatically:

* Welcomes the signed-in client with the Simplified Food & Fitness header UI.
* Shows live stats for saved items, active categories, and the latest update time.
* Provides category chips, a search bar, and pagination so clients can quickly filter their library.
* Displays each ingredient as an elegant card with macro snapshots, cost details, and edit/preview actions.
* Handles empty and search-without-results states with clear calls to action for adding new ingredients.

Clients must be logged in to view their library; logged-out visitors will see the custom login form provided by the plugin.
