// Function to check if an element with a given ID exists and has a value
function has_id(id) {
    try {
        return document.getElementById(id) !== null;
    } catch (e) {
        return false;
    }
}

// Function to check if a form element with a given name exists
function has_name(name) {
    try {
        return typeof cfrm[name] !== 'undefined';
    } catch (e) {
        return false;
    }
}

// Get value of an element by ID or alert if the element is missing
function $$(id) {
    if (!has_id(id) && !has_name(id)) {
        alert("Field " + id + " does not exist!\nForm validation configuration error.");
        return false;
    }
    if (has_id(id)) {
        return document.getElementById(id).value;
    }
    return null;
}

// Get DOM element by ID
function $val(id) {
    return document.getElementById(id);
}

// Trim whitespace from the value of an element by ID
function trim(id) {
    let element = $val(id);
    if (element) {
        element.value = element.value.trim();
    }
}

// Required fields 
var required = {
    field: [],

    // Add a new field to the required list
    add: function(name, type, message) {
        this.field.push([name, type, message]);
    },

    // Retrieve all required fields
    out: function() {
        return this.field;
    },

    // Clear all required fields
    clear: function() {
        this.field = [];
    }
};

// Validation manager
var validate = {
    // Main validation function
    check: function(cform) {
        let error_message = 'Please fix the following errors:\n\n';
        let focus_field = ''; // Field to focus on first error
        let is_valid = true;

        // Iterate through all required fields
        for (let i = 0; i < required.field.length; i++) {
            let [field, type, message] = required.field[i];

            // Validate field based on type
            if (!this.checkit(field, type, cform)) {
                error_message += `${message} must be supplied\n`;

                // Set focus to the first invalid field
                if (has_id(field) && !focus_field) {
                    focus_field = field;
                }
                is_valid = false;
            }
        }

        // Display error messages if validation failed
        if (!is_valid) {
            alert(error_message);
        }

        // Focus on the first invalid field
        if (focus_field) {
            document.getElementById(focus_field).focus();
        }

        return is_valid;
    },

    // Individual field validation
    checkit: function(field, type, cform) {
        let value = $$(field);

        // NOT_EMPTY validation
        if (type === "NOT_EMPTY") {
            return this.trim(value).length > 0;
        }

        // EMAIL validation
        if (type === "EMAIL") {
            let email_pattern = /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            return email_pattern.test(value);
        }

        // Default case: validation passes
        return true;
    },

    // Trim whitespace from a string
    trim: function(s) {
        return s ? s.trim() : '';
    }
};