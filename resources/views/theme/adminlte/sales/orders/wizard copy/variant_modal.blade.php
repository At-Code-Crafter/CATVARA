@extends('theme.adminlte.layouts.modal')

{{-- 
    Since we don't have a dedicated layout for partials that are included 
    via @include needing strict structure, we just place the modal HTML here.
    This file is meant to be @include()'d in step2.blade.php
--}}

<!-- Variant Modal -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content border-0 shadow-lg" style="height: 80vh; border-radius: 1.5rem; overflow:hidden;">
      <div class="modal-header bg-dark text-white border-0 px-4 py-3">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-cubes mr-2"></i> Configure Product</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0 d-flex flex-column h-100">
        <!-- Content injected via JS -->
        <div class="p-5 text-center text-muted">
          <h4>Loading options...</h4>
        </div>
      </div>
    </div>
  </div>
</div>
