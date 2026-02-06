@extends('acf::admin.layout')

@section('title', 'Field Groups')

@section('actions')
  <a href="{{ route('acf.admin.groups.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
    <i class="bi bi-plus-lg me-1"></i> New Group
  </a>
@endsection

@section('content')
  <div class="acf-card bg-white p-0 overflow-hidden">
    <table class="table table-hover align-middle mb-0">
      <thead class="bg-light">
        <tr>
          <th class="ps-4 py-3">Group Name</th>
          <th>Key</th>
          <th>Fields</th>
          <th>Position</th>
          <th>Status</th>
          <th class="text-end pe-4">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($groups as $group)
          <tr>
            <td class="ps-4">
              <a href="{{ route('acf.admin.groups.edit', $group) }}" class="fw-bold text-decoration-none text-dark">
                {{ $group->name }}
              </a>
            </td>
            <td><code>{{ $group->key }}</code></td>
            <td><span class="badge bg-secondary rounded-pill">{{ $group->fields_count }}</span></td>
            <td>{{ ucfirst($group->position) }}</td>
            <td>
              @if ($group->is_active)
                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
              @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
              @endif
            </td>
            <td class="text-end pe-4">
              <div class="btn-group">
                <a href="{{ route('acf.admin.groups.edit', $group) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger"
                  onclick="if(confirm('Delete this group? All fields and values will be lost.')) document.getElementById('delete-group-{{ $group->id }}').submit()">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
              <form id="delete-group-{{ $group->id }}" action="{{ route('acf.admin.groups.destroy', $group) }}"
                method="POST" style="display: none;">
                @csrf @method('DELETE')
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="bi bi-folder-x display-4 d-block mb-3"></i>
              No field groups found. Start by creating one!
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
