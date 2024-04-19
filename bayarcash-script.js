document.addEventListener("DOMContentLoaded", function() {
    // Event listener for button click
    document.getElementById("verify-token-button").addEventListener("click", function(event) {
        // Prevent the default form submission behavior
        event.preventDefault();

        // Retrieve PAT from input field
        var patKey = document.getElementById("pat_key").value;

        // Verify token
        verifyToken(patKey);
    });

    // Check if pat_key is not empty on page load
    var patKeyOnLoad = document.getElementById("pat_key").value;
    if (patKeyOnLoad) {
        verifyToken(patKeyOnLoad);
    }

    // Function to verify token
    function verifyToken(patKey) {
        // Show "Validating PAT token..." message
        var validatingMessage = "Validating PAT token...";
        document.getElementById("verify-token-status").innerText = validatingMessage;

        // Send AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "https://console.bayar.cash/api/transactions", true);
        xhr.setRequestHeader("Accept", "application/json");
        xhr.setRequestHeader("Authorization", "Bearer " + patKey);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Success
                    var successMessage = "PAT Token is valid";
                    document.getElementById("verify-token-status").innerText = successMessage;
                    updateStatusStyle(successMessage);
                } else {
                    // Error
                    console.error("Error verifying token. Status:", xhr.status);
                    var errorMessage = "Invalid PAT Token";
                    document.getElementById("verify-token-status").innerText = errorMessage;
                    updateStatusStyle(errorMessage);
                }
            }
        };
        xhr.send();
    }

    // Function to update status style
    function updateStatusStyle(status) {
        var statusElement = document.getElementById("verify-token-status");
        var iconElement = document.createElement("i");
        iconElement.classList.add("fas"); // Font Awesome class
        if (status === "PAT Token is valid") {
            statusElement.style.color = "green";
            iconElement.classList.add("fa-check-circle"); // Font Awesome check circle icon
        } else if (status === "Invalid PAT Token") {
            statusElement.style.color = "red";
            iconElement.classList.add("fa-times-circle"); // Font Awesome times circle icon
        }
        iconElement.setAttribute("aria-hidden", "true");
        statusElement.innerHTML = ''; // Clear previous content
        statusElement.appendChild(iconElement);
        statusElement.appendChild(document.createTextNode(status));
    }
});
