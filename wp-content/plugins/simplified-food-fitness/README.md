# Simplified Food & Fitness

## USDA API Key Configuration

The plugin integrates with the [USDA FoodData Central API](https://fdc.nal.usda.gov/api-guide.html) for nutrition data.

To configure the API key:

1. Obtain an API key from the USDA website.
2. In the WordPress admin dashboard, navigate to **Settings → Nutrition Label Settings**.
3. Enter your key in the **USDA API Key** field and save.
4. Alternatively, you can set an `USDA_API_KEY` environment variable which will override the saved option.

The key is required for features that search nutrition data through the USDA service.
