@if (session()->has('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            text: "{{ session()->get('success') }}",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "Ok, mengerti!",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    });
</script>
@endif

@if (session()->has('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            text: "{{ session()->get('error') }}",
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Ok, mengerti!",
            customClass: {
                confirmButton: "btn btn-danger"
            }
        });
    });
</script>
@endif

@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: "Gagal",
            html: "@foreach ($errors->all() as $error)<span>{{ $error }}</span><br>@endforeach",
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Ok, mengerti!",
            customClass: {
                confirmButton: "btn btn-danger"
            }
        });
    });
</script>
@endif
