<style>
/* Add this to your shared CSS/SCSS file */
.action-icon{
  width: 34px;
  height: 34px;
  padding: 0;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  box-shadow: 0 8px 18px rgba(2, 6, 23, 0.04);
}

.action-icon i{
  font-size: 14px;
}

</style>
<div class="d-flex align-items-center" style="gap:8px;">
  {{-- View --}}
  @if (!empty($showUrl))
    <a href="{{ $showUrl }}"
      class="btn btn-xs btn-outline-secondary action-icon"
      data-toggle="tooltip"
      data-placement="top"
      title="View">
      <i class="far fa-eye"></i>
    </a>
  @endif

  @if (empty($row->deleted_at))

    {{-- Edit --}}
    @if (!empty($editUrl))
      @if (!empty($editSidebar))
        <button type="button"
          data-url="{{ $editUrl }}"
          class="btn btn-xs btn-outline-secondary action-icon"
          onclick="getAside()"
          data-toggle="tooltip"
          data-placement="top"
          title="Edit">
          <i class="far fa-edit"></i>
        </button>
      @else
        <a href="{{ $editUrl }}"
          class="btn btn-xs btn-outline-secondary action-icon"
          data-toggle="tooltip"
          data-placement="top"
          title="Edit">
          <i class="far fa-edit"></i>
        </a>
      @endif
    @endif

    {{-- Delete --}}
    @if (!empty($deleteUrl))
      <button type="button"
        class="btn btn-xs btn-outline-danger action-icon btn-delete"
        data-url="{{ $deleteUrl }}"
        data-toggle="tooltip"
        data-placement="top"
        title="Delete">
        <i class="far fa-trash-alt"></i>
      </button>
    @endif

  @elseif(!empty($restoreUrl))

    {{-- Restore --}}
    <button type="button"
      data-url="{{ $restoreUrl }}"
      class="btn btn-xs btn-outline-success action-icon btn-delete"
      data-toggle="tooltip"
      data-placement="top"
      title="Restore">
      <i class="fas fa-undo"></i>
    </button>

  @endif
</div>

@push('scripts')
  <script>
    $(function () {
      // Bootstrap 4 tooltips
      if ($.fn.tooltip) {
        $('body').tooltip({
          selector: '[data-toggle="tooltip"]',
          trigger: 'hover',
          container: 'body',
          boundary: 'window'
        });
      }
    });
  </script>
@endpush
