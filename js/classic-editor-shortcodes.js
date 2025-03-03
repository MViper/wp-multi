document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("wp_multi_insert_shortcode");
    const dropdown = document.getElementById("wp_multi_shortcode_dropdown");

    if (btn && dropdown) {
        btn.addEventListener("click", function () {
            const shortcode = dropdown.value;

            if (shortcode) {
                window.send_to_editor("[" + shortcode + "]");
            } else {
                alert("Bitte einen Shortcode auswählen.");
            }
        });
    }
});
