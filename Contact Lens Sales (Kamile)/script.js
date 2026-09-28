// Kamile Kacinskaite
// C00312390
// Project Contact Lens Sales Screen   
// 19/02/26
 
var months = 1;
 
// returns true if prescription strength is within the allowed range (-5 to +5)
function isPrescriptionEligible(strength) 
{
    var val = parseFloat(strength);
    if (isNaN(val)) return false;
    return val >= -5 && val <= 5;
}
 
// shows or hides the prescription warning banner and enables/disables the submit button
function setPrescriptionWarning(show, leftStrength, rightStrength) 
{
    var warning = document.getElementById("prescriptionWarning");
    var submitBtn = document.querySelector("input[type='submit']");
 
    // if show is true display the warning with details about which eye(s) are ineligible otherwise hide it
    if (show) 
    {
        // construct the warning message based on which prescription(s) are ineligible
        var msg = " This customer is not eligible for contact lenses. ";
        if (!isPrescriptionEligible(leftStrength) && !isPrescriptionEligible(rightStrength)) {
            msg += "Both eye prescriptions are outside the ±5.00 limit.";
        } else if (!isPrescriptionEligible(leftStrength)) {
            msg += "Left eye prescription (" + leftStrength + ") is outside the ±5.00 limit.";
        } else {
            msg += "Right eye prescription (" + rightStrength + ") is outside the ±5.00 limit.";
        }
        warning.innerText = msg;
        warning.style.display = "block";
        submitBtn.disabled = true;
        submitBtn.style.opacity = "0.45";
        submitBtn.style.cursor  = "not-allowed";
    } else {
        warning.style.display = "none";
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
        submitBtn.style.cursor  = "pointer";
    }
}
 // loads customer details and eye test results when a customer is selected from the dropdown
function loadCustomerDetails() 
{
    var select = document.getElementById("customerSelect");
    var option = select.options[select.selectedIndex];
 
    if (option.value === "") {
        resetDisplay();
        return;
    }
 
    // customer details
    document.getElementById("dispAddress").innerText = option.getAttribute("data-address") || "—";
    document.getElementById("dispDOB").innerText     = option.getAttribute("data-dob")     || "—";
 
    // eye test details
    var custID = option.value;
    if (eyeTests && eyeTests[custID]) {
        var et = eyeTests[custID];
        document.getElementById("dispLeft").innerText     = et.left_lens;
        document.getElementById("dispRight").innerText    = et.right_lens;
        document.getElementById("dispTestDate").innerText = et.date_of_test;
 
        var suitableRow = document.getElementById("suitableRow");
        var suitableSpan = document.getElementById("dispSuitable");
 
        // check if either eye prescription is outside the ±5.00 range and update the suitability message and warning banner accordingly
        var leftOk  = isPrescriptionEligible(et.left_lens);
        var rightOk = isPrescriptionEligible(et.right_lens);
 
        // if either eye is ineligible show the appropriate message and display the warning banner otherwise show the eligible message and hide the warning
        if (!leftOk || !rightOk) {
            suitableSpan.innerText = " No — prescription outside ±5.00 range";
            suitableRow.style.display = "flex";
            setPrescriptionWarning(true, et.left_lens, et.right_lens);
        } else {
            suitableSpan.innerText = " Yes — eligible for contact lenses";
            suitableRow.style.display = "flex";
            setPrescriptionWarning(false);
        }
    } else {
        document.getElementById("dispLeft").innerText     = "No eye test on record";
        document.getElementById("dispRight").innerText    = "No eye test on record";
        document.getElementById("dispTestDate").innerText = "—";
        document.getElementById("suitableRow").style.display = "none";
        setPrescriptionWarning(false);
    }
 
    calculateTotal();
}
 // increases or decreases the number of months of supply and updates the display and total cost accordingly
function changeMonths(amount) 
{
    months += amount;
    if (months < 1)  months = 1;
    if (months > 12) months = 12;
    document.getElementById("monthsDisplay").innerText = months;
    document.getElementById("monthsInput").value       = months;
    calculateTotal();
}
 // calculates the total cost based on the selected lenses and number of months and updates the display
function calculateTotal() 
{
    var leftSelect  = document.getElementById("left_eye_id");
    var rightSelect = document.getElementById("right_eye_id");
 
    var leftPrice  = parseFloat(leftSelect.options[leftSelect.selectedIndex]?.getAttribute("data-price"))  || 0;
    var rightPrice = parseFloat(rightSelect.options[rightSelect.selectedIndex]?.getAttribute("data-price")) || 0;
 
    // price per eye is per month supply
    var total = (leftPrice + rightPrice) * months;
    document.getElementById("totalDisplay").innerText = total.toFixed(2);
}
 // resets all displayed customer and eye test details hides the suitability message and warning banner resets the total cost and months of supply to their default values
function resetDisplay() 
{
    document.getElementById("dispAddress").innerText  = "—";
    document.getElementById("dispDOB").innerText      = "—";
    document.getElementById("dispLeft").innerText     = "—";
    document.getElementById("dispRight").innerText    = "—";
    document.getElementById("dispTestDate").innerText = "—";
    document.getElementById("suitableRow").style.display = "none";
    document.getElementById("totalDisplay").innerText = "0.00";
    setPrescriptionWarning(false);
    months = 1;
    document.getElementById("monthsDisplay").innerText = "1";
    document.getElementById("monthsInput").value = "1";
}