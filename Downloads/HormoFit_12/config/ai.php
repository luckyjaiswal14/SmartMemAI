<?php
$gemini_api_key = getenv("GEMINI_API_KEY");

if(!$gemini_api_key){
    $gemini_api_key = "AIzaSyBXLogdxbeePFJQqx3lXvDR6Z2EKntJ45s";
}

$gemini_models = [
    "gemini-2.5-flash",
    "gemini-2.0-flash",
    "gemini-flash-latest",
    "gemini-1.5-flash"
];

$gemini_api_versions = ["v1", "v1beta"];
?>
