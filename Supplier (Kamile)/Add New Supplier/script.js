// Kamile Kacinskaite
// C00312390
// Project Add New Supplier Screen
// The user supplies details about a new supplier and when they confirm all details are correct a new record is added to the Supplier Table 
// 12/02/26

window.onload = function()  // command that runs when the page is fully loaded
{
    
    const form = document.getElementById("supplierForm");

    if (form) 
    {
        form.onsubmit = function() 
        {
            // this creates the browser pop-up
            return confirm("Are you sure you want to insert this supplier record?");
        };
    }
};