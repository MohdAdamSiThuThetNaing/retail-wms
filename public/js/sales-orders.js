document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("sales-order-form");
    const tbody = document.getElementById("sales-items-body");
    const template = document.getElementById("sales-item-template");
    const addButton = document.getElementById("add-sales-item");

    if (!form || !tbody || !template || !addButton) {
        return;
    }

    const money = (value) => {
        return Number(value || 0).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const getNumber = (input) => {
        const value = Number.parseFloat(input?.value ?? 0);
        return Number.isFinite(value) ? value : 0;
    };

    const reindexRows = () => {
        tbody.querySelectorAll(".sales-item-row").forEach((row, index) => {
            const fields = [
                [".product-select", "product_id"],
                [".item-quantity", "quantity"],
                [".item-price", "unit_price"],
                [".item-discount", "discount_amount"],
                [".item-notes", "notes"],
            ];

            fields.forEach(([selector, field]) => {
                const input = row.querySelector(selector);

                if (input) {
                    input.name = `items[${index}][${field}]`;
                }
            });
        });
    };

    const calculateTotals = () => {
        let subtotal = 0;

        tbody.querySelectorAll(".sales-item-row").forEach((row) => {
            const quantity = getNumber(row.querySelector(".item-quantity"));
            const price = getNumber(row.querySelector(".item-price"));
            const discount = getNumber(row.querySelector(".item-discount"));

            const gross = quantity * price;
            const lineTotal = Math.max(0, gross - discount);

            subtotal += lineTotal;

            const output = row.querySelector(".item-line-total");

            if (output) {
                output.textContent = money(lineTotal);
            }
        });

        const discount = getNumber(document.getElementById("discount_amount"));
        const tax = getNumber(document.getElementById("tax_amount"));

        const total = Math.max(0, subtotal - discount) + tax;

        document.getElementById("sales-subtotal").textContent = money(subtotal);
        document.getElementById("sales-discount").textContent = money(discount);
        document.getElementById("sales-tax").textContent = money(tax);
        document.getElementById("sales-grand-total").textContent = money(total);
    };

    const addRow = () => {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector(".sales-item-row");

        tbody.appendChild(fragment);
        reindexRows();
        calculateTotals();

        return row;
    };

    addButton.addEventListener("click", () => {
        addRow();
    });

    tbody.addEventListener("change", (event) => {
        if (event.target.matches(".product-select")) {
            const row = event.target.closest(".sales-item-row");
            const selectedOption = event.target.selectedOptions[0];
            const priceInput = row.querySelector(".item-price");

            if (selectedOption && selectedOption.value) {
                priceInput.value = selectedOption.dataset.price || "";
            }

            calculateTotals();
        }
    });

    tbody.addEventListener("input", (event) => {
        if (
            event.target.matches(".item-quantity") ||
            event.target.matches(".item-price") ||
            event.target.matches(".item-discount")
        ) {
            calculateTotals();
        }
    });

    tbody.addEventListener("click", (event) => {
        const button = event.target.closest(".remove-sales-item");

        if (!button) {
            return;
        }

        const rows = tbody.querySelectorAll(".sales-item-row");

        if (rows.length <= 1) {
            alert("At least one product is required.");
            return;
        }

        button.closest(".sales-item-row").remove();

        reindexRows();
        calculateTotals();
    });

    document
        .getElementById("discount_amount")
        ?.addEventListener("input", calculateTotals);

    document
        .getElementById("tax_amount")
        ?.addEventListener("input", calculateTotals);

    form.addEventListener("submit", () => {
        reindexRows();
    });

    reindexRows();
    calculateTotals();
});
