// Student Name: Scott Cardiff //
// Purpose: To double check and cofirm whether the order was delivered for Deliveries
// Student ID: C00311728
// Name of Screen: Deliveries
// Date: 24/03/2026 

document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".deliveredForm").forEach(form => {

        form.addEventListener("submit", function (e) {

            const ans = prompt("Has this order been delivered in full? (Y/N)");

            if (!ans || ans.trim().toUpperCase() !== "Y") {
                e.preventDefault();
            }
        });

    });

});