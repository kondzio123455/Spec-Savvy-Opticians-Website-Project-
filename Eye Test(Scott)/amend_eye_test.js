/* Student Name: Scott Cardiff
// Purpose: 
// Student ID: C00311728
// Name of Screen: Amend Eye Test
// Date: 24/03/2026 
*/

document.addEventListener("DOMContentLoaded", function () {

  const amendForm = document.getElementById("amendForm");
  if (!amendForm) return; // not on edit screen

  amendForm.addEventListener("submit", function (e) {

    const ans = prompt("Are you sure you want to save these changes? (Y/N)");

    if (!ans || ans.trim().toUpperCase() !== "Y") {
      e.preventDefault();
    }
  });

});