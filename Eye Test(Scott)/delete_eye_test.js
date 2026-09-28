
// Student Name: Scott Cardiff //
// Purpose: Double-check before deleting an eye test (Y/N)
// Student ID: C00311728
// Name of Screen: Delete Eye Test
// Date: 24/03/2026 


document.addEventListener("DOMContentLoaded", function () {

  document.querySelectorAll(".deleteForm").forEach(form => { //looks for deleteForm in php and loops for each one submitted

    form.addEventListener("submit", function (e) {

      const testId = form.querySelector('input[name="test_ID"]').value; //Thus finds the specific input field name and stores the value

      const ans = prompt(`Are you sure you want to delete this eye test (ID: ${testId}) ? (Y/N)`);

      if (!ans || ans.trim().toUpperCase() !== "Y") {
        e.preventDefault(); // stop delete
      }
    });

  });

});