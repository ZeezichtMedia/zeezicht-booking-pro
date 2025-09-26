<?php
/**
 * Debug Routing Test
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>ZZBP Routing Debug</h1>";

// Test rewrite rules
global $wp_rewrite;
echo "<h2>Rewrite Rules:</h2>";
echo "<pre>";
print_r($wp_rewrite->rules);
echo "</pre>";

// Test query vars
echo "<h2>Query Vars:</h2>";
echo "<pre>";
print_r($wp->query_vars);
echo "</pre>";

// Test accommodation slug
$accommodation_slug = get_query_var('zzbp_accommodation_slug');
echo "<h2>Current Accommodation Slug:</h2>";
echo "<p>" . ($accommodation_slug ? $accommodation_slug : 'None') . "</p>";

// Test API
echo "<h2>API Test:</h2>";
try {
    require_once('includes/class-zzbp-api.php');
    $api = new ZZBP_Api();
    $accommodations = $api->get_accommodations();
    echo "<p>Found " . count($accommodations) . " accommodations</p>";
    
    if (!empty($accommodations)) {
        echo "<h3>Available Accommodations:</h3>";
        echo "<ul>";
        foreach ($accommodations as $acc) {
            $slug = sanitize_title($acc['name']);
            $url = home_url("/accommodations/{$slug}/");
            echo "<li><a href='{$url}'>{$acc['name']}</a> (slug: {$slug})</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>API Error: " . $e->getMessage() . "</p>";
}

// Test current URL
echo "<h2>Current URL Info:</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "</p>";
?>
