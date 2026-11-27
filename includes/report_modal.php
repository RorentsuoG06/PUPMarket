<div class="modal fade" id="listingReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Report Listing or Seller</h2>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <form id="listingReportForm"
                  data-csrf-token="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="listing_id">
                <div class="modal-body">
                    <p class="text-muted small">
                        Reports are reviewed by PUPMarket moderators. Please provide accurate information.
                    </p>
                    <div class="mb-3">
                        <label class="form-label" for="reportTargetType">What are you reporting?</label>
                        <select class="form-select" id="reportTargetType" name="target_type" required>
                            <option value="listing">This listing</option>
                            <option value="seller">This seller</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reportReason">Reason</label>
                        <select class="form-select" id="reportReason" name="reason" required>
                            <option value="">Choose a reason</option>
                            <option value="scam">Scam or fraudulent activity</option>
                            <option value="prohibited_item">Prohibited or unsafe item</option>
                            <option value="misleading_listing">Misleading listing information</option>
                            <option value="harassment">Harassment or inappropriate behavior</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="reportDetails">Additional details (optional)</label>
                        <textarea class="form-control"
                                  id="reportDetails"
                                  name="details"
                                  rows="4"
                                  maxlength="1000"></textarea>
                    </div>
                    <p id="listingReportStatus"
                       class="small mb-0 d-none"
                       role="status"
                       aria-live="polite"></p>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                            class="btn btn-pup"
                            id="listingReportSubmit">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
