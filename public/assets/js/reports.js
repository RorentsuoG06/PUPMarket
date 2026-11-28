(function () {
    function setupListingReports() {
        const reportButton = document.getElementById("reportListingBtn");
        const reportForm = document.getElementById("listingReportForm");
        const productModal = document.getElementById("productModal");
        const reportModal = document.getElementById("listingReportModal");
        const status = document.getElementById("listingReportStatus");
        if (!reportButton || !reportForm || !productModal || !reportModal) {
            return;
        }

        const submitButton = document.getElementById("listingReportSubmit");

        function setStatus(message, type) {
            status.textContent = message;
            status.className = `small mt-3 mb-0 report-form-status text-${type}`;
        }

        function resetForm() {
            reportForm.reset();
            status.textContent = "";
            status.className = "small mb-0 d-none";
            submitButton.disabled = false;
            reportForm.querySelectorAll("select, textarea").forEach((field) => {
                field.disabled = false;
            });
        }

        reportButton.addEventListener("click", function () {
            if (reportButton.dataset.authenticated !== "true") {
                window.location.href = "login.php";
                return;
            }

            const listingId = reportButton.dataset.listingId;
            if (!listingId || reportButton.disabled) {
                return;
            }
            reportForm.querySelector('[name="listing_id"]').value = listingId;
            status.textContent = "";
            status.className = "small mb-0 d-none";

            const openReportModal = function () {
                bootstrap.Modal.getOrCreateInstance(reportModal).show();
            };
            const productModalInstance = bootstrap.Modal.getInstance(productModal);
            if (productModalInstance) {
                productModal.addEventListener("hidden.bs.modal", openReportModal, { once: true });
                productModalInstance.hide();
            } else {
                openReportModal();
            }
        });

        reportModal.addEventListener("hidden.bs.modal", resetForm);
        reportForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            if (!reportForm.reportValidity()) {
                return;
            }

            submitButton.disabled = true;
            setStatus("Submitting your report…", "muted");
            const formData = new FormData(reportForm);
            formData.set("action", "create");
            try {
                const response = await fetch("api/reports.php", {
                    method: "POST",
                    headers: {
                        "X-CSRF-Token": reportForm.dataset.csrfToken || ""
                    },
                    body: formData
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Unable to submit your report.");
                }

                setStatus("Thank you. Your report has been sent to the moderation team.", "success");
                reportForm.querySelectorAll("select, textarea").forEach((field) => {
                    field.disabled = true;
                });
            } catch (error) {
                console.error("Marketplace report could not be submitted.", error);
                setStatus(
                    error instanceof Error ? error.message : "Unable to submit your report.",
                    "danger"
                );
                submitButton.disabled = false;
            }
        });
    }

    function setupModerationReports() {
        const app = document.getElementById("moderationReportsApp");
        const container = document.getElementById("moderationReports");
        const status = document.getElementById("moderationStatus");
        const filter = document.getElementById("moderationStatusFilter");
        const pagination = document.getElementById("moderationPagination");
        const previousButton = document.getElementById("moderationPrevious");
        const nextButton = document.getElementById("moderationNext");
        const pageStatus = document.getElementById("moderationPageStatus");
        if (!app || !container || !filter) {
            return;
        }

        let currentPage = 1;
        let totalPages = 1;
        let requestSequence = 0;
        const reasonLabels = {
            scam: "Scam or fraudulent activity",
            prohibited_item: "Prohibited or unsafe item",
            misleading_listing: "Misleading listing information",
            harassment: "Harassment or inappropriate behavior",
            other: "Other"
        };
        const statusLabels = {
            pending: "Pending",
            reviewed: "Reviewed",
            dismissed: "Dismissed",
            actioned: "Actioned"
        };

        function setStatus(message, type = "info") {
            status.textContent = message;
            status.className = `alert alert-${type}`;
        }

        function appendText(parent, tagName, className, text) {
            const element = document.createElement(tagName);
            if (className) {
                element.className = className;
            }
            element.textContent = text;
            parent.append(element);
            return element;
        }

        function makeReportCard(report) {
            const card = document.createElement("article");
            card.className = "card moderation-report-card";

            const header = document.createElement("div");
            header.className = "d-flex flex-wrap justify-content-between align-items-start gap-2";
            appendText(
                header,
                "h2",
                "h5 mb-0",
                `Report #${report.report_id} — ${report.target_type === "seller" ? "Seller" : "Listing"}`
            );
            appendText(
                header,
                "span",
                `badge moderation-status-badge moderation-status-${report.status}`,
                statusLabels[report.status] || report.status
            );
            card.append(header);

            const target = report.target_type === "seller"
                ? `Seller: ${report.reported_username_snapshot} · Listing: ${report.listing_name_snapshot}`
                : `Listing: ${report.listing_name_snapshot} · Seller: ${report.reported_username_snapshot}`;
            appendText(card, "p", "moderation-report-target", target);
            appendText(
                card,
                "p",
                "small text-muted mb-2",
                `Reported by ${report.reporter_username} · ${report.created_at}`
            );
            appendText(
                card,
                "p",
                "mb-2",
                `Reason: ${reasonLabels[report.reason] || report.reason}`
            );
            if (report.details) {
                appendText(card, "p", "moderation-report-details", report.details);
            }

            const form = document.createElement("form");
            form.className = "moderation-review-form";
            form.dataset.reportReview = "true";
            form.dataset.reportId = String(report.report_id);

            const statusField = document.createElement("div");
            statusField.className = "moderation-review-field";
            const statusLabel = appendText(statusField, "label", "form-label", "Decision");
            const statusSelect = document.createElement("select");
            statusSelect.className = "form-select";
            statusSelect.name = "status";
            statusSelect.required = true;
            statusLabel.htmlFor = `reportStatus${report.report_id}`;
            statusSelect.id = statusLabel.htmlFor;
            Object.entries(statusLabels).forEach(([value, label]) => {
                const option = document.createElement("option");
                option.value = value;
                option.textContent = label;
                option.selected = report.status === value;
                statusSelect.append(option);
            });
            statusField.append(statusSelect);

            const noteField = document.createElement("div");
            noteField.className = "moderation-review-field";
            const noteLabel = appendText(noteField, "label", "form-label", "Moderator note");
            const note = document.createElement("textarea");
            note.className = "form-control";
            note.name = "moderator_note";
            note.rows = 2;
            note.maxLength = 2000;
            note.value = report.moderator_note || "";
            noteLabel.htmlFor = `reportNote${report.report_id}`;
            note.id = noteLabel.htmlFor;
            noteField.append(note);

            const actionRow = document.createElement("div");
            actionRow.className = "d-flex justify-content-end";
            const saveButton = document.createElement("button");
            saveButton.type = "submit";
            saveButton.className = "btn btn-pup";
            saveButton.textContent = "Save decision";
            actionRow.append(saveButton);
            form.append(statusField, noteField, actionRow);
            card.append(form);
            return card;
        }

        async function loadReports(page = 1, successMessage = "") {
            const sequence = ++requestSequence;
            setStatus("Loading moderation reports…", "info");
            const params = new URLSearchParams({
                status: filter.value,
                page: String(page)
            });
            try {
                const response = await fetch(`api/reports.php?${params.toString()}`, {
                    headers: { "Accept": "application/json" }
                });
                const result = await response.json();
                if (
                    !response.ok ||
                    !result.success ||
                    !Array.isArray(result.reports) ||
                    !result.pagination
                ) {
                    throw new Error(result.message || "Unable to load moderation reports.");
                }
                if (sequence !== requestSequence) {
                    return;
                }

                currentPage = Number(result.pagination.page);
                totalPages = Number(result.pagination.totalPages);
                container.replaceChildren(...result.reports.map(makeReportCard));
                pagination.classList.toggle("d-none", totalPages <= 1);
                pageStatus.textContent =
                    `Page ${currentPage} of ${totalPages} · ${Number(result.pagination.total)} reports`;
                previousButton.disabled = currentPage <= 1;
                nextButton.disabled = currentPage >= totalPages;
                if (result.reports.length === 0) {
                    appendText(
                        container,
                        "p",
                        "moderation-empty-state",
                        filter.value === "pending"
                            ? "There are no pending reports."
                            : "There are no reports in this view."
                    );
                }
                if (successMessage) {
                    setStatus(successMessage, "success");
                } else {
                    status.classList.add("d-none");
                }
            } catch (error) {
                if (sequence !== requestSequence) {
                    return;
                }
                console.error("Moderation reports could not be loaded.", error);
                setStatus(
                    error instanceof Error ? error.message : "Unable to load moderation reports.",
                    "danger"
                );
            }
        }

        filter.addEventListener("change", () => loadReports(1));
        previousButton.addEventListener("click", function () {
            if (currentPage > 1) {
                loadReports(currentPage - 1);
            }
        });
        nextButton.addEventListener("click", function () {
            if (currentPage < totalPages) {
                loadReports(currentPage + 1);
            }
        });
        container.addEventListener("submit", async function (event) {
            const form = event.target.closest("[data-report-review]");
            if (!form) {
                return;
            }
            event.preventDefault();
            if (!form.reportValidity()) {
                return;
            }

            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            setStatus("Saving moderation decision…", "info");
            const formData = new FormData(form);
            formData.set("action", "review");
            formData.set("report_id", form.dataset.reportId);
            try {
                const response = await fetch("api/reports.php", {
                    method: "POST",
                    headers: {
                        "X-CSRF-Token": app.dataset.csrfToken || ""
                    },
                    body: formData
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Unable to update the report.");
                }
                await loadReports(currentPage, "Moderation decision saved.");
            } catch (error) {
                console.error("Moderation decision could not be saved.", error);
                setStatus(
                    error instanceof Error ? error.message : "Unable to update the report.",
                    "danger"
                );
                button.disabled = false;
            }
        });

        loadReports();
    }

    document.addEventListener("DOMContentLoaded", function () {
        setupListingReports();
        setupModerationReports();
    });
})();
