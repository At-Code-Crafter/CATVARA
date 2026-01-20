@extends('catvara.layouts.app')

@section('page-title', 'Import Products')
@section('page-description', 'Import products from Excel or CSV file')
@section('page-buttons')
    <a href="{{ company_route('catalog.products.index') }}" class="btn btn-white">
        <i class="fas fa-arrow-left mr-2"></i> Back to Products
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Import Products</h4>
            </div>
            <div class="card-body">
                <form action="{{ company_route('catalog.products.import.store') }}" method="POST" enctype="multipart/form-data" id="import-form">
                    @csrf
                    <div class="form-group">
                        <label for="file">Select Excel or CSV file</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <label class="custom-file-label" for="file">Choose file...</label>
                        </div>
                        <small class="form-text text-muted">Supported formats: .xlsx, .xls, .csv (Max 10MB)</small>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btn-import">
                        <i class="fas fa-file-import mr-2"></i> Import
                    </button>
                </form>

                <!-- Progress -->
                <div id="import-progress" class="d-none mt-4">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                            Processing...
                        </div>
                    </div>
                </div>

                <!-- Result -->
                <div id="import-result" class="mt-4"></div>

                @if(session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Expected Columns</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Your file should have these columns (header row required):</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success mr-2"></i> <strong>Category Name</strong></li>
                    <li><i class="fas fa-check text-success mr-2"></i> <strong>Brand Name</strong></li>
                    <li><i class="fas fa-check text-success mr-2"></i> <strong>Product Name</strong></li>
                    <li><i class="fas fa-check text-success mr-2"></i> <strong>Variant SKU</strong></li>
                    <li><i class="fas fa-minus text-muted mr-2"></i> Cost (optional)</li>
                    <li><i class="fas fa-minus text-muted mr-2"></i> Price columns (optional)</li>
                    <li><i class="fas fa-minus text-muted mr-2"></i> Stock columns (optional)</li>
                </ul>
                <hr>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    You can use an exported file as a template. Categories and Brands will be created automatically if they don't exist.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.custom-file-label::after {
    content: "Browse";
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Custom file input label
    $('#file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Form submit with AJAX
    $('#import-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        if (!$('#file')[0].files[0]) {
            alert('Please select a file');
            return;
        }

        $('#btn-import').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Importing...');
        $('#import-progress').removeClass('d-none');
        $('#import-result').html('');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#import-progress').addClass('d-none');
                $('#btn-import').prop('disabled', false).html('<i class="fas fa-file-import mr-2"></i> Import');

                if (response.success) {
                    var html = '<div class="alert alert-success">';
                    html += '<h5><i class="fas fa-check-circle mr-2"></i> Import Completed!</h5>';
                    html += '<p>' + response.message + '</p>';
                    if (response.errors && response.errors.length > 0) {
                        html += '<hr><p class="mb-1"><strong>Warnings:</strong></p>';
                        html += '<ul class="mb-0">';
                        response.errors.slice(0, 10).forEach(function(err) {
                            html += '<li>' + err + '</li>';
                        });
                        if (response.errors.length > 10) {
                            html += '<li>...and ' + (response.errors.length - 10) + ' more</li>';
                        }
                        html += '</ul>';
                    }
                    html += '<hr><a href="' + response.redirect + '" class="btn btn-primary">View Products</a>';
                    html += '</div>';
                    $('#import-result').html(html);
                } else {
                    $('#import-result').html('<div class="alert alert-danger"><i class="fas fa-times-circle mr-2"></i> ' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#import-progress').addClass('d-none');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Import failed';
                $('#import-result').html('<div class="alert alert-danger"><i class="fas fa-times-circle mr-2"></i> ' + msg + '</div>');
                $('#btn-import').prop('disabled', false).html('<i class="fas fa-file-import mr-2"></i> Import');
            }
        });
    });
});
</script>
@endpush
