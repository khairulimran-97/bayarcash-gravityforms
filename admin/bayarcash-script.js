document.addEventListener("DOMContentLoaded", function() {
    // Attempt to retrieve the button element
    const verifyTokenButton = document.getElementById("verify-token-button");

    // Check if the button element exists
    if (verifyTokenButton) {
        // Add event listener for button click
        verifyTokenButton.addEventListener("click", function(event) {
            // Prevent the default form submission behavior
            event.preventDefault();

            // Retrieve PAT from input field
            const patKey = document.getElementById("pat_key").value;

            // Verify token
            verifyToken(patKey);
        });
    } else {
        console.error("Button element not found. Unable to attach event listener.");
    }

    // Check if pat_key is not empty on page load
    const patKeyOnLoad = document.getElementById("pat_key").value;
    if (patKeyOnLoad) {
        verifyToken(patKeyOnLoad);
    }

    // Function to verify token
    function verifyToken(patKey) {
        // Show "Validating PAT token..." message
        document.getElementById("verify-token-status").innerText = "Validating PAT token...";

        // Send AJAX request to api.php
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "api.php", true); // Send to api.php
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function() {
            let errorMessage;
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Success
                        const successMessage = "PAT Token is valid";
                        document.getElementById("verify-token-status").innerText = successMessage;
                        updateStatusStyle(successMessage);
                    } else {
                        // Error
                        console.error("Error verifying token. Message:", response.message);
                        errorMessage = "Invalid PAT Token";
                        document.getElementById("verify-token-status").innerText = errorMessage;
                        updateStatusStyle(errorMessage);
                    }
                } else {
                    // Error
                    console.error("Error verifying token. Status:", xhr.status);
                    errorMessage = "Failed to verify PAT Token";
                    document.getElementById("verify-token-status").innerText = errorMessage;
                    updateStatusStyle(errorMessage);
                }
            }
        };
        xhr.send(JSON.stringify({ patKey: patKey }));
    }

    // Function to update status style
    function updateStatusStyle(status) {
        const statusElement = document.getElementById("verify-token-status");
        const iconElement = document.createElement("i");
        iconElement.classList.add("fas"); // Font Awesome class
        if (status === "PAT Token is valid") {
            statusElement.style.color = "green";
            iconElement.classList.add("fa-check-circle"); // Font Awesome check circle icon
        } else if (status === "Invalid PAT Token" || status === "Failed to verify PAT Token") {
            statusElement.style.color = "red";
            iconElement.classList.add("fa-times-circle"); // Font Awesome times circle icon
        }
        iconElement.setAttribute("aria-hidden", "true");
        statusElement.innerHTML = ''; // Clear previous content
        statusElement.appendChild(iconElement);
        statusElement.appendChild(document.createTextNode(status));
    }
});