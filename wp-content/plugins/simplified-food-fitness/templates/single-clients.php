<?php

if (!defined('ABSPATH')) exit;

// Ensure CSS is loaded even if enqueue fails
echo '<link rel="stylesheet" href="' . SFF_PLUGIN_URL . 'assets/css/sff-styles.css?ver=1.0.2">';

// 👇 Early access control check — block unauthorized access
if (!is_user_logged_in()) {
    if (function_exists('sff_custom_login_form')) {
        echo sff_custom_login_form();
    } else {
        wp_login_form(); // fallback if sff_custom_login_form is not loaded
    }
    return;
}



// Logo URL
$logo_url = "https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png";
?>

<div class="dashboard-container" style="max-width:1200px; margin:auto; padding:20px; font-family:'Segoe UI', Arial, sans-serif;">
    
    <!-- Header Section -->
    <div style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; text-align:left; margin-bottom:30px;">
        
        <!-- Left Logo -->
        <div style="flex-shrink:0;">
            <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:70px; width:auto; max-width:200px;">
        </div>
    </div>
</div>

<?php
// Ensure only logged-in users can see the lead details
// if (!is_user_logged_in()) {
//     echo "<p style='text-align:center; padding:20px; color:red;'>🔒 You must be logged in to view this lead.</p>";
//     exit;



// Get lead details
$lead_id = get_the_ID();
$custom_fields = get_post_meta($lead_id);
?>

<div class="sff-lead-container">
    <h2 class="sff-lead-title">Client Profile: <?php the_title(); ?></h2>

    <?php if ($custom_fields): ?>
        <div class="sff-lead-section">
            <h3>📝 Personal Information</h3>
            <?php foreach ($custom_fields as $key => $value): ?>
    <?php if (!empty($value[0]) && substr($key, 0, 1) !== '_'): ?>
        <?php
        $clean_label = ucwords(str_replace('_', ' ', preg_replace('/^sff_/', '', $key)));
        ?>
        <div class="sff-lead-card">
            <label><?php echo $clean_label; ?>:</label>
            <span><?php echo esc_html($value[0]); ?></span>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

        </div>
    <?php else: ?>
        <p style="text-align: center; color: gray;">No additional information available.</p>
    <?php endif; ?>
</div>

<!-- Calculate Macros Button -->
<div style="text-align:center; margin-top:20px;">
    <button id="calculate-macros-btn" data-lead-id="<?php echo esc_attr($lead_id); ?>" 
        style="padding:12px 20px; background:#28a745; color:white; border:none; border-radius:5px; font-size:16px; cursor:pointer;">
        🥗 Calculate Macros
    </button>
</div>

<!-- Placeholder for calculated macros -->
<!-- Placeholder for BMR -->
<!-- 🔥 Macro Results -->
<div id="macro-result" class="sff-lead-section" style="margin-top: 30px; display: none; font-family:'Segoe UI', Arial, sans-serif;">

  <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); max-width: 1000px; margin: auto;">
    <h3 style="font-size: 28px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px; text-align:center;">
      🔥 Your Macro Summary
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">

      <div class="sff-lead-card"><label>BMR:</label><span id="bmr-output"></span> kcal/day</div>
      <div class="sff-lead-card"><label>BMI:</label><span id="bmi-output"></span></div>
      <div class="sff-lead-card"><label>TDEE:</label><span id="tdee-output"></span> kcal/day</div>
      <div class="sff-lead-card"><label>Adjusted Calories:</label><span id="calories-output"></span> kcal/day</div>
      <div class="sff-lead-card"><label>Activity Factor:</label><span id="activity-output"></span> ×</div>
      <div class="sff-lead-card"><label>Goal:</label><span id="goal-output"></span></div>
      <div class="sff-lead-card"><label>Weight:</label><span id="weight-output"></span> kg</div>
      <div class="sff-lead-card"><label>Height:</label><span id="height-output"></span> cm</div>
      <div class="sff-lead-card"><label>Age:</label><span id="age-output"></span> years</div>
      <div class="sff-lead-card"><label>Protein Target:</label><span id="protein-output"></span> g</div>
      <div class="sff-lead-card"><label>Carbs Target:</label><span id="carbs-output"></span> g</div>
      <div class="sff-lead-card"><label>Fats Target:</label><span id="fats-output"></span> g</div>
    </div>

    <!-- 📘 BMR Formula -->
    <!-- 📘 BMR Formula -->
    <div id="bmr-formula-box" style="margin-top: 30px; font-size: 14px; color: #555; background:#f7f8fa; padding:15px; border-radius:8px;">
      <strong>📘 BMR Formula Used (Mifflin-St Jeor):</strong><br><br>
      <ul style="list-style: disc; padding-left: 20px;">
        <li><b>Men:</b> BMR = 10 × weight (kg) + 6.25 × height (cm) − 5 × age + 5<br>
          <code id="bmr-male-formula"></code>
        </li>
        <li><b>Women:</b> BMR = 10 × weight (kg) + 6.25 × height (cm) − 5 × age − 161<br>
          <code id="bmr-female-formula"></code>
        </li>
      </ul>
    </div>

    <!-- Botón para convertir en cliente -->
    <div style="text-align:center; margin-top: 30px;">
      <button id="convert-to-client-btn"
              data-lead-id="<?php echo esc_attr($lead_id); ?>"
              style="padding:12px 20px; background:#0073aa; color:white; border:none; border-radius:5px; font-size:16px; cursor:pointer;">
        ➕ Convert to Client
      </button>
    </div>


  </div>
</div>




<script>
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("calculate-macros-btn").addEventListener("click", function() {
        var leadId = this.getAttribute("data-lead-id");

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=calculate_macros&lead_id=' + leadId
})
.then(response => response.json())
.then(data => {
    console.log("Server Response:", data);

    if (data.success && data.data.bmr) {
        const res = data.data;
        // Insert calculated BMR formulas
        const weight = parseFloat(res.weight_kg);
        const height = parseFloat(res.height_cm);
        const age = parseInt(res.age);

        document.getElementById("macro-result").style.display = "block";

        document.getElementById("bmr-output").innerText = res.bmr;
        document.getElementById("bmi-output").innerText = res.bmi;
        document.getElementById("tdee-output").innerText = res.tdee;
        document.getElementById("calories-output").innerText = res.adjusted_calories;
        document.getElementById("activity-output").innerText = res.activity_factor;
        document.getElementById("weight-output").innerText = res.weight_kg;
        document.getElementById("height-output").innerText = res.height_cm;
        document.getElementById("age-output").innerText = res.age;
        document.getElementById("goal-output").innerText = res.goal;
        document.getElementById("protein-output").innerText = res.protein_g;
        document.getElementById("carbs-output").innerText = res.carb_g;
        document.getElementById("fats-output").innerText = res.fat_g;
        document.getElementById("bmr-male-formula").innerText =
          `= 10 × ${weight} + 6.25 × ${height} − 5 × ${age} + 5 = ` +
          Math.round((10 * weight) + (6.25 * height) - (5 * age) + 5);

        document.getElementById("bmr-female-formula").innerText =
          `= 10 × ${weight} + 6.25 × ${height} − 5 × ${age} − 161 = ` +
          Math.round((10 * weight) + (6.25 * height) - (5 * age) - 161);

    } else {
        alert("❌ " + (data.data?.message || "Unknown error."));
    }
})

.catch(error => {
    console.error("AJAX Error:", error);
    alert("❌ AJAX request failed.");
}); 

    });
});

</script>

