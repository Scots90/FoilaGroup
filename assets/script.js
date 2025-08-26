// Wait for the entire HTML document to be loaded before running the script
document.addEventListener('DOMContentLoaded', function() {

    /**
     * Handles the dynamic display of the "Time 2" input field on the job form.
     * This part of the script will only run if it finds the necessary elements on the page.
     */
    const collectionTimeType = document.getElementById('collection_time_type');
    const deliveryTimeType = document.getElementById('delivery_time_type');

    if (collectionTimeType && deliveryTimeType) {
        const collectionTime2Group = document.getElementById('collection_time_2_group');
        const deliveryTime2Group = document.getElementById('delivery_time_2_group');

        // A reusable function to toggle the visibility of the "Time 2" field
        const toggleTime2Field = (typeSelect, time2Group) => {
            if (typeSelect.value === 'Time Slot') {
                time2Group.style.display = 'block'; 
            } else {
                time2Group.style.display = 'none';
            }
        };

        // Add event listeners to the dropdowns
        collectionTimeType.addEventListener('change', () => toggleTime2Field(collectionTimeType, collectionTime2Group));
        deliveryTimeType.addEventListener('change', () => toggleTime2Field(deliveryTimeType, deliveryTime2Group));

        // Run the functions once on page load to set the correct initial state
        toggleTime2Field(collectionTimeType, collectionTime2Group);
        toggleTime2Field(deliveryTimeType, deliveryTime2Group);
    }


    /**
     * Adds a confirmation dialog to all delete links/buttons.
     * To use this, add class="delete-link" to your delete <a> tags.
     */
    const deleteLinks = document.querySelectorAll('.delete-link');

    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            // Prevent the link from navigating immediately
            event.preventDefault(); 

            // Show a confirmation dialog
            const userConfirmed = confirm('Are you sure you want to delete this item? This action cannot be undone.');

            // If the user clicks "OK", proceed with the link's original action
            if (userConfirmed) {
                window.location.href = this.href;
            }
        });
    });
    
    
    /**
     * Handles the mobile "hamburger" menu toggle.
     */
    const hamburger = document.querySelector('.hamburger');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (hamburger && navbarCollapse) {
        hamburger.addEventListener('click', function() {
            navbarCollapse.classList.toggle('active');
        });
    }

});