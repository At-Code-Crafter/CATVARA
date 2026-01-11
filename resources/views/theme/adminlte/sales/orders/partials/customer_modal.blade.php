<div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 14px; overflow:hidden;">
      <div class="modal-header">
        <h5 class="modal-title">Select Customer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <div class="p-3 border-bottom">
          <input type="text" class="form-control" id="customerSearchInput" placeholder="Search customers..." autofocus>
        </div>
        <div class="list-group list-group-flush" id="customerList" style="max-height: 300px; overflow-y: auto;">
            <!-- filled by JS -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
